<?php

/*
########################################################################
#
#	お宿
#　　CSVの問い合わせ番号でDBから顧客番号（cid）取得しCSV作成・ダウンロード
#
########################################################################*/

header('Content-Type: text/html; charset=UTF-8');
date_default_timezone_set("Asia/Tokyo");
ini_set('memory_limit', '2048M');

/*
担当部署コード,担当部署名,問い合わせ番号,商品番号,パンフレットコード,宿泊日,エリア２,エリア３,エリア４,エリア２コード,エリア３コード,エリア４コード,施設コード,施設名,部屋名,部屋タイプ名,部屋タイプＩＤ,プラン名,媒体名,媒体コード,予約日,キャンセル日,顧客No,参加者漢字氏名,参加者カナ氏名,性別,都道府県,PAX区分,取扱額,手数料・システム利用料,協力費,１人部屋,２人部屋,３人部屋,４人部屋,５人部屋,６人部屋,７人部屋,８人部屋,９人部屋,１０人部屋,１１人部屋以上,旅行形態,交通手段,チェックイン時間,回答方法,希望時刻,受付担当者コード,受付営業所コード,ステイタス,確認手配,客→施設,施設→客,プラン区分,cid
*/
 

class oyadoData{
	
	function __construct($_FILES) {
	//function __construct() {
		//ファイルデータ
		$this->file_name='';
		$this->downfile_name='';
		$this->FileDir ='/home/xhankyu/public_html/photo_db/webtool/oyadoIDNO/data/';
		
			$file = $this->FileDir.'1709照合前.csv';
					

		/*if (is_uploaded_file($_FILES["upfile"]["tmp_name"])) {
			$file_tmp_name = $_FILES["upfile"]["tmp_name"];
			$file_name = $_FILES["upfile"]["name"];
			$this->file_name = $file_name;
  
			if (pathinfo($file_name, PATHINFO_EXTENSION) != 'csv') {
				$err_msg = 'CSVファイルのみ対応しています。';
			} else{
				$this->DirCleanling();
				if (move_uploaded_file($file_tmp_name, $this->FileDir. $file_name)) {
					//後で削除できるように権限を644に
					chmod($this->FileDir . $file_name, 0644);
					$msg = $file_name . "をアップロードしました。";
					$file = $this->FileDir.$file_name;
					$org=$this->getCsvFileData($file);
					
	
				} else {
				  $err_msg = "ファイルをアップロードできません。";
				}
			}
		} else {
			$err_msg = "ファイルが選択されていません。";
		}*/

		
		$org=$this->getCsvFileData($file);
		$this->csvDataAry='';
		$this->outDataAry='';


		if(is_array($org)){
			
			$this->setData($org);//アップされたエクセルを配列に生成
		}
		else{
			echo '有効なcsvではありません：'.$time = date("Y/m/d H:i:s.")."<br>";
			echo  $msg."<br>";
			exit;
		}

		if(is_array($this->csvDataAry)){
			//sqlリクエスト
			$this->getCid();
		}
		else{
			echo '有効なcsvではありません：'.$time = date("Y/m/d H:i:s.")."<br>";
			exit;
		}
/*
		if(is_array($this->outDataAry)){
			$this->MakeCsv();
		}else{
			echo '有効なcsvではありません：'.$time = date("Y/m/d H:i:s.")."<br>";
			exit;
		}*/
		
		
	}
	
