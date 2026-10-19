<?php
require __DIR__ . '/bootstrap.php';
require __DIR__ . '/backup-database.php';
require __DIR__ . '/notification-schema.php';
$backup=backupDatabase('before-notifications');
notificationSchema(db());
echo "Skema notifikasi siap. Riwayat lama tidak dikirim ulang. Backup: $backup\n";
