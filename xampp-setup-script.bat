@echo off
echo XAMPP セットアップスクリプト
echo ========================

REM プロジェクトフォルダの作成
echo プロジェクトフォルダを作成しています...
if not exist "C:\xampp\htdocs\dsphoto_ai" (
    mkdir "C:\xampp\htdocs\dsphoto_ai"
    echo フォルダを作成しました: C:\xampp\htdocs\dsphoto_ai
) else (
    echo フォルダは既に存在します: C:\xampp\htdocs\dsphoto_ai
)

REM 現在のプロジェクトファイルをコピー
echo.
echo プロジェクトファイルをコピーしますか？ (Y/N)
set /p confirm=
if /i "%confirm%"=="Y" (
    xcopy /E /I /Y "%~dp0cms_photo_image" "C:\xampp\htdocs\dsphoto_ai\cms_photo_image\"
    xcopy /E /I /Y "%~dp0photo_db" "C:\xampp\htdocs\dsphoto_ai\photo_db\"
    echo ファイルのコピーが完了しました
)

REM データベース設定の確認
echo.
echo データベース設定を確認してください:
echo - MySQL ユーザー: root
echo - MySQL パスワード: (空白)
echo - データベース名: photodb_image
echo.

REM XAMPPコントロールパネルの起動
echo XAMPPコントロールパネルを起動しますか？ (Y/N)
set /p launch=
if /i "%launch%"=="Y" (
    start "" "C:\xampp\xampp-control.exe"
)

echo.
echo セットアップが完了しました！
echo ブラウザで http://localhost/dsphoto_ai/ にアクセスしてください
pause