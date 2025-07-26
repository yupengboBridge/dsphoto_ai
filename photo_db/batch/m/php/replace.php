<?php error_reporting(0);
try {
	
	$serverRoot = "/home/xhankyu/public_html/m/";// 10.0.5.2
		
	$encodeForm = "UTF-8";// SJIS
	$encodeTo = "UTF-8";
	$rowTextTo = "auto";// GBK
	$bunkatu = mb_convert_encoding("〓〓", 'SJIS');
	
	/* 这里是读取模板文件夹中的文件中的模板*/
	$templateDir = $serverRoot.'template';//根目录
	$newFileDir = $serverRoot.'replaced';//根目录
	
	if (!is_dir($templateDir)) {
		
		echo "no template Dir";
		return ;
	}
	if (!is_dir($newFileDir)) {
		
		mkdir($newFileDir);
		chmod($newFile, 0777);
	}
	
	$templatePath = scandir($templateDir);// Array ( [0] => . [1] => .. [2] => ngo [3] => tyo )
	$templateCount = count($templatePath);// 计算数组的长度
	//echo "1<br/>";
	// *********文件夹  排序  【开始】*********************************************************************************** //
	//$a = "$serverRoot";
	if (is_dir($serverRoot)) {
	
		if ($dh = opendir($serverRoot)) {
			$i = 0;
			while (($file = readdir($dh)) !== false) {
	
				if ($file != "." && $file != "..") {
					if(!strpos($file,'tyohawaii')){
						continue;	
					}
					$files[$i]["name"] = $file;
					
					$i++;
				}
			}
		}
		closedir($dh);
		foreach ($files as $k => $v) {
			
			$name[$k] = $v['name'];
		}
		array_multisort($name, SORT_DESC, SORT_STRING, $name);//按名字排序
	}

	// 循环目录下的模板文件
	for($i = 0; $i < $templateCount; $i++) {
		
		if ($templatePath[$i] == '.' || $templatePath[$i] == '..') {
			
			continue;
		}
		//echo "3<br/>";
		$templatePath1 ="$templateDir/$templatePath[$i]";// .../template目录下面的目录
		$temp = $templatePath[$i];// 倒数第二个路径
		//echo "<br/>++++++++++++++$temp+++++++++++++++++<br/> ";
		$templatePath2 = scandir($templatePath1);
		$templateCount2 = count($templatePath2);
		foreach ($templatePath2 as $templateCount2 => $tempValue) {
			
			if ($tempValue == '.' || $tempValue == '..') {
				
				continue;
			}
			
			$tempname = $tempValue;
			//echo "4<br/>";
			
			$path = "$templatePath1/$tempname"; // 全路径
			//echo "<br/>++++++++++++++$path++++++++++++<br/>";
			
			// 读取   模板的   文本文件
			$cbody = file($path);
			$isXing = false;
			$replaceText = '';
			// 循环读取 模板文件 中的每一行
			foreach ($cbody as $line_num => $text) {
				//echo "5<br/>";
				//$text = mb_convert_encoding($text, 'SJIS');
				//echo "<br/>***c************************************<br/>";
				//echo "text:<input value='$text' style='width: 600px;'/><br/>";
				// 截取 需要的字符
				//echo $text;
				$tmpTestList = explode('〓〓', $text);
				if (count($tmpTestList) < 3) {
					
					$text = str_replace("この記事の続きを読む >>", mb_convert_encoding("この記事の続きを読む >>", 'SJIS','auto'), $text);
					$replaceText .= $text;
					continue;
				}
				//echo "61<br/>";
				$xian = strstr($tmpTestList[1], "_");
				if ($xian) {
						
					$ids = explode("_", $tmpTestList[1]);
					$id = $ids[0];
					$miDate = intval($ids[1]);
					$miIndex = intval($ids[1]);
				} else {
						
					$id = $tmpTestList[1];
					$miDate = intval(1);
				}
				//echo $id."<br/>";
				$date = date("ymd");
				$a = true;
				//echo "6<br/>";
				if ($miDate == "*") {
					
					//echo "7<br/>";
					$isXing = true;
					$replaceText = '';
					break;
				}
				
				//echo "8<br/>";
				// 循环遍历文件夹 （根据$miDate 得到相应的文件夹）
				foreach ($name as $key => $val) {
					//echo "9<br/>";
					$fileNme = $name[$key];
					// ---------- yahv 2015-1-30 add ----------
					if (date('y') != substr($fileNme, 0, 2)) {
						continue;
					}
					// ========== yahv 2015-1-30 add ==========
					$fileNmeSubStr = substr($fileNme, 6);
					if ($fileNmeSubStr != $temp) {
				
						continue;
					} else {
				
						$miDate--;
					}
						
					if ($miDate > 0) {
				
						continue;
					} else if ($miDate < 0) {
				
						break;
					}
					if ($id == "hawaiidate") {
				
						$nian = substr($fileNme, 0, 2);
						$yue = substr($fileNme, 2, 2);
						$ri = substr($fileNme, 4, 2);
						$a = "20".$nian.mb_convert_encoding("年", 'SJIS','auto').$yue.mb_convert_encoding("月", 'SJIS','auto').$ri.mb_convert_encoding("日号", 'SJIS','auto');
						
						if (count($tmpTestList) > 4) {
							
							for ($index=3; $index<count($tmpTestList); $index++) {
								
								if (($index%2)==1) {
									
									$ids2 = explode("_", $tmpTestList[3]);
									$id2 = $ids2[0];
									$miIndex2 = intval($ids2[1]);
									$rowText2 = getHtml($id2,$fileNme);
									$text = str_replace('〓〓'."{$id2}_{$miIndex2}".'〓〓', $rowText2, $text);
									//echo $text.'<br/>';									
								}
							}
						}
						$text = str_replace("イメージ", mb_convert_encoding("イメージ", 'SJIS','auto'), $text);
						$text = str_replace("この記事の続きを読む >>", mb_convert_encoding("この記事の続きを読む >>", 'SJIS','auto'), $text);
						$replaceText .= str_replace('〓〓'."{$id}_{$miIndex}".'〓〓', $a, $text);//"20$nian年$yue月$ri日号"
						//echo $replaceText;
						continue;
					}
					if ($id == 'hawaiiurl') {
				
						$b = "http://x.hankyu-travel.com/m/".$fileNme."/index.html";
						if (count($tmpTestList) > 4) {
							
							for ($index=3; $index<count($tmpTestList); $index++) {
								
								if (($index%2)==1) {
									
									$ids2 = explode("_", $tmpTestList[3]);
									$id2 = $ids2[0];
									$miIndex2 = intval($ids2[1]);
									$rowText2 = getHtml($id2,$fileNme);
									$text = str_replace('〓〓'."{$id2}_{$miIndex2}".'〓〓', $rowText2, $text);
									//echo $text.'<br/>';									
								}
							}
						}	
						$rowText = getHtml($id,$fileNme);
						$text = str_replace("イメージ", mb_convert_encoding("イメージ", 'SJIS','auto'), $text);
						
						$text = str_replace("この記事の続きを読む >>", mb_convert_encoding("この記事の続きを読む >>", 'SJIS','auto'), $text);
						$replaceText .= str_replace('〓〓'."{$id}_{$miIndex}".'〓〓', $b, $text);
						//echo $replaceText;
						continue;
					}
					// 模板文件中出现两组==xxx==的时候，第二组的处理方法
					if (count($tmpTestList) > 4) {
						
						for ($index=3; $index<count($tmpTestList); $index++) {
							
							if (($index%2)==1) {
								
								$ids2 = explode("_", $tmpTestList[3]);
								$id2 = $ids2[0];
								$miIndex2 = intval($ids2[1]);
								$rowText2 = getHtml($id2,$fileNme);
								$text = str_replace('〓〓'."{$id2}_{$miIndex2}".'〓〓', $rowText2, $text);
								//echo $text.'<br/>';									
							}
						}
					}
					$rowText = getHtml($id,$fileNme);
					$text = str_replace("イメージ", mb_convert_encoding("イメージ", 'SJIS','auto'), $text);						
					$text = str_replace("この記事の続きを読む >>", mb_convert_encoding("この記事の続きを読む >>", 'SJIS','auto'), $text);
					$replaceText .= str_replace('〓〓'."{$id}_{$miIndex}".'〓〓', $rowText, $text);
						//echo $replaceText;
				}
				//$replaceText .= str_replace('〓〓'."{$id}_{$miDate}".'〓〓', $strInnerText, $text).PHP_EOL;				
			}
			
			// **********遇到  "*" 的 处理 ************************************************************************* //
			
			/**
			 * 1.首先   循环  排序后的文件夹 
			 * 2.然后  循环  template文件
			 * 3.
			 */
			if ($isXing) {
				//echo "a<br/>";
				//echo "<br/>________$replaceText-------------------<br/>";
				foreach ($name as $key => $val) {
				
					$fileNme = $name[$key];
					// ---------- yahv 2015-1-30 add ----------
					if (date('y') != substr($fileNme, 0, 2)) {
						continue;
					}
					// ========== yahv 2015-1-30 add ==========
					$fileNmeSubStr = substr($fileNme, 6, 9);
					//echo "<br/>========$fileNmeSubStr=============<br/>";
					if ($fileNmeSubStr == 'tyohawaii') {
						//echo "b<br/>";
						////////////////////////////////////////////////////////////////////
						// 循环读取 模板文件 中的每一行
						foreach ($cbody as $line_num => $text) {
							
							//$text = mb_convert_encoding($text, 'SJIS');
							//echo "<br/>***c************************************<br/>";
							//echo "text:<input value='$text' style='width: 600px;'/><br/>";
						
							// 截取 需要的字符
							$tmpTestList = explode('〓〓', $text);
							$aaaaa = count($tmpTestList);
							if (count($tmpTestList) < 3) {
									
								$text = str_replace("この記事の続きを読む >>", mb_convert_encoding("この記事の続きを読む >>", 'SJIS','auto'), $text);
								$replaceText .= $text;
								continue;
							}

							$ids = explode("_", $tmpTestList[1]);
							$id = $ids[0];
							$miDate = $ids[1];
							$miIndex = $ids[1];
							$date = date("ymd");
							$a = true;
							//$date
							if ($id == "hawaiidate") {
								
								$nian = substr($fileNme, 0, 2);
								$yue = substr($fileNme, 2, 2);
								$ri = substr($fileNme, 4, 2);
								//$a = "20".$nian."年".$yue."月".$ri."日号";
								$a = mb_convert_encoding("20".$nian."年".$yue."月".$ri."日号", 'SJIS','auto');
								//echo "1"."<br/>";
								if (count($tmpTestList) > 4) {
										
									for ($index=3; $index<count($tmpTestList); $index++) {
					
										if (($index%2)==1) {
												
											$ids2 = explode("_", $tmpTestList[3]);
											$id2 = $ids2[0];
											$miIndex2 = $ids2[1];
											
											$rowText2 = getHtml($id2,$fileNme);
											$text = str_replace('〓〓'."{$id2}_{$miIndex2}".'〓〓', $rowText2, $text);
											//echo $text.'<br/>';
										}
									}
								}
								//$rowText = getHtml($id,$fileNme);
								$text = str_replace("イメージ", mb_convert_encoding("イメージ", 'SJIS','auto'), $text);
								$text = str_replace("この記事の続きを読む >>", mb_convert_encoding("この記事の続きを読む >>", 'SJIS','auto'), $text);
								$replaceText .= str_replace('〓〓'."{$id}_{$miIndex}".'〓〓', $a, $text);//"20$nian年$yue月$ri日号"
								continue;
							}

							if ($id == 'hawaiiurl') {
								$b = "http://x.hankyu-travel.com/m/".$fileNme."/index.html";
								//echo "2"."<br/>";
								if (count($tmpTestList) > 4) {
										
									for ($index=3; $index<count($tmpTestList); $index++) {
					
										if (($index%2)==1) {
												
											$ids2 = explode("_", $tmpTestList[3]);
											$id2 = $ids2[0];
											$miIndex2 = $ids2[1];

											//echo "<br/>====$id2====$miIndex2===<br/>";
											$rowText2 = getHtml($id2, $fileNme);
											$text = str_replace('〓〓'."{$id2}_{$miIndex2}".'〓〓', $rowText2, $text);
											//echo $text.'<br/>';
										}
									}
								}
								$rowText = getHtml($id,$fileNme);
								$text = str_replace("イメージ", mb_convert_encoding("イメージ", 'SJIS','auto'), $text);
								$text = str_replace("この記事の続きを読む >>", mb_convert_encoding("この記事の続きを読む >>", 'SJIS','auto'), $text);
								$replaceText .= str_replace('〓〓'."{$id}_{$miIndex}".'〓〓', $b, $text);
								continue;
							}
							//echo "**************".$fileNme.'<br/>';
							//echo "e<br/>";
							// 模板文件中出现两组==xxx==的时候，第二组的处理方法

							//echo "33333**************".$aaaaa.'<br/>';
							if (count($tmpTestList) > 4) {
								
								for ($index=3; $index<count($tmpTestList); $index++) {
								
									if (($index%2)==1) {
											
										$ids2 = explode("_", $tmpTestList[3]);
										$id2 = $ids2[0];
										$miIndex2 = $ids2[1];
										$rowText2 = getHtml($id2, $fileNme);
										$text = str_replace('〓〓'."{$id2}_{$miIndex2}".'〓〓', $rowText2, $text);
									}
								}
							}
							$rowText = getHtml($id,$fileNme);
							$text = str_replace("イメージ", mb_convert_encoding("イメージ", 'SJIS','auto'), $text);
							$text = str_replace("この記事の続きを読む >>", mb_convert_encoding("この記事の続きを読む >>", 'SJIS','auto'), $text);
							$replaceText .= str_replace('〓〓'."{$id}_{$miIndex}".'〓〓', $rowText, $text);
						}
						////////////////////////////////////////////////////////////////////
					}
				}
			}
			
			// *********************************************************************************** //
			
			$newFile = "$newFileDir/$temp";
			if (!is_dir($newFile)) {
			
				mkdir($newFile);
				chmod($newFile, 0777);
			}
			//$replaceName = "replace.html";
			// 写入文件
			$this_php_file_charset = 'utf-8';
			//$p=iconv($this_php_file_charset,"utf-8",$replaceText);
			//$p=mb_convert_encoding($replaceText, "UTF-8");
			//$p=mb_convert_encoding($replaceText, "SJIS");
			//file_put_contents("$newFile/"."replace_"."$tempValue",$p);
			file_put_contents("$newFile/"."replace_"."$tempValue", $replaceText);
			chmod("$newFile/"."replace_"."$tempValue", 0777);
			$replaceText = '';
		}
	}
			echo mb_convert_encoding('メルマガ処理が全部終わりました。', 'SJIS','auto');
} catch (Exception $e) {
	print_r($e);
}

function getHtml($id,$fileNme) {
	$serverRoot = "/home/xhankyu/public_html/m/";
	
	$encodeForm = "UTF-8";// SJIS
	//$encodeTo = "UTF8";
	$encodeTo = "UTF-8";
	$rowTextTo = "auto";// GBK
	
	$filePath = $serverRoot.$fileNme."/";
	//echo $filePath.'<br/>';
	$htmlPath = scandir($filePath);// Array ( [0] => . [1] => .. [2] => tempA.html [3] => tempB.html [4] => tempC.html )
	//print_r($htmlPath);
	$htmlPathCount = count($htmlPath);// 计算数组的长度
	//echo 'b'.'<br/>';
	//print_r($htmlPathCount);
	//echo "18<br/>";
	//echo "aaaaa: $filePath<br/>";
	//因为数组前两个默认为. 和..，故有意义的下标应该为2
	// 循环目录下的模板文件
	for ($htmlCount = 0; $htmlCount < $htmlPathCount; $htmlCount++) {
		//echo 'c'.'<br/>';
		if ($htmlPath[$htmlCount] == '.' || $htmlPath[$htmlCount] == '..') {
	
			continue;
		}
		$htmlPath = $filePath.$htmlPath[$htmlCount];
		// 需要处理的文件（html）
		//echo $htmlPath;
		if (is_file($htmlPath) && substr($htmlPath, -5) == ".html") {
			//substr($htmlName, -5) == ".html") {
	
			// 有这个文件，进行处理
			/* 先 读取文件中的html文件的源码
			 * 根据上面截取的id 进行搜索
			*/
			$htmlRows = file($htmlPath);
			$isAri = false;
			// 得到 html 文本中的  每一行
			for ($k = 0; $k < count($htmlRows); $k++) {
				$rowText = $htmlRows[$k];
				$left = '"';
				$htmlId = "id=".$left.$id.$left;
				$rowText2 = strstr($rowText, $htmlId);
	
				// 这个是  整行  的处理
				if ($rowText2) {
					$ltList1 = strpos($rowText, "<");
					$ltList2 = strpos($rowText, " ");
	
					// 得到  所在行的  标签
					$rest = substr($rowText, $ltList1+1, $ltList2-1);
					//echo "mmmmmm:<textarea>$rest</textarea><br/>";
	
					// 得到  字符串   所在行的  下一行
					for ($l = $k + 1; $l < count($htmlRows); $l++) {
	
						// 查找 结束标签  （存在：break；不存在：内容行累加）
						if($rest == 'img'){
							$rowTextCunzai = strstr($rowText, ">");
						}else{
							$rowTextCunzai = strstr($rowText, $rest.">");
						}
						//$rowTextCunzai = strstr($rowText, $rest.">");
						if ($rowTextCunzai) {
	
							break;
						} else {
	
							//$rowText .= mb_convert_encoding($htmlRows[$l], 'UTF-8');
							$rowText .= $htmlRows[$l];
						}
					}

					if($rest != 'img'){
						//echo "+++++++++++rowwwwww:<textarea>$rowText</textarea><br/>";
						$one = strpos($rowText, ">");// strpos 查找  字符   出现的   第一个位置
						$last = strrpos($rowText, "<");// strrpos 查找  字符   出现的   最后一个位置
						$rowText1 = substr($rowText,0, $last);// 截取符合条件  之前  的字符
						$rowText = substr($rowText1, $one + 1);// 截取符合条件  之后  的字符
						//echo ">>>>>>>>>>>>>>>>>>>>>>>>>rowwwwww:<textarea>$rowText</textarea><br/>";
					}
					
					$rowImg = strstr($rowText, "src=");// strstr 查找字符  所在的  行
					
					// 这个是 Img 的处理
					if ($rowImg) {
						$imgOne = strpos($rowText, "src=");// strpos 查找  字符   出现的   第一个位置
						$imgLast = strrpos($rowText, "alt");// strrpos 查找  字符   出现的   最后一个位置
	
						// 截取符合条件  之前  的字符(-2 是去掉一个空格和最后的 ",如:id=" " )
						$searchImg = substr($rowText, 0, $imgLast - 2);
						$rowText = substr($searchImg, $imgOne + 5);// 截取符合条件  之后  的字符
					}
					$isAri = true;
					break;
				}
			}

			if ($isAri) {
				
				return $rowText;
			} else {
				
				return '';
			}
		} else {
			
			return '';
		}
	}
}



