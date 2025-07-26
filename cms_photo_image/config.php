<?php

// config.php

//mb_language( "ja" );
$charset = 'UTF-8';
//ini_set("mbstring.internal_encoding", $charset);
//mb_internal_encoding($charset);
//mb_http_output($charset);


// データーベース情報です
$db_host = 'localhost';

$db_user = 'root';

$db_password = 'root@Hcst2022';
$db_name = 'photodb_image';
$db_charset = 'utf8';
$db_link;

// サイト情報
$site_name = '写真管理システム';

$site_url = 'http://cmsphotoimg.hcstec.com/';
$image_url = 'http://cmsphotoimg.hcstec.com/';
//$site_url = 'http://www.e-mon.vc/test/photo_db/';
$login_page = $site_url . 'login.php';
$logout_page = $site_url . 'logout.php';
$after_login_page = $site_url . 'find.php';
$image_search_url = $image_url."image_search_kikan2.php?p_photo_mno=";

// ファイルアップロード用
$upload_conf['dir'] = "./uploads/";										// アップロードするディレクトリ
$upload_conf['temp_dir'] = "./temporary/";								// テンポラリーディレクトリ
$upload_conf['maxsize'] = 6000000;										// アップロードファイルの制限サイズ
$upload_conf['site_url'] = $site_url;									// サイトURL

$csv['local_export_dir'] = '../photo_db/csv1/';

// サムネイル保存用フォルダ
$thumb_dir = array("./thumb1/", "./thumb2/", "./thumb3/","./thumb4/", "./thumb7/");
$thumb_width = array(400, 200, 200,400, 0);	// サムネイルの横幅　最初の800は固定です。（ここに設定されているだけ作成します。）
$write_credit = array(true, true, true, true, true);		// クレジットをサムネイルに書き込むかどうか　最初のtrueは固定です。

$font_name = "./sazanami-gothic.ttf";
$credit_fontsize = array(8, 10, 14, 16, 16, 16);						// -160, -320, -480, -640, -800, 801-（変更しないで下さい）

$table_log_name = "photo_log_cms";//ログテーブル名前 yupengbo add 2011/11/21

//$comp_code = '00000';

//
//
//
//
//// web
//$site_domain = 'e-mon.vc';
//$site_admin_url = 'http://www.e-mon.vc/test/photodb/admin/';
//
//$item_image_dir = '/var/www/html/test/images';
//$item_image_url = './test/images';
//
//// mail
//$support_mail = 'fedora@e-mon.vc';
//$order_confirm_mail = 'fedora@e-mon.vc';
//
//// MD5
//$magic_code = 'abcd123456789++';
//
//// カテゴリー最大表示行数です。(0チェックしていないので注意）
//$category_max_row = 10;
//define('CATEGORY_ORDER_BY_NAME', 'category_id');
//define('CATEGORYCMB_ORDER_BY_NAME', 'category_name');
//
//// 部署最大表示行数です。(0チェックしていないので注意）
//$department_max_row = 10;
//define('DEPARTMENT_ORDER_BY_NAME', 'department_id');
//define('DEPARTMENTCMB_ORDER_BY_NAME', 'department_name');
//
//// 著作権者最大表示行数です。(0チェックしていないので注意）
//$copyright_max_row = 10;
//define('COPYRIGHT_ORDER_BY_NAME', 'copyright_id');
//define('COPYRIGHTCMB_ORDER_BY_NAME', 'copyright_name');
//
//// 写真家最大表示行数です。(0チェックしていないので注意）
//$photographer_max_row = 10;
//define('PHOTOGRAPHER_ORDER_BY_NAME', 'photographer_id');
//define('PHOTOGRAPHERCMB_ORDER_BY_NAME', 'photographer_name');
//
//// エージェンシー最大表示行数です。(0チェックしていないので注意）
//$agency_max_row = 10;
//define('AGENCY_ORDER_BY_NAME', 'agency_id');
//define('AGENCYCMB_ORDER_BY_NAME', 'agency_name');
//
//// 仕事最大表示行数です。(0チェックしていないので注意）
//$work_max_row = 10;
//define('WORK_ORDER_BY_NAME', 'work_id');
//define('WORKCMB_ORDER_BY_NAME', 'work_name');
//
//// 国最大表示行数です。(0チェックしていないので注意）
//$country_max_row = 10;
//define('COUNTRY_ORDER_BY_NAME', 'country_id');
//define('COUNTRYCMB_ORDER_BY_NAME', 'country_name');
//
//// 施設最大表示行数です。(0チェックしていないので注意）
//$institution_max_row = 10;
//define('INSTITUTION_ORDER_BY_NAME', 'institution_id');
//define('INSTITUTIONCMB_ORDER_BY_NAME', 'institution_name');
//
//// 観光地最大表示行数です。(0チェックしていないので注意）
//$sightseeing_max_row = 10;
//define('SIGHTSEEING_ORDER_BY_NAME', 'sightseeing_id');
//
//// エリア最大表示行数です。(0チェックしていないので注意）
//$area_max_row = 10;
//define('AREA_ORDER_BY_NAME', 'area_id');
//define('AREACMB_ORDER_BY_NAME', 'area_name');
//
//// 都市最大表示行数です。(0チェックしていないので注意）
//$city_max_row = 10;
//define('CITY_ORDER_BY_NAME', 'city_id');
//
//// ユーザー最大表示行数です。(0チェックしていないので注意）
//$user_max_row = 10;
//define('USER_ORDER_BY_NAME', 'user_id');
//
//// 写真最大表示行数です。(0チェックしていないので注意）
//$photograph_max_row = 5;
//define('PHOTOGRAPH_ORDER_BY_NAME', 'photo_mno');
//
//// サイト最大表示行数です。(0チェックしていないので注意）
//$site_max_row = 10;
//define('SITE_ORDER_BY_NAME', 'site_id');
//define('SITECMB_ORDER_BY_NAME', 'site_name');
//
//// 項目最大表示行数です。(0チェックしていないので注意）
//$item_max_row = 10;
//define('ITEM_ORDER_BY_NAME', 'site_id');
//
//
//
//# メール送信環境
//$mailon = "1";										// 登録通知用メール機能を使用："1"、不使用："0"
//$mailServer = "192.168.1.100";
//$mailFrom = "fedora@e-mon.vc";
//$mailUser = "fedora";
//$mailPassword = "111111";
//$mailAdmin = "fedora@e-mon.vc";						// 登録通知用メールアドレス（管理者用）
//$support_mail = "info@bridge.vc";
//$mailPort = 25;
//$msubject = "画像の登録申請が完了しました。";
//
//$noimage_url = "http://www.e-mon.vc/test/photodb/images/noimage.jpg";
//
//
//
# メール送信環境
$mail_config["server"] = "mail11.chicappa.jp";
$mail_config["from"] = "yupengbo@bridge.vc";
$mail_config["user"] = "bridge.vc-yupengbo";
$mail_config["password"] = "111111";
$mail_config["port"] = 587;
$mail_config["subject"] = "期限が切れた画像リスト";
$mail_config["from_name"] = "DS PHOTO WEB";
$mail_config["char"] =  "UTF-8";
$mail_config["auth"] = true;
$mail_config["mailer"] = "smtp";
define("REGPHOTOMNO","ALLUP");
?>