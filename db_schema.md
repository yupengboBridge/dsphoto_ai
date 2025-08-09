### データベース主要テーブル辞書（概要）

本ドキュメントは、`photodb_image` スキーマで本システムが主に参照・更新するテーブルの役割と主要カラムを要約したものです（コード参照ベースの概要）。

---

## 画像コア

#### photoimg（画像メタ・公開情報）
- 役割: 画像の基本情報・公開状態・掲載期間など
- 主キー: `photo_id`
- 主なカラム:
  - `photo_mno`（画像管理番号）、`bud_photo_no`（MALL番号含む）、`mall_no`
  - `photo_name`, `photo_explanation`, `source_image_no`
  - `publishing_situation_id`（公開区分）, `is_publish`
  - `take_picture_time_id`, `take_picture_time2_id`
  - `range_of_use_id`, `borrowing_ahead_id`, `content_borrowing_ahead`
  - `additional_constraints1`, `additional_constraints2`
  - `dfrom`, `dto`, `kikan`, `is_extension`
  - `registration_account`, `registration_person`, `permission_account`, `permission_person`, `register_date`, `permission_date`
  - 画像パス類: `photo_filename`, `photo_filename_th1..th10`（CMSでは `th11..th13` も更新）, `ext`, `image_size_x`, `image_size_y`
  - 運用: `photo_server_flg`, `ds_change_image`, `note`

#### photo_imgdata（画像バイナリ）
- 役割: 画像バイナリ（原寸/サムネ）保管
- 主キー兼外部キー: `photo_id` → photoimg
- 主なカラム: `image1`, `image2`, `image3`

#### registration_classification（分類紐付け）
- 役割: 画像と分類4要素の紐付け
- 主キー: なし（`photo_id`+各IDの組合せ）
- 外部キー: `photo_id` → photoimg、`classification_id` → classification、`direction_id` → direction、`country_prefecture_id` → country_prefecture、`place_id` → place

#### keyword（キーワード）
- 役割: 画像に紐づくキーワード
- 主キー: なし（`photo_id`+`keyword_name`想定）
- 外部キー: `photo_id` → photoimg
- カラム: `keyword_name`

---

## 分類マスタ系

#### classification / direction / country_prefecture / place
- 4階層の地理/分類マスタ
- 主キー:
  - `classification(classification_id)`
  - `direction(direction_id)`（FK: `classification_id`）
  - `country_prefecture(country_prefecture_id)`（FK: `direction_id`）
  - `place(place_id)`（FK: `country_prefecture_id`）
- 名称カラム: `classification_name`, `direction_name`, `country_prefecture_name`, `place_name`

#### country_case
- 役割: 国名表記ゆれ補正（`country_name_case0..10`）

#### category
- 役割: キーワードカテゴリ（`parent_id`をもつ階層）

---

## 公開/利用条件マスタ

- `publishing_situation(publishing_situation_id, name)`
- `borrowing_ahead(borrowing_ahead_id, name)`
- `range_of_use(range_of_use_id, name)`
- `take_picture_time(take_picture_time_id, label)`
- `take_picture_time2(take_picture_time2_id, label)`

---

## アルバム

#### album
- 主キー: `album_id`
- 主なカラム: `album_name`, `album_explanation`, `registration_date`, `state`

#### album_detail
- 主キー: `album_detail_id`
- 外部キー: `album_id` → album、`photo_id` → photoimg

---

## カウンタ/採番/バッチ/ログ

#### disp_counter
- 役割: 表示回数（画像管理番号単位）
- 主キー相当: `photo_mno` + `disp_date`
- カラム: `counter`

#### lastnumber
- 役割: 管理番号採番（`lastnumber_name`ごと）
- 主キー: `lastnumber_name`
- カラム: `lastnumber`

#### mall_task
- 役割: MALL連携タスク（S3連携処理の状態管理）
- 主キー: `id`
- カラム: `task_start_datetime`, `task_end_datetime`

#### csvfile
- 役割: CSV 入出力ログ
- 主キー: 実装依存（`id`想定）
- カラム: `file_name`, `up_user`, `up_time`, `down_user`, `down_time`

---

## ユーザー

#### user
- 主キー: `user_id`
- 主なカラム: `login_id`, `user_name`, `compcode`, `login_date`
- photoimg とは `registration_account`（login_id）などで論理的に関連

---

## 関連図（参考）
- ER 図は `system_overview.md` および README 内の Mermaid 図を参照

---

## 備考
- 本定義はコード参照ベースの概要です。厳密な制約（NOT NULL、インデックス、ユニークキー）や未使用カラムが存在する場合があります。
- 実スキーマ差異がある場合は、実DBの `SHOW CREATE TABLE` などで検証の上、追記してください。

