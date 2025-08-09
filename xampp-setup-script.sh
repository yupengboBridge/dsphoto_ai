#!/bin/bash

echo "XAMPP セットアップスクリプト (Mac/Linux)"
echo "======================================"

# OS判定
if [[ "$OSTYPE" == "darwin"* ]]; then
    XAMPP_PATH="/Applications/XAMPP/htdocs"
    XAMPP_CONTROL="/Applications/XAMPP/manager-osx.app"
else
    XAMPP_PATH="/opt/lampp/htdocs"
    XAMPP_CONTROL="/opt/lampp/lampp"
fi

# プロジェクトディレクトリの作成
echo "プロジェクトフォルダを作成しています..."
if [ ! -d "$XAMPP_PATH/dsphoto_ai" ]; then
    sudo mkdir -p "$XAMPP_PATH/dsphoto_ai"
    echo "フォルダを作成しました: $XAMPP_PATH/dsphoto_ai"
else
    echo "フォルダは既に存在します: $XAMPP_PATH/dsphoto_ai"
fi

# 権限設定
echo "権限を設定しています..."
sudo chmod -R 755 "$XAMPP_PATH/dsphoto_ai"
sudo chown -R $(whoami):$(whoami) "$XAMPP_PATH/dsphoto_ai"

# プロジェクトファイルのコピー
echo ""
read -p "プロジェクトファイルをコピーしますか？ (y/n): " confirm
if [[ $confirm == [yY] ]]; then
    cp -R ./cms_photo_image "$XAMPP_PATH/dsphoto_ai/"
    cp -R ./photo_db "$XAMPP_PATH/dsphoto_ai/"
    echo "ファイルのコピーが完了しました"
fi

# データベース設定の確認
echo ""
echo "データベース設定を確認してください:"
echo "- MySQL ユーザー: root"
echo "- MySQL パスワード: (空白)"
echo "- データベース名: photodb_image"
echo ""

# XAMPPの起動
if [[ "$OSTYPE" == "darwin"* ]]; then
    read -p "XAMPP Managerを起動しますか？ (y/n): " launch
    if [[ $launch == [yY] ]]; then
        open "$XAMPP_CONTROL"
    fi
else
    read -p "XAMPPを起動しますか？ (y/n): " launch
    if [[ $launch == [yY] ]]; then
        sudo "$XAMPP_CONTROL" start
    fi
fi

echo ""
echo "セットアップが完了しました！"
echo "ブラウザで http://localhost/dsphoto_ai/ にアクセスしてください"