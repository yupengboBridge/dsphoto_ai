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



//引数：ファイル作成を全件か前日からの差分で作成
$flg='all';//全件
//$flg='';//差分


//引数：作成ファイル　海外・国内バス
$naigai ='all';//海外と国内バス
//$naigai ='i';//海外のみ
//$naigai ='d';//国内バスのみ

new priceCheckCron($flg,$naigai);
//new TsvUproad;
//

/*---------------------------------------------
　料金データファイル作成
 
 比較チェックファイルを元にfilegetcontentsで料金取得　base_price_check_d.csv、base_price_check_i.csv
 日付が今日になったものが料金差分、日付がuploadはエクセルがアップロードされた新規のデータ
 基本差分でサーバにput　flg='';
 全件必要な時は引数：flg='all';（比較チェックファイルで日付を見ない）
 （*比較チェックファイルはindex.phpでエクセルがアップロードされたデータを元に作成　base_d.csv、base_d.csv）
 
-----------------------------------------------*/

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
		$this->checkFileDir ='/home/xhankyu/public_html/photo_db/webtool/PriceCheck/data_test/';
		
		if($naigai=='all'){
			$this->checkFileAry  = array(
				'i' => 'base_price_check_i.csv',
				'd' => 'base_price_check_d.csv'
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
			
		
		//アウトファイル
		$this->outFileDir ='/home/xhankyu/public_html/photo_db/webtool/PriceCheck/outdata/';
		
		$this->outfileAryI ='trabid_i.xlsx';
		$this->outfileAryD ='trabid_d.xlsx';

		foreach($this->checkFileAry as $n => $file){
			
			$msg = $this->getFile($this->checkFileDir.$file);
	
			$this->setDataAry='';
			$this->outDataAry='';

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
				$this->MakeCheckCsv($n);//base用csv作成
				
			}else{
				$time = date("Y/m/d H:i:s.");
				echo 'NO getPrice data'.$time."\n";
				exit;
			}
		
		}
		$isZipEx = '/home/xhankyu/public_html/photo_db/webtool/PriceCheck/outdata/trabid_i.xlsx';
		$isExl = '/home/xhankyu/public_html/photo_db/webtool/PriceCheck/outdata/trabid_d.xlsx';
		//ディレクトリにデータがあったらアップロード
		if($naigai=='all'){
			if(empty($isZipEx) && empty($isExl)){
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
				new TsvUproad;//ftpアップロード
				echo 'end：'.$time = date("Y/m/d H:i:s.")."\n";
			}
			
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
			'元広告グループ',
			'rqUrl',
			'thedate',
			'naigai',
		);
		$dItem =array(
			'Target campaign',
			'Target ad group',
			'tour_price(text)',
			'URL(text)',
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
					if($ary['URL(text)']==$url){
						if($this->setFlg=='all'){//国内cronは全件
							$ary['tour_price(text)']=$v['tour_price(text)'];
							$ary['thedate']=date("Y-m-d");
							break;
						}
						else{
							//★★　差分チェック
							if($ary['thedate']=='update'|| $ary['tour_price(text)']!==$v['tour_price(text)'] ){
								$ary['tour_price(text)']=$v['tour_price(text)'];
								$ary['thedate']=date("Y-m-d");
							}
							break;
						}
					}
				}
			}
				
			$this->outDataAry[$n]=$ary;

		}
	}
	
	/*
	function MakeCsv(){
		$n=0;
		$iItem = array(
			'アカウントID',
			'キャンペーン名',
			'広告グループ名',
			'Destination URL_1',
			'Destination URL_2',
			'min_price',
			'コース名'
		);
		
		$n=0;
		$itme='';
		$outdata='';

		
		foreach($this->outDataAry as $i => $ary){
		
			$data='';
			if(isset($ary['Destination URL_1'])){
				foreach($iItem as $i){
					if($n==0){
						
						$itme .= $i."\t";
						//海外ファイル名
						$filename = $this->outfileAryI;
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
						
						$data .=$ary[$i]."\t";
					}
				}
			}
	
			if(!empty($data)){
				
				$outdata .= trim($data)."\n";
			}
			$n++;
		}
		$this->DirCleanling();
		if(!empty($outdata)){
			$outdata = $itme."\n".$outdata;
			$OutData = mb_convert_encoding($outdata,"SJIS", "UTF-8");
			$OutFile = $this->outFileDir.$filename;

			$fp = fopen($OutFile,"w");
			fwrite($fp,$OutData);
			fclose($fp);
		}
		
	}*/
	
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
			$filename ='trabid_i';
		}
		elseif($naigai=='d'){
			$Item =array(
				'Target campaign',
				'Target ad group',
				'tour_price(text)',
			);
			$filename ='trabid_d';
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
		$path = $this->outFileDir.'trabid_'.'*';
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
	
			if($ymd !='trabid_d' && $ymd !='trabid_i'){
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

				if($naigai=='i' && empty($Required)){	
					$Required ='元URL';
				}
				elseif($naigai=='d' && empty($Required)){
					$Required ='URL(text)';
				}
			}
		 }

		if(empty($naigai)){
			return 'no destination_url1 or url';
		}

		$dataAry='';
		foreach($obj as $n =>$data){
				$ary='';
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
		
		$rqAry='';
		foreach($this->setDataAry as $i => $ary){

			if(isset($ary['naigai'])){
				if($ary['naigai'] =="i"){
					$rqAry['i'][$ary['元URL']]=$ary['rqUrl'];
					
				}
				elseif($ary['naigai'] == "d"){
					$rqAry['d'][$ary['URL(text)']]=$ary['rqUrl'];
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
						if($naigai == 'i' ){
							$min_price = '料金無しのため掲載なし';
						}
						elseif($naigai == 'd'){
							$min_price = '料金無しのため掲載なし';
						}
						
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

							$nokori = ($min % 100000000) % 10000;

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
									$min_price=$min_price.'〜';
								}
							}
						}
						elseif($naigai == 'd'){
							// 最低価格
							$min	= intval($aryAllPrise[0]);
							$man = floor(($min % 100000000) / 10000);

							$nokori = ($min % 100000000) % 10000;

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
						}

					}
					///// コース名取得 /////
					//class="tourName">【札幌駅・大谷地・新札幌】ラベンダー香る富良野・美瑛と十勝岳連峰　日帰り</p>
					$course_name='';
					if(isset($aryRq['html'])){
						$courseTag_start = strpos( $aryRq['html'] , 'class="tourName"' );
						if( !empty($courseTag_start) ){
							$courseTag_str = substr($aryRq['html'], $courseTag_start, 300);
							$courseName_start	= strpos( $courseTag_str , '>' );
							$courseName_str		= substr( $courseTag_str , ($courseName_start+1));
							$courseName_end		= strpos( $courseName_str , '</p>' );
							$course_name		= substr( $courseName_str , 0 , $courseName_end );
						}
					}
					unset($aryRq);
				
					$rqdata['tour_price(text)'] = $min_price;
					
					//$rqdata['コース名'] = $course_name;
					
					$this->getRqDataAry[$url]= $rqdata;
				
					unset($rqdata);
					sleep(1);
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
		
		$this->outfileAryI ='trabid_i.xlsx';
		$this->outfileAryD ='trabid_d.xlsx';
		
		
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
				if($Type == FTP_ASCII && stripos($file,'.zip') !== false){
					continue;
				}
				elseif($Type == FTP_BINARY && stripos($file,'.zip') === false){
					continue;
				}
				/*if(stripos($file,'_'.date("Ymd").'.tsv')!==false){
					ftp_put($this->conn_id, $file, $this->TgLocalDir.$file, $Type);
				}*/
				if(stripos($file,'trabid_d.xlsx')!==false){
					
					ftp_put($this->conn_id, $file, $this->TgLocalDir.$file,FTP_BINARY);
				}
				if(stripos($file,'trabid_i.xlsx')!==false){
					
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

