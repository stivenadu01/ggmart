const fs = require('fs');
const vm = require('vm');
const assert = require('assert/strict');
const scripts = ['admin/pesanan', 'user/transaksi'].map(page => fs.readFileSync(`app/views/pages/${page}.php`, 'utf8').match(/<script>([\s\S]*?)<\/script>/)[1]);
let requests = [], stage, action, redirect, notifications = [];
const queryState = {};
const counts = { menunggu_pembayaran: 12, menunggu_verifikasi: 2, diproses: 5, dikirim: 1 };
const context = vm.createContext({
  URLSearchParams, BASE_URL: '/ggmart',
  utils: { getQuery: key => key === 'tahap' ? stage : null, setQuery: (key,value) => { queryState[key]=value; }, orderAction: () => action, orderEndpoint: a => '/'+a.endpoint },
  window: { location: { replace: url => { redirect=url; } } },
  document: { querySelector: () => null },
  Alpine: { store: name => name === 'auth' ? { user: { id_pengguna: 7 } } : { confirm: async () => true, toast: message => notifications.push(message) } },
  API: { get: async url => { requests.push(url); return { success: true, data: [], stageCounts: counts, pagination: { total: 12, total_pages: 2 } }; }, post: async () => ({ success: true }) }
});
scripts.forEach(script => vm.runInContext(script, context));
async function run() {
  const admin = context.pesananPage();
  await admin.init();
  assert.equal(admin.tabs.length, 4); assert.ok(!admin.tabs.some(t => t.value === 'selesai'));
  assert.equal(admin.stageCounts.menunggu_pembayaran, 12);
  await admin.goPage(2); assert.equal(admin.stageCounts.menunggu_pembayaran, 12);
  console.log('PASS: Admin badges retain totals across pagination');
  await admin.changeStage('diproses');
  for (const endpoint of ['konfirmasi','tunai']) {
    action = { endpoint, question: 'Test' }; await admin.act({ kode_transaksi: 'test' });
    assert.equal(admin.stage, 'diproses'); assert.equal(admin.pagination.page, 1);
    assert.ok(notifications.at(-1).includes('Riwayat Transaksi'));
  }
  console.log('PASS: Completion refreshes active orders without selecting a removed tab');
  action = { endpoint: 'kirim', question: 'Test' }; await admin.act({ kode_transaksi: 'test' }); assert.equal(admin.stage, 'dikirim');
  stage = 'selesai'; await context.pesananPage().init(); assert.equal(redirect, '/ggmart/admin/transaksi?status=selesai');
  console.log('PASS: Shipping selects the shipping tab; legacy completed tab opens history');
  stage = undefined; const customer = context.transaksiPage(); await customer.init();
  assert.equal(customer.filter.status, ''); assert.equal(customer.tabs[0].label, 'Semua');
  assert.ok(!new URLSearchParams(requests.at(-1).split('?')[1]).has('tahap'));
  await customer.changeStatus('dibatalkan'); assert.equal(queryState.tahap,'dibatalkan');
  await customer.changeStatus(''); assert.equal(queryState.tahap,'');
  assert.ok(!new URLSearchParams(requests.at(-1).split('?')[1]).has('tahap'));
  console.log('PASS: All is the default and removes the stage filter after selecting a specific tab');
  stage = 'diproses'; const linked = context.transaksiPage(); await linked.init(); assert.equal(linked.filter.status, 'diproses');
  console.log('PASS: Payment redirects still open the processing tab');
  let finishOld;
  context.API.get = url => new Promise(resolve => { if (url.includes('tahap=dikirim')) finishOld = resolve; else resolve({ success:true,data:[],pagination:{total:99,total_pages:10} }); });
  const racing = context.transaksiPage(); racing.filter.status = 'dikirim'; const older = racing.load();
  racing.filter.status = ''; await racing.load(); finishOld({success:true,data:[],pagination:{total:1,total_pages:1}}); await older;
  assert.equal(racing.pagination.total,99); assert.equal(racing.loading,false);
  console.log('PASS: Rapid tab changes cannot overwrite the latest result with an older response');
}
run().catch(error => { console.error(error); process.exitCode=1; });
