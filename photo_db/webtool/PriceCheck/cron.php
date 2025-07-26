<?php

/*
########################################################################
#
#	料金チェックプログラム
#　　
#　　リストを元に専門店の最安値を取得し、トラコスサーバに毎日差分ファイルをput
########################################################################*/
header('Content-Type: text/html; charset=UTF-8');
date_default_timezone_set("Asia/Tokyo");
//ini_set('memory_limit', '256M');
@ini_set('memory_limit', -1);

$flg='all';//全件
$naigai ='all';//全部
//$naigai ='d';
new priceCheckCron($flg,$naigai);
//new TsvUproad;
/*---------------------------------------------
　料金データファイル作成

 比較チェックファイルを元にfilegetcontentsで料金取得　base_price_check_d.csv、base_price_check_i.csv
 日付がuploadはエクセルがアップロードされた新規のデータ
 サーバにput　flg='';
 全件必要な時は引数：flg='all';（比較チェックファイルで日付を見ない）
 （*比較チェックファイルはindex.phpでエクセルがアップロードされたデータを元に作成　base_d.csv、base_d.csv）

-----------------------------------------------*/

//新しい料金取得
class priceCheckCron{

	public $outFileDir;
	public $outDataAry;
	public $outfileAryI;
	public $outfileAryd;

