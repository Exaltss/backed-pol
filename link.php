<?php
echo "<h2>🔍 Mencari Jejak Folder Foto...</h2>";

$rootPath = '/home/tulgungp/public_html';

function findFolder($dir, $folderName) {
    $result = [];
    $items = @scandir($dir);
    if (!$items) return $result;

    foreach ($items as $item) {
        if ($item == '.' || $item == '..') continue;
        $path = $dir . '/' . $item;
        
        if (is_dir($path)) {
            if ($item == $folderName) {
                $result[] = $path;
            }
            $result = array_merge($result, findFolder($path, $folderName));
        }
    }
    return $result;
}

// Cari folder profile_photos
$found = findFolder($rootPath, 'reports');

if (empty($found)) {
    echo "❌ <b>Folder 'profile_photos' TIDAK DITEMUKAN di mana pun!</b><br>";
    echo "Artinya folder tersebut mungkin terhapus saat proses reset tadi.";
} else {
    echo "✅ <b>DITEMUKAN!</b> Folder tersebut ada di:<br><ul>";
    foreach ($found as $f) {
        echo "<li><code>$f</code></li>";
    }
    echo "</ul>";
}
?>