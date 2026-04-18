<?php
// JALUR IDENTITAS ASLI DI SERVER
$target = '/home/tulgungp/public_html/storage/app/public';
$link   = '/home/tulgungp/public_html/public/storage';

echo "<h2>🛠️ Membangun Jembatan public/storage</h2>";

// 1. Cek Folder Target
if (is_dir($target)) {
    echo "✅ Folder asal ditemukan.<br>";
} else {
    echo "❌ ERROR: Folder asal <code>$target</code> tidak ada! Buat dulu foldernya.<br>";
    exit;
}

// 2. Bersihkan jika ada sisa link lama
if (file_exists($link)) {
    @unlink($link);
}

// 3. Eksekusi Symlink
if (symlink($target, $link)) {
    echo "🚀 <b>BERHASIL!</b> Jembatan <code>public/storage</code> sudah aktif.<br>";
    echo "Sekarang folder <b>storage</b> di dalam <b>public</b> sudah terhubung ke gudang aslinya.";
} else {
    echo "❌ <b>GAGAL!</b> Server menolak perintah symlink.<br>";
    echo "Solusi: Hubungi support hosting, minta aktifkan fitur 'Symlink' atau 'FollowSymLinks'.";
}
?>