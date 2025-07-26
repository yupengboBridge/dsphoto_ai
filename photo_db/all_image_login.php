<?php
require_once('./config.php');
require_once('./lib.php');

set_time_limit(0);

// セッション管理をスタートします。
session_start();
if(empty($_SESSION['login_id'])&&isset($_COOKIE['login_id'])&&$_COOKIE['login_id']!="")
{
	$_SESSION['login_id'] = array_get_value($_COOKIE,'login_id' ,"");
	$_SESSION['user_name'] = array_get_value($_COOKIE,'user_name' ,"");
	$_SESSION['security_level'] = array_get_value($_COOKIE,'security_level' ,"");
	$_SESSION['compcode'] = array_get_value($_COOKIE,'compcode' ,"");
	$_SESSION['group'] = array_get_value($_COOKIE,'group' ,"");
	$_SESSION['user_id'] = array_get_value($_COOKIE,'user_id' ,"");
}
$s_login_id = array_get_value($_SESSION,'login_id' ,"");
$s_login_name = array_get_value($_SESSION,'user_name' ,"");
$s_security_level = array_get_value($_SESSION,'security_level' ,"");
$comp_code = array_get_value($_SESSION,'compcode' ,"");
$s_group_id = array_get_value($_SESSION,'group' ,"");
$s_user_id = array_get_value($_SESSION,'user_id' ,"");
$p_action = array_get_value($_REQUEST,'p_action' ,"");
$upload_msg = "";

$all_image_dir = "./upload_images/";   //一括登録の時、イメージファイルのパース
$upload_csvfile_conf['dir']="./uploads_csv/";
$upload_csvfile_conf['maxsize'] = 10000000;//約10MB

if (!empty($s_security_level)) $s_security_level = (int)$s_security_level;
if (!empty($s_login_id))
{
	if ($p_action == "uploadfile")
	{
		$upload_msg = uploadExcelfile($_FILES['p_filename'],'insert');
	}
	elseif ($p_action == "uploadupdatefile")
	{
		$upload_msg = uploadExcelfile($_FILES['p_updatefilename'],'update');
	}
	elseif ($p_action == "uploaddelfile")
	{
		$upload_msg = uploadExcelfile($_FILES['p_delfilename'],'delete');
	}
	
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD html 4.01 Transitional//EN">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="content-script-type" content="text/javascript" />
<title>写真一括登録/更新/削除</title>
<link href="./css/base.css" rel="stylesheet" type="text/css" />
<style type="text/css">
#divLoading {  width:100%;height:100%; 
               filter:alpha(Opacity=50);-moz-opacity:0.5;opacity: 0.5;z-index:100;
         position:absolute;
         display:none;
         background:url(img/wait.gif) no-repeat;
         background-color:#cfcfcf;
         background-position:center;
		 text-align:center;
		 left:0;
         }
</style>
<script src="./js/common.js" type="text/javascript" charset="utf-8"></script>
<script src="./js/jquery.js"  type="text/javascript"  charset="utf-8"></script>
<script type="text/javascript">

function funAllSubmit(filename,form)
{
	var flname = $('#'+filename);
//var flname = document.register_image_input.p_filename;
	// アップファイルを選択しない場合
	if (flname.val().length == 0)
	{
		alert('ファイルを選択してください。\r\n');
		flname.focus();
		return false;
	}

	var dotpos = flname.val().lastIndexOf(".");
	var ext = flname.val().substr(dotpos);
	ext = ext.toLowerCase();
	// 拡張子のチェック
	if (ext != ".csv")
	{
		alert("申請できない種類のファイルです。\r\n（拡張子.csvのみ申請登録可能です。）");
		flname.focus();
		return false;
	}
	$('#divLoading').show();
	$('#'+form).submit();

}
function init()
{
	//setCookie("bt_cnt",0);
	//setCookie("classname","");
}

</script>
</head>
<body style="background:url(parts/header_bg.gif) repeat-x; text-align:center;" onload="init();">
<div id="divLoading"></div>
<div>
  <!--  <iframe scrolling="no" frameborder="0" id="iframe_top" height="75"  width="900" name="top"  src="./findtop<?php echo $s_security_level?>.php"></iframe> -->
</div>
<form enctype="multipart/form-data" name="fm_uploadfile" id="fm_uploadfile" action="./all_image_login.php?p_action=uploadfile" method="post">
	<div style="width:600px;margin:20px auto;">
		<dl class="reg_filedata reg_clear reg_list_none_top required">
			<dt>csvファイル</dt>
			<dd class="bt_reg_reference">
				<input type="file" name="p_filename" id="p_filename" value="" style="width:400px"/>
			</dd>
			<dd style="padding-top:20px"><input id="allsubmit" style="width: 150px; height: 50px;" type="button" value="一括新規登録" onclick="funAllSubmit('p_filename','fm_uploadfile')"/></dd>
		</dl>
	</div>
</form>
<form enctype="multipart/form-data" name="fm_uploadupdatefile" id="fm_uploadupdatefile" action="./all_image_login.php?p_action=uploadupdatefile" method="post">
	<div style="width:600px;margin:20px auto;">
		<dl class="reg_filedata reg_clear reg_list_none_top required">
			<dt>csvファイル</dt>
			<dd class="bt_reg_reference">
				<input type="file" name="p_updatefilename" id="p_updatefilename" value="" style="width:400px"/>
			</dd>
			<dd style="padding-top:20px"><input id="allupdatesubmit" style="width: 150px; height: 50px;" type="button" value="一括更新" onclick="funAllSubmit('p_updatefilename','fm_uploadupdatefile')"/></dd>
		</dl>
	</div>
</form>
<form enctype="multipart/form-data" name="fm_uploaddelfile" id="fm_uploaddelfile" action="./all_image_login.php?p_action=uploaddelfile" method="post">
	<div style="width:600px;margin:20px auto;">
		<dl class="reg_filedata reg_clear reg_list_none_top required">
			<dt>csvファイル</dt>
			<dd class="bt_reg_reference">
				<input type="file" name="p_delfilename" id="p_delfilename" value="" style="width:400px"/>
			</dd>
			<dd style="padding-top:20px"><input id="alldelsubmit" style="width: 150px; height: 50px;" type="button" value="一括削除" onclick="funAllSubmit('p_delfilename','fm_uploaddelfile')"/></dd>
		</dl>
	</div>
</form>
<?php if($upload_msg !="") print_r($upload_msg);?>
</body>
  <noframes>
	  <body>
	  <P>このページを表示するには、フレームをサポートしているブラウザが必要です。</P>
	  </body>
  </noframes>
