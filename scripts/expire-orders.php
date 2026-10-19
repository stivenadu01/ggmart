<?php
require __DIR__ . '/bootstrap.php';
expireOrders();
reconcilePendingQris(30);
echo "Status pembayaran dan batas waktu pesanan diperiksa.\n";