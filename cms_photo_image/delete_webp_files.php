<?php
require_once(dirname(__FILE__) . '/config.php');
require_once(dirname(__FILE__) . '/lib.php');

define("BASE_DIR", dirname(__FILE__));
define("LOG_DIR", BASE_DIR . '/log');
define("PHOTOIMG_TABLE", 'photoimg');
define("PREFIX_DB", '/photo_db/./');
define("PREFIX_CMS", '/cms_photo_image/./');

// 対象のファイル名リスト（.jpgは除去）
$photoMnoList = [
    '00000-LH25-00000',
    '00000-SP24-03718',
    '00000-SP24-03717',
    '00000-BP24-01304',
    '00000-SP24-06292',
    '00000-ALLUP-00028',
    '00000-ALLUP-00017',
    '00000-ALLUP-00016',
    '00000-ALLUP-00015',
    '00000-ALLUP-00014',
    '00000-ALLUP-00013',
    '00000-ALLUP-00030',
    '00000-ALLUP-00023',
    '00000-ALLUP-00027'
];

if (!is_dir(LOG_DIR)) {
    mkdir(LOG_DIR, 0777, true);
}
$logFile = LOG_DIR . '/delete_webp_log_' . date('Ymd') . '.log';

$db = db_connect();

// IN句を組み立て
$placeholders = implode(',', array_fill(0, count($photoMnoList), '?'));
$sql = "SELECT photo_mno, photo_server_flg,
               photo_filename_th1, photo_filename_th2, photo_filename_th3, photo_filename_th4,
               photo_filename_th5, photo_filename_th6, photo_filename_th7, photo_filename_th8,
               photo_filename_th9, photo_filename_th10, photo_filename_th11, photo_filename_th12,
               photo_filename_th13 
        FROM " . PHOTOIMG_TABLE . " 
        WHERE photo_mno IN ($placeholders)";
$stmt = $db->prepare($sql);
$stmt->execute($photoMnoList);
$rows = $stmt->fetchAll();

foreach ($rows as $row) {
    $photoMno = $row['photo_mno'];
    $serverFlg = (int)$row['photo_server_flg'];
    $prefixToRemove = ($serverFlg === 0) ? PREFIX_DB : PREFIX_CMS;
    $rootDir = ($serverFlg === 0)
        ? str_replace('cms_photo_image', 'photo_db', BASE_DIR)
        : BASE_DIR;

    for ($i = 1; $i <= 13; $i++) {
        $field = "photo_filename_th{$i}";
        if (!empty($row[$field])) {
            $url = $row[$field];
            $relativePath = parse_url($url, PHP_URL_PATH);
            $relativePath = preg_replace('#^' . preg_quote($prefixToRemove, '#') . '#', '', $relativePath);

            $fullPath = $rootDir . '/' . $relativePath;
            $filename = pathinfo($fullPath, PATHINFO_FILENAME);
            $dir = dirname($fullPath);
            $webpPath = $dir . '/' . $filename . '.webp';

            if (file_exists($webpPath)) {
                if (unlink($webpPath)) {
                    logMessage($logFile, "【管理番号: {$photoMno}】✔ WebPファイル削除: $webpPath");
                } else {
                    logMessage($logFile, "【管理番号: {$photoMno}】✘ 削除失敗: $webpPath");
                }
            } else {
                logMessage($logFile, "【管理番号: {$photoMno}】存在しない（削除スキップ）: $webpPath");
            }
        }
    }
}

function logMessage($file, $message) {
    $time = date('[Y-m-d H:i:s]');
    file_put_contents($file, "$time $message\n", FILE_APPEND);
}