</html>
<?php
}else{
	// ログイン画面へ遷移する
	header_out($logout_page);
}
$db_link = null;
exit(0);
?>
<?php 
	function uploadExcelfile($file,$operate)
	{
		global $upload_conf, $all_image_dir,$upload_csvfile_conf,$comp_code;
		try
		{
			// ＤＢへ接続します。
			$db_link = db_connect();
		}
		catch(Exception $cla)
		{
			// 異常を出力する
			$msg[] = $cla->getMessage();
			error_exit($msg);
		}
		$uploderror = $file['error'];  //エラー
		$message ="";
		if($uploderror > 0)
		{
			$message = "csvファイルのアップロードが失敗しました！";
			return $message;
		}
		// 定義ファイルの内容をチェックします。
//		if (empty($upload_csvfile_conf))
//		{
//			$message = "保存用ディレクトリが設定されていません。";
//			return $message;
//		}
		$uploadmaxsize = $upload_csvfile_conf['maxsize'];    //最大サイズ
		$uploadsize = $file['size'];    //ファイルのサイズ
		if($uploadsize > $uploadmaxsize)
		{
			$message ="ファイルサイズが設定した最大値を超えています。";
			return $message;
		}
		// アップロードするファイル名から拡張子を抜出します。
		preg_match("/\.[^.]*$/i", $file['name'], $ext_tmp);			// extに拡張子
		// 拡張子を小文字に変換します。
		$ext = strtolower($ext_tmp[0]);
		$reg_time = time();										// 登録日時
		$rnd = rand(100, 999);									// 乱数100-999
		$uploadfilesavename = date("YmdHis", $reg_time) . $rnd;	
		$uploaddir = $upload_csvfile_conf['dir'].$uploadfilesavename.$ext;
		$uploadflg = move_uploaded_file($file['tmp_name'], $uploaddir);
		if($uploadflg)
		{
			$message = "csvファイルのアップロードが成功しました。<br>";
		}
		else 
		{
			$message = "csvファイルのアップロードが失敗しました。";
		}
		/***csvファイルに列のindex***/
		$col_old_photo_id_index = 0;//旧photo_id
		$col_old_photo_mno_index = 1; //旧画像管理番号
		
		$col_publishing_situation_id_index = 2; //掲載状況
		$col_registration_division_id_index = 3; //登録区分
		$col_source_image_no_index = 4;//素材管理番号
		$col_bud_photo_no_index = 5;//BUD_PHOTO番号
		$col_photo_name_index = 6; //被写体の名称
		$col_photo_explanation_index = 7; //素材（画像）の詳細内容
		$col_take_picture_time_id_index = 8; //撮影時期
		$col_take_picture_time2_id_index = 9; //撮影時期2
		$col_dfrom_index = 10; //掲載期間From
		$col_dto_index = 11 ; //掲載期間To
		$col_kikan_index = 12; //期間
		$col_borrowing_ahead_id_index = 13;//写真入手元
		$col_content_borrowing_ahead_index = 14;//写真入手元　その他
		$col_range_of_use_id_index = 15;//掲載可能範囲
		$col_use_condition_index = 16;//外部出稿条件付き
		$col_additional_constraints1_index = 17;//付加条件（クレジット）要クレジット
		$col_additional_constraints2_index = 18;//付加条件（要確認）
		$col_monopoly_use_index = 19;//このアカウントのみ使用可
		$col_copyright_owner_index = 20;//版権所有者
		$col_customer_section_index = 21;//お客様情報　部署名
		$col_customer_name_index = 22;//お客様情報　名前
		$col_registration_account_index = 23;//新規者のアカント
		$col_registration_person_index = 24;//新規者の名前
		$col_permission_account_index = 25;//許可者のアカント
		$col_permission_person_index = 26;//許可者の名前
		$col_permission_date_index = 27;//許可日
		$col_note_index = 28;//備考
		$col_register_date = 29;//新規日付
		$col_mno_index = 30; //画像管理番号(画像ファイル)
		
//		$col_category_index = 30;//カテゴリ
//		$col_classification_index = 31;//分類ID
//		$col_direction_index = 32; //方面ID
//		$col_country_prefecture_index = 33; //国・都道府県
//		$col_photo_place_index = 34;//
		/*****/
		setlocale(LC_ALL,'ja_JP.UTF-8');
		
		// CSVファイルを開く
		$csvfile = fopen($uploaddir,"r");
		// CSVファイルからフィールド名を取得する
//		if (!feof($csvfile))
//		{
//			// CSVの内容
//			$csv_fields = fgetcsv($csvfile,1000000,"\t");
//		} else {
//			// CSVファイルを閉じる
//			fclose($csvfile);
//		}
		
		$cnt = 0;//成功の件数
		$total_cnt = 0;//総件数
		$err_cnt = 0;//エラー件数
		$exited_cnt = 0;//存在件数
		$str = "";
		// ファイルの内容より繰り返し一覧データを作成する
		while(!feof($csvfile))
		{
			try{
				// 行の内容は配列にする
				$pi = new PhotoImageDB ();
				$csv_content = fgetcsv($csvfile,1000000,",");
				if (count($csv_content) <= 0 || empty($csv_content)) continue;
				$total_cnt = $total_cnt + 1;

				//一括更新CSV中的列，与一括新规CSV中的列的偏移量
				$offset = 1;
				//print_r($csv_content);die();
				if ($operate=="insert"&&count($csv_content) != 31)
				{
					$str =  $str."<p style='color: red'>画像ファイル名：".$csv_content[$col_mno_index]."　フィールド数は違います。</p><br/>";
					$errmessage2 = "画像ファイル名：".$csv_content[$col_mno_index].",フィールド数は違います。".date("Y-m-d H:i:s")."\r\n";
					write_log_tofile($errmessage2);
					continue;
				}
				elseif ($operate=="update"&&count($csv_content) != 31)
				{
				    //用于更新的CSV，图片文件名在第一列
				    $str =  $str."<p style='color: red'>画像ファイル名：".$csv_content[$col_old_photo_mno_index]."　フィールド数は違います。</p><br/>";
				    $errmessage2 = "画像ファイル名：".$csv_content[$col_old_photo_mno_index].",フィールド数は違います。".date("Y-m-d H:i:s")."\r\n";
				    write_log_tofile($errmessage2);
				    continue;
				}
				// 一括削除
				if ($operate=="delete")
				{
					$photo_id = $csv_content[$col_old_photo_id_index];
					$is_exist = check_exist($db_link,$photo_id);
					
					if(!$is_exist)
					{
						$str =  $str."<p style='color: red'>画像ファイル名：".$csv_content[$col_old_photo_mno_index]." が存在していません。</p><br />";
						$errmessage2 = "画像ファイル名：".$csv_content[$col_old_photo_mno_index]." が存在していません。".date("Y-m-d H:i:s")."\r\n";
						write_log_tofile($errmessage2);
					}
					else 
					{
						$logstr = "";
						$s_login_id = array_get_value($_SESSION,'login_id' ,"");
						$s_login_name = array_get_value($_SESSION,'user_name' ,"");
						$p_photo_mno = "";
						$p_photo_name = "";
						$photo_explanation = "";
						$bud_photo_no = "";
						$registration_person = "";
						$date_from = "";
						$date_to = "";
						
						$pi->get_photo_mno($db_link,$photo_id,$p_photo_mno,$p_photo_name,
						                    $photo_explanation,$bud_photo_no,$registration_person,
						                    $date_from,$date_to);
						
						if (!empty($p_photo_mno) && !empty($p_photo_name))
						{
							$logstr = date("Y-m-d H:i:s").",".$s_login_id.",".$s_login_name.",".$p_photo_mno.",".preg_replace("/,/"," ",preg_replace("'([\r\n])[\s]+'", " ",$p_photo_name));
							$logstr .= ",".preg_replace("/,/"," ",preg_replace("'([\r\n])[\s]+'", " ",$photo_explanation)).",".preg_replace("/,/"," ",preg_replace("'([\r\n])[\s]+'", " ",$bud_photo_no)).",";
							$logstr .= $date_from.",".$date_to.",0,".$registration_person."\r\n";
						}
						$result = $pi->delete_data($db_link, $photo_id);
						if (!empty($logstr))
						{
							write_log_tofile3($logstr);
						}
						if (!$result)
						{
							$str =  $str."<p style='color: red'>".$csv_content[$col_old_photo_mno_index]."削除の処理は警告です。許可者の削除が失敗しました</p>";
							$errmessage2 = "画像ファイル名：".$csv_content[$col_old_photo_mno_index].",削除の処理は警告です。許可者の削除が失敗しました。".date("Y-m-d H:i:s")."\r\n";
							write_log_tofile($errmessage2);
						}
						else 
						{
							$cnt = $cnt + 1;
							$errmessage2 ="画像ファイル名：".$csv_content[$col_old_photo_mno_index]."削除成功 。photo_id:".$photo_id."  ".date("Y-m-d H:i:s")."\r\n";
							write_log_tofile2($errmessage2);
						}
					}
					continue;
				}
				// 一括登録
				//「写真入手元」の項目がnullの場合エラーになる
				if ($operate=="insert")
				{
	//				$reg_p_obtaining = $csv_content[$col_borrowing_ahead_id_index];
	//				if(empty($reg_p_obtaining))
	//				{
	//					$str =  $str."<p style='color: red'>画像ファイル名：".$csv_content[$col_mno_index]."「写真入手元」の項目がnullです。</p><br />";
	//					$errmessage2 = "画像ファイル名：".$csv_content[$col_mno_index].",「写真入手元」の項目がnullです。".date("Y-m-d H:i:s")."\r\n";
	//					
	//					write_log_tofile($errmessage2);
	//					continue;
	//				}
					$classification_id1 = "";											// 分類ID
					$classification_name1 = "";											// 分類
					$direction_id1 = "";												// 方面ID
					$direction_name1 = "";												// 方面
					$country_prefecture_id1 = "";										// 国・都道府県ID
					$country_prefecture_name1 = "";										// 国・都道府県
					$place_id1 = "";													// 地名ID
					$place_name1 = "";													// 地名
			
					$p_photo_extentions = "";											// 画像内容
					$p_photo_extentions_ok = "";										// 画像内容
					
					//データ
					$data_ary = array();
					//BUD_PHOTO番号
					$bud_photo_no = $csv_content[$col_bud_photo_no_index];
		//			if (!empty($bud_photo_no) && strlen($bud_photo_no) > 0)
		//			{
		//				//登録区分
		//				$data_ary['reg_division'] = 2;
		//			} else {
		//				//登録区分
		//				$data_ary['reg_division'] = 1;
		//			}
					//
					//$data_ary['']=$csv_content[];
					$filename = $csv_content[$col_mno_index];
					$credit = $csv_content[$col_additional_constraints1_index];
					$fl= new allImageFileUpload($filename,$credit);
					if($fl->result == false)
					{
						$str =  $str."<p style='color: red'>画像ファイル名：".$csv_content[$col_mno_index]."イメージファイルの処理はエラーです。</p><br />";
						$str = $str.$fl->message.'<br>';
						$errmessage2 = "画像ファイル名：".$csv_content[$col_mno_index]."イメージファイルの処理はエラーです。".date("Y-m-d H:i:s")."\r\n";
						
						write_log_tofile($errmessage2);
						continue;
					}
					else 
					{
						// ファイルをアップロードします。
						$fl->upload();
						
						$resize_flg=true;
						//「バナー」を選択した場合に、①画像はResizeをしなくて、元のサイズのまま登録。②クレジットもしないようにする
						$reg_div = $csv_content[$col_registration_division_id_index];
						if($reg_div==3)
						{
							$write_credit = array(false, false, false, false, false);
							$fl->set_write_ok($write_credit);
							$resize_flg=false;
						}
						// サムネイルを作成します。
						$fl->make_thumbfile($resize_flg);
	
						// DB保存用のデータを設定します。
						$pi->up_url = $fl->up_url;					// アップロードURL
						$pi->img_width = $fl->img_width;			// イメージサイズ（横）
						$pi->img_height = $fl->img_height;			// イメージサイズ（縦）
						$pi->ext = $fl->ext;						// 拡張子
						$pi->image_size_x = $fl->img_width[0];		// 画像サイズ（横）
						$pi->image_size_y = $fl->img_height[0];		// 画像サイズ（縦）
						
						// 画像情報新規用のデータを設定します。
						$pi->photo_mno = "00000"; // 初期値を設定する
						//$pi->photo_mno =$csv_content[];// 画像管理番号
						$pi->comp_code = $comp_code; //ユーザー管理番号を設定する
						$pi->publishing_situation_id = 1;	// 掲載状況(「申請中」を設定する)
						//$pi->publishing_situation_id = $csv_content[$col_publishing_situation_id_index];// 掲載状況
						$pi->registration_division_id = $csv_content[$col_registration_division_id_index];// 登録区分
						if(empty($pi->registration_division_id) || trimspace($pi->registration_division_id)=="")
						{
							$str =  $str."<p style='color: red'>画像ファイル名：".$csv_content[$col_mno_index]." 登録区分が入力していません。</p><br />";
							$errmessage2 = "画像ファイル名：".$csv_content[$col_mno_index]." 登録区分が入力していません。".date("Y-m-d H:i:s")."\r\n";
							
							write_log_tofile($errmessage2);
							continue;
						}
						else
						{
							$division_id= check_registration_division($db_link,$pi->registration_division_id);
							if($division_id =="ERR")
							{
								$str =  $str."<p style='color: red'>画像ファイル名：".$csv_content[$col_mno_index]." DBに登録区分存在性チェックをする時、エラーが発生しまいました。</p><br />";
								$errmessage2 = "画像ファイル名：".$csv_content[$col_mno_index]." DBに登録区分存在性チェックをする時、エラーが発生しまいました。".date("Y-m-d H:i:s")."\r\n";
								
								write_log_tofile($errmessage2);
								continue;
							}
							else if($division_id == -1)
							{
								$str =  $str."<p style='color: red'>画像ファイル名：".$csv_content[$col_mno_index]." 入力した登録区分が存在していません。</p><br />";
								$errmessage2 = "画像ファイル名：".$csv_content[$col_mno_index]." 入力した登録区分が存在していません。".date("Y-m-d H:i:s")."\r\n";
								
								write_log_tofile($errmessage2);
								continue;
							}
							else 
							{
								$pi->registration_division_id = $division_id;
							}
						}
						if(empty($csv_content[$col_source_image_no_index]))
						{
							$pi->source_image_no = "元画像なし";    //元画像管理番号「元画像なし」を設定する
						}
						else 
						{
							$pi->source_image_no = $csv_content[$col_source_image_no_index];  // 元画像管理番号
						}
						$pi->bud_photo_no = $csv_content[$col_bud_photo_no_index];//BUD_PHOTO番号
						if($pi->registration_division_id==2)
						{
							if(empty($pi->bud_photo_no) || trimspace($pi->bud_photo_no)=="")
							{
								$str =  $str."<p style='color: red'>画像ファイル名：".$csv_content[$col_mno_index]." 「BUD PHOT DBあり」を選択した場合、BUD_PHOTO番号「なし」を選択できない。</p><br />";
								$errmessage2 = "画像ファイル名：".$csv_content[$col_mno_index]." 「BUD PHOT DBあり」を選択した場合、BUD_PHOTO番号「なし」を選択できない。".date("Y-m-d H:i:s")."\r\n";
								
								write_log_tofile($errmessage2);
								continue;
							}
						}
						if($pi->registration_division_id==3)
						{
							$pi->photo_org_no = "";// 元画像番号
						}
						if($pi->registration_division_id==4)
						{
							$pi->photo_url = "";// 元画像番号
						}
						$pi->photo_name = $csv_content[$col_photo_name_index];  // 写真名（タイトル）
						if(empty($pi->photo_name) || trimspace($pi->photo_name)=="" )
						{
							$str =  $str."<p style='color: red'>画像ファイル名：".$csv_content[$col_mno_index]." 被写体の名称が入力していません。</p><br />";
							$errmessage2 = "画像ファイル名：".$csv_content[$col_mno_index]." 被写体の名称が入力していません。".date("Y-m-d H:i:s")."\r\n";
							
							write_log_tofile($errmessage2);
							continue;
						}
						$pi->photo_explanation = $csv_content[$col_photo_explanation_index]; // 写真説明
						$pi->take_picture_time_id =$csv_content[$col_take_picture_time_id_index]; // 撮影時期１
						$pi->take_picture_time2_id =$csv_content[$col_take_picture_time2_id_index];// 撮影時期２
						$pi->dfrom = $csv_content[$col_dfrom_index];//掲載期間（From）
						if(empty($pi->dfrom) || trimspace($pi->dfrom)=="")
						{
							$pi->dfrom = date("Y-m-d");//掲載期間（From）
							//$pi->dfrom = "2000/01/01";//掲載期間（From）
						}
						$pi->dto = $csv_content[$col_dto_index]; //掲載期間（To）
						
						if(empty($pi->dto) || trimspace($pi->dto)=="")
						{
							$pi->dto ="2100/01/01";; //掲載期間（To）
						}
	//					else if(is_date($pi->dto))
	//					{
	//						$pi->kikan ="shitei";
	//					}
	//					else 
	//					{
	//						$str =  $str."<p style='color: red'>画像ファイル名：".$csv_content[$col_mno_index]." 掲載期間が正しく入力されていません。</p><br />";
	//						$errmessage2 = "画像ファイル名：".$csv_content[$col_mno_index]." 掲載期間が正しく入力されていません。".date("Y-m-d H:i:s")."\r\n";
	//						
	//						write_log_tofile($errmessage2);
	//						continue;
	//					}
						$pi->kikan =$csv_content[$col_kikan_index];// 期間
						if(empty($pi->kikan) || trimspace($pi->kikan)=="")
						{
							$pi->kikan ="mukigen";
	//						$str =  $str."<p style='color: red'>画像ファイル名：".$csv_content[$col_mno_index]." 掲載期間が入力していません。</p><br />";
	//						$errmessage2 = "画像ファイル名：".$csv_content[$col_mno_index]." 掲載期間が入力していません。".date("Y-m-d H:i:s")."\r\n";
	//						
	//						write_log_tofile($errmessage2);
	//						continue;
						}
						$pi->borrowing_ahead_id =$csv_content[$col_borrowing_ahead_id_index];// 写真入手元ID
						if(empty($pi->borrowing_ahead_id) || trimspace($pi->borrowing_ahead_id)=="")
						{
							$str =  $str."<p style='color: red'>画像ファイル名：".$csv_content[$col_mno_index]." 写真入手元IDが入力していません。</p><br />";
							$errmessage2 = "画像ファイル名：".$csv_content[$col_mno_index]." 写真入手元IDが入力していません。".date("Y-m-d H:i:s")."\r\n";
							
							write_log_tofile($errmessage2);
							continue;
						}
						// 写真入手元の「その他」を選択した場合
	//					if ($pi->borrowing_ahead_id == 2)
	//					{
	//						$pi->content_borrowing_ahead = $csv_content[$col_content_borrowing_ahead_index];// 写真入手元内容
	//					}
						$pi->content_borrowing_ahead = $csv_content[$col_content_borrowing_ahead_index];// 写真入手元内容
						if(empty($pi->content_borrowing_ahead) || trimspace($pi->content_borrowing_ahead) == "")
						{
							//$pi->borrowing_ahead_id =1;
							$str =  $str."<p style='color: red'>画像ファイル名：".$csv_content[$col_mno_index]." 写真入手元が入力していません。</p><br />";
							$errmessage2 = "画像ファイル名：".$csv_content[$col_mno_index]." 写真入手元が入力していません。".date("Y-m-d H:i:s")."\r\n";
							
							write_log_tofile($errmessage2);
							continue;
						}
	//					else 
	//					{
	//						$pi->borrowing_ahead_id = 2;
	//					}
						$pi->range_of_use_id =$csv_content[$col_range_of_use_id_index];//使用範囲
						// 使用範囲の「外部出稿条件付き」を選択した場合
						if($pi->range_of_use_id == 3)
						{
							$pi->use_condition = $csv_content[$col_use_condition_index]; //出稿条件
						}
		//				$reg_addition = $csv_content[];//付加条件
		//				// 付加条件の「要クレジット」を選択した場合
		//				if($reg_addition == 0)
		//				{
		//					$pi->additional_constraints1 = $csv_content[$col_additional_constraints1_index];  // 付加条件（クレジット）
		//				}
		//				if($reg_addition == 1)
		//				{
		//					$pi->additional_constraints2 = $csv_content[$col_additional_constraints2_index];  //付加条件（要確認）
		//				}
						$pi->additional_constraints1 = $csv_content[$col_additional_constraints1_index];  // 付加条件（クレジット）
						$pi->additional_constraints2 = $csv_content[$col_additional_constraints2_index];  //付加条件（要確認）
						$pi->monopoly_use = $csv_content[$col_monopoly_use_index]; // 独占使用
						$pi->copyright_owner = $csv_content[$col_copyright_owner_index]; // 版権所有者
						$pi->customer_section = $csv_content[$col_customer_section_index]; //お客様部署
						$pi->customer_name =$csv_content[$col_customer_name_index];//お客様名
						$pi->registration_account =$csv_content[$col_registration_account_index];//登録申請アカウント
						$pi->registration_person =$csv_content[$col_registration_person_index];//登録申請者
						$pi->permission_account = $csv_content[$col_permission_account_index]; //許可者のアカント
						$pi->permission_person = $csv_content[$col_permission_person_index]; //許可者の名前
						$pi->permission_date = $csv_content[$col_permission_date_index];//許可日
						$pi->note =$csv_content[$col_note_index];//備考
						$pi->register_date = date("Y/m/d H:i:s"); //登録日、システムの日付を設定する
			//print_r($csv_content);	print_r('<br>');print_r($pi);		
	//				$pi->keyword_str =$csv_content[];//キーワード文字列（スペース区切り）
						$pi->keyword_str = getKeyword($db_link,$csv_content[$col_old_photo_id_index]); //キーワード
						if(empty($pi->keyword_str) || $pi->keyword_str=="")
						{
							$errmessage2 = "画像ファイル名：".$csv_content[$col_mno_index].",イメージファイルの処理は警告です。既存のキーワードがありません。".date("Y-m-d H:i:s")."\r\n";
							write_log_tofile($errmessage2);
						}
						$p_class = get_registration_classification($db_link,$csv_content[$col_old_photo_id_index]);
						foreach ($p_class as $value)
						{
							$pi->registration_classifications->set_id($value["c_id"], $value["d_id"], $value["cp_id"], $value["p_id"]);
						}
						
						$pi->insert_data($db_link); //データ登録
	//					if($pi->publishing_situation_id != 1)
	//					{
							$new_photo_id = getMaxPhotoId($db_link);
							
							if($new_photo_id !="ERR")
							{
								try {
									$pi->photo_id = $new_photo_id;
									//$pi->update_data($db_link); //データ更新
									$flg = updatePermissionInfo($db_link, $csv_content[$col_permission_account_index], $csv_content[$col_permission_person_index], $csv_content[$col_permission_date_index], $new_photo_id);
								}
								catch (Exception $e)
								{
									$str =  $str."<p style='color: red'>".$csv_content[$col_mno_index]."イメージファイルの処理は警告です。許可者の更新が失敗しました</p>";
									$str =  $str.$e->getMessage();
									$errmessage2 = "画像ファイル名：".$csv_content[$col_mno_index].",イメージファイルの処理は警告です。許可者の更新が失敗しました。".$e->getMessage().date("Y-m-d H:i:s")."\r\n";
									write_log_tofile($errmessage2);
								}
							}
							else 
							{
								$str =  $str."<p style='color: red'>".$csv_content[$col_mno_index]."イメージファイルの処理は警告です。許可者の更新が失敗しました</p>";
								$errmessage2 = "画像ファイル名：".$csv_content[$col_mno_index].",イメージファイルの処理は警告です。許可者の更新が失敗しました。".date("Y-m-d H:i:s")."\r\n";
								write_log_tofile($errmessage2);
							}
							
	//					}
						$cnt = $cnt + 1;
						$errmessage2 ="画像ファイル名：".$csv_content[$col_mno_index]."保存成功 。photo_id:".$new_photo_id."  ".date("Y-m-d H:i:s")."\r\n";
						write_log_tofile2($errmessage2);
					}
				}
				
				// 一括更新
				//「写真入手元」の項目がnullの場合エラーになる
				if ($operate=="update")
				{
				    $classification_id1 = "";											// 分類ID
				    $classification_name1 = "";											// 分類
				    $direction_id1 = "";												// 方面ID
				    $direction_name1 = "";												// 方面
				    $country_prefecture_id1 = "";										// 国・都道府県ID
				    $country_prefecture_name1 = "";										// 国・都道府県
				    $place_id1 = "";													// 地名ID
				    $place_name1 = "";													// 地名
				    	
				    $p_photo_extentions = "";											// 画像内容
				    $p_photo_extentions_ok = "";										// 画像内容
				    
				    //用于更新的CSV，图片文件名在第一列
				    $filename = $csv_content[0];
				    $photo_id = $pi->photo_id = $csv_content[$col_old_photo_id_index + $offset];
			        $photo_mno = $pi->photo_mno = $csv_content[$col_old_photo_mno_index + $offset];
		            $reg_div = $csv_content[$col_registration_division_id_index + $offset];

				    //文件名为空时，不更新图片
				    $is_update_img = !empty($filename);
				    if($is_update_img)
				    {
				        $credit = $csv_content[$col_additional_constraints1_index + $offset];
				        $fl= new allImageFileUpload($filename,$credit);
				        if($fl->result == false)
				        {
				            $str =  $str."<p style='color: red'>画像管理番号：".$photo_mno."イメージファイルの処理はエラーです。</p><br />";
				            $str = $str.$fl->message.'<br>';
				            $errmessage2 = "画像管理番号：".$photo_mno."イメージファイルの処理はエラーです。".date("Y-m-d H:i:s")."\r\n";
				        
				            write_log_tofile($errmessage2);
				            continue;
				        }
				        else
				        {
				            // ファイルをアップロードします。
				            $fl->upload();
				        
				            $resize_flg=true;
				            //「バナー」を選択した場合に、①画像はResizeをしなくて、元のサイズのまま登録。②クレジットもしないようにする
				            if($reg_div==3)
				            {
				                $write_credit = array(false, false, false, false, false);
				                $fl->set_write_ok($write_credit);
				                $resize_flg=false;
				            }
				            // サムネイルを作成します。
				            $fl->make_thumbfile($resize_flg);
				        }
				        
				        // DB保存用のデータを設定します。
				        $pi->up_url = $fl->up_url;					// アップロードURL
				        $pi->img_width = $fl->img_width;			// イメージサイズ（横）
				        $pi->img_height = $fl->img_height;			// イメージサイズ（縦）
				        $pi->ext = $fl->ext;						// 拡張子
				        $pi->image_size_x = $fl->img_width[0];		// 画像サイズ（横）
				        $pi->image_size_y = $fl->img_height[0];		// 画像サイズ（縦）
				    }
				    
			        // 画像情報新規用のデータを設定します。
			        $pi->comp_code = $comp_code; //ユーザー管理番号を設定する
			        
			        $pi->publishing_situation_id = 1;	// 掲載状況(「申請中」を設定する)
			        $pi->registration_division_id = $reg_div;// 登録区分
			        if(empty($pi->registration_division_id) || trimspace($pi->registration_division_id)=="")
			        {
			            $str =  $str."<p style='color: red'>画像管理番号：".$photo_mno." 登録区分が入力していません。</p><br />";
			            $errmessage2 = "画像管理番号：".$photo_mno." 登録区分が入力していません。".date("Y-m-d H:i:s")."\r\n";
			            	
			            write_log_tofile($errmessage2);
			            continue;
			        }
			        else
			        {
			            $division_id= check_registration_division($db_link,$pi->registration_division_id);
			            if($division_id =="ERR")
			            {
			                $str =  $str."<p style='color: red'>画像管理番号：".$photo_mno." DBに登録区分存在性チェックをする時、エラーが発生しまいました。</p><br />";
			                $errmessage2 = "画像管理番号：".$photo_mno." DBに登録区分存在性チェックをする時、エラーが発生しまいました。".date("Y-m-d H:i:s")."\r\n";
			
			                write_log_tofile($errmessage2);
			                continue;
			            }
			            else if($division_id == -1)
			            {
			                $str =  $str."<p style='color: red'>画像管理番号：".$photo_mno." 入力した登録区分が存在していません。</p><br />";
			                $errmessage2 = "画像管理番号：".$photo_mno." 入力した登録区分が存在していません。".date("Y-m-d H:i:s")."\r\n";
			
			                write_log_tofile($errmessage2);
			                continue;
			            }
			            else
			            {
			                $pi->registration_division_id = $division_id;
			            }
			        }
			        if(empty($csv_content[$col_source_image_no_index + $offset]))
			        {
			            $pi->source_image_no = "元画像なし";    //元画像管理番号「元画像なし」を設定する
			        }
			        else
			        {
			            $pi->source_image_no = $csv_content[$col_source_image_no_index + $offset];  // 元画像管理番号
			        }
			        $pi->bud_photo_no = $csv_content[$col_bud_photo_no_index + $offset];//BUD_PHOTO番号
			        if($pi->registration_division_id==2)
			        {
			            if(empty($pi->bud_photo_no) || trimspace($pi->bud_photo_no)=="")
			            {
			                $str =  $str."<p style='color: red'>画像管理番号：".$photo_mno." 「BUD PHOT DBあり」を選択した場合、BUD_PHOTO番号「なし」を選択できない。</p><br />";
			                $errmessage2 = "画像管理番号：".$photo_mno." 「BUD PHOT DBあり」を選択した場合、BUD_PHOTO番号「なし」を選択できない。".date("Y-m-d H:i:s")."\r\n";
			
			                write_log_tofile($errmessage2);
			                continue;
			            }
			        }
			        if($pi->registration_division_id==3)
			        {
			            $pi->photo_org_no = "";// 元画像番号
			        }
			        if($pi->registration_division_id==4)
			        {
			            $pi->photo_url = "";// 元画像番号
			        }
			        $pi->photo_name = $csv_content[$col_photo_name_index + $offset];  // 写真名（タイトル）
			        if(empty($pi->photo_name) || trimspace($pi->photo_name)=="" )
			        {
			            $str =  $str."<p style='color: red'>画像管理番号：".$photo_mno." 被写体の名称が入力していません。</p><br />";
			            $errmessage2 = "画像管理番号：".$photo_mno." 被写体の名称が入力していません。".date("Y-m-d H:i:s")."\r\n";
			            	
			            write_log_tofile($errmessage2);
			            continue;
			        }
			        $pi->photo_explanation = $csv_content[$col_photo_explanation_index + $offset]; // 写真説明
			        $pi->take_picture_time_id =$csv_content[$col_take_picture_time_id_index + $offset]; // 撮影時期１
			        $pi->take_picture_time2_id =$csv_content[$col_take_picture_time2_id_index + $offset];// 撮影時期２
			        $pi->dfrom = $csv_content[$col_dfrom_index + $offset];//掲載期間（From）
			        if(empty($pi->dfrom) || trimspace($pi->dfrom)=="")
			        {
			            $pi->dfrom = date("Y-m-d");//掲載期間（From）
			        }
			        $pi->dto = $csv_content[$col_dto_index + $offset]; //掲載期間（To）
			
			        if(empty($pi->dto) || trimspace($pi->dto)=="")
			        {
			            $pi->dto ="2100/01/01";; //掲載期間（To）
			        }
			        $pi->kikan =$csv_content[$col_kikan_index + $offset];// 期間
			        if(empty($pi->kikan) || trimspace($pi->kikan)=="")
		            {
		                $pi->kikan ="mukigen";
			        }
				    $pi->borrowing_ahead_id =$csv_content[$col_borrowing_ahead_id_index + $offset];// 写真入手元ID
			        if(empty($pi->borrowing_ahead_id) || trimspace($pi->borrowing_ahead_id)=="")
					{
						$str =  $str."<p style='color: red'>画像管理番号：".$photo_mno." 写真入手元IDが入力していません。</p><br />";
										    $errmessage2 = "画像管理番号：".$photo_mno." 写真入手元IDが入力していません。".date("Y-m-d H:i:s")."\r\n";
			        	
    			        write_log_tofile($errmessage2);
    			        continue;
				    }
		            $pi->content_borrowing_ahead = $csv_content[$col_content_borrowing_ahead_index + $offset];// 写真入手元内容
	                if(empty($pi->content_borrowing_ahead) || trimspace($pi->content_borrowing_ahead) == "")
					{
	                    $str =  $str."<p style='color: red'>画像管理番号：".$photo_mno." 写真入手元が入力していません。</p><br />";
	                    $errmessage2 = "画像管理番号：".$photo_mno." 写真入手元が入力していません。".date("Y-m-d H:i:s")."\r\n";
	                    	
	                    write_log_tofile($errmessage2);
	                    continue;
				    }
				    $pi->range_of_use_id =$csv_content[$col_range_of_use_id_index + $offset];//使用範囲
				    // 使用範囲の「外部出稿条件付き」を選択した場合
				    if($pi->range_of_use_id == 3)
				    {
				        $pi->use_condition = $csv_content[$col_use_condition_index + $offset]; //出稿条件
				    }
			        $pi->additional_constraints1 = $csv_content[$col_additional_constraints1_index + $offset];  // 付加条件（クレジット）
			        $pi->additional_constraints2 = $csv_content[$col_additional_constraints2_index + $offset];  //付加条件（要確認）
			        $pi->monopoly_use = $csv_content[$col_monopoly_use_index + $offset]; // 独占使用
			        $pi->copyright_owner = $csv_content[$col_copyright_owner_index + $offset]; // 版権所有者
			        $pi->customer_section = $csv_content[$col_customer_section_index + $offset]; //お客様部署
			        $pi->customer_name =$csv_content[$col_customer_name_index + $offset];//お客様名
			        $pi->registration_account =$csv_content[$col_registration_account_index + $offset];//登録申請アカウント
			        $pi->registration_person =$csv_content[$col_registration_person_index + $offset];//登録申請者
			        $pi->permission_account = $csv_content[$col_permission_account_index + $offset]; //許可者のアカント
			        $pi->permission_person = $csv_content[$col_permission_person_index + $offset]; //許可者の名前
			        $pi->permission_date = $csv_content[$col_permission_date_index + $offset];//許可日
			        $pi->note =$csv_content[$col_note_index + $offset];//備考
			        $pi->register_date = $csv_content[$col_register_date + $offset]; //登録日
					$pi->keyword_str = getKeyword($db_link,$csv_content[$col_old_photo_id_index + $offset]); //キーワード
			        if(empty($pi->keyword_str) || $pi->keyword_str=="")
			        {
				        $errmessage2 = "画像管理番号：".$photo_mno.",イメージファイルの処理は警告です。既存のキーワードがありません。".date("Y-m-d H:i:s")."\r\n";
			            write_log_tofile($errmessage2);
			        }
			        $p_class = get_registration_classification($db_link,$csv_content[$col_old_photo_id_index + $offset]);
	                foreach ($p_class as $value)
	                {
	                   $pi->registration_classifications->set_id($value["c_id"], $value["d_id"], $value["cp_id"], $value["p_id"]);
			        }
				
			        //データ更新
			        if($is_update_img)
			        {
			            $pi->batch_update_data($db_link);
			        }
			        else
			        {
			            $pi->update_data($db_link);
			        }
    			
					$cnt = $cnt + 1;
					$errmessage2 ="画像管理番号：".$photo_mno."更新成功 。photo_id:".$pi->photo_id."  ".date("Y-m-d H:i:s")."\r\n";
					write_log_tofile2($errmessage2);
				}

			}
			catch (Exception $ex)
			{
				$errmessage2 = "";
				$str .= "<p style='color: red'>";
				
			    if(!empty($filename))
			    {
			        $errmessage2 .= "画像ファイル名：".$filename;
			        $str .= $filename;
			    }
			    else
			    {
			        $errmessage2 .= "画像管理番号：".$photo_mno;
			        $str .= $photo_id;
			    }
				$str .= "イメージファイルの処理はエラーです。</p>";
				$str = $str.$ex->getMessage();
				$errmessage2 .= "イメージファイルの処理はエラーです。".$ex->getMessage().date("Y-m-d H:i:s")."\r\n";
				write_log_tofile($errmessage2);
			}
		}
		fclose($csvfile);
		$err_cnt = $total_cnt - $cnt - $exited_cnt;
		$message = $message .$total_cnt."件を処理しました。"."成功：".$cnt."件，エラー：".$err_cnt."件";
		$errmessage2 = $total_cnt."件を処理しました。"."成功：".$cnt."件，エラー：".$err_cnt."件   ".date("Y-m-d H:i:s")."\r\n";
		write_log_tofile($errmessage2);
		$message = $message.$str;
		return $message;
	}

	/*
	 * 関数名：write_log_tofile
	 * 関数説明：CSVでバッチする時、エラーがある場合、エラーをログファイルに出力する
	 * パラメタ：errmsg:エラーメッセージ
	 * 戻り値：無し
	 */
	function write_log_tofile($errmsg)
	{
		// CSVファイルを出力する
		$logfileName = date("Y-m-d").'_csv_batch_login_errorlog.log';
		$file = fopen("./log/".$logfileName,"a+");
		fwrite($file,$errmsg);
		fclose($file);
	}
	
	function write_log_tofile2($errmsg)
	{
		// CSVファイルを出力する
		$logfileName = date("Y-m-d").'_csv_batch_login_log.log';
		$file = fopen("./log/".$logfileName,"a+");
		fwrite($file,$errmsg);
		fclose($file);
	}
	/*
	 * 関数名：write_log_tofile
	 * 関数説明：画像を削除すると、削除した画像はログファイルに出力する
	 * パラメタ：logmsg:ログ情報
	 * 戻り値：無し
	 */
	function write_log_tofile3($logmsg)
	{
		$file = fopen("./log/delete_image.log","a+");
		fwrite($file,$logmsg);
		fclose($file);
	}
	/*
 * 関数名：trimspace
 * 関数説明：文字列の前後の全角と半角スペースを削除する
 * パラメタ：
 * str：文字列
 * 戻り値：スペースを削除した文字列
 */
	function trimspace($str)
	{
		$tmp2 = preg_replace('/^[ 　]*(.*?)[ 　]*$/u', '$1', $str);
		return $tmp2;
	}
	/*
 * 関数名：check_registration_division
 * 関数説明：登録区分存在性のチェック
 * パラメタ：$db_link,$divisionId
 * str：文字列
 * 戻り値：登録区分存在の場合：TRUE；存在しない場合：FALSE
 */
	function check_registration_division($db_link,$divisionId)
	{
		if(!$db_link)
		{
			return "ERR";
		}
		try{
			$sql ="select registration_division_id from registration_division2 where registration_division_id='$divisionId'";
			$stmt = $db_link->prepare($sql);
			// SQLを実行します。
			$result = $stmt->execute();
	
			// 実行結果をチェックします。
			if ($result == true)
			{
				// 実行結果がOKの場合の処理です。
				//$icount = $stmt->rowCount();
				if(!!($data = $stmt->fetch(PDO::FETCH_ASSOC)))
				{
					return $data["registration_division_id"];
				}
				else 
				{
					return -1;
				}
			}
			else {
				return "ERR";
			}
		}
		catch (Exception $ex)
		{
			return "ERR";
		}
		
	}
