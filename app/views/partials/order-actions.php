<a :href="BASE_URL + '/admin/transaksi/' + encodeURIComponent(item.kode_transaksi)" class="admin-action-secondary admin-action-sm">Detail</a>
<a x-show="utils.customerWhatsApp(item)" :href="utils.customerWhatsApp(item)" target="_blank" rel="noopener noreferrer" class="admin-action-secondary admin-action-sm">Hubungi Pelanggan</a>
<button type="button" x-show="utils.orderAction(item)" @click="act(item)" :disabled="processing" class="admin-action-primary admin-action-sm" x-text="utils.orderAction(item)?.label || ''"></button>
<button type="button" x-show="['pending','diproses','dikirim'].includes(item.status)" @click="batal(item)" :disabled="processing" class="admin-action-danger admin-action-sm">Batal</button>
