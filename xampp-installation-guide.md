# XAMPP インストールガイド

## 1. XAMPPのダウンロード

1. 公式サイトにアクセス: https://www.apachefriends.org/jp/index.html
2. お使いのOSに合わせてダウンロード：
   - Windows版: XAMPP for Windows
   - Mac版: XAMPP for OS X
   - Linux版: XAMPP for Linux

## 2. Windowsでのインストール手順

### インストール
1. ダウンロードした`xampp-windows-x64-x.x.x-x-installer.exe`を実行
2. UACの警告が出たら「はい」をクリック
3. アンチウイルスソフトの警告が出た場合は「OK」
4. インストール先を選択（デフォルト: `C:\xampp`）
5. コンポーネントを選択：
   - Apache ✓（必須）
   - MySQL ✓（必須）
   - PHP ✓（必須）
   - phpMyAdmin ✓（推奨）
   - その他は必要に応じて
6. 「Next」→「Install」でインストール開始

### 初期設定
1. インストール完了後、XAMPP Control Panelが起動
2. ApacheとMySQLの「Start」ボタンをクリック
3. ファイアウォールの許可を求められたら「許可」

## 3. Macでのインストール手順

### インストール
1. ダウンロードした`.dmg`ファイルをダブルクリック
2. XAMPPアイコンをApplicationsフォルダにドラッグ
3. Applicationsフォルダから「XAMPP」を起動
4. セキュリティ警告が出たら「開く」をクリック

### 初期設定
1. XAMPP Managerで「Start」タブを選択
2. 「Start All」をクリック
3. すべてのサービスが緑色になることを確認

## 4. 動作確認

1. ブラウザで `http://localhost/` にアクセス
2. XAMPPのダッシュボードが表示されれば成功
3. phpMyAdminの確認: `http://localhost/phpmyadmin/`

## 5. プロジェクトの配置

### Windows
```
C:\xampp\htdocs\dsphoto_ai\
```

### Mac
```
/Applications/XAMPP/htdocs/dsphoto_ai/
```

### Linux
```
/opt/lampp/htdocs/dsphoto_ai/
```

## 6. PHP設定の確認

`php.ini`の場所：
- Windows: `C:\xampp\php\php.ini`
- Mac: `/Applications/XAMPP/etc/php.ini`
- Linux: `/opt/lampp/etc/php.ini`

### 重要な設定項目
```ini
; アップロードサイズ
upload_max_filesize = 6M
post_max_size = 8M

; メモリ制限
memory_limit = 256M

; エラー表示（開発環境）
display_errors = On
error_reporting = E_ALL

; タイムゾーン
date.timezone = Asia/Tokyo

; 文字エンコーディング
default_charset = "UTF-8"
mbstring.language = Japanese
mbstring.internal_encoding = UTF-8
```

## 7. トラブルシューティング

### ポート競合エラー
- Apacheが起動しない場合、80番ポートが使用中の可能性
- XAMPP Control Panel → Config → Apache (httpd.conf)
- `Listen 80` を `Listen 8080` に変更
- アクセス時は `http://localhost:8080/`

### MySQLが起動しない
- 3306ポートが使用中の可能性
- 既存のMySQLサービスを停止するか、ポート変更

### アクセス権限エラー
- htdocsフォルダの権限を確認
- 必要に応じて書き込み権限を付与