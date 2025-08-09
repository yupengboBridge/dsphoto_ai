<?php
// デバッグテスト用ファイル
// ブレークポイントを設定してテストしてください

echo "デバッグテスト開始<br>\n";

$test_variable = "Hello from XAMPP";
echo "変数の値: " . $test_variable . "<br>\n";

// ブレークポイントをここに設定
for ($i = 0; $i < 5; $i++) {
    echo "ループカウント: " . $i . "<br>\n";
}

// 関数のテスト
function testFunction($param) {
    return "受け取った値: " . $param;
}

$result = testFunction("デバッグテスト");
echo $result . "<br>\n";

// PHP情報の表示
echo "PHP Version: " . phpversion() . "<br>\n";

// Xdebugが有効か確認
if (extension_loaded('xdebug')) {
    echo "Xdebugが有効です<br>\n";
    echo "Xdebug Version: " . phpversion('xdebug') . "<br>\n";
} else {
    echo "Xdebugが無効です<br>\n";
}

echo "デバッグテスト終了<br>\n";
?>