	function __construct($flg,$naigai) {

		if($flg=='all'){
			$this->setFlg ='all';
			echo '全件start：'.$time = date("Y/m/d H:i:s.")."\n";
		}
		else{
			$this->setFlg ='diff';
			echo '差分start：'.$time = date("Y/m/d H:i:s.")."\n";
		}

		//比較チェックファイル
		$this->checkFileDir ='/home/xhankyu/public_html/photo_db/webtool/PriceCheck/data/';
		if($naigai=='all'){

			$this->checkFileAry  = array(
				'i' => 'base_price_check_i.csv',
				'd' => 'base_price_check_d.csv',
				'bus' => 'base_price_check_bus.csv',
				'taiwan' => 'base_price_check_taiwan.csv',
				'korea' => 'base_price_check_korea.csv',
				'hawaii' => 'base_price_check_hawaii.csv'
			);
		}
		elseif($naigai=='i'){
			$this->checkFileAry  = array(
				'i' => 'base_price_check_i.csv'
			);
		}
		elseif($naigai=='d'){
			$this->checkFileAry  = array(
				'd' => 'base_price_check_d.csv'
			);
		}
		elseif($naigai=='bus'){
			$this->checkFileAry  = array(
				'bus' => 'base_price_check_bus.csv'
			);
		}
		elseif($naigai=='taiwan'){
			$this->checkFileAry  = array(
				'taiwan' => 'base_price_check_taiwan.csv'
			);
		}
		elseif($naigai=='korea'){
			$this->checkFileAry  = array(
				'korea' => 'base_price_check_korea.csv'
			);
		}
		elseif($naigai=='hawaii'){
			$this->checkFileAry  = array(
				'hawaii' => 'base_price_check_hawaii.csv'
			);
		}


		//アウトファイル
		$this->outFileDir ='/home/xhankyu/public_html/photo_db/webtool/PriceCheck/outdata/';

		$this->outfileAryI ='Adcustmizer_i.xlsx';//trabid_i
		$this->outfileAryD ='Adcustmizer_d_kw.xlsx';//trabid_d_kw
		$this->outfileAryBus ='bus_geofeed.xlsx';
		$this->outfileAryTaiwan ='taiwan_area_Adcustmizer_i.xlsx';//taiwan_area_trabid_i
		$this->outfileAryKorea ='korea_area_Adcustmizer_i.xlsx';//korea_area_trabid_i
		$this->outfileAryHawaii ='hawaii_area_Adcustmizer_i.xlsx';//hawaii_area_trabid_i


		foreach($this->checkFileAry as $n => $file){

			$msg = $this->getFile($this->checkFileDir.$file);

			$this->setDataAry=[];
			$this->outDataAry=[];

			if(is_array($msg)){
				$this->setData($msg,$n);//csvファイルからリクエストurlデータ取得
			}
			else{
				echo 'no file end：'.$time = date("Y/m/d H:i:s.")."\n";
				echo  $msg."\n";
				exit;
			}


			if(is_array($this->setDataAry)){
				echo 'getPriceStart：'.$time = date("Y/m/d H:i:s.")."\n";
				$this->getMinPrice();//まとめたurlでhttpリクエスト

				if(is_array($this->getRqDataAry)){
					$this->makeOutData();//urlを元に料金が変更あったかチェック
				}
			}
			else{
				$time = date("Y/m/d H:i:s.");
				echo 'NO Valid data'.$time."\n";
				exit;
			}

			if(is_array($this->outDataAry)){
				if($n =='i'){
					//$this->MakeCsv();//put用tsv作成
					$this->MakeXlsx($n);//put用xlsx作成
				}
				elseif($n =='d'){
					$this->MakeXlsx($n);//put用xlsx作成
				}
				elseif($n =='bus'){
					$this->MakeXlsx($n);//put用xlsx作成
				}
				elseif($n =='taiwan'){
					$this->MakeXlsx($n);//put用xlsx作成
				}
				elseif($n =='korea'){
					$this->MakeXlsx($n);//put用xlsx作成
				}
				elseif($n =='hawaii'){
					$this->MakeXlsx($n);//put用xlsx作成
				}
				$this->MakeCheckCsv($n);//base用csv作成

			}else{
				$time = date("Y/m/d H:i:s.");
				echo 'NO getPrice data'.$time."\n";
				exit;
			}

		}

		$isExlI = '/home/xhankyu/public_html/photo_db/webtool/PriceCheck/outdata/Adcustmizer_i.xlsx';
		$isExlD = '/home/xhankyu/public_html/photo_db/webtool/PriceCheck/outdata/Adcustmizer_d_kw.xlsx';
		$isExlBus ='/home/xhankyu/public_html/photo_db/webtool/PriceCheck/outdata/bus_geofeed.xlsx';
		$isExlTaiwan = '/home/xhankyu/public_html/photo_db/webtool/PriceCheck/outdata/taiwan_area_Adcustmizer_i.xlsx';
		$isExlKorea = '/home/xhankyu/public_html/photo_db/webtool/PriceCheck/outdata/korea_area_Adcustmizer_i.xlsx';
		$isExlHawaii = '/home/xhankyu/public_html/photo_db/webtool/PriceCheck/outdata/hawaii_area_Adcustmizer_i.xlsx';

		//ディレクトリにデータがあったらアップロード
		if($naigai=='all'){
			if(empty($isExlI) && empty($isExlD)&& empty($isExlBus)&& empty($isExlTaiwan)&& empty($isExlKorea)&& empty($isExlHawaii)){
				echo 'noPut end：'.$time = date("Y/m/d H:i:s.")."\n";
			}
			else{
				new TsvUproad;//ftpアップロード

				echo 'end：'.$time = date("Y/m/d H:i:s.")."\n";
			}
		}
		elseif($naigai =='i'){
			if(empty($isZipEx)){
				echo 'noPut end：'.$time = date("Y/m/d H:i:s.")."\n";
			}
			else{
				//new TsvUproad;//ftpアップロード
				echo 'end：'.$time = date("Y/m/d H:i:s.")."\n";
			}

		}
		elseif($naigai =='bus'){
			//new TsvUproad;//ftpアップロード
		}
		elseif($naigai =='taiwan'){
			//new TsvUproad;//ftpアップロード
		}
		elseif($naigai =='korea'){
			//new TsvUproad;//ftpアップロード
		}
		elseif($naigai =='hawaii'){
			//new TsvUproad;//ftpアップロード
		}
		elseif($naigai =='d'){
			new TsvUproad;//ftpアップロード
		}

	}

	function MakeCheckCsv($naigai){

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
			'naigai',
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

		foreach($this->outDataAry as $k => $ary){
			$trimdata='';
			$data='';
			if($naigai=='i'){
				foreach($iItem as $i){
					if($n==0){
						$itme .= $i."\t";
						//海外ファイル名
						$filename =$this->checkFileAry['i'];
					}
					else{
						$data .=$ary[$i]."\t";
					}
				}
			}
			elseif($naigai=='d'){

				foreach($dItem as $d){
					if($n==0){
						$itme .=$d."\t";
						//国内ファイル名
						$filename =$this->checkFileAry['d'];

					}
					else{
						$data .=$ary[$d]."\t";
					}
				}

			}
			elseif($naigai=='bus'){

				foreach($busItem as $b){
					if($n==0){
						$itme .=$b."\t";
						//国内ファイル名
						$filename =$this->checkFileAry['bus'];

					}
					else{
						$data .=$ary[$b]."\t";
					}
				}

			}
			elseif($naigai=='taiwan'){

				foreach($taiwanItem as $b){
					if($n==0){
						$itme .=$b."\t";
						//国内ファイル名
						$filename =$this->checkFileAry['taiwan'];

					}
					else{
						$data .=$ary[$b]."\t";
					}
				}

			}
			elseif($naigai=='korea'){

				foreach($koreaItem as $b){
					if($n==0){
						$itme .=$b."\t";
						$filename =$this->checkFileAry['korea'];

					}
					else{
						$data .=$ary[$b]."\t";
					}
				}

			}
			elseif($naigai=='hawaii'){

				foreach($hawaiiItem as $b){
					if($n==0){
						$itme .=$b."\t";
						//国内ファイル名
						$filename =$this->checkFileAry['hawaii'];

					}
					else{
						$data .=$ary[$b]."\t";
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
			$outdata = $itme."\n".$outdata;
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

	function makeOutData(){

		foreach($this->setDataAry as $n => &$ary){

			if($ary["naigai"]=='i'){
				foreach($this->getRqDataAry as $url=>$v){
					if($ary['元URL']==$url){

						if($this->setFlg=='all'){
							$ary['tour_price(text)']=$v['tour_price(text)'];
							//$ary['コース名']=$v['コース名'];
							$ary['thedate']=date("Y-m-d");
							break;
						}
						else{
							//★★　差分チェック
							if($ary['thedate']=='update'|| $ary['tour_price(text)']!==$v['tour_price(text)'] ){
								$ary['tour_price(text)']=$v['tour_price(text)'];
								//$ary['コース名']=$v['コース名'];
								$ary['thedate']=date("Y-m-d");
								break;
							}
						}
					}

				}
			}
			elseif($ary["naigai"]=='d'){
				foreach($this->getRqDataAry as $url=>$v){
					if($ary['URL']==$url){
						if($this->setFlg=='all'){//国内cronは全件
							$ary['価格(text)']=$v['tour_price(text)'];
							$ary['thedate']=date("Y-m-d");
							break;
						}
						else{
							//★★　差分チェック
							if($ary['thedate']=='update'|| $ary['価格(text)']!==$v['tour_price(text)'] ){
								$ary['価格(text)']=$v['tour_price(text)'];
								$ary['thedate']=date("Y-m-d");
							}
							break;
						}
					}

				}
			}
			elseif($ary["naigai"]=='bus'){
				foreach($this->getRqDataAry as $url=>$v){

					if(isset($ary['URL'])){
						if($ary['URL']==$url){

							if($this->setFlg=='all'){//国内cronは全件
								$ary['tour_price(text)']=$v['tour_price(text)'];
								$ary['thedate']=date("Y-m-d");
								break;
							}
						}

					}
				}
			}
			elseif($ary["naigai"]=='taiwan'){
				foreach($this->getRqDataAry as $url=>$v){
					if($ary['URL']==$url){
						if($this->setFlg=='all'){//国内cronは全件
							$ary['tour_price(text)']=$v['tour_price(text)'];
							$ary['thedate']=date("Y-m-d");
							break;
						}
					}
				}
			}
			elseif($ary["naigai"]=='korea'){
				foreach($this->getRqDataAry as $url=>$v){
					if($ary['URL']==$url){
						if($this->setFlg=='all'){//国内cronは全件
							$ary['tour_price(text)']=$v['tour_price(text)'];
							$ary['thedate']=date("Y-m-d");
							break;
						}
					}
				}
			}
			elseif($ary["naigai"]=='hawaii'){
				foreach($this->getRqDataAry as $url=>$v){
					if($ary['URL']==$url){
						if($this->setFlg=='all'){//国内cronは全件
							$ary['tour_price(text)']=$v['tour_price(text)'];
							$ary['thedate']=date("Y-m-d");
							break;
						}
					}
				}
			}
			$this->outDataAry[$n]=$ary;

		}
	}

	function MakeXlsx($naigai){
		$this->DirCleanling();
		include_once( dirname( __FILE__ ) . '/Classes/PHPExcel.php' );
		include_once( dirname( __FILE__ ) . '/Classes/PHPExcel/IOFactory.php' );
		$n=0;
		if($naigai=='i'){
			$Item =array(
				'Target campaign',
				'Target ad group',
				'Target keyword text',
				'Target keyword match type',
				'title1(text)',
				'tour_price(text)',
			);
			$filename ='Adcustmizer_i';
		}
		elseif($naigai=='d'){
			$Item =array(
				'Target campaign',
				'Target ad group',
				'Target keyword text',
				'Target keyword match type',
				'価格(text)',
				'目的地(text)',
				'目的地の都道府県(text)',
			);
			$filename ='Adcustmizer_d_kw';
		}
		elseif($naigai=='bus'){
			$Item =array(
				'Target location',
				'出発地(text)',
				'tour_price(text)',
				'Target location restriction',
			);
			$filename ='bus_geofeed';
		}

		elseif($naigai=='taiwan'){
			$Item =array(
				'Target campaign',
				'Target ad group',
				'Target keyword text',
				'Target keyword match type',
				'title1(text)',
				'tour_price(text)',
				'Target location',
				'出発地(text)',
				'Target location restriction',
			);
			$filename ='taiwan_area_Adcustmizer_i';
		}
		elseif($naigai=='korea'){
			$Item =array(
				'Target campaign',
				'Target ad group',
				'title1(text)',
				'tour_price(text)',
				'Target location',
				'出発地(text)',
				'Target location restriction',
			);
			$filename ='korea_area_Adcustmizer_i';
		}
		elseif($naigai=='hawaii'){
			$Item =array(
				'Target campaign',
				'Target ad group',
				'title1(text)',
				'tour_price(text)',
				'Target location',
				'出発地(text)',
				'Target location restriction',
			);
			$filename ='hawaii_area_Adcustmizer_i';
		}


		$n=0;
		$itme='';
		$outdata='';

		$Excel = new PHPExcel();
		$sheet = $Excel->getActiveSheet();
		//$sheet->setTitle(1);
		//$filename =$this->outfileAryD;



		$row = 1;
		if(is_array($this->outDataAry)){
			chmod($this->outFileDir. $filename.'.xlsx', 0666);
			rename($this->outFileDir. $filename.'.xlsx', $this->outFileDir.$filename.'_'.date("Ymd", strtotime('-1 day')).'.xlsx');
			foreach($this->outDataAry as $i => $ary){
				$col = 0;
				//$data='';


				foreach($Item as $d){
					if($i==0){
						$data =$d;

					}
					else{
						if($this->setFlg=='all'){
						}
						else{
							//★★差分だしなら
							if($ary['thedate'] !== date("Y-m-d")){
								continue;
							}
						}
						$data =$ary[$d];
					}
					$sheet->setCellValueByColumnAndRow($col++,$row,$data);
				}
				 $row++;

			}
			$writer = PHPExcel_IOFactory::createWriter($Excel, 'Excel2007');
			$writer->save($this->outFileDir.$filename.'.xlsx');
		}
	}


	function DirCleanling(){

		//バックアップファイルを削除
		//$path = $this->outFileDir.'trabid_'.'*.tsv';
		//$path = $this->outFileDir.'trabid_'.'*';
		$path = $this->outFileDir.'*';
		//$time = strtotime("-1 month");//1ヶ月前
		$time = strtotime("-3 week");
		foreach (glob($path) as $filename) {
			if(stripos($filename,'.xlsx')!==false){

				$str = substr($filename, -13);
				$ymd=str_replace('.xlsx','',$str);
			}
			else{
				$str = substr($filename, -12);
				$ymd=str_replace('.tsv','',$str);
			}

			if($ymd !='trabid_d' && $ymd !='bid_d_kw' && $ymd !='trabid_i' && $ymd !='_geofeed' && $ymd !='tmizer_i' && $ymd !='zer_d_kw' && $ymd !='tmizer_i'){
				$filetime = strtotime($ymd);
				// 7日より前のファイルなら
				if ($filetime < $time){
					// 削除
					@unlink($filename);
				}
			}
		}
	}




	function getFile($file){
		// 処理時間の上限設定を緩和
		ini_set("max_execution_time","360000");
		if ( file_exists($file)) {
			$handle = fopen($file, "r");
			if ($handle) {
				while (!feof($handle)) {
						$buffer = rtrim(fgets($handle, 9999));
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

	function setData($obj,$naigai){

		$Required = "";
		foreach($obj[0] as $i =>$key){
			$a = trim($key);
			if(!empty($a)){
				$keyAry[$i]=$key;

				/*if($naigai=='i' && empty($Required)){
					$Required ='元URL';
				}
				elseif($naigai=='d' && empty($Required)){
					$Required ='専門店URL';
				}
				elseif($naigai=='bus' && empty($Required)){
					$Required ='URL(text)';
				}*/
			}
		 }

		if(empty($naigai)){
			return 'no destination_url1 or url';
		}

		$dataAry='';
		foreach($obj as $n =>$data){
				$ary=[];
				foreach($keyAry as $i =>$d){
					$ary[$d]=$data[$i];
				}
			$this->setDataAry[]=$ary;
		}

		unset($obj);
		unset($keyAry);
	}


	/*データ取得*/
	function httpDataGet($url){
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
				echo 'FileGetError：'.$stat_tokens[1]."\n";
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
			   echo 'FileGetTimeOutError：'.$url."\n";
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

	/*最低価格取得.*/
	function getMinPrice(){
		$rqAry=[];
		foreach($this->setDataAry as $i => $ary){
			if(isset($ary['naigai'])){
				if($ary['naigai'] =="i"){
					$rqAry['i'][$ary['元URL']]=$ary['rqUrl'];
				}
				elseif($ary['naigai'] == "d"){
					 $rqAry['d'][$ary['URL']]=$ary['rqUrl'];
				}
				elseif($ary['naigai'] == "bus"){
					$rqAry['d'][$ary['URL']]=$ary['rqUrl'];
				}
				elseif($ary['naigai'] == "korea"){
					$rqAry['i'][$ary['URL']]=$ary['rqUrl'];
				}
				elseif($ary['naigai'] == "hawaii"){
					$rqAry['i'][$ary['URL']]=$ary['rqUrl'];
				}
				elseif($ary['naigai'] == "taiwan"){
					$rqAry['i'][$ary['URL']]=$ary['rqUrl'];
				}
				else{
					continue;
				}
			}

		}
		if(is_array($rqAry)){
			$conut=0;
			foreach($rqAry as $naigai => $ary){
				foreach($ary as $url => $rqurl){
				echo $conut."\n";
					$rq = $this->httpDataGet($rqurl);

					$aryRq	= @json_decode ($rq[0][0], true );

					$allPrise_str = @$aryRq['allPrice'];

					if( empty($allPrise_str) || $allPrise_str =='−' ){

						//echo 'no Set_AllPrice：'.$url ."\n";
						$min_price = '料金無しのため掲載なし';
						unset($aryRq);
					}
					else{
						// 円を排除
						$allPrise_str	= str_replace( '円' , '' , $allPrise_str );
						// カンマを排除
						$allPrise_str	= str_replace( ',' , '' , $allPrise_str );
						// 〜 で分離
						$aryAllPrise	= explode( '〜' , $allPrise_str );

						if($naigai == 'i' ){
							// 最低価格
							$min	= intval($aryAllPrise[0]);
							$man = floor(($min % 100000000) / 10000);

							//$nokori = ($min % 100000000) % 10000;
							$nokori = sprintf('%04d',($min % 100000000) % 10000);
							if($man==0){
								if(strlen($nokori)>3){
									$number = number_format($nokori);
									/*$n= str_replace(',','.', strval($number));
									$n = rtrim($n,"000");
									$n = rtrim($n,"00");
									$n = rtrim($n,"0");
									$n = rtrim($n,".");
									$min_price = $n.'千円';*/
									$min_price = $number.'円';
								}
								else{
									$min_price = $nokori.'円';
								}
							}else{
								$n =rtrim($nokori,"000");
								$n =rtrim($n,"00");
								$n =rtrim($n,"0");
								$n =rtrim($n,".");
								$n =substr($n, 0, 2);
								if(!empty($n)){
									$min_price = $man .'.'. $n.'万円';
								}
								else{
									$min_price = $man .'万円';
								}
							}
							if(!empty($aryAllPrise[1]) && $aryAllPrise[1]!="0"){
								$max = intval($aryAllPrise[1]);
								$min = intval($aryAllPrise[0]);
								if($min < $max){
									//$min_price=$min_price.'〜';
									$min_price=$min_price."～";
								}
							}
						}
						elseif($naigai == 'd'){
							// 最低価格
							$min	= intval($aryAllPrise[0]);
							$man = floor(($min % 100000000) / 10000);
							$nokori = sprintf('%04d',($min % 100000000) % 10000);
							//$nokori = ($min % 100000000) % 10000;

							if($man==0){
								if(strlen($nokori)>3){
									$number = number_format($nokori);
									/*$n= str_replace(',','.', strval($number));
									$n = rtrim($n,"000");
									$n = rtrim($n,"00");
									$n = rtrim($n,"0");
									$n = rtrim($n,".");
									$min_price = $n.'千円';*/
									$min_price = $number.'円';
								}
								else{
									$min_price = $nokori.'円';
								}
							}else{
								$n =rtrim($nokori,"000");
								$n =rtrim($n,"00");
								$n =rtrim($n,"0");
								$n =rtrim($n,".");
								$n =substr($n, 0, 2);
								if(!empty($n)){
									$min_price = $man .'.'. $n.'万円';
								}
								else{
									$min_price = $man .'万円';
								}
							}
							if(!empty($aryAllPrise[1]) && $aryAllPrise[1]!="0"){
								$max = intval($aryAllPrise[1]);
								$min = intval($aryAllPrise[0]);
								if($min < $max){
									//$min_price=$min_price.'〜';
									$min_price=$min_price."～";
								}
							}
						}

					}

					$rqdata['tour_price(text)'] = $min_price;

					$this->getRqDataAry[$url]= $rqdata;

					unset($rqdata);
					//sleep(1);					
					$conut++;
				}
			}
		}

	}

}



/*---------------------------------------------
　FTP接続生成したファイルをtrabitサーバに移す
-----------------------------------------------*/

Class TsvUproad{
	function __construct(){

		$this->outFileDir ='/home/xhankyu/public_html/photo_db/webtool/PriceCheck/outdata/';


		$this->FTP_Connection();
		$this->TgConnectDir = '/';
		$this->TgLocalDir = $this->outFileDir;

		//$this->fileAry = $isZipEx;

		$this->FTP_ChDirectory();//対象ディレクトリーに移動
		$this->FTP_Up(FTP_ASCII);	//アップロード
		//$this->FTP_Up(FTP_BINARY);	//ZIPアップロード
		$this->FTP_DisConnection();
	}

	///FTP接続を確率する
	function FTP_Connection(){
		// 接続の設定
		$ftp_server = 'trabid.tciwork.com';
		$ftp_user_name = "hankyu-ftp";
		$ftp_user_pass = "1qFI3joF1A";
		$this->conn_id = ftp_connect($ftp_server);
		// ユーザ名とパスワードでログインする
		ftp_login($this->conn_id, $ftp_user_name, $ftp_user_pass);
	}

	//ディレクトリを移動
	function FTP_ChDirectory(){
		if (ftp_chdir($this->conn_id, $this->TgConnectDir)) {
			ftp_pasv($this->conn_id, true);
		}
	}

	function FTP_Up($Type){
		//アップロードファイル一覧
		if ($handle = opendir($this->TgLocalDir)) {
			while (false !== ($file = readdir($handle))) {
				if ($file == '.' || $file == '..' || $file == '.DS_Store' || stripos($file,'_bk') !== false){
					continue;
				}
				/*if($Type == FTP_ASCII && stripos($file,'.zip') !== false){
					continue;
				}
				elseif($Type == FTP_BINARY && stripos($file,'.zip') === false){
					continue;
				}
			*/

				if(stripos($file,'Adcustmizer_i.xlsx')!==false){

					ftp_put($this->conn_id, $file, $this->TgLocalDir.$file,FTP_BINARY);
				}
				if(stripos($file,'Adcustmizer_d_kw.xlsx')!==false){

					ftp_put($this->conn_id, $file, $this->TgLocalDir.$file,FTP_BINARY);
				}

				if(stripos($file,'bus_geofeed.xlsx')!==false){

					ftp_put($this->conn_id, $file, $this->TgLocalDir.$file,FTP_BINARY);
				}
				if(stripos($file,'taiwan_area_Adcustmizer_i.xlsx')!==false){
					ftp_put($this->conn_id, $file, $this->TgLocalDir.$file,FTP_BINARY);
				}
				if(stripos($file,'korea_area_Adcustmizer_i.xlsx')!==false){
					ftp_put($this->conn_id, $file, $this->TgLocalDir.$file,FTP_BINARY);
				}
				if(stripos($file,'hawaii_area_Adcustmizer_i.xlsx')!==false){
					ftp_put($this->conn_id, $file, $this->TgLocalDir.$file,FTP_BINARY);
				}


			}
			closedir($handle);
		}

	}
	//FTP接続を閉じる
	function FTP_DisConnection(){
		ftp_close($this->conn_id);
	}
}
?>
