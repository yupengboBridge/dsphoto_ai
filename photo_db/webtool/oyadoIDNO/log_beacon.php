<?php
########################################################################
#
#  お宿予約完了画面ビーコン用(cid,bookingno)
# 
#  DB:ximage table:oyado_cid_bookingno
# 
#  対象ページ
#  ・お宿予約完了画面
#
#  【プログラム機能説明】
#	.取得したいファイルにパラメータをつけたimgタグを取り込む
#	.値を取得し、x.hankyu(サーバ) ximage(DB) :oyado_cid_bookingno(table) へ登録
#  顧客番号：cid　問い合わせ番号：bookingno
#
#　/home/xhankyu/public_html/photo_db/webtool/oyadoIDNO
#
#  @copyright  2017 BUD International
#  @version    1.0.0
########################################################################

//include('./log_save/db.inc.php');
date_default_timezone_set("Asia/Tokyo");

echo file_get_contents("./parts/noimage.gif");

save_to_db();
exit;

/*
*******************************************************
 * save_to_db()
 * 
 * DBに書き込む
*******************************************************/
function save_to_db(){
	//$cid ="11111111";
	//$bookingno = "001-17-006367";
	$cid ='';
	$bookingno ='';	
	if(isset($_GET['cid']) && $_GET['bookingno']){
		//顧客番号
		$cid = $_GET['cid'];
		//問い合わせ番号
		$bookingno = $_GET['bookingno'];
	}
	//全て空ならリターン
	if(empty($cid) && empty($bookingno)){
		return false;
	}
	
	//DBサーバ
	$SERVER	  = "10.254.2.63";// データベースのサーバーIP
	$DB_NAME  = "ximage";// データベース名前	
	$DB_USR   = "ximage";// データベースのユーザー	
	$DB_PSD   = "kCK!7wu4";// データベースのパスワード
	
	
	//DB接続
	try {
		$pdo = new PDO('mysql:host='.$SERVER.';dbname='.$DB_NAME.';charset=utf8',$DB_USR ,$DB_PSD);
		$pdo->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
	} catch (PDOException $e) {
		$error = new ErrorMes;
		$error->WriteMes('データベース接続に失敗しました' . $e->getMessage());
		exit;
	}
	
	//DB書き込み
	$stmt = $pdo -> prepare("INSERT INTO oyado_cid_bookingno (BOOKINGNO,CID) VALUES (:BOOKINGNO,:CID)");
	$stmt->bindParam(':BOOKINGNO', $bookingno, PDO::PARAM_STR);
	$stmt->bindParam(':CID', $cid, PDO::PARAM_STR);
	$stmt->execute();
	
	
	
	
	
	$pdo= null;

}
exit;






?>