/**
 * キーワードを取得する
 *
 * @param $db_link ,$photo_mno
 */
	
	function getKeyword($db_link,$photo_id)
	{
		if(!$db_link)
		{
			return "";
		}
		try {
			//$sql ="SELECT kt.keyword_name FROM keyword kt INNER JOIN photoimg pt ON kt.photo_id = pt.photo_id WHERE pt.photo_mno = '$photo_mno'";
			$sql ="SELECT keyword_name FROM keyword  WHERE photo_id = '$photo_id'";
			$stmt = $db_link->prepare($sql);
			// SQLを実行します。
			$result = $stmt->execute();
			// 実行結果をチェックします。
			if ($result == true)
			{
				// 実行結果がOKの場合の処理です。
				//$icount = $stmt->rowCount();
				if(!!($data = $stmt->fetch(PDO::FETCH_ASSOC)))
				{
					return $data["keyword_name"];
				}
				else 
				{
					return "";
				}
			}
			else {
				return "";
			}
		}
		catch (Exception $ex)
		{
			return "";
		}
	}
	/**
	 * 
	 * Enter description here ...
	 * @param unknown_type $db_link
	 * @param unknown_type $p_photo_id
	 * 戻り値：画像ID存在の場合：TRUE；存在しない場合：FALSE
	 */
	function check_exist($db_link, $photo_id)
	{
		if (!is_numeric($photo_id))
		{
			throw new Exception("画像ID(引数：photo_id)に数値以外が設定されています。");
		}

		$sql = "SELECT * FROM photoimg WHERE photo_id = ?";

		$stmt = $db_link->prepare($sql);
		$stmt->bindParam(1, $photo_id);
		$result = $stmt->execute();
		if ($result == true)
		{
			// 処理数を取得します。
			$icount = $stmt->rowCount();

			// 選択されたデータ数が１かどうかチェックします。
			if ($icount == 1)
			{
				return true;
			}
			else
			{
				return false;
			}
		}
		else
		{
			$err = $stmt->errorInfo();
			throw new Exception("画像の読み込みに失敗しました。（条件設定エラー）");
		}
	}
