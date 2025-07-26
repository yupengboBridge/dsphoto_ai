<?php

/*
########################################################################
#
#	料金チェックプログラム　
#　　エクセルでアップロードされたファイルを元に専門店リクエストの比較チェックベースファイル作成
#
########################################################################*/
header('Content-Type: text/html; charset=UTF-8');
date_default_timezone_set("Asia/Tokyo");
//ini_set('memory_limit', '2048M');
@ini_set('memory_limit', -1);

//ベースファイルが新しくなったらこれを使う
class priceCheck{

	function __construct() {

		//アップロードされたエクセルデータファイルデータ
		$this->baseFileDir ='/home/xhankyu/public_html/photo_db/webtool/PriceCheck/data_test/';
		$this->baseFileAry  = array(
			'i' => 'base_i.csv',
			'd' => 'base_d.csv',
			'bus' => 'base_bus.csv',
			'taiwan' => 'base_taiwan.csv',
			'korea' => 'base_korea.csv',
			'hawaii' => 'base_hawaii.csv'
		);

		//比較チェックファイル
		$this->checkFileDir ='/home/xhankyu/public_html/photo_db/webtool/PriceCheck/data_test/';
		$this->checkFileAry  = array(
			'i' => 'base_price_check_i.csv',
			'd' => 'base_price_check_d.csv',
			'bus' => 'base_price_check_bus.csv',
			'taiwan' => 'base_price_check_taiwan.csv',
			'korea' => 'base_price_check_korea.csv',
			'hawaii' => 'base_price_check_hawaii.csv'
		);

		echo 'start：'.$time = date("Y/m/d H:i:s.")."\n";
		include_once( dirname( __FILE__ ) . '/Classes/PHPExcel.php' );
		include_once( dirname( __FILE__ ) . '/Classes/PHPExcel/IOFactory.php' );
		$cacheMethod = PHPExcel_CachedObjectStorageFactory::cache_to_phpTemp;
		$cacheSettings = array('dir' => '/tmp');
		PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);

		$extension='';
		$excelfilepath='';
		//アップロードファイル名
		preg_match('/[^.]+$/', $_FILES["upfile"]["name"], $tmp);
		$extension = $tmp[0];
		$excelfilepath = $_FILES["upfile"]["tmp_name"];

		//ファイル拡張子によってリーダーインスタンスを変える
		$reader = null;
		$msg = $this->importExcel($extension,$excelfilepath);
		$this->setDataAry='';
		$this->outDataAry='';


		if(is_array($msg)){
	
			$this->setData($msg);//アップされたエクセルを配列に生成
		}
		else{
			echo 'no file end：'.$time = date("Y/m/d H:i:s.")."\n";
			echo  $msg."\n";
		}


