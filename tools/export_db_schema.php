<?php
// tools/export_db_schema.php
// 実DBからテーブル定義・カラム・インデックスをMarkdownとして出力

error_reporting(E_ALL);
ini_set('display_errors', 'On');

define('BASE_DIR', dirname(__DIR__));

// photo_db の config.php を流用（DB接続情報）
$configPath = BASE_DIR . DIRECTORY_SEPARATOR . 'photo_db' . DIRECTORY_SEPARATOR . 'config.php';
if (!file_exists($configPath)) {
    fwrite(STDERR, "config.php が見つかりません: $configPath\n");
    exit(1);
}
require $configPath; // ここで $db_host, $db_name, $db_user, $db_password, $db_charset が読み込まれる

$dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s', $db_host, $db_name, $db_charset);

try {
    $pdo = new PDO($dsn, $db_user, $db_password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (Exception $e) {
    fwrite(STDERR, "DB接続に失敗しました: " . $e->getMessage() . "\n");
    exit(1);
}

$outPath = BASE_DIR . DIRECTORY_SEPARATOR . 'db_schema_detailed.md';
$fp = fopen($outPath, 'w');
if (!$fp) {
    fwrite(STDERR, "出力ファイルを開けませんでした: $outPath\n");
    exit(1);
}

fwrite($fp, "### photodb_image スキーマ定義（自動抽出）\n\n");
fwrite($fp, "生成時刻: " . date('Y-m-d H:i:s') . "\n\n");

// テーブル一覧（ベーステーブルのみ）
$tables = [];
foreach ($pdo->query("SHOW FULL TABLES WHERE Table_type='BASE TABLE'") as $row) {
    // カラム名は "Tables_in_{dbname}" 固定のため動的に解決
    foreach ($row as $k => $v) {
        if (strpos($k, 'Tables_in_') === 0) {
            $tables[] = $v;
            break;
        }
    }
}

// 並べ替え（見やすさのため）
sort($tables, SORT_STRING);

foreach ($tables as $table) {
    fwrite($fp, "---\n\n");
    fwrite($fp, "## `{$table}`\n\n");

    // 行数
    try {
        $cntStmt = $pdo->query("SELECT COUNT(*) AS cnt FROM `{$table}`");
        $cnt = $cntStmt->fetch()['cnt'] ?? '0';
        fwrite($fp, "- レコード数: {$cnt}\n\n");
    } catch (Exception $e) {
        // 権限等で失敗した場合は無視
    }

    // SHOW CREATE TABLE
    $createStmt = $pdo->query("SHOW CREATE TABLE `{$table}`");
    $createRow = $createStmt->fetch(PDO::FETCH_NUM);
    $createSql = isset($createRow[1]) ? $createRow[1] : '';
    if ($createSql !== '') {
        fwrite($fp, "**CREATE TABLE**:\n\n");
        fwrite($fp, "```sql\n{$createSql};\n```\n\n");
    }

    // カラム詳細
    try {
        $colStmt = $pdo->query("SHOW FULL COLUMNS FROM `{$table}`");
        $columns = $colStmt->fetchAll();
        if ($columns) {
            fwrite($fp, "**Columns**:\n\n");
            fwrite($fp, "| Field | Type | Null | Key | Default | Extra | Collation | Comment |\n");
            fwrite($fp, "|---|---|---|---|---|---|---|---|\n");
            foreach ($columns as $c) {
                $line = sprintf(
                    "| %s | %s | %s | %s | %s | %s | %s | %s |\n",
                    $c['Field'] ?? '',
                    $c['Type'] ?? '',
                    $c['Null'] ?? '',
                    $c['Key'] ?? '',
                    $c['Default'] === null ? 'NULL' : (string)$c['Default'],
                    $c['Extra'] ?? '',
                    $c['Collation'] ?? '',
                    str_replace(["\r", "\n", "|"], [' ', ' ', '\\|'], $c['Comment'] ?? '')
                );
                fwrite($fp, $line);
            }
            fwrite($fp, "\n");
        }
    } catch (Exception $e) {
        // 無視
    }

    // インデックス
    try {
        $idxStmt = $pdo->query("SHOW INDEX FROM `{$table}`");
        $indexes = $idxStmt->fetchAll();
        if ($indexes) {
            fwrite($fp, "**Indexes**:\n\n");
            fwrite($fp, "| Key_name | Non_unique | Seq_in_index | Column_name | Sub_part | Index_type |\n");
            fwrite($fp, "|---|---:|---:|---|---:|---|\n");
            foreach ($indexes as $i) {
                $line = sprintf(
                    "| %s | %d | %d | %s | %s | %s |\n",
                    $i['Key_name'] ?? '',
                    (int)($i['Non_unique'] ?? 0),
                    (int)($i['Seq_in_index'] ?? 0),
                    $i['Column_name'] ?? '',
                    isset($i['Sub_part']) ? (string)$i['Sub_part'] : '',
                    $i['Index_type'] ?? ''
                );
                fwrite($fp, $line);
            }
            fwrite($fp, "\n");
        }
    } catch (Exception $e) {
        // 無視
    }
}

fwrite($fp, "---\n\n※ 本出力は実DBから自動抽出した情報です。運用環境と差異がある場合は、同手順で再取得してください。\n");

fclose($fp);

echo "出力完了: {$outPath}\n";