/**
 * 分類を取得する
 *
 * @param $db_link ,$photo_mno
 */	
	function get_registration_classification($db_link,$photo_id)
	{
		$result_array = array();
		
		if(!$db_link)
		{
			return "";
		}
		try {
//			$sql = "SELECT rt.classification_id,rt.direction_id,rt.country_prefecture_id,rt.place_id ";
//			$sql.=" FROM registration_classification rt INNER JOIN photoimg pt ON rt.photo_id = pt.photo_id";
//			$sql.=" WHERE pt.photo_mno = '$photo_mno'";
			$sql = "SELECT rt.classification_id,rt.direction_id,rt.country_prefecture_id,rt.place_id ";
			$sql.=" FROM registration_classification rt ";
			$sql.=" WHERE rt.photo_id = '$photo_id'";
			$stmt = $db_link->prepare($sql);
			// SQLを実行します。
			$result = $stmt->execute();
			// 実行結果をチェックします。
			if ($result == true)
			{
				
				while(!!($data = $stmt->fetch(PDO::FETCH_ASSOC)))
				{
					$p_classification = array(
					"c_id"=> $data["classification_id"],
					"d_id"=> $data["direction_id"],
					"cp_id"=> $data["country_prefecture_id"],
					"p_id"=> $data["place_id"],
					);
					array_push($result_array, $p_classification);
				}
			}
		}
		catch (Exception $ex)
		{
			
		}
		return $result_array;
	}
	
