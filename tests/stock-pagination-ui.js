const fs=require('fs');
const vm=require('vm');
const assert=require('assert/strict');
const script=fs.readFileSync('app/views/pages/admin/stok.php','utf8').match(/<script>([\s\S]*?)<\/script>/)[1];
let lastQuery;
const query={};
const context=vm.createContext({console,utils:{getQuery:()=>null,setQuery:(key,value)=>{query[key]=value;}},API:{get:async url=>{
  lastQuery=url;
  const params=new URLSearchParams(url.split('?')[1]);
  return {success:true,data:[{id_mutasi:Number(params.get('page'))}],pagination:{total:110}};
}}});
vm.runInContext(script,context);
async function run() {
  const page=context.stokPage(); await page.load();
  await page.goPage(5); assert.equal(page.pagination.page,5); assert.equal(page.mutasi[0].id_mutasi,5); assert.equal(query.page,5);
  assert.ok(lastQuery.includes('&page=5'));
  assert.equal(page.visiblePages().filter(p=>p==='…').length,2);
  console.log('PASS: Numbered jump requests the correct page and retains both pagination gaps');
  await page.goPage(11); assert.equal(page.mutasi[0].id_mutasi,11);
  await page.goPage('…'); assert.equal(page.pagination.page,11);
  await page.goPage(12); assert.equal(page.pagination.page,11);
  console.log('PASS: Last-page jump works and invalid page values are ignored');
  page.pagination.page=99; await page.load(); assert.equal(page.pagination.page,11); assert.equal(page.mutasi[0].id_mutasi,11); assert.equal(page.loading,false);
  console.log('PASS: Out-of-range URLs reload the actual last page');
  let finishOld;
  context.API.get=url=>new Promise(resolve=>{if(url.includes('&page=1')) finishOld=resolve; else resolve({success:true,data:[{id_mutasi:2}],pagination:{total:110}});});
  page.pagination.page=1; const old=page.load(); page.pagination.page=2; await page.load();
  finishOld({success:true,data:[{id_mutasi:1}],pagination:{total:110}}); await old;
  assert.equal(page.mutasi[0].id_mutasi,2);
  console.log('PASS: An older request cannot replace the current page result');
}
run().catch(e=>{console.error(e);process.exitCode=1;});