	function getCid(){
		
		//DBサーバ
		$SERVER	  = "10.254.2.63";// データベースのサーバーIP
		$DB_NAME  = "ximage";// データベース名前	
		$DB_USR   = "ximage";// データベースのユーザー	
		$DB_PSD   = "kCK!7wu4";// データベースのパスワード
		//oyado_cid_bookingno　//テーブルネーム
	
		$this->dbdata='';
		//DB接続
		try {
			$pdo = new PDO('mysql:host='.$SERVER.';dbname='.$DB_NAME.';charset=utf8',$DB_USR ,$DB_PSD);	
			$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);//PDOオブジェクト自体に指定。レスポンスは常に連
			
			$sth = $pdo->prepare('select * from oyado_cid_bookingno');
			//$sth = $pdo->prepare($sql);
			$sth->execute();
			$this->dbdata = $sth->fetchAll();
			
			//$sql = "DELETE FROM oyado_cid_bookingno WHERE DATE < CURRENT_TIMESTAMP + INTERVAL - 7 HOUR";
			$sql = "DELETE FROM oyado_cid_bookingno WHERE DATE < CURRENT_TIMESTAMP + INTERVAL - 12 MONTH";//1年前削除レコード
			$stmt = $pdo->prepare($sql);
			$stmt->execute();
			//$this->dd = $stmt->fetchAll();
			$pdo = null;
			/*echo "<pre>";
print_r($this->dd);
echo "</pre>";*/
			

		} catch (PDOException $e) {
			$error = new ErrorMes;
			$error->WriteMes('データベース接続に失敗しました' . $e->getMessage());
			exit;
		}
	/*	echo "<pre>";
print_r($this->dbdata);
echo "</pre>";*/
		$this->MakeCsv();
		/*
		$this->outDataAry='';
		if(is_array($this->dbdata)){
			
			foreach($this->csvDataAry as $i => &$dataAry){
				if(!empty($dataAry['問い合わせ番号'])){
					
					$bookingno = trim($dataAry['問い合わせ番号']);
					$B = trim($dbAry['BOOKINGNO']);
					//sql書く
					foreach($this->dbdata as $dbAry){
						if($B == $bookingno){
							echo $bookingno;
							$dataAry['cid']=$dbAry['CID'];
							break;
						}
					}
					$this->outDataAry[$i] = $dataAry;
				}
				else{
					continue;
				}
			}
		}*/
		/*
		echo "<pre>";
print_r($this->outDataAry);
echo "</pre>";*/
	
	}
	
	function DirCleanling(){
		
		//バックアップファイルを削除
		$path = $this->FileDir."*.csv";	
		$rmTime = time() - (1 * 24 * 60 * 60);
                  			// 1 日 * 24 時間 * 60 分 * 60 秒; // 1日前の時間を求める
		foreach (glob($path) as $filename) {
			// 1日より前のファイルなら
			if (filemtime($filename) < $rmTime) {
				// 削除
				@unlink($filename);
			}
		
		}
	}

	
	function getCsvFileData($file){
		// 処理時間の上限設定を緩和
		ini_set("max_execution_time","360000");
		if ( file_exists($file)) {
			$handle = fopen($file, "r");
			if ($handle) {
				while (!feof($handle)) {
						$buffer = trim(fgets($handle, 9999));
						if(!empty($buffer)){
							//$dataAry = explode("\t", $buffer);
							$dataAry = explode(",", $buffer);
							mb_convert_variables('utf-8', 'SJIS',$dataAry);
							$csv[] = $dataAry;
						}
				}
			}
			fclose($handle);

			return $csv;
		}
		else{
			return false;
		}
	}
	
	function MakeCsv(){
		ini_set("max_execution_time","360000");
		
		$n=0;
		$itme='';
		$outdata='';

		foreach($this->dbdata as $n => $ary){
			$trimdata='';
			$data='';
			foreach($ary as $key => $d){
				
				if($n==0){
					$itme .= $key.",";
				}
				//else{
				$data .=$d.",";
				//}
			}
			if(!empty($data)){
				$trimdata = rtrim($data,',');
				$outdata .= $trimdata."\n";
			}
			$n++;
		}

		if(!empty($outdata)){
			$OutData = $itme."\n".$outdata;
			$Data = mb_convert_encoding($OutData ,"SJIS", "UTF-8");
			$OutFile = $this->FileDir.'db.csv';
			//$OutFile = '/home/xhankyu/public_html/photo_db/webtool/oyadoIDNO/data/ht_web_hk_extract_cid.csv';
			if (!$handle = fopen($OutFile, 'w')){
				echo "Cannot open file ($OutFile)";
				exit;
			}
			if (fwrite($handle, $Data) === FALSE) {
				echo "Cannot write to file ($OutFile)";
				exit;
			}
			fclose($handle);
			$this->downfile_name = $this->file_name;
		}
		
	}
	

	
			
	function setData($obj){	

		$dataAry='';
		foreach($obj as  $n  =>$ary){

				if($n==0){
					if($ary[2]=='問い合わせ番号'){
						$keyAry='';
						foreach($ary as $k =>$data){
							$a = trim($data);
							if(!empty($a)){
								$keyAry[$k]=$a;
							}
						}
						//顧客番号の項目追加
						if(is_array($keyAry)){
							array_push($keyAry,'cid');
						}
					}
					else{
						return false;
					}
			
				}
				else{
					$dataary ='';
					foreach($keyAry as $i =>$d){
						if(isset($ary[$i])){
							$dataary[$d] = $ary[$i];
						}
						 else{
							$dataary[$d] = '';
						}
					}	   
					$dataAry[]=$dataary;	
				}

			}
			$this->csvDataAry = $dataAry;
			unset($obj);
			unset($keyAry);
			unset($dataAry);
	}
	
	
}
	

?>
<!--
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta name="robots" content="noindex" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>お宿プログラム</title>
<link rel="stylesheet" type="text/css" href="style.css" />
<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
<script src="https://code.jquery.com/ui/1.12.0/jquery-ui.js"></script>

<link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/themes/smoothness/jquery-ui.css">

</head>
<body>

<div id="container">
<div class="contents pt150">
	<div class="inner">
		<section>
			<h1 class="type1"><span class="color1">お宿</span>プログラム</h1>
		</section>
	</div>
</div>


<div class="contents bg1">
	<div class="inner">
		<section id="new">
			<h2 id="newinfo_hdr" class="close">実績データ（CSVファイル）をアップロード</h2>
			<form method="post" enctype="multipart/form-data">
			<input name="upfile" type="file" size="100"><input class="btnDisabled" type="submit" value="アップロード">
			<p>※一行目は、項目行。項目列「問い合わせ番号」が３列目にあることが必須。</p>
			</form>
			
			<font color="red" id="message" ></font>			
		</section>
-->
		
<?php
/*if(count($_FILES)> 0){	
	if($_FILES ["upfile"]["size"]>0){
		$o=new oyadoData($_FILES);
		
		if(!empty($o->downfile_name)){
echo <<<EOD
<section>
<h2>「問い合わせ番号」をキーに照合し、双方にデータがあった行末に、cidの値を付与したものをダウンロード</h2>
<form id="fDownload" name="fDownload" action="download_data.php?file={$o->downfile_name}" method="post">
<input class="" type="button" onclick="document.fDownload.submit();" value="ダウンロード">
</form>
</section>
EOD;
			
		}
	}
	else{
		echo<<< EOD
<script type="text/javascript">
	$('#message').html( "ファイルが選択されていません" );
</script>
EOD;
		
	}
}
else{
	echo<<< EOD
<script type="text/javascript">
	$('#message').html( "ファイルが選択されていません" );
</script>
EOD;
	
}	*/
		new oyadoData($_FILES);
?>
<!--
</div>
</div>

<footer>
<small>Copyright&copy; BUD Internaitonal Allrights reserved.</small>
</footer>

</div>


<script type="text/javascript">


$(function(){
	$("form").submit(function() {
		$(".btnDisabled").attr( "disabled" , true );
		$('#message').html( "<b>処理中...</b>" );
	});
});
</script>


</body>
</html>-->