/**
 * photo_idを取得する
 * 
 * @param $db_link
 */
	function getMaxPhotoId($db_link)
	{
		if(!$db_link)
		{
			return "ERR";
		}
		try {
			$sql = "SELECT MAX(photo_id) AS photo_id FROM photoimg ";
			$stmt = $db_link->prepare($sql);
			// SQLを実行します。
			$result = $stmt->execute();
			// 実行結果をチェックします。
			if ($result == true)
			{
				if(!!($data = $stmt->fetch(PDO::FETCH_ASSOC)))
				{
					return $data["photo_id"];
				}
				else {
					return "ERR";
				}
			}
			else 
			{
				return "ERR";
			}
		}
		catch (Exception $ex)
		{
			return "ERR";
		}
	}	
	
	/**
	 * 許可者についての情報更新
	 */
	function updatePermissionInfo($db_link,$account,$person,$date,$photo_id)
	{
		if(!$db_link)
		{
			return "ERR0";
		}
		try {
			$sql = "UPDATE  photoimg set ";
			$sql .= "permission_account=?,";
			$sql .= "permission_person=?,";
			$sql .= "permission_date=?";
			$sql .= " WHERE photo_id=?";
			$stmt = $db_link->prepare($sql);
			if(!empty($account))
			{
				$stmt->bindParam(1,$account);
			} else {
				$stmt->bindValue(1,null);
			}
			if(!empty($person))
			{
				$stmt->bindParam(2,$person);
			} else {
				$stmt->bindValue(2,null);
			}
			if(!empty($date))
			{
				$stmt->bindParam(3,$date);
			} else {
				$stmt->bindValue(3,null);
			}
			$stmt->bindParam(4,$photo_id);
			
			// SQLを実行します。
			$result = $stmt->execute();
			// 実行結果をチェックします。
			if ($result == true)
			{		
				return "OK";
			}
			else 
			{
				return "ERR1";
			}
		}
		catch (Exception $ex)
		{
			return "ERR2";
		}
	}
/**
 * 国名を取得する
 *
 * @param unknown_type $p_cp_name
 */
function getCountryPrefectureName($p_cp_name)
{
	global $db_link;
	$ret_cp_name = $p_cp_name;
	if(!empty($p_cp_name))
	{
		$where = " WHERE ";
		$where .= "country_name_case0 = '".$p_cp_name."'";
		$where .= " OR country_name_case1 = '".$p_cp_name."'";
		$where .= " OR country_name_case2 = '".$p_cp_name."'";
		$where .= " OR country_name_case3 = '".$p_cp_name."'";
		$where .= " OR country_name_case4 = '".$p_cp_name."'";
		$where .= " OR country_name_case5 = '".$p_cp_name."'";
		$where .= " OR country_name_case6 = '".$p_cp_name."'";
		$where .= " OR country_name_case7 = '".$p_cp_name."'";
		$where .= " OR country_name_case8 = '".$p_cp_name."'";
		$where .= " OR country_name_case9 = '".$p_cp_name."'";
		$where .= " OR country_name_case10 = '".$p_cp_name."'";

		$sql = "SELECT country_name_case0 FROM country_case ".$where;
		$stmt = $db_link->prepare($sql);
		// SQLを実行します。
		$result = $stmt->execute();
		// 実行結果をチェックします。
		if ($result == true)
		{
			// 実行結果がOKの場合の処理です。
			$icount = $stmt->rowCount();
			if ($icount > 0)
			{
				$registration_country_name = $stmt->fetch(PDO::FETCH_ASSOC);
				$ret_cp_name = $registration_country_name['country_name_case0'];
			}
		}
	}
	return $ret_cp_name;
}
/*
 * 関数名：get_id
 * 関数説明：分類、方面、国、都市を取得する
 * パラメタ：
 * strflg:フラグ
 * p_c:分類
 * p_d:方面
 * p_cp:国
 * p_p:都市
 * 戻り値："1"（正常）/"-1"（エラー）/"無し（データ無し）"
 */
