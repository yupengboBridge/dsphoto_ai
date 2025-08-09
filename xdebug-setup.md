# XAMPP PHP Debug Setup for Cursor

## 1. XAMPPのphp.ini設定

XAMPPのphp.iniファイル（通常は`C:\xampp\php\php.ini`）に以下を追加：

```ini
[XDebug]
zend_extension="xdebug.dll"
xdebug.mode=debug
xdebug.start_with_request=yes
xdebug.client_host=127.0.0.1
xdebug.client_port=9003
xdebug.log="C:\xampp\tmp\xdebug.log"
```

## 2. Cursor側の設定

### launch.json設定
`.vscode/launch.json`ファイルを作成：

```json
{
    "version": "0.2.0",
    "configurations": [
        {
            "name": "Listen for Xdebug",
            "type": "php",
            "request": "launch",
            "port": 9003,
            "pathMappings": {
                "C:\\xampp\\htdocs\\dsphoto_ai": "${workspaceFolder}"
            }
        }
    ]
}
```

## 3. デバッグ手順

1. XAMPPでApacheを起動
2. Cursorでデバッグを開始（F5キー）
3. ブラウザでPHPページにアクセス
4. ブレークポイントで停止

## 注意事項

- XAMPPのPHPバージョンとXdebugのバージョンが互換性があることを確認
- ファイアウォールでポート9003を許可
- パスマッピングが正しいことを確認