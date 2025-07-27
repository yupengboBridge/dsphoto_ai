<?php
$csvFile = '/home/xhankyu/public_html/cms_photo_image/csv/photoimg202505291256.csv';
$handle = fopen($csvFile, 'r');

if ($handle === false) {
    die("Failed to open CSV file.\n");
}

$headers = fgetcsv($handle);
$photoMnoIndex = array_search('photo_mno', $headers);
$thIndexes = [];
for ($i = 1; $i <= 13; $i++) {
    $colName = "photo_filename_th{$i}";
    $thIndexes[$colName] = array_search($colName, $headers);
}

while (($row = fgetcsv($handle)) !== false) {
    $photoMno = $row[$photoMnoIndex];

    foreach ($thIndexes as $col => $idx) {
        $original = $row[$idx] ?? '';
        if (!$original) continue;

        if (strpos($original, 'cms_photo_image') !== false) {
            $root = '/home/xhankyu/public_html/cms_photo_image';
        } elseif (strpos($original, 'photo_db') !== false) {
            $root = '/home/xhankyu/public_html/photo_db';
        } else {
            continue;
        }

        if (preg_match('#(\./thumb\d+/.+)$#', $original, $matches)) {
            $relativePath = $matches[1]; // 提取出 ./thumbX/...
            $relativePath = str_replace('./', '/', $relativePath); // 变成 /thumbX/...
            $relativePath = preg_replace('/\.(jpg|jpeg|png)$/i', '.webp', $relativePath); // .webp 替换扩展名
            $fullPath = $root . $relativePath;

            if (!file_exists($fullPath)) {
                echo "Missing file: $original\t photo_mno: $photoMno\n";
            }
        } else {
            echo "Unrecognized format: $original\t photo_mno: $photoMno\n";
        }
    }
}

fclose($handle);
