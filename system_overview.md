### System Overview (dsphoto_ai)

このドキュメントは、`photo_db` と `cms_photo_image` の相互関係、外部連携（S3）、データフロー、共有設定/資産を俯瞰できるようにまとめたものです。

関連ドキュメント:
- `photo_db/photo_db_readme.md`
- `cms_photo_image/cms_photo_image.md`

---

## 全体アーキテクチャ（図）

```mermaid
graph TD
  subgraph External
    S3[(AWS S3 Bucket)]
    Mail[(SMTP / PHPMailer)]
  end

  subgraph CMS (運用/管理)
    CMS[cms_photo_image]
    CMS_LIB[lib.php
(アップロード/サムネ生成/WEBP)]
  end

  subgraph Public/Batch
    PDB[photo_db]
    MALL[malltools/Task
(S3連携タスク)]
    MIB[malltools/mall_image_batch.php
(insert/update/delete)]
  end

  subgraph Infrastructure
    DB[(MySQL: photodb_image)]
    FS[/uploads, /thumb*/]
  end

  CMS -->|登録/編集/検索| DB
  CMS -->|画像アップロード/サムネ| FS
  CMS_LIB --> FS

  PDB -->|検索/配信/kikan*| DB
  PDB -->|画像参照(uploads/WEBP)| FS

  S3 -->|CSV+画像ダウンロード| MALL
  MALL -->|処理/エラー通知| Mail
  MALL -->|挿入/更新/削除| MIB
  MIB -->|メタ/キーワード/分類| DB
  MIB -->|サムネ生成/保存| FS
  MALL -->|処理済へ移動| S3
```

---

## 役割の整理
- **cms_photo_image**: 運用者向けの登録/編集/承認/検索UI。`lib.php`にCMS固有のサムネ生成（thumb11/12/13）とWebP出力があり、`photoimg`および`uploads`配下に反映。
- **photo_db**: 配信用/検索フロントと各種バッチ（画像バイナリ化、期限切れ削除、CSV出力）。`malltools`によりS3からの連携CSV+画像を取り込み、DB/ファイルへ反映。
- **共通DB**: `photodb_image`。両者は同一スキーマを共有（`photoimg`, `photo_imgdata`, `keyword` 等）。
- **共通FS**: `/uploads`と`/thumb*`を双方が参照/生成。`photo_db`の`image_search_kikan*`は`cms_photo_image`側の実体を検知して生成/配信する分岐あり。

---

## 共有/重複資産
- 設定: `config.php`, `kikanConfig.php`（両プロジェクトに存在、接続/ルートが同期）
- 期間配信: `image_search_kikan*.php` が双方にあり、世代/機能差分（WEBP対応やフォント/クレジット描画）
- モデル/処理: `PhotoImage*`, `ImageSearch`, `RegistrationClassifications`, `DispCounter` など類似クラスが双方に存在

---

## 運用ポイント
- **S3連携の運用**: `malltools/Task.php`はS3の未処理ディレクトリを検出し、CSV/画像をダウンロード→検証→`mall_image_batch.php`でDB/FS反映→S3の`processed/`へ移動。
- **画像形式/サムネ**: CMSではthumb11/12/13（750x470/2600x1200/1252x578）とWebPも生成。配信側はkikan*系で所望サイズ生成/クレジット描画/WEBP優先返却。
- **バイナリ保存**: 公開中画像の`photo_imgdata`へのバイナリ格納（`make_image_bainari.php`）。
- **期限切れ削除**: DTO経過画像の削除（`delete_image_*`）。ログをCSV/メールで通知。

---

## 参照リスト
- CMS: `cms_photo_image/cms_photo_image.md`
- 配信/バッチ: `photo_db/photo_db_readme.md`
- S3連携: `photo_db/malltools/Task.php`, `.../service/S3/S3.php`, `.../mall_image_batch.php`

