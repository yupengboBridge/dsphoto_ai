<?php
########################################
#	専門店へのリンクを作る
########################################
/*
	最適化データを利用して、存在する国・方面のみの専門店へのリンクを、出発地ごとにHTMl生成する。
	こたにくんのために作るけど、他でも使えるかも！
*/


/*--------------------
	初期設定
----------------------*/
$xRootPath = '/home2/chroot/home/xhankyu/public_html/photo_db/';
//$xRootPath = '/home/xhankyu/public_html/photo_db/';
$MyMainDir = $xRootPath . 'others/senmonten/';

/*ディレクトリ*/
$MyHtmlDir = $MyMainDir . 'html/';

/*最適化ファイル*/
$DestListI = $xRootPath . 'csv/ab_destination_list.csv';
$DestListD = $xRootPath . 'csv/dome_destination_list.csv';

/*発地の対応表ファイル*/
$HatsuListI = $xRootPath . 'csv/i_p_hatsu_url.csv';
$HatsuListD = $xRootPath . 'csv/d_p_hatsu_url.csv';

/*拠点専門店リスト*/
$KyotenSenmonListI = $xRootPath . 'csv/i_hatsu_country_url.csv';
$KyotenSenmonListD = $xRootPath . 'csv/d_hatsu_prefecture_url.csv';

/*専門店トップリスト*/
$SenmonTopListI = $xRootPath . 'csv/i_p_country_url.csv';
$SenmonTopListD = $xRootPath . 'csv/d_p_prefecture_url.csv';

/*全専門店・拠点専門店リスト*/
$SenmonListAll = $xRootPath . 'csv/senmon_list.csv';

//全拠点一覧
$kyotenList = array('tyo','osa','ngo','fuk','spk','sdj','chs');




/*--------------------
	動くよ
----------------------*/
//海外
new MakeKyotenHtmlI;

//国内
new MakeKyotenHtmlD;



/*--------------------
	モジュール
----------------------*/
/*
	海外のHTMLを作ります
*/
class MakeKyotenHtmlI{

	var $HatsuTypeAry;
	var $HatsuCountryAry;
	var $ValidURL;

	/*起動項目*/
	function __construct(){
		global $HatsuListI, $DestListI, $KyotenSenmonListI, $SenmonTopListI, $SenmonListAll;

		/*DS発地と7拠点の対応表を作ります*/
		//対応表の元となるリスト
		$this->HatsuList = $HatsuListI;
		//データの変数名（海外と国内で変わるから、ここに置いておく）
		$dataVarAry = array('p_hatsu','p_hatsu_name','url','letter','view_name','view_type');
		//対応表つくる
		$this->MakeCorHatsuKyoten($dataVarAry);

		/*最適化ファイルで存在する国名を拠点毎にまとめる*/
		//元となるリスト
		$this->DestList = $DestListI;
		//データの変数名（海外と国内で変わるから、ここに置いておく）
		$dataVarAry = array('p_hatsu','p_hatsu_name','p_dest','p_dest_name','p_country','p_country_name');
		//まとめつくる
		$this->MakeHatsuCountry($dataVarAry);

		/*拠点専門店のURL一覧で存在確認*/
		//元となるリスト
		$this->KyotenSenmonList = $KyotenSenmonListI;
		//データの変数名（海外と国内で変わるから、ここに置いておく）
		$dataVarAry = array('p_country','p_country_name','tyo','osa','ngo','fuk','spk','sdj','chs');
		//有効国リスト
		$this->MakeValidHatsuCountry($dataVarAry);

		/*専門店トップのURL一覧で存在確認*/
		//元となるリスト
		$this->SenmonTopList = $SenmonTopListI;
		//データの変数名（海外と国内で変わるから、ここに置いておく）
		$dataVarAry = array('p_country','p_country_name','url');
		//有効国リスト最終
		$this->MakeValidHatsuCountry2($dataVarAry);

		/*せっせと作った拠点URLのリンクでHTML生成に必要な配列作る*/
		//元となるリスト
		$this->SenmonListAll = $SenmonListAll;
		//データの変数名（海外と国内で変わるから、ここに置いておく）
		$dataVarAry = array('kyoten','dest_name','senmon_name','url');
		//書き出す直前！
		$this->MakeKyotenData($dataVarAry);

		/*配列を元に書き出します*/
		$this->WriteHtml();

//print_r($this->forHtmlAry);

//ちょっと今だけ確認する
//foreach($this->ALLValidURL as $url){
//	if(empty($this->SenmonListAllAry[$url])){
//		echo $url;
//	}
//}

	}


	/*------拠点専門店のURL一覧で存在確認------*/
	function WriteHtml(){
		global $MyHtmlDir;
		foreach($this->forHtmlAry as $Hatsu => $DestAry){
			/*この階層はHTMLを書き出す ここから*/

			//まず、方面毎にぐるぐる回してHTMLを準備します
			$HTML = NULL;
			$Cnt = 0;
			foreach($DestAry as $DestName => $CountriesAry){
				//海外は日本だったら飛ばす
				if($DestName == '日本'){
					continue;
				}
				//国毎にしてliを作ります
				$HTML_li = NULL;
				//最後を知っておきたいから、forで書くよ
				$CountriesCnt = count($CountriesAry);
				for($i=0; $i<$CountriesCnt; $i++){
					if($i == $CountriesCnt-1){	//最後の国の場合
						$LiClass = ' class="senmon_li_end"';
					}
					else{
						$LiClass = '';
					}
					$HTML_li .=<<<EOD
<li{$LiClass}><a href="{$CountriesAry[$i]['url']}">{$CountriesAry[$i]['name']}</a></li>

EOD;
				}
				//最初だけstartクラスを付ける
				$DivClass = '';
				if($Cnt == 0){
					$DivClass = ' start';
				}
				//そしたら、方面毎のHTML
				$HTML .=<<<EOD
<div class="senmon_list{$DivClass}">
<p class="senmon_dest"><span class="dest_name">{$DestName}</span><span class="arrow_i">≫</span></p>
<ul class="senmon_country">
{$HTML_li}
</ul>
</div>

EOD;
				$Cnt++;
			}

			//そしたら、ページの生成
			$this->WriteStr =<<<EOD
<div class="senmon_box">
<div class="senmon_box_head">
<h2><span class="senmon_head_i">まだまだあります！ 海外旅行</span></h2>
</div>
<div class="senmon_box_contents">
{$HTML}
</div>
</div> 

EOD;

			$this->WriteFileName = $MyHtmlDir . $Hatsu . '_i.html';
			$this->WriteHtmlAction();
			/*この階層はHTMLを書き出す ここまで*/
		}
	}


	/*------書き出しファンクション------*/
	function WriteHtmlAction (){
		if (!$handle = fopen($this->WriteFileName, 'w')) {
			exit;
		}
		if (fwrite($handle, $this->WriteStr) === FALSE) {
			exit;
		}
		fclose($handle);
	}


	/*------拠点専門店のURL一覧で存在確認------*/
	function MakeKyotenData($dataVarAry){
		//CSVファイルが無かったらサヨナラ
		if(!is_file($this->SenmonListAll)){
			return;
		}

		$handle = fopen($this->SenmonListAll, "r");
		if ($handle) {
			while (!feof($handle)) {
				$buffer = rtrim(fgets($handle, 9999));	//日本語ファイルはfgetcsv使うのやめておく
				if(!empty($buffer)){
					$data = explode("\t", str_replace('"', '', $buffer));
					foreach($dataVarAry as $key => $val){
						$$val = $data[$key];
					}
					if(empty($kyoten)){
						continue;
					}
					//★☆いまだけ確認用に！！
					$this->SenmonListAllAry[$url] = 1;

					//有効な国だけを残して専門店リンク
					if(empty($this->ValidURL[$url]) && $senmon_name != '沖縄離島'){
							continue;
					}
					$this->forHtmlAry[$kyoten][$dest_name][] = array(
						 'name' => $senmon_name
						,'url' => $url
					);
				}
			}
		}
	}


	/*------拠点専門店のURL一覧で存在確認------*/
	function MakeValidHatsuCountry2($dataVarAry){
		//CSVファイルが無かったらサヨナラ
		if(!is_file($this->SenmonTopList)){
			return;
		}

		$handle = fopen($this->SenmonTopList, "r");
		if ($handle) {
			while (!feof($handle)) {
				$buffer = rtrim(fgets($handle, 9999));	//日本語ファイルはfgetcsv使うのやめておく
				if(!empty($buffer)){
					$data = explode(',', str_replace('"', '', $buffer));
					foreach($dataVarAry as $key => $val){
						$$val = $data[$key];
					}
					if(empty($p_country)){
						continue;
					}
					//★☆いまだけ確認用に！！
					$this->ALLValidURL[] = $url;
					//国が存在したら、URLを！
					if(empty($this->HatsuCountryAry['top'][$p_country])){
						continue;
					}
					$this->ValidURL[$url] = 1;
				}
			}
		}
	}


	/*------拠点専門店のURL一覧で存在確認------*/
	function MakeValidHatsuCountry($dataVarAry){
		global $kyotenList;
		//CSVファイルが無かったらサヨナラ
		if(!is_file($this->KyotenSenmonList)){
			return;
		}

		$handle = fopen($this->KyotenSenmonList, "r");
		if ($handle) {
			while (!feof($handle)) {
				$buffer = rtrim(fgets($handle, 9999));	//日本語ファイルはfgetcsv使うのやめておく
				if(!empty($buffer)){
					$data = explode(',', str_replace('"', '', $buffer));
					foreach($dataVarAry as $key => $val){
						$$val = $data[$key];
					}
					if(empty($p_country)){
						continue;
					}
					//発地毎にデータまとめる
					foreach($kyotenList as $kyoten){
						//★☆いまだけ確認用に！！
						$this->ALLValidURL[] = $$kyoten;
						//国が存在したら、URLを！
						if(empty($this->HatsuCountryAry[$kyoten][$p_country])){
							continue;
						}
						$this->ValidURL[$$kyoten] = 1;
					}
				}
			}
		}
	}


	/*------最適化ファイルで存在する国名の拠点毎まとめ------*/
	function MakeHatsuCountry($dataVarAry){
		//CSVファイルが無かったらサヨナラ
		if(!is_file($this->DestList)){
			return;
		}

		$handle = fopen($this->DestList, "r");
		if ($handle) {
			while (!feof($handle)) {
				$buffer = rtrim(fgets($handle, 9999));	//日本語ファイルはfgetcsv使うのやめておく
				if(!empty($buffer)){
					$data = explode(',', str_replace('"', '', $buffer));
					foreach($dataVarAry as $key => $val){
						$$val = $data[$key];
					}
					if(!is_numeric($p_hatsu)){
						continue;
					}
					if(empty($this->HatsuTypeAry[$p_hatsu])){
						continue;
					}
					$myKyoten = $this->HatsuTypeAry[$p_hatsu];
					$this->HatsuCountryAry[$myKyoten][$p_country] = 1;
					//全発地分もやる
					$this->HatsuCountryAry['top'][$p_country] = 1;
				}
			}
		}
	}


	/*------DS発地と7拠点の対応表------*/
	function MakeCorHatsuKyoten($dataVarAry){
		//CSVファイルが無かったらサヨナラ
		if(!is_file($this->HatsuList)){
			return;
		}

		$handle = fopen($this->HatsuList, "r");
		if ($handle) {
			while (!feof($handle)) {
				$buffer = rtrim(fgets($handle, 9999));	//日本語ファイルはfgetcsv使うのやめておく
				if(!empty($buffer)){
					$data = explode(',', str_replace('"', '', $buffer));
					foreach($dataVarAry as $key => $val){
						$$val = $data[$key];
					}
					if(!is_numeric($p_hatsu)){
						continue;
					}
					//hijとchsの置換をします
					if($letter == 'hij'){
						$letter = 'chs';
					}
					$this->HatsuTypeAry[$p_hatsu] = $letter;
				}
			}
		}
	}
}




class MakeKyotenHtmlD extends MakeKyotenHtmlI{
	/*起動項目*/
	function __construct(){
		global $HatsuListD, $DestListD, $KyotenSenmonListD, $SenmonTopListD, $SenmonListAll;

		/*DS発地と7拠点の対応表を作ります*/
		//対応表の元となるリスト
		$this->HatsuList = $HatsuListD;
		//データの変数名（海外と国内で変わるから、ここに置いておく）
		$dataVarAry = array('p_hatsu','p_hatsu_name','p_hatsu_sub','p_hatsu_sub_name','url','letter','view_name','view_type');
		//対応表つくる
		$this->MakeCorHatsuKyoten($dataVarAry);
		/*最適化ファイルで存在する国名を拠点毎にまとめる*/
		//元となるリスト
		$this->DestList = $DestListD;
		//データの変数名（海外と国内で変わるから、ここに置いておく）
		$dataVarAry = array('p_hatsu','p_hatsu_name','p_hatsu_sub','p_hatsu_sub_name','p_dest','p_dest_name','p_country','p_country_name');
		//まとめつくる
		$this->MakeHatsuCountry($dataVarAry);

		/*拠点専門店のURL一覧で存在確認*/
		//元となるリスト
		$this->KyotenSenmonList = $KyotenSenmonListD;
		//データの変数名（海外と国内で変わるから、ここに置いておく）
		$dataVarAry = array('p_country','p_country_name','tyo','osa','ngo','fuk','spk','sdj','chs');
		//有効国リスト
		$this->MakeValidHatsuCountry($dataVarAry);

		/*専門店トップのURL一覧で存在確認*/
		//元となるリスト
		$this->SenmonTopList = $SenmonTopListD;
		//データの変数名（海外と国内で変わるから、ここに置いておく）
		$dataVarAry = array('p_country','p_country_name','url');
		//有効国リスト最終
		$this->MakeValidHatsuCountry2($dataVarAry);

		/*せっせと作った拠点URLのリンクでHTML生成に必要な配列作る*/
		//元となるリスト
		$this->SenmonListAll = $SenmonListAll;
		//データの変数名（海外と国内で変わるから、ここに置いておく）
		$dataVarAry = array('kyoten','dest_name','senmon_name','url');
		//書き出す直前！
		$this->MakeKyotenData($dataVarAry);

		/*配列を元に書き出します*/
		$this->WriteHtml();

//print_r($this->forHtmlAry);

//ちょっと今だけ確認する
//foreach($this->ALLValidURL as $url){
//	if(empty($this->SenmonListAllAry[$url])){
//		echo $url;
//	}
//}

	}


	/*------拠点専門店のURL一覧で存在確認------*/
	function WriteHtml(){
		global $MyHtmlDir;
		foreach($this->forHtmlAry as $Hatsu => $DestAry){
			/*この階層はHTMLを書き出す ここから*/

			//まず、方面毎にぐるぐる回してHTMLを準備します
			$HTML = NULL;
			foreach($DestAry as $DestName => $CountriesAry){
				//国毎にしてliを作ります
				$HTML_li = NULL;
				//最後を知っておきたいから、forで書くよ
				$CountriesCnt = count($CountriesAry);
				for($i=0; $i<$CountriesCnt; $i++){
					if($i == $CountriesCnt-1){	//最後の国の場合
						$LiClass = ' class="senmon_li_end"';
					}
					else{
						$LiClass = '';
					}
					$HTML_li .=<<<EOD
<li{$LiClass}><a href="{$CountriesAry[$i]['url']}">{$CountriesAry[$i]['name']}</a></li>

EOD;
				}
				//そしたら、方面毎のHTML
				$HTML .=<<<EOD
<div class="senmon_list start">
<ul class="senmon_country dest_d">
{$HTML_li}
</ul>
</div>

EOD;
			}

			//そしたら、ページの生成
			$this->WriteStr =<<<EOD
<div class="senmon_box">
<div class="senmon_box_head">
<h2><span class="senmon_head_d">まだまだあります！ 国内旅行</span></h2>
</div>
<div class="senmon_box_contents">
{$HTML}
</div>
</div> 

EOD;

			$this->WriteFileName = $MyHtmlDir . $Hatsu . '_d.html';
			$this->WriteHtmlAction();
			/*この階層はHTMLを書き出す ここまで*/
		}
	}

}


?>