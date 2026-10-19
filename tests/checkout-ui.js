const fs = require('fs');
const vm = require('vm');
const assert = require('assert/strict');
const source = fs.readFileSync('public/assets/js/order.js', 'utf8');
let role = 'admin', scheduled, location, stage, calls, method = 'qris', sourceKind = 'kasir';
const stores = { auth: { get user() { return { role }; } }, cart: { items: [{ jumlah: 1 }], totalHarga: 1000, async checkout() {} } };
const fixture = () => ({ kode_transaksi: 'GT-test', sumber_transaksi: sourceKind, metode_bayar: method, status: stage === 'paid' ? (sourceKind === 'kasir' ? 'selesai' : 'diproses') : stage === 'expired' ? 'dibatalkan' : 'pending', status_bayar: stage === 'paid' ? 'lunas' : stage === 'expired' ? 'kedaluwarsa' : 'belum_bayar', qris_url: stage !== 'initial' ? 'https://api.sandbox.midtrans.com/qr' : null, midtrans_order_id: stage !== 'initial' ? 'GT-test' : null });
const context = vm.createContext({ URLSearchParams, utils: {}, BASE_URL: '/ggmart', Alpine: { store: name => stores[name] }, document: { hidden: false }, window: { location: { pathname: '/ggmart/pembayaran/GT-test', replace: value => { location = value; } }, scrollTo() {} }, globalThis: { crypto: { randomUUID() { return 'test'; } } }, setTimeout: fn => { scheduled = fn; return 1; }, clearTimeout: () => { scheduled = null; }, API: {
  async get() { return { data: fixture() }; },
  async post(endpoint) { calls.push(endpoint); if (endpoint === '/pembayaran/qris') stage = 'qr'; else if (endpoint === '/pembayaran/status') stage = 'paid'; }
} });
vm.runInContext(source, context);
async function run() {
  const checkout = context.checkoutPage(); checkout.config = { zona: [{ id: 1, kecamatan: 'Oebobo', kelurahan: 'Fatululi', tarif: '5000.00' }, { id: 2, kecamatan: 'Alak', kelurahan: 'Alak', tarif: '30000.00' }] };
  assert.equal(checkout.step, 1); checkout.next(); assert.equal(checkout.step, 2);
  checkout.form.metode_pengiriman = 'kirim'; checkout.form.kecamatan = 'Oebobo'; checkout.form.zona_id = 1;
  assert.equal(checkout.kelurahan.length, 1); assert.equal(checkout.ongkir, 5000); assert.equal(checkout.total, 6000);
  checkout.form.kecamatan = 'Alak'; checkout.changeDistrict(); assert.equal(checkout.form.zona_id, ''); assert.equal(checkout.ongkir, 0);
  checkout.config.metode = { tunai: true, transfer: true, qris: true };
  checkout.form.nama_penerima = 'Pelanggan'; checkout.form.no_hp_penerima = '081234567890';
  checkout.form.metode_pengiriman = 'ambil'; checkout.form.metode_bayar = 'tunai'; assert.equal(checkout.ready, true);
  checkout.form.metode_pengiriman = 'kirim'; assert.equal(checkout.ready, false); checkout.changeDelivery(); assert.equal(checkout.form.metode_bayar, 'transfer');
  console.log('PASS: Cash pickup allowed; cash delivery rejected and payment choice reset');
  console.log('PASS: Two-step checkout and kecamatan-dependent shipping total');
  stage = 'initial'; calls = []; location = undefined;
  const panel = context.paymentPanel(); panel.$dispatch = () => {};
  await panel.init(); assert.equal(calls[0], '/pembayaran/qris'); assert.equal(location, undefined); assert.ok(panel.trx.qris_url); assert.ok(scheduled);
  console.log('PASS: QRIS displays automatically and unpaid counter does not redirect');
  await scheduled(); assert.ok(calls.includes('/pembayaran/status')); assert.equal(location, '/ggmart/admin/kasir');
  assert.ok(!calls.includes('/transaksi/konfirmasi'));
  console.log('PASS: Automatic status check returns to cashier only after server completes sale');
  panel.destroy(); assert.equal(scheduled, null);
  stage = 'expired'; location = undefined; calls = [];
  const expired = context.paymentPanel(); expired.$dispatch = () => {}; await expired.init(); assert.equal(location, undefined); assert.equal(scheduled, null); assert.equal(calls.length, 0);
  console.log('PASS: Expired QRIS stops polling without completing or redirecting');
  method = 'transfer'; stage = 'qr'; location = undefined; calls = [];
  const transfer = context.paymentPanel(); transfer.$dispatch = () => {}; await transfer.init();
  assert.ok(scheduled); assert.equal(calls.length, 0); assert.equal(location, undefined);
  stage = 'paid'; await scheduled(); assert.equal(location, '/ggmart/admin/kasir');
  console.log('PASS: Manual transfer uses database polling and returns after admin completes sale');
  transfer.destroy();
  method = 'qris'; stage = 'paid'; location = undefined;
  context.window.location.search = '?cetak=1';
  const print = context.paymentPanel(); print.$dispatch = () => {}; await print.init();
  assert.equal(location, '/ggmart/struk/GT-test?auto=1'); print.destroy();
  console.log('PASS: Print request survives payment and opens receipt before cashier return');
  role = 'pelanggan'; sourceKind = 'online'; stage = 'paid'; location = undefined;
  context.window.location.search = '?checkout=1';
  const customer = context.paymentPanel(); customer.$dispatch = () => {}; await customer.init();
  assert.equal(location, '/ggmart/transaksi?tahap=diproses'); customer.destroy();
  console.log('PASS: Paid customer checkout redirects to processing-orders tab');
  // Cetak otomatis tidak memulangkan operator sebelum dialog cetak ditutup.
  const receiptSource = fs.readFileSync('app/views/pages/user/struk.php', 'utf8');
  const printScript = receiptSource.match(/<script>([\s\S]*?)<\/script>/)[1].replace(/<\?=[\s\S]*?\?>/g, JSON.stringify('/ggmart/admin/kasir'));
  const events = {}; let printed = 0, printReturn;
  vm.runInNewContext(printScript, { window: { addEventListener: (name, listener) => { events[name] = listener; }, print: () => printed++, location: { replace: url => { printReturn = url; } } }, setTimeout: callback => callback() });
  events.load(); assert.equal(printed, 1); assert.equal(printReturn, undefined);
  events.afterprint(); assert.equal(printReturn, '/ggmart/admin/kasir');
  console.log('PASS: Automatic receipt prints then returns after print dialog closes');
}
run().catch(error => { console.error(error); process.exitCode = 1; });