		if(is_array($this->setDataAry)){
			$this->MakeCheckCsv();//ベースチェック用のcsv作成
			$time = date("Y/m/d H:i:s.");
			echo 'Completion　csv'.$time."\n";
			exit;
		}else{
			$time = date("Y/m/d H:i:s.");
			echo 'NO getPrice data'.$time."\n";
		}

	}

	function MakeCheckCsv(){
		$n=0;
		$iItem = array(
			'Target campaign',
			'Target ad group',
			'Target keyword text',
			'Target keyword match type',
			'title1(text)',
			'tour_price(text)',
			'元URL',
			'rqUrl',
			'thedate',
			'naigai'
		);
		$dItem =array(
			'Target campaign',
			'Target ad group',
			'Target keyword text',
			'Target keyword match type',
			'価格(text)',
			'目的地(text)',
			'目的地の都道府県(text)',
			'URL',
			'rqUrl',
			'thedate',
			'naigai'
		);

		$busItem =array(
			'Target location',
			'出発地(text)',
			'tour_price(text)',
			'Target location restriction',
			'URL',
			'rqUrl',
			'thedate',
			'naigai'
		);
		$taiwanItem = array(
			'Target campaign',
			'Target ad group',
			'Target keyword text',
			'Target keyword match type',
			'title1(text)',
			'tour_price(text)',
			'Target location',
			'出発地(text)',
			'Target location restriction',
			'URL',
			'rqUrl',
			'thedate',
			'naigai'
		);
		$koreaItem = array(
			'Target campaign',
			'Target ad group',
			'title1(text)',
			'tour_price(text)',
			'Target location',
			'出発地(text)',
			'Target location restriction',
			'URL',
			'rqUrl',
			'thedate',
			'naigai'
		);
		$hawaiiItem = array(
			'Target campaign',
			'Target ad group',
			'title1(text)',
			'tour_price(text)',
			'Target location',
			'出発地(text)',
			'Target location restriction',
			'URL',
			'rqUrl',
			'thedate',
			'naigai'
		);

		$n=0;
		$itme='';
		$outdata='';

		foreach($this->setDataAry as $i => $ary){
			$trimdata='';
			$data='';
			if($this->naigai=='i'){
				foreach($iItem as $i){
					if($n==0){
						$itme .= $i."\t";
						//海外ファイル名
						$filename =$this->checkFileAry['i'];
						$data .=$ary[$i]."\t";
					}
					else{
						$data .=$ary[$i]."\t";
					}
				}
			}
			elseif($this->naigai=='d'){

				foreach($dItem as $d){
					if($n==0){
						$itme .=$d."\t";
						//国内ファイル名
						$filename =$this->checkFileAry['d'];
						$data .=$ary[$d]."\t";

					}
					else{
						$data .=$ary[$d]."\t";
					}
				}

			}
			elseif($this->naigai=='bus'){

				foreach($busItem as $b){
					if($n==0){
						$itme .=$b."\t";
						//国内ファイル名
						$filename =$this->checkFileAry['bus'];
						$data .=$ary[$b]."\t";

					}
					else{
						$data .=$ary[$b]."\t";
					}
				}

			}
			elseif($this->naigai=='taiwan'){
				foreach($taiwanItem as $i){
					if($n==0){
						$itme .= $i."\t";
						//海外ファイル名
						$filename =$this->checkFileAry['taiwan'];
						$data .=$ary[$i]."\t";
					}
					else{
						$data .=$ary[$i]."\t";
					}
				}
			}
			elseif($this->naigai=='korea'){
				foreach($koreaItem as $i){
					if($n==0){
						$itme .= $i."\t";
						//海外ファイル名
						$filename =$this->checkFileAry['korea'];
						$data .=$ary[$i]."\t";
					}
					else{
						$data .=$ary[$i]."\t";
					}
				}
			}
			elseif($this->naigai=='hawaii'){
				foreach($hawaiiItem as $i){
					if($n==0){
						$itme .= $i."\t";
						//海外ファイル名
						$filename =$this->checkFileAry['hawaii'];
						$data .=$ary[$i]."\t";
					}
					else{
						$data .=$ary[$i]."\t";
					}
				}
			}
			if(!empty($data)){
				$trimdata = trim($data);
				$outdata .= $trimdata."\n";
			}
			$n++;
		}

		if(!empty($outdata)){
			$outdata = trim($itme)."\n".$outdata;
			$OutData = mb_convert_encoding($outdata,"SJIS", "UTF-8");
			$OutFile = $this->checkFileDir.$filename;

			if (!$handle = fopen($OutFile, 'w')) {
				echo "Cannot open file ($OutFile)";
				exit;
			}
			if (fwrite($handle, $OutData) === FALSE) {
				echo "Cannot write to file ($OutFile)";
				exit;
			}
			fclose($handle);
		}
	}

	function getFile($file){
		// 処理時間の上限設定を緩和
		ini_set("max_execution_time","360000");
		$csv='';
		if ( file_exists($file)) {
			$handle = fopen($file, "r");
			if ($handle) {
				while (!feof($handle)) {
						$buffer = trim(fgets($handle, 9999));
						if(!empty($buffer)){
							$dataAry = explode("\t", $buffer);
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

	function MakeBaseCsv($obj,$naigai){
		ini_set("max_execution_time","360000");
		$n=0;
		$itme='';
		$outdata='';

		foreach($obj[1] as $i =>$key){
			$a = trim($key);
			if(!empty($a)){
				$keyAry[$i]=$key;
			}
		 }

		foreach($obj as $n => $ary){
			$data='';
			$out='';
			$c = trim($ary['A']);
			if(!empty($c)){
			foreach($keyAry as $i => $v){
				$v = trim($ary[$i]);
				$data .=$v."\t";
			}
			$out = trim($data);
			$outdata .=$out."\n";
			$n++;
			}
		}

		$OutData = mb_convert_encoding($outdata,"SJIS", "UTF-8");
		$OutFile = $this->baseFileDir.$this->baseFileAry[$naigai];

		if (!$handle = fopen($OutFile, 'w')) {
			echo "Cannot open file ($OutFile)";
			exit;
		}
		if (fwrite($handle, $OutData) === FALSE) {
			echo "Cannot write to file ($OutFile)";
			exit;
		}
		fclose($handle);

	}

	function importExcel($extension,$excelfilepath){
		// 処理時間の上限設定を緩和
		ini_set("max_execution_time","360000");


		if($extension == "xls"){
			$reader = PHPExcel_IOFactory::createReader( 'Excel5' );
		//xlsx
		}else{
			 $reader = PHPExcel_IOFactory::createReader( 'Excel2007' );
		}

		if( $reader ){
			//. エクセルファイルを読み込む
			$excel = $reader->load( $excelfilepath );
			$obj = $excel->getActiveSheet()->toArray( null, false, true, true );
			if(count($obj)< 1){
				return 'no read excel file';
			}
			else{
				return $obj;
			}
		}
		unset($reader);
		unset($excel);
	}

	function setData($obj){

		$this->naigai = "";
		$Required = "";

		if($obj[1]['A']){
			if(strpos($obj[1]['A'],'Target location') !== false){
				$this->naigai = "bus";
			}
		}

		if($obj[2]['A']){
			if(strpos($obj[2]['A'],'ggsc_kaigai_000_kyotennashi_taiwan') !== false ){
					$this->naigai = "taiwan";
			}
			elseif(strpos($obj[2]['A'],'ggsc_kaigai_000_kyotennashi_korea') !== false ){
					$this->naigai = "korea";
			}
			elseif(strpos($obj[2]['A'],'ggsc_kaigai_000_kyotennashi_hawai') !== false ){
					$this->naigai = "hawaii";
			}
			elseif(strpos($obj[2]['A'],'ggsc_kaigai_025_okinawa') !== false){
				$this->naigai = "i";
			}
			elseif(strpos($obj[2]['A'],'ggsc_kokunai_011_kansai') !== false){
				$this->naigai = "d";
			}
		}

		foreach($obj[1] as $i =>$key){
			$a = trim($key);
			if(!empty($a)){
				$keyAry[$i]=$key;

				if($this->naigai =="i" && $key=='元URL' && empty($Required)){
					$Required ='元URL';
			
				}
				if($this->naigai =="korea" && $key=='URL' && empty($Required)){
					$Required ='URL';
				}
				if($this->naigai =="hawaii" && $key=='URL' && empty($Required)){
					$Required ='URL';
				}
				if($this->naigai =="taiwan" && $key=='URL' && empty($Required)){
					$Required ='URL';
				}
				elseif($this->naigai =="d" && $key=='URL' && empty($Required)){

					$Required ='URL';
				}
				elseif($this->naigai =="bus" && $key=='URL' && empty($Required)){

					$Required ='URL';
				}
			}
		 }

		if(empty($this->naigai)){
			return 'no deta';
		}
		else{
			$this->MakeBaseCsv($obj,$this->naigai);
		}

		$dataAry='';

		foreach($obj as $n =>$data){
			/*if($n=="1"){
				continue;
			}*/
			$ary='';
			$c = trim($data['A']);
			if(!empty($c)){
				foreach($keyAry as $i =>$d){

					$ary[$d]=$data[$i];
				}
				$dataAry[]=$ary;
			}
		}

		unset($obj);
		unset($keyAry);


		if(is_array($dataAry)){

			$this->getMaster();

			//ベースファイル読み込み
			$baseAry='';
			$baseAry = $this->getFile($this->checkFileDir.$this->checkFileAry[$this->naigai]);
			$this->checkDataAry='';

			if(is_array($baseAry)){

				foreach($baseAry[0] as $i =>$key){
					$trim = trim($key);
					if(!empty($trim )){
						$keyAry[$i]=$trim ;
					}
				}

				foreach($baseAry as $n =>$data){

					$ary='';
					foreach($keyAry as $i =>$d){
						if(!empty($data[$i])){
						$t = trim($data[$i]);
						$ary[$d]=$t;
						}
						else{
							$ary[$d]='';
						}
					}
					$this->checkDataAry[]=$ary;
				}

				unset($baseAry);
				unset($keyAry);
			}

/*
			foreach($dataAry as $no => $updata){
				if(empty($updata[$Required])|| $updata[$Required]==$Required){
					continue;
				}

				if(strpos($updata[$Required],'hankyu-travel.com/') !== false){

					//ベースファイルとアップロードファイルの差分チェック　チェックしない時はコメントアウト
					if(is_array($this->checkDataAry)){
						foreach($this->checkDataAry as $n => $chekAry){

							if($chekAry[$Required]==$Required){
								continue;
							}


							if($this->naigai =='i'){
								if($chekAry['Target campaign']== $updata['Target campaign'] &&
									$chekAry['Target ad group']== $updata['Target ad group'] &&
									$chekAry['Target keyword text']== $updata['Target keyword text'] &&
									$chekAry['Target keyword match type']== $updata['Target keyword match type'] &&
									$chekAry['title1(text)']== $updata['title1(text)']&&
									$chekAry['元URL']== $updata['元URL']&&
									$chekAry['元広告グループ']== $updata['元広告グループ'] ){
									unset($dataAry[$no]);

								}

							}
							elseif($this->naigai =='d'){
								if($chekAry['Target campaign']== $updata['Target campaign'] &&
									$chekAry['Target ad group']== $updata['Target ad group'] &&
									$chekAry['URL(text)']== $updata['URL(text)'] ){
									unset($dataAry[$no]);
								}

							}
							//★★1ヶ月前の料金データは削除
							//if(isset($chekAry['thedate'])){
							//	if($chekAry['thedate'] !=='update' && (strtotime(date($chekAry['thedate'])) < strtotime("-1 month"))){
							//		unset($this->checkDataAry[$n]);
							//	}
							//}
						}
					}
				}
			}*/


			//アップロードファイルに新しい入稿があったら
			if(is_array($dataAry) && count($dataAry) > 0){
				$updataAry=array();

				foreach($dataAry as $k => $dataAryV){
					$aryParams='';
					$kyoten ='';
					$ary='';
					foreach($dataAryV as $k =>$v){
						if($k==$v){
							//項目飛ばす
							continue;
						}
						$ary[$k] =$v;
					}
					if(is_array($ary)){
						$urlAry = explode('/',$dataAryV[$Required]);
						$max = count($urlAry)-1;
						$kyoten = str_replace('.php','',$urlAry[$max]);
						if($urlAry[0]){

							foreach($this->masterAry['senmon'] as $path => $data){
								$urlary = explode('hankyu-travel.com/',$dataAryV[$Required]);

								$dir = preg_replace('!/[^/]*$!', '/', $urlary[1]);

								if($dir == $path){
									$aryParams['p_mokuteki'] = $data['p_mokuteki'];
									$naigai = $data['naigai'];
									break;
								}
							}
						}


					if($this->naigai =="i" || $this->naigai =="taiwan"|| $this->naigai =="hawaii"|| $this->naigai =="korea"){
						$get_url	= 'http://www.hankyu-travel.com/search/ajax.php';
						$aryParams['p_hatsu']=@$this->masterAry['p_hatsu'][$kyoten];
						$ary['tour_price(text)'] = '';
						$naigai='i';

					}
					else{
						$get_url = 'http://www.hankyu-travel.com/search/ajax_d.php';
						$aryParams['p_hatsu_sub']=@$this->masterAry['p_hatsu_sub'][$kyoten];
						$ary['tour_price(text)'] = '';
						$naigai='d';
					}

					//if(strpos($dataAryV[$Required], 'http://www.hankyu-travel.com/bus' ) !== false ){
					if($this->naigai =="bus"){
						$aryParams['p_bunrui'] = '813';
						$aryParams['p_transport']	= '1';
						$aryParams['MyNaigai']	= 'd';
						$naigai='d';
					}

					// パラメータ追加
					$aryParams['status'] = 'onLoad';
					// p_price_min 1000円未満は入稿しない
					$aryParams['p_price_min'] = 1000;
					$aryParams['MyNaigai']=$naigai;
					$aryParams['kind'] = 'GetList';
					$aryParams['p_rtn_data'] = 'p_hatsu_name';

					$query = http_build_query($aryParams);

					$rqUrl='';
					$rqUrl = $get_url.'?'.$query;

					if($this->naigai =="bus"){
						$naigai='bus';
					}
					if($this->naigai =="taiwan"){
						$naigai='taiwan';
					}
					if($this->naigai =="korea"){
						$naigai='korea';
					}
					if($this->naigai =="hawaii"){
						$naigai='hawaii';
					}

					$ary['rqUrl'] = $rqUrl;
					$thedate = 'update';
					$ary['thedate'] = $thedate;
					$ary['naigai'] = $naigai;

					$updataAry[]=$ary;


					unset($ary);
					unset($aryParams);
					}

				}

				//$this->setDataAry = array_merge($this->checkDataAry,$updataAry);
				$this->setDataAry = $updataAry;//差分チェックしない時使用

				unset($updataAry);
			}
			else{
				$this->setDataAry = $this->checkDataAry;
			}

		}
	}


	function getMaster(){

		//専門マスター取得
		$senmonCsvAry = $this->getCsv('http://www.hankyu-travel.com/sharing/master/master_senmon.csv');
		foreach($senmonCsvAry[3] as $n=> $data){

			if($n==0 || $n==6 || $n==7 ||$n==9 ||$n==10 ||$n==11  ){
				$senK[$n]=$data;
			}
		}
		foreach($senmonCsvAry as $data){
			$mokuteki='';
			$mokutekiary='';
			$p_mokuteki='';
			if($data[0]=="i" || $data[0]=="d"){
				foreach($senK as $n => $v){

					$senmonAry[$data[7]][$v]=$data[$n];
					if($n==9){
						$mokuteki =$data[$n];
					}
					elseif($n==10){
						$cAry = explode(',',$data[$n]);
						foreach($cAry as $v){
							$mokutekiary[] = $mokuteki .'-'.$v;
						}
						unset($cAry);
					}
					elseif($n==11){
						$cAry = explode(',',$data[$n]);

						foreach($mokutekiary as $v){
							foreach($cAry as $c){
								$p_mokuteki .=','.$v .'-'.$c;
							}
						}
						unset($cAry);
						unset($mokutekiary);
						$p_mokuteki = ltrim($p_mokuteki, ',');
						$senmonAry[$data[7]]['p_mokuteki']=$p_mokuteki;
						$senmonAry[$data[7]]['naigai']=$data[0];
					}
				}

			}
		}
		//ｐ_hatsuマスター取得
		$p_hatsuCsvAry = $this->getCsv('http://www.hankyu-travel.com/sharing/master/master_p_hatsu.csv');
		foreach($p_hatsuCsvAry as $ary){
			if(empty($p_hatsuAry[$ary[5]])){
				$p_hatsuAry[$ary[5]]=$ary[1];
			}
			else{
				$p_hatsuAry[$ary[5]].=','.$ary[1];
			}
		}

		//ｐ_hatsu_subマスター取得
		$p_hatsu_subCsvAry = $this->getCsv('http://www.hankyu-travel.com/sharing/master/master_p_hatsu_sub.csv');
		foreach($p_hatsu_subCsvAry as $ary){
			if($ary[7] =='孫ID'){
				continue;
			}
			if(empty($p_hatsu_subAry[$ary[7]])){
				$p_hatsu_subAry[$ary[7]]=$ary[1];
			}
			else{
				$p_hatsu_subAry[$ary[7]].=','.$ary[1];
			}
		}
		$ary='';
		$this->masterAry['senmon'] = $senmonAry;
		$this->masterAry['p_hatsu'] = $p_hatsuAry;
		$this->masterAry['p_hatsu_sub'] = $p_hatsu_subAry;
	}

	/*データ取得*/
	function getCsv($url){
		$data = @file_get_contents($url, false,
        stream_context_create(array(
            'http' => array(
                'timeout'=>20
            )
        )));
		if($data === FALSE){
			if(count($http_response_header) > 0)
			{
				$stat_tokens = explode(' ', $http_response_header[0]);
				echo 'FileGetError：'.$stat_tokens[1]."<br>";
				switch($stat_tokens[1])
				{
				case 404:
					// 404 Not found の場合
					break;
				case 500:
					// 500 Internal Server Error の場合
					break;
				default:
					// その他
					break;
				}
			}
			else
			{
			   echo 'FileGetTimeOutError：'.$url."<br>";
			}
		}

		$temp = tmpfile();
		$csv  = array();

		fwrite($temp, $data);
		rewind($temp);

		while (($dataAry = fgetcsv($temp, 0, "\t")) !== FALSE) {
			$csv[] = $dataAry;
		}
		fclose($temp);
		return $csv;
	}

}


?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta name="robots" content="noindex" />
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>料金チェックプログラム</title>
	<link rel="stylesheet" type="text/css" href="style.css" />
	<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
	<script src="https://code.jquery.com/ui/1.12.0/jquery-ui.js"></script>
	<script src="http://ajax.googleapis.com/ajax/libs/jqueryui/1/i18n/jquery.ui.datepicker-ja.min.js"></script>
	<link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/themes/smoothness/jquery-ui.css">
</head>
<body>

<div id="container">
<div class="contents pt150">
	<div class="inner">
		<section>
			<h1 class="type1"><span class="color1">料金</span>チェックプログラム</h1>
		</section>
	</div>
</div>


<div class="contents bg1">
	<div class="inner">
		<section id="new">
			<h2 id="newinfo_hdr" class="close">出稿中URL一覧.xlsxをアップロード</h2>
			<form method="post" enctype="multipart/form-data">
			<input name="upfile" type="file" size="100"><input class="btnDisabled" type="submit" value="アップロード">
			<p>※一行目は、項目行。項目列に海外は[元URL]、国内は[専門店URL]、バスは[URL]があることが必須。<br />
			※アップロードされたファイルを元に、毎日深夜２時よりバッチ処理で最低価格を取得します。</p>
			</form>
			<font color="red" id="message" ></font>
<?php
if(count($_FILES)> 0){
	if($_FILES ["upfile"]["size"]>0){
		new priceCheck($_FILES);
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

}

?>

		</section>
	<!--	<section>
			<h2>最低価格を付加したものをダウンロード</h2>
<?php


$BaseDir = '/home/xhankyu/public_html/photo_db/webtool/PriceCheck/outdata_test/';


	$isEx = glob($BaseDir.'*.tsv', GLOB_BRACE);

	$RetLi='';
	$RetLd='';
	if(!empty($isEx)){
		rsort($isEx);
		foreach($isEx as $FileName){
			$BaseName = basename($FileName);
			$a = str_replace('.tsv','',$BaseName);
			$b=explode('_',$a);
			$date = substr($b[2], 0, 4) . '/' . substr($b[2], 4, 2) . '/' . substr($b[2], 6, 2);
			if(strpos($BaseName, '_i_') !== false){
				$RetLi .= <<<EOD
<li><a href="download_data.php?file={$BaseName}&type=i">price_check_download_i.csv</a>（{$date}）</li>
EOD;
			}
			else{
				$RetLd .= <<<EOD
<li><a href="download_data.php?file={$BaseName}&type=d">price_check_download_d.csv</a>（{$date}）</li>
EOD;
			}


		}
	}
?>
<ul style=" display: inline-table">
<li style="color: #c80000">海外</li>
<?php echo $RetLi; ?>
</ul>
<ul style=" display: inline-table; padding-left: 50px">
<li style="color: #c80000">国内・バス</li>
<?php echo $RetLd; ?>
</ul>
</section>
-->
<!--<section>
			<h2>最低価格を付加したものをダウンロード</h2>
			<form id="fDownload" name="fDownload" action="http://strategy-tool.com/hankyu/price_checks/download" method="post">
			<dl id="newinfo">
				<dt>[期間(YYYY-MM-DD)]</dt>
				<dd><label for="PriceCheckSelectDateStart"></label><input name="data[PriceCheck][select_date_start]" type="text" style="ime-mode:disabled" class="datepicker" value="2017-07-18" id="PriceCheckSelectDateStart" />　～　
				<label for="PriceCheckSelectDateEnd"></label><input name="data[PriceCheck][select_date_end]" type="text" style="ime-mode:disabled" class="datepicker" value="2017-07-18" id="PriceCheckSelectDateEnd" /></dd>
				<dt>[比較日（YYYY-MM-DD）※期間の最終日と比較したい日付がある場合にのみ入力]</dt>
				<dd><label for="PriceCheckHikakuDate"></label><input name="data[PriceCheck][hikaku_date]" type="text" style="ime-mode:disabled" class="datepicker" value="" id="PriceCheckHikakuDate" /></dd>
				<dt>[比較日を指定した場合の出力方法　※比較日を入れた場合にのみ有効]</dt>
				<dd><input type="radio" name="data[PriceCheck][output_type]" id="PriceCheckOutputTypeAll" value="all" checked="checked"  /><label for="PriceCheckOutputTypeAll">すべて</label>　<input type="radio" name="data[PriceCheck][output_type]" id="PriceCheckOutputTypeSabun" value="sabun"  /><label for="PriceCheckOutputTypeSabun">差分のみ</label><br />
				<input class="" type="button" onclick="document.fDownload.submit();" value="ダウンロード"></dd>
			</dl>
			</form>
		</section>-->
	</div>
</div>
<!--/contents-->
<footer>
<small>Copyright&copy; BUD Internaitonal Allrights reserved.</small>
</footer>

</div>


<script type="text/javascript">

jQuery( function() {

	jQuery( '.datepicker' ) . datepicker(
	{
		dayNamesMin: ["日", "月", "火", "水", "木", "金", "土"],
		monthNames: ["1月", "2月", "3月", "4月", "5月", "6月", "7月", "8月", "9月", "10月", "11月", "12月"],
		nextText: "次月",
		prevText: "前月",
		dateFormat: 'yy-mm-dd',
		showOtherMonths: true,
		selectOtherMonths: true,
		showAnim: 'drop',
		showMonthAfterYear:true,
		yearSuffix:"年",
	});

} );

$(function(){
	$("form").submit(function() {
		$(".btnDisabled").attr( "disabled" , true );
		$('#message').html( "<b>処理中...</b>" );
	});
});
</script>


</body>
</html>
