### ER 図（photodb_image）

```mermaid
erDiagram
  PHOTOIMG ||--|| PHOTO_IMGDATA : has
  PHOTOIMG ||--o{ REGISTRATION_CLASSIFICATION : has
  PHOTOIMG ||--o{ KEYWORD : has
  ALBUM ||--o{ ALBUM_DETAIL : has
  ALBUM_DETAIL }o--|| PHOTOIMG : contains

  CLASSIFICATION ||--o{ DIRECTION : has
  DIRECTION ||--o{ COUNTRY_PREFECTURE : has
  COUNTRY_PREFECTURE ||--o{ PLACE : has

  REGISTRATION_CLASSIFICATION }o--|| CLASSIFICATION : uses
  REGISTRATION_CLASSIFICATION }o--|| DIRECTION : uses
  REGISTRATION_CLASSIFICATION }o--|| COUNTRY_PREFECTURE : uses
  REGISTRATION_CLASSIFICATION }o--|| PLACE : uses

  PUBLISHING_SITUATION ||--o{ PHOTOIMG : typed_by
  BORROWING_AHEAD ||--o{ PHOTOIMG : typed_by
  RANGE_OF_USE ||--o{ PHOTOIMG : typed_by
  TAKE_PICTURE_TIME ||--o{ PHOTOIMG : typed_by
  TAKE_PICTURE_TIME2 ||--o{ PHOTOIMG : typed_by
  USER ||--o{ PHOTOIMG : registration_account

  DISP_COUNTER }o--|| PHOTOIMG : "via photo_mno"
  LASTNUMBER ||..|| PHOTOIMG : numbering
  MALL_TASK ||..|| PHOTOIMG : batch
  CSVFILE ||..|| PHOTOIMG : logs

  PHOTOIMG {
    int photo_id PK
    string photo_mno
    int publishing_situation_id FK
    int take_picture_time_id FK
    int take_picture_time2_id FK
    int range_of_use_id FK
    int borrowing_ahead_id FK
    string registration_account
    datetime dfrom
    datetime dto
  }
  PHOTO_IMGDATA {
    int photo_id PK FK
    blob image1
    blob image2
    blob image3
  }
  REGISTRATION_CLASSIFICATION {
    int photo_id FK
    int classification_id FK
    int direction_id FK
    int country_prefecture_id FK
    int place_id FK
  }
  KEYWORD {
    int photo_id FK
    string keyword_name
  }
  ALBUM {
    int album_id PK
    string album_name
    datetime registration_date
  }
  ALBUM_DETAIL {
    int album_detail_id PK
    int album_id FK
    int photo_id FK
  }
  CLASSIFICATION {
    int classification_id PK
    string classification_name
  }
  DIRECTION {
    int direction_id PK
    int classification_id FK
    string direction_name
  }
  COUNTRY_PREFECTURE {
    int country_prefecture_id PK
    int direction_id FK
    string country_prefecture_name
  }
  PLACE {
    int place_id PK
    int country_prefecture_id FK
    string place_name
  }
  PUBLISHING_SITUATION {
    int publishing_situation_id PK
    string name
  }
  BORROWING_AHEAD {
    int borrowing_ahead_id PK
    string name
  }
  RANGE_OF_USE {
    int range_of_use_id PK
    string name
  }
  TAKE_PICTURE_TIME {
    int take_picture_time_id PK
    string label
  }
  TAKE_PICTURE_TIME2 {
    int take_picture_time2_id PK
    string label
  }
  USER {
    int user_id PK
    string login_id
    string user_name
    string compcode
  }
  DISP_COUNTER {
    string photo_mno
    date disp_date
    int counter
  }
  LASTNUMBER {
    string lastnumber_name PK
    int lastnumber
  }
  MALL_TASK {
    int id PK
    datetime task_start_datetime
    datetime task_end_datetime
  }
  CSVFILE {
    int id PK
    string file_name
    string up_user
    datetime up_time
  }
```

