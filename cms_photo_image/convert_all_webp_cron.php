<?php
// 共通設定ファイルとライブラリファイルを読み込み
require_once(dirname(__FILE__) . '/config.php');
require_once(dirname(__FILE__) . '/lib.php');

// 定数定義
define("BASE_DIR", dirname(__FILE__)); // このPHPファイルの場所（例：/var/www/html/cms_photo_image）
define("LOG_DIR", BASE_DIR . '/log');  // ログ出力ディレクトリ
define("PHOTOIMG_TABLE", 'photoimg');  // 操作対象テーブル名
define("PREFIX_DB", '/photo_db/./');   // photo_server_flg = 0 の場合のURL前缀
define("PREFIX_CMS", '/cms_photo_image/./'); // photo_server_flg != 0 の場合のURL前缀

// ＤＢへ接続します
$db_link = db_connect();

// ログディレクトリが存在しない場合は作成
if (!is_dir(LOG_DIR)) {
    mkdir(LOG_DIR, 0777, true);
}
$logFile = LOG_DIR . '/convert_log_' . date('Ymd') . '.log';

// 最大件数（1回あたり）
$limit = 5000;

// 対象データを取得
$sql = "SELECT photo_mno, photo_server_flg,
               photo_filename_th1, photo_filename_th2, photo_filename_th3, photo_filename_th4,
               photo_filename_th5, photo_filename_th6, photo_filename_th7, photo_filename_th8,
               photo_filename_th9, photo_filename_th10, photo_filename_th11, photo_filename_th12,
               photo_filename_th13 
        FROM " . PHOTOIMG_TABLE . " 
        WHERE update_date < '2025-05-19 00:00:00' AND (is_all_webp=0 OR is_all_webp is null)
        ORDER BY photo_id ASC
        LIMIT :limit";
$stmt = $db_link->prepare($sql);
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->execute();
$rows = $stmt->fetchAll();

// 各レコードを処理
foreach ($rows as $row) {
    $photoMno = $row['photo_mno'];
    $serverFlg = (int)$row['photo_server_flg'];
    $allConverted = true;

    // URLパスのプレフィックスを設定
    $prefixToRemove = ($serverFlg === 0) ? PREFIX_DB : PREFIX_CMS;

    // 使用するROOT_DIRを動的に設定
    $rootDir = ($serverFlg === 0)
        ? str_replace('cms_photo_image', 'photo_db', BASE_DIR)
        : BASE_DIR;

    // サムネイルファイル13個を処理
    for ($i = 1; $i <= 13; $i++) {
        $field = "photo_filename_th{$i}";
        if (!empty($row[$field])) {
            $url = $row[$field];

            // 相対パスを抽出
            $relativePath = parse_url($url, PHP_URL_PATH);
            $relativePath = preg_replace('#^' . preg_quote($prefixToRemove, '#') . '#', '', $relativePath);

            // フルパス構築（ルートディレクトリは動的に選択）
            $fullPath = $rootDir . '/' . $relativePath;

            // ファイル名とWebPパス
            $filenameWithExt = basename($fullPath);
            $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
            $dir = dirname($fullPath);
            $webpPath = $dir . '/' . $filename . '.webp';

			// 元画像ファイルの存在チェック
			if (!file_exists($fullPath)) {
				logMessage($logFile, "【管理番号: {$photoMno}】元画像が存在しません: $fullPath");
				continue;
			}

            // webpが存在するか確認
            if (!file_exists($webpPath)) {
                $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
                $allowed = ['jpg', 'jpeg', 'png'];

                if (in_array($ext, $allowed)) {
                    $success = convertToWebp($fullPath, $webpPath, $ext);
                    if ($success) {
                        logMessage($logFile, "【管理番号: {$photoMno}】変換成功: $fullPath → $webpPath");
                    } else {
                        logMessage($logFile, "【管理番号: {$photoMno}】変換失敗: $fullPath");
                        $allConverted = false;
                    }
                } else {
                    logMessage($logFile, "【管理番号: {$photoMno}】未対応フォーマット: $fullPath");
                    $allConverted = false;
                }
            }
        }
    }

    // すべてのファイルがwebpに変換された場合のみフラグ更新
    if ($allConverted) {
        $updateStmt = $db_link->prepare("UPDATE " . PHOTOIMG_TABLE . " SET is_all_webp = 1 WHERE photo_mno = :photo_mno");
        $updateStmt->execute([':photo_mno' => $photoMno]);
        logMessage($logFile, "【管理番号: {$photoMno}】✔ is_all_webp を1に更新しました");
    } else {
        logMessage($logFile, "【管理番号: {$photoMno}】✘ 未変換の画像があるため更新しません");
    }
}

/**
 * WebP形式に変換する関数
 */
function convertToWebp($source, $destination, $ext) {
    switch ($ext) {
        case 'jpg':
        case 'jpeg':
            $image = imagecreatefromjpeg($source);
            break;
        case 'png':
            $image = imagecreatefrompng($source);
            imagepalettetotruecolor($image);
            imagealphablending($image, true);
            imagesavealpha($image, true);
            break;
        case 'gif':
            $image = imagecreatefromgif($source);
            break;
        default:
            return false;
    }

    if ($image === false) return false;

    $result = imagewebp($image, $destination, 80);
    imagedestroy($image);
    return $result;
}

/**
 * ログ出力関数
 */
function logMessage($file, $message) {
    $time = date('[Y-m-d H:i:s]');
    file_put_contents($file, "$time $message\n", FILE_APPEND);
}