function get_id($strflg, $p_c = "", $p_d = "", $p_cp = "", $p_p = "")
{
	global $db_link;
	global $classification_id1, $direction_id1, $country_prefecture_id1, $place_id1;
	global $classification_name1,$direction_name1,$country_prefecture_name1,$place_name1;

	$classification_id1 = "";											// 分類ID
	$classification_name1 = "";											// 分類
	$direction_id1 = "";												// 方面ID
	$direction_name1 = "";												// 方面
	$country_prefecture_id1 = "";										// 国・都道府県ID
	$country_prefecture_name1 = "";										// 国・都道府県
	$place_id1 = "";													// 地名ID
	$place_name1 = "";													// 地名

	$sql = "SELECT classification.classification_id, classification_name,";
	$sql .= "direction.direction_id, direction_name, country_prefecture.country_prefecture_id,";
	$sql .= "country_prefecture_name,place_id,place_name";
	$sql .= " FROM classification, direction, country_prefecture, place";

	$sqlwhere = "";
	if ($p_c != null && !empty($p_c))
	{
		if (strlen($p_c) > 0)
		{
			$sqlwhere = " WHERE classification_name = '".$p_c."'";
		}
	}

	if ($p_d != null && !empty($p_d))
	{
		if (strlen($p_d) > 0)
		{
			if (!empty($sqlwhere))
			{
				$sqlwhere .= " AND direction_name = '".$p_d."'";
			} else {
				$sqlwhere = " WHERE direction_name = '".$p_d."'";
			}
			$sqlwhere .= " AND direction.classification_id = classification.classification_id";
		}
	}

	if ($p_cp != null && !empty($p_cp))
	{
		if (strlen($p_cp) > 0)
		{
			if (!empty($sqlwhere))
			{
				$sqlwhere .= " AND country_prefecture_name = '".$p_cp."'";
			} else {
				$sqlwhere = " WHERE country_prefecture_name = '".$p_cp."'";
			}
			$sqlwhere .= " AND country_prefecture.direction_id = direction.direction_id";
			if ($p_d == null || empty($p_d) || strlen($p_d) <= 0)
			{
				$sqlwhere .= " AND direction.classification_id = classification.classification_id";
			}
		}
	}

	if ($p_p != null && !empty($p_p))
	{
		if (strlen($p_p) > 0)
		{
			if (!empty($sqlwhere))
			{
				$sqlwhere .= " AND place_name = '".$p_p."'";
			} else {
				$sqlwhere = " WHERE place_name = '".$p_p."'";
			}
			$sqlwhere .= " AND place.country_prefecture_id = country_prefecture.country_prefecture_id";
			if ($p_cp == null || empty($p_cp) || strlen($p_cp) <= 0)
			{
				$sqlwhere .= " AND country_prefecture.direction_id = direction.direction_id";
			}
			if ($p_d == null || empty($p_d) || strlen($p_d) <= 0)
			{
				$sqlwhere .= " AND direction.classification_id = classification.classification_id";
			}
		}
	}

	if (!empty($sqlwhere))
	{
		$sqlwhere .= " LIMIT 1";
		$sql .= $sqlwhere;
	} else {
		return false;
	}

	$stmt = $db_link->prepare($sql);
	// SQLを実行します。
	$result = $stmt->execute();

	// 実行結果をチェックします。
	if ($result == true)
	{
		// 実行結果がOKの場合の処理です。
		$icount = $stmt->rowCount();
		if ($icount > 0)
		{
			$reg_c = $stmt->fetch(PDO::FETCH_ASSOC);

			// 分類IDなどを保存します。
			if ($strflg == "0001" || $strflg == "0011" || $strflg == "0101" || $strflg == "0111" ||
			    $strflg == "1001" || $strflg == "1011" || $strflg == "1111")
			{
				$classification_id1 =  $reg_c['classification_id'];
				$classification_name1 = $reg_c['classification_name'];

				$direction_id1 = $reg_c['direction_id'];
				$direction_name1 = $reg_c['direction_name'];

				$country_prefecture_id1 = $reg_c['country_prefecture_id'];
				$country_prefecture_name1 = $reg_c['country_prefecture_name'];

				$place_id1 = $reg_c['place_id'];
				$place_name1 = $reg_c['place_name'];
			} elseif ($strflg == "0010" || $strflg == "0110" || $strflg == "1010" || $strflg == "1110") {
				$classification_id1 =  $reg_c['classification_id'];
				$classification_name1 = $reg_c['classification_name'];

				$direction_id1 = $reg_c['direction_id'];
				$direction_name1 = $reg_c['direction_name'];

				$country_prefecture_id1 = $reg_c['country_prefecture_id'];
				$country_prefecture_name1 = $reg_c['country_prefecture_name'];
			} elseif ($strflg == "0100") {
				$classification_id1 =  $reg_c['classification_id'];
				$classification_name1 = $reg_c['classification_name'];

				$direction_id1 = $reg_c['direction_id'];
				$direction_name1 = $reg_c['direction_name'];
			} elseif ($strflg == "1000") {
				$classification_id1 =  $reg_c['classification_id'];
				$classification_name1 = $reg_c['classification_name'];
			} else {
				return "無し";
			}
			return "1";
		} else {
			//都市を設定した場合、都市より検索しない場合

			if ($strflg == "0011")
		    {
		    	//国よりもう一度検索する、検索した場合、都市は登録しない
		    	$retval = get_id2($strflg, "", "", $p_cp, "");
		    	if ($retval == "無し")
		    	{
		    		return "無し";
		    	} elseif ((int)$retval > 0) {
					$place_id1 = "";
					$place_name1 = "";

					return "1";
		    	} else {
		    		return "-1";
		    	}
		    } elseif ($strflg == "0101") {
		    	//方面よりもう一度検索する、検索した場合、国と都市は登録しない
		    	$retval = get_id2($strflg, "", $p_d, "", "");
		    	if ($retval == "無し")
		    	{
		    		return "無し";
		    	} elseif ((int)$retval > 0) {
					$country_prefecture_id1 = "";
					$country_prefecture_name1 = "";
					$place_id1 = "";
					$place_name1 = "";

					return "1";
		    	} else {
		    		return "-1";
		    	}
		    } elseif ($strflg == "0111") {
		    	//国と方面よりもう一度検索する、検索した場合、都市は登録しない
		    	$retval = get_id2($strflg, "", $p_d, $p_cp, "");
		    	if ($retval == "無し")
		    	{
		    		return "無し";
		    	} elseif ((int)$retval > 0) {
					$place_id1 = "";
					$place_name1 = "";

					return "1";
		    	} else {
		    		return "-1";
		    	}
		    } elseif ($strflg == "1001") {
		    	//分類よりもう一度検索する、検索した場合、方面と国と都市は登録しない
		    	$retval = get_id2($strflg, $p_c , "", "", "");
		    	if ($retval == "無し")
		    	{
		    		return "無し";
		    	} elseif ((int)$retval > 0) {
					$direction_id1 = "";
					$direction_name1 = "";
					$country_prefecture_id1 = "";
					$country_prefecture_name1 = "";
					$place_id1 = "";
					$place_name1 = "";

					return "1";
		    	} else {
		    		return "-1";
		    	}
		   	} elseif ($strflg == "1011") {
				//国と分類よりもう一度検索する、検索した場合、都市は登録しない
		   		$retval = get_id2($strflg, $p_c , "", $p_cp, "");
		   		if ($retval == "無し")
		    	{
		    		return "無し";
		    	} elseif ((int)$retval > 0) {
					$place_id1 = "";
					$place_name1 = "";

					return "1";
		    	} else {
		    		return "-1";
		    	}
		   	} elseif ($strflg == "1111") {
		   		$retval = get_id2($strflg, $p_c , $p_d, $p_cp, "");
		   		if ($retval == "無し")
		    	{
		    		return "無し";
		    	} elseif ((int)$retval > 0) {
					$place_id1 = "";
					$place_name1 = "";

					return "1";
		    	} else {
		    		return "-1";
		    	}
		   	} else {
		   		return "無し";
		   	}
		}
	} else {
		return "-1";
	}
}
//	/*
//	 * 関数名：set_insertdata
//	 * 関数説明：画像情報新規用のデータを設定する
//	 * パラメタ：$data_ary
//	 * 戻り値：無し
//	 */
//	function set_insertdata($data_ary)
//	{
//		global $pi;
//		
//	}
	/*ファイルをアップロードします
	 * 
	 * */
	class allImageFileUpload
	{
		var $message;									// メッセージ
		var $result;									// アップロード結果
		var $img_width;									// イメージサイズ（横）	0:元、1:サムネイル1、2:サムネイル2・・・
		var $img_height;								// イメージサイズ（縦）	0:元、1:サムネイル1、2:サムネイル2・・・
		var $up_url;									// アップロードURL（最終的にアップロードされたURL）
														//					0:元、1:サムネイル1、2:サムネイル2・・・
		var $ext;										// 拡張子（元ファイル名）
	
		private $filename;								// ファイル情報
		private $dirno;									// uploadおよびthumbフォルダ以下0-9のどのフォルダに入れるか
		private $svname;								// 保存ファイル名作成元（YmdHis999）
		private $svfullpath;							// 保存ファイル名（フルパス:./$uploadconf['dir']/YmdHis999/dirno/svname.ext）
		private $upfile;								// アップロードファイル名
		private $uploadsize;							// アップロードサイズ
		private $uploadconf;							// アップロード用定義
														//	$uploadconf['dir'] = "./uploads/";			アップロードフォルダ
														//	$uploadconf['temp_dir'] = "./temporary/";	テンポラリーフォルダ
														//	$uploadconf['maxsize'] = 1000000;			 アップロードファイルの制限サイズ
														//	$uploadconf['site_url'] = 'http:			サイトURL
		private $flwidth;								// サムネイルを作成するときの横幅
		private $thumbdir;								// サムネイルを保存するフォルダ
		private $font_name;								// フォント名
		private $credit;								// クレジット
		private $write_ok;								// フォントを書き込むかどうか
		
		private $image_dir;                             //一括登録の時、イメージファイルのパース

	/**
	 * コンストラクター
	 */
		function allImageFileUpload($filenm,$cre)
		{
			// config.phpからデフォルト値を読み込むためのglobalです。
			global $upload_conf, $thumb_dir, $thumb_width, $font_name, $write_credit;
			global $credit_fontsize,$all_image_dir;
			
			// メンバーを初期化します。
			$this->message = "";						// メッセージ
			$this->result = true;						// 結果＝成功（true）
			$this->file = "";							// ファイル情報
			$this->svname = "";							// 保存ファイル名作成元（YmdHis999）
			$this->uploadsize = 0;						// アップロードサイズ
			$this->ext = "";							// 拡張子
			$this->font_name = "";						// フォント名
			$this->dirno = 0;							// uploadおよびthumbフォルダ以下のディレクトリ名
			$this->credit = "";							// クレジット
	
			$this->img_width = array();					// イメージサイズ（横）			0:元、1:サムネイル1、2:サムネイル2・・・
			$this->img_height = array();				// イメージサイズ（横）			0:元、1:サムネイル1、2:サムネイル2・・・
			$this->write_ok = array();					// クレジットを書き込むかどうか		0:元、1:サムネイル1、2:サムネイル2・・・
			$this->flwidth = array();					// サムネイルを作成するときの横幅	0:元、1:サムネイル1、2:サムネイル2・・・
			$this->thumbdir = array();					// サムネイルを保存するフォルダ		0:元、1:サムネイル1、2:サムネイル2・・・
			$this->up_url = array();					// アップロードURL				0:元、1:サムネイル1、2:サムネイル2・・・
			$this->svfullpath = array();				// 保存ファイル名（フルパス）		0:元、1:サムネイル1、2:サムネイル2・・・
			
			// ファイル情報を設定します。
			$this->filename = $filenm;
			//一括登録の時、イメージファイルのパース
			$this->image_dir = $all_image_dir;
			// インスタンス生成時に定義情報が設定されていない場合で、
			if (!empty($upload_conf))
			{
				// config.phpにデフォルト値が設定されていれば、
				// その値を使用します。
				$this->uploadconf = $upload_conf;
			}
			else
			{
				// config.phpにデフォルト値が設定されていなければ、
				// エラーとします。
				$this->result = false;
				$this->message = "アップロード用定義が設定されていません。";
				throw new Exception($this->message);
			}
			// 定義ファイルの内容をチェックします。
			if (empty($this->uploadconf['dir']))
			{
				$this->result = false;
				$this->message = "保存用ディレクトリが設定されていません。";
				throw new Exception($this->message);
			}
	
			if (empty($this->uploadconf['temp_dir']))
			{
				$this->result = false;
				$this->message = "テンポラリーディレクトリが設定されていません。";
				throw new Exception($this->message);
			}
	
			if (empty($this->uploadconf['maxsize']))
			{
				$this->result = false;
				$this->message = "アップロード最大サイズが設定されていません。";
				throw new Exception($this->message);
			}
	
			if (empty($this->uploadconf['site_url']))
			{
				$this->result = false;
				$this->message = "URLが設定されていません。";
				throw new Exception($this->message);
			}
//			if (empty($this->uploadconf['image_dir']))
//			{
//				$this->result = false;
//				$this->message = "イメージのパースが設定されていません。";
//				throw new Exception($this->message);
//			}
		    // インスタンス生成時にサムネイル保存用フォルダが設定されていない場合で、
			if (!empty($thumb_dir))
			{
				// config.phpにデフォルト値が設定されていれば、
				// その値を使用します。
				$this->thumbdir = $thumb_dir;
			}
			else
			{
				// config.phpにデフォルト値が設定されていなければ、
				// エラーとします。
				$this->result = false;
				$this->message = "サムネイル保存用フォルダが設定されていません。";
				throw new Exception($this->message);
			}
			// インスタンス生成時にサムネイル作成時の横幅が設定されていない場合で、
			if (!empty($thumb_width))
			{
				// config.phpにデフォルト値が設定されていれば、
				// その値を使用します。
				$this->flwidth = $thumb_width;
			}
			else
			{
				// config.phpにデフォルト値が設定されていなければ、
				// エラーとします。
				$this->result = false;
				$this->message = "サムネイル作成時の横幅が設定されていません。";
				throw new Exception($this->message);
			}
			// インスタンス生成時にクレジット書込用のフォントが設定されていない場合で、
			if (!empty($font_name))
			{
				// config.phpにデフォルト値が設定されていれば、
				// その値を使用します。
				$this->font_name = $font_name;
			}
			else
			{
				// config.phpにデフォルト値が設定されていなければ、
				// エラーとします。
				$this->result = false;
				$this->message = "フォントが設定されていません。";
				throw new Exception($this->message);
			}
			// インスタンス生成時にクレジットを書き込むかどうかが設定されていない場合で、
			if (!empty($write_credit))
			{
				// config.phpにデフォルト値が設定されていれば、
				// その値を使用します。
				$this->write_ok = $write_credit;
			}
			else
			{
				// config.phpにデフォルト値が設定されていなければ、
				// エラーとします。
				$this->result = false;
				$this->message = "クレジットを書き込むかどうかが設定されていません。";
				throw new Exception($this->message);
			}
			// クレジットを設定します。
			$this->credit = $cre;	
			// 指定されたアップロードファイル名を取得します。
			$this->upfile = $filenm;								// アップロードファイル名
	
			// アップロードするファイル名から拡張子を抜出します。
			preg_match("/\.[^.]*$/i", $this->upfile, $ext_tmp);			// extに拡張子
	
			// 拡張子を小文字に変換します。
			$this->ext = strtolower($ext_tmp[0]);
			// 拡張子のエラーチェックをします。
			if (empty($this->ext))
			{
				$this->result = false;
				$this->message = "ファイルの拡張子が付いていません。";
				throw new Exception($this->message);
			}
	
			// 拡張子が申請できる拡張子かどうかをチェックします。
			if ($this->ext != ".jpg" && $this->ext != ".jpeg" && $this->ext != ".png" && $this->ext != ".gif")
			{//&& $this->ext != ".gif"
				$this->result = false;
				$this->message = "申請できない種類のファイルです。（拡張子.jpeg、.jpg、.pngのみ申請可能です。）";
				throw new Exception($this->message);
			}
	
			// アップロードするファイル名が指定されているかチェックします。
			if (empty($this->upfile))
			{
				// アップロードするファイル名が空の場合は、エラーとします。
				$this->result = false;
				$this->message = "アップロードするファイルが指定されていません。";
				throw new Exception($this->message);
			}
			// アップロードするファイル名が指定されているかチェックします。
			if (!is_file($this->image_dir.$this->upfile))
			{
				// アップロードするファイル名が空の場合は、エラーとします。
				$this->result = false;
				//$this->message = "アップロードするファイルが存在していません。";
				$this->message = "イメージファイルが存在していません。";
				throw new Exception($this->message);
			}
			else {
				// アップロードするファイルサイズを取得します。
				$this->uploadsize = filesize($this->image_dir.$this->upfile);							// アップロードサイズ
			}
			// ファイルサイズか０かどうかチェックします。
			if ($this->uploadsize == 0)
			{
				// ファイルサイズが０の場合は、エラーとします。
				$this->result = false;
				$this->message = "ファイルサイズが０です。";
				throw new Exception($this->message);
			}

			// ファイルサイズが設定した最大値を超えているかチェックします。
			if ($this->uploadsize > $this->uploadconf['maxsize'])
			{
				// 設定した最大値を超えた場合は、エラーとします。
				$this->result = false;
				$this->message = "ファイルサイズが設定した最大値を超えています。";
				throw new Exception($this->message);
			}
		}
		
	/**
	 * ファイルをアップロードします。
	 */
		function upload()
		{
			// チェックでエラーが発生している場合は、例外をスローします。
			if ($this->result == false)
			{
				throw new Exception($this->message);
			}
			// 保存用ファイル名をYmdHis999.xxx（同じ拡張子）で生成します。
			$reg_time = time();												// 登録日時
			$rnd = rand(1, 10000);											// 乱数1-10000
			$this->dirno = rand(0, 9);										// ディレクトリ名（0-9）をランダムで決定します。
			$this->dirno .= "/";
			$this->svname = date("YmdHis", $reg_time) . $rnd;				// 保存ファイル名（元）
//			// 一旦、テンポラリーにアップしたファイルを保存します。
//			$tmppath = $this->uploadconf['temp_dir'].$this->svname;
//			copy($this->image_dir.$this->filename,$tmppath);
//			// 保存したファイルのタイプを取得します。
//			$type = exif_imagetype($this->uploadconf['temp_dir'].$this->svname);
//			if ($type == IMAGETYPE_GIF || $type == IMAGETYPE_JPEG || $type == IMAGETYPE_PNG)
//			{
				// ファイルタイプがGIF、JPEG、PNGだった場合はテンポラリー→アップロードディレクトリにファイルを移動します。
				$this->svfullpath = array();
				$this->svfullpath[] = $this->uploadconf['dir']. $this->dirno . $this->svname.$this->ext;
				copy($this->image_dir.$this->filename,$this->svfullpath[0]);
				// 画像のサイズを取得します。
				$this->img_width = array();
				$this->img_height = array();
				$size = @getimagesize($this->svfullpath[0]);
				list($width, $height, $type, $attr) = $size;
				$this->img_width[] = $width;
				$this->img_height[] = $height;
	
				// アップロードされたファイル名を設定します。
				$this->up_url = array();
				$this->up_url[] = $this->uploadconf['site_url'] . $this->svfullpath[0];
//			}
//			else
//			{
//				// ファイルタイプがそれ以外の場合はそのファイルを削除します。
//				unlink($tmppath);
//				$this->result = false;
//				$this->message = "アップロードしたファイルタイプがjpg,gif,png以外です。";
//				throw new Exception($this->message);
//			}	
			
		}
		
		/**
		 * 获取字符串中的数字
		 * 
		 * @param string $str
		 *            原字符串
		 * @return string $result
		 *            仅含数字的字符串
		 */
		function findNum($str = '')
		{
			$str = trim($str);
			if (empty($str)) {
				return '';
			}
			$result = '';
			for ($i = 0; $i < strlen($str); $i ++) {
				if (is_numeric($str[$i])) {
					$result .= $str[$i];
				}
			}
			return $result;
		}
		
		/**
		 * サムネイルを作成します。
		 *  元ファイルと縦・横同じ比率で作成します。
		 *    ※ bmpはGD関数無いため作成できません。
		 *
		 */
		function make_thumbfile($resize=true)
		{
			// チェックでエラーが発生している場合は、例外をスローします。
			if ($this->result == false)
			{
				throw new Exception($this->message);
			}
			// サムネイルを作成するときの元ファイルを決定します。
			$srcfilename = "";
			if (!empty($this->svfullpath[0]))
			{
				$srcfilename = $this->svfullpath[0];
			}
			else
			{
				$this->result = false;
				$this->message = "サムネイルを作成する元ファイルが指定されていません。";
				throw new Exception($this->message);
			}
	
			// サムネイルを作成するときの横幅が設定されているかチェックします。
			if (empty($this->flwidth))
			{
				$this->result = false;
				$this->message = "サムネイルを作成するときの横幅が指定されていません。";
				throw new Exception($this->message);
			}
	
			// サムネイルを作成するフォルダが設定されているかチェックします。
			if (empty($this->thumbdir))
			{
				$this->result = false;
				$this->message = "サムネイルを作成するフォルダが指定されていません。";
				throw new Exception($this->message);
			}
	
			// クレジット書込用フォント名が設定されているかチェックします。
			if (empty($this->font_name))
			{
				$this->result = false;
				$this->message = "クレジット書込用フォント名が指定されていません。";
				throw new Exception($this->message);
			}
	
			// クレジットを書き込むかどうかが設定されているかチェックします。
			if (empty($this->write_ok))
			{
				$this->result = false;
				$this->message = "クレジットを書き込むかどうかが指定されていません。";
				throw new Exception($this->message);
			}
			
			// 画像のサイズを取得します。
			$size = @getimagesize($srcfilename);
			list($width, $height, $type, $attr) = $size;


			// 設定されているサムネイルのサイズとフォルダの数を比較します。
			$szmax = count($this->flwidth);
			$dirmax = count($this->thumbdir);
			if ($dirmax < $szmax)
			{
				$this->result = false;
				$this->message = "サムネイルを保存するフォルダの数が足りません。";
				throw new Exception($this->message);
			}
	try{
			// サムネイルを作成するときの横幅が設定されている分だけ、サムネイルを作成します。
			for ($i = 0 ; $i < $szmax ; $i++)
			{
				if((int)$i == 3 || (int)$i == 2 || (int)$i == 5)
				{
					//thumb4
					if((int)$i == 3)
					{
						$photo_filename_th1 = $this->up_url[1];
						$tmp = substr($photo_filename_th1,strpos($photo_filename_th1,"./"));
						$tmp1 = str_replace("th1","th4",$tmp);
						$tmp2 = str_replace("thumb1","thumb4",$tmp1);
					} elseif((int)$i == 2) {//thumb3
						$photo_filename_th2 = $this->up_url[2];
						$tmp = substr($photo_filename_th2,strpos($photo_filename_th2,"./"));
						$tmp1 = str_replace("th2","th3",$tmp);
						$tmp2 = str_replace("thumb2","thumb3",$tmp1);
					}
					elseif((int)$i == 5) {//thumb6
					    $photo_filename_th5 = $this->up_url[5];
					    $tmp = substr($photo_filename_th5,strpos($photo_filename_th5,"./"));
					    $tmp1 = str_replace("th5","th6",$tmp);
					    $tmp2 = str_replace("thumb5","thumb6",$tmp1);
					}
					
					$size = @getimagesize($tmp);
					list($width, $height, $type, $attr) = $size;

					// 縦・横の比率を合わせて、サムネイル用の縦、横を計算します。
					$thumb_width = $this->flwidth[$i];
					if($thumb_width == 0 || (int)$width < $thumb_width||$resize===false)
					{
						$thumb_width = $width;
					}
					$thumb_height = ($thumb_width / $width) * $height;
					// 画像サイズをセットします。
					$this->img_width[] = $thumb_width;
					$this->img_height[] = $thumb_height;
					// フォントサイズを決定します。
					if((int)$i == 3)
					{
						if($width == 400)
						{
							$font_size = 88;
						} elseif($width == 800) {
							$font_size = 168;
						} elseif($width == 200) {
							$font_size = 38;
						}
					} elseif((int)$i == 2) {//thumb3
						$font_size = 38;
					}
					elseif((int)$i == 5) {//thumb6
					    $font_size = 168;
					}
					if($resize)
					{
						$cre_str ="SAMPLE";
					}
					else
					{
						$cre_str ="";
					}
		
					// 画像のタイプに合わせて、サムネイルを作成します。
					if ($type == IMAGETYPE_JPEG)
					{
						if($resize===false)
						{
							copy($tmp,$tmp2);
//							$cmd = "cp ".$tmp." ".$tmp2;
//							exec($cmd);
						} else {
							// アップロードしたファイルを読み込みます。
							$ufimage = @ImageCreateFromJPEG($tmp);
							// 空のサムネイル画像を作成します。
							$thumb = @ImageCreateTrueColor($thumb_width, $thumb_height);
							// 空のサムネイル画像にアップロードしたファイルをコピーします。
							@imagecopyresampled($thumb, $ufimage, 0, 0, 0, 0, $thumb_width, $thumb_height, $width, $height);
							// 画像にクレジットを書き込みます。
							$thumb = $this->write_credit2($thumb, $cre_str, $font_size, $thumb_width, $thumb_height);
							@imagejpeg($thumb, $tmp2);
						}
					} else if ($type == IMAGETYPE_GIF){
						if($resize===false)
						{
							copy($tmp,$tmp2);
//							$cmd = "cp ".$tmp." ".$tmp2;
//							exec($cmd);
						} else {
							$retflg = $this->IsAnimatedGif($tmp);
							//アニメの場合
							if($retflg == 1)
							{
								$this->imagick_gif_thumb($tmp,$tmp2,$thumb_width,$thumb_height);
							} else {
								// アップロードしたファイルを読み込みます。
								$ufimage = @ImageCreateFromGIF($tmp);
								// 空のサムネイル画像を作成します。
								$thumb = @ImageCreateTrueColor($thumb_width, $thumb_height);
								// 空のサムネイル画像にアップロードしたファイルをコピーします。
								@imagecopyresampled($thumb, $ufimage, 0, 0, 0, 0, $thumb_width, $thumb_height, $width, $height);
								// 画像にクレジットを書き込みます。
								$thumb = $this->write_credit2($thumb, $cre_str, $font_size, $thumb_width, $thumb_height);
								@imagegif($thumb, $tmp2);
							}
						}
					} else if ($type == IMAGETYPE_PNG){
						if($resize===false)
						{
							copy($tmp,$tmp2);
//							$cmd = "cp ".$tmp." ".$tmp2;
//							exec($cmd);
						} else {
							// アップロードしたファイルを読み込みます。
							$ufimage = @ImageCreateFromPNG($tmp);
							// 空のサムネイル画像を作成します。
							$thumb = @ImageCreateTrueColor($thumb_width, $thumb_height);
							// 空のサムネイル画像にアップロードしたファイルをコピーします。
							@imagecopyresampled($thumb, $ufimage, 0, 0, 0, 0, $thumb_width, $thumb_height, $width, $height);
							// 画像にクレジットを書き込みます。
							$thumb = $this->write_credit2($thumb, $cre_str, $font_size, $thumb_width, $thumb_height);
							@imagepng($thumb, $tmp2);
						}
					}

					// アップロードされたファイル名を設定します。
					$this->svfullpath[] = $tmp2;
					$this->up_url[] = $this->uploadconf['site_url'] . $tmp2;
	
					if($resize===true)
					{
						// 画像を破棄します。
						@imagedestroy($ufimage);
						@imagedestroy($thumb);
					}
				} else {
			    	// 画像のサイズを取得します。
			    	$size = @getimagesize($srcfilename);
			    	list($width, $height, $type, $attr) = $size;
					// 縦・横の比率を合わせて、サムネイル用の縦、横を計算します。
					$thumb_width = $this->flwidth[$i];
					if($thumb_width == 0 || (int)$width < $thumb_width||$resize===false)
					{
						$thumb_width = $width;
					}
					
					//th编号
					$thNum = $this->findNum($this->thumbdir[$i]);
					if (empty($thNum)) {
					    $thNum = strval($i + 1);
					}
					
					$thumb_height = ($thumb_width / $width) * $height;
					// 画像サイズをセットします。
					$this->img_width[] = $thumb_width;
					$this->img_height[] = $thumb_height;

	
					// 画像のタイプに合わせて、サムネイルを作成します。
					if ($type == IMAGETYPE_JPEG)
					{
						if($resize===false)
						{
							$thfilename = $this->thumbdir[$i] . $this->dirno . $this->svname . "th" . $thNum . $this->ext;
							$cmd = "cp ".$srcfilename." ".$thfilename;
							exec($cmd);
						} else {
							// アップロードしたファイルを読み込みます。
							$ufimage = @ImageCreateFromJPEG($srcfilename);
	
							// 空のサムネイル画像を作成します。
							$thumb = @ImageCreateTrueColor($thumb_width, $thumb_height);
	
							// 空のサムネイル画像にアップロードしたファイルをコピーします。
							@imagecopyresampled($thumb, $ufimage, 0, 0, 0, 0, $thumb_width, $thumb_height, $width, $height);
	
							// クレジットを書き込みます。
							//if ($this->write_ok[$i] == true)
							if ($this->write_ok[$i] == true && !empty($this->credit) && strlen($this->credit) > 0)
							{
								// フォントサイズを決定します。
								$font_size = $this->decide_fontsize($thumb_width);
	
								// 画像にクレジットを書き込みます。
								$thumb = $this->write_credit($thumb, $this->credit, $font_size, $thumb_width, $thumb_height);
							}
							// ファイルを保存します。
							$thfilename = $this->thumbdir[$i] . $this->dirno . $this->svname . "th" . $thNum . $this->ext;
							@imagejpeg($thumb, $thfilename);
						}
					} else if ($type == IMAGETYPE_GIF){
						if($resize===false)
						{
							$thfilename = $this->thumbdir[$i] . $this->dirno . $this->svname . "th" . $thNum . $this->ext;
//							$cmd = "cp ".$srcfilename." ".$thfilename;
//							exec($cmd);
							copy($srcfilename,$thfilename);
							$thfilename = $this->thumbdir[$i] . $this->dirno . $this->svname . "th" . $thNum . $this->ext;
							$this->imagick_gif_thumb($srcfilename,$thfilename,$thumb_width,$thumb_height);
						} else {
							$retflg = $this->IsAnimatedGif($srcfilename);
							//静的な場合
							if($retflg == 0)
							{
								// アップロードしたファイルを読み込みます。
								$ufimage = @ImageCreateFromGIF($srcfilename);
	
								// 空のサムネイル画像を作成します。
								$thumb = @ImageCreateTrueColor($thumb_width, $thumb_height);
	
								// 空のサムネイル画像にアップロードしたファイルをコピーします。
								@imagecopyresampled($thumb, $ufimage, 0, 0, 0, 0, $thumb_width, $thumb_height, $width, $height);
	
								// クレジットを書き込みます。
								//if ($this->write_ok[$i] == true)
								if ($this->write_ok[$i] == true && !empty($this->credit) && strlen($this->credit) > 0)
								{
									// フォントサイズを決定します。
									$font_size = $this->decide_fontsize($thumb_width);
	
									// 画像にクレジットを書き込みます。
									$thumb = $this->write_credit($thumb, $this->credit, $font_size, $thumb_width, $thumb_height);
								}
								// ファイルを保存します。
								$thfilename = $this->thumbdir[$i] . $this->dirno . $this->svname . "th" . $thNum . $this->ext;
								@imagegif($thumb, $thfilename);
							} else {
								$thfilename = $this->thumbdir[$i] . $this->dirno . $this->svname . "th" . $thNum . $this->ext;
								$this->imagick_gif_thumb($srcfilename,$thfilename,$thumb_width,$thumb_height);
							}
						}
					} else if ($type == IMAGETYPE_PNG){
						if($resize===false)
						{
							$thfilename = $this->thumbdir[$i] . $this->dirno . $this->svname . "th" . $thNum . $this->ext;
							copy($srcfilename,$thfilename);
//							$cmd = "cp ".$srcfilename." ".$thfilename;
//							exec($cmd);
						} else {
							// アップロードしたファイルを読み込みます。
							$ufimage = @ImageCreateFromPNG($srcfilename);
	
							// 空のサムネイル画像を作成します。
							$thumb = @ImageCreateTrueColor($thumb_width, $thumb_height);
	
							// 空のサムネイル画像にアップロードしたファイルをコピーします。
							@imagecopyresampled($thumb, $ufimage, 0, 0, 0, 0, $thumb_width, $thumb_height, $width, $height);
	
							// クレジットを書き込みます。
							//if ($this->write_ok[$i] == true)
							if ($this->write_ok[$i] == true && !empty($this->credit) && strlen($this->credit) > 0)
							{
								// フォントサイズを決定します。
								$font_size = $this->decide_fontsize($thumb_width);
	
								// 画像にクレジットを書き込みます。
								$thumb = $this->write_credit($thumb, $this->credit, $font_size, $thumb_width, $thumb_height);
							}
							// ファイルを保存します。
							$thfilename = $this->thumbdir[$i] . $this->dirno . $this->svname . "th" . $thNum . $this->ext;
							@imagepng($thumb, $thfilename);
						}
					}
					// アップロードされたファイル名を設定します。
					$this->svfullpath[] = $thfilename;
					$this->up_url[intval($thNum)] = $this->uploadconf['site_url'] . $thfilename;
					if($resize===true)
					{
						// 画像を破棄します。
						@imagedestroy($ufimage);
						@imagedestroy($thumb);
					}
				}
			}
		}
		catch (Exception $ex)
		{
			$this->result = false;
			$this->message = $ex->getMessage();
			throw new Exception($this->message);
			
		}
	}
	function set_write_ok($write_ok)
	{
		$this->write_ok = $write_ok;
	}
function write_credit($img, $cre_str, $fsize, $width_i, $height_i)
	{
		// クレジット書き込み用の設定を行います。
		// 書き込み角度を設定します。
		$font_angle = 0;

		//GD環境情報を取得します。
		$arrInfo = gd_info();

		// 書き込むクレジットを設定します。
		$telop_text = "";
		if ($arrInfo['JIS-mapped Japanese Font Support']) {
			// GDが対応している場合はUTF-8への変換は不要です。
			$telop_text = $cre_str;
		}
		else
		{
			// 組込みテキスト
			// GDが対応していない場合はUTF-8へ変換します。（UTF-8に変換しない場合、文字化けします。）
			$telop_text =  mb_convert_encoding($cre_str, "UTF-8", "auto");
		}

		//yupengbo add 20101208 start
		$str_len = mb_strlen($telop_text);
		if($str_len > 16)
		{
			for($i=2;$i>0;$i--)
			{
				// 半透明のグレーバック表示位置
				$alpha_x1 = 5;
				$alpha_x2 = $width_i - 5;

				$alpha_y1 = $height_i - ($fsize + 10) - 5;
				if($i==2) $alpha_y1 = $height_i - ($fsize + 10) - 25;
				$alpha_y2 = $height_i - 5;

				// クレジット書き込み位置
				$tx = $alpha_x1 + 5;
				$ty = $alpha_y1 + $fsize + 5;

				// テキストカラー（黒）
				$font_color_b = ImageColorAllocate ($img, 0, 0, 0);
				// テキストカラー（白）
				$font_color_w = ImageColorAllocate ($img, 255, 255, 255);
				// アルファチャンネル（グレー）
				$alpha = imagecolorallocatealpha($img, 0, 0, 0, 90);

				// 画像の一部を透かしイメージにします。
				imagefilledrectangle ($img , $alpha_x1 , $alpha_y1, $alpha_x2, $alpha_y2, $alpha);

				if($i==2) $tmp_telop_text = mb_substr($telop_text,0,16,"utf-8");
				if($i==1) $tmp_telop_text = mb_substr($telop_text,16,16,"utf-8");

				//テキスト描画
				ImageTTFText($img, $fsize, $font_angle, $tx, $ty, $font_color_w, $this->font_name, $tmp_telop_text);
				ImageTTFText($img, $fsize, $font_angle, $tx, $ty, $font_color_w, $this->font_name, $tmp_telop_text);
			}
			  //wangtongchao 2011/08/23 change start tianjiaif()puanduan
		} elseif($str_len > 0) {
			  //wangtongchao 2011/08/23 change end
		//yupengbo add 20101209 end
			// 半透明のグレーバック表示位置
			$alpha_x1 = 5;
			$alpha_x2 = $width_i - 5;

			$alpha_y1 = $height_i - ($fsize + 10) - 5;
			$alpha_y2 = $height_i - 5;

			// クレジット書き込み位置
			$tx = $alpha_x1 + 5;
			$ty = $alpha_y1 + $fsize + 5;

			// テキストカラー（黒）
			$font_color_b = ImageColorAllocate ($img, 0, 0, 0);
			// テキストカラー（白）
			$font_color_w = ImageColorAllocate ($img, 255, 255, 255);
			// アルファチャンネル（グレー）
			$alpha = imagecolorallocatealpha($img, 0, 0, 0, 90);

			// 画像の一部を透かしイメージにします。
			imagefilledrectangle ($img , $alpha_x1 , $alpha_y1, $alpha_x2, $alpha_y2, $alpha);

			//テキスト描画
			ImageTTFText($img, $fsize, $font_angle, $tx, $ty, $font_color_w, $this->font_name, $telop_text);
			ImageTTFText($img, $fsize, $font_angle, $tx, $ty, $font_color_w, $this->font_name, $telop_text);
		}
		return $img;
	}
	
	function write_credit2($img, $cre_str, $fsize, $width_i, $height_i)
	{
		// クレジット書き込み用の設定を行います。
		// 書き込み角度を設定します。
		$font_angle = 0;

		//GD環境情報を取得します。
		$arrInfo = gd_info();

		// 書き込むクレジットを設定します。
		$telop_text = "";
		if ($arrInfo['JIS-mapped Japanese Font Support']) {
			// GDが対応している場合はUTF-8への変換は不要です。
			$telop_text = $cre_str;
		}
		else
		{
			// 組込みテキスト
			// GDが対応していない場合はUTF-8へ変換します。（UTF-8に変換しない場合、文字化けします。）
			$telop_text =  mb_convert_encoding($cre_str, "UTF-8", "auto");
		}

		// 半透明のグレーバック表示位置
		$alpha_x1 = 5;
		$alpha_x2 = $width_i - 5;

		$alpha_y1 = $height_i - ($fsize + 10) - 5;
		$alpha_y2 = $height_i - 5;

		// クレジット書き込み位置
		if($width_i == 200)
		{
			$tx = $alpha_x1 + 22;
			$ty = 92;
		}
		if($width_i == 400)
		{
			$tx = $width_i/2-170;
			$ty = $height_i/2+35;
		}
		if($width_i == 800)
		{
			$tx = $width_i/2-334;
			$ty = $height_i/2+65;
		}

		// アルファチャンネル（グレー）
		$alpha = imagecolorallocatealpha($img, 255, 255, 255, 100);

		//テキスト描画
		ImageTTFText($img, $fsize, $font_angle, $tx, $ty, $alpha, "./sazanami-gothic.ttf", $telop_text);
		ImageTTFText($img, $fsize, $font_angle, $tx, $ty, $alpha, "./sazanami-gothic.ttf", $telop_text);

		return $img;
	}
	/**
	 * クレジット書き込み用のフォントサイズを決定します。
	 */
	function decide_fontsize($thwidth)
	{
		global $credit_fontsize;

		// クレジット書込用フォントサイズが設定されているかチェックします。
		if (count($credit_fontsize)<6)
		{
			$this->result = false;
			$this->message = "クレジット書込用フォントサイズが指定されていません。";
			throw new Exception($this->message);
		}
		// クレジット書込用フォントサイズを決定します。
		if ($thwidth <= 160)
		{
			$font_size = $credit_fontsize[0];
		}
		else if ($thwidth <= 320)
		{
			$font_size = $credit_fontsize[1];
		}
		else if ($thwidth <= 480)
		{
			$font_size = $credit_fontsize[2];
		}
		else if ($thwidth <= 640)
		{
			$font_size = $credit_fontsize[3];
		}
		else if ($thwidth <= 800)
		{
			$font_size = $credit_fontsize[4];
		}
		else
		{
			$font_size = $credit_fontsize[5];
		}

		return $font_size;
	}
	function imagick_gif_thumb($srcfilename,$descfilename,$newW,$newH)
	{
		$src = new Imagick($srcfilename);
		$dest = new Imagick();
		$colorTransparent = new ImagickPixel("transparent");
		foreach($src as $img)
		{
		    $imageInfo = $img->getImagePage();
		    $tmp = new Imagick();

		    $tmp->newImage($imageInfo['width'], $imageInfo['height'], $colorTransparent, 'gif');
		    $tmp->compositeImage($img, Imagick::COMPOSITE_OVER, $imageInfo['x'], $imageInfo['y']);
		    $tmp->thumbnailImage($newW,$newH, true);

		    $dest->addImage($tmp);
		    $dest->setImagePage($tmp->getImageWidth(), $tmp->getImageHeight(), 0, 0);
		    $dest->setImageDelay($img->getImageDelay());
		    $dest->setImageDispose($img->getImageDispose());
		}
		$dest->coalesceImages();
		$dest->writeImages($descfilename, true);
		$dest->clear();
	}
	function IsAnimatedGif($filename)
	{
		$fp=fopen($filename, 'rb');
		$filecontent=fread($fp, filesize($filename));
		fclose($fp);
		return strpos($filecontent,chr(0x21).chr(0xff).chr(0x0b).'NETSCAPE2.0')===FALSE?0:1;
	}
}
?>
