<?php
header("Content-type: text/html; charset=UTF-8");
require_once('./config.php');
require_once('./lib.php');

try
{
	// ＤＢへ接続します。
	$db_link = db_connect();

	//$src_ary = array(" スポーツ ゴルフ "," 湖 "," 動物園・水族館 ");
	//$rep_ary = array(" 施設 ゴルフ場 "," 湖沼 "," 動物園・水族館・遊園地 ");
	$src_ary = array(" 湖 ");
	$rep_ary = array(" 湖沼 ");
//	$rep_ary = array(" スポーツ ゴルフ "," 湖 "," 動物園・水族館 ");
//	$src_ary = array(" 施設 ゴルフ場 "," 湖沼 "," 動物園・水族館・遊園地 ");
	$html = "";
//	$html2 = "";
	for ($i = 0; $i < count($src_ary); $i++)
	{
		$keyword = $src_ary[$i];

		$sql_keyword = "select * from keyword where keyword_name like '%".$keyword."%' order by photo_id";
		$stmt_keyword = $db_link->prepare($sql_keyword);
		$result_keyword = $stmt_keyword->execute();
		if ($result_keyword == true)
		{
			$j = 0;
			while($keyword_data = $stmt_keyword->fetch(PDO::FETCH_ASSOC))
			{
				$p_photo_id = $keyword_data['photo_id'];
				$tmp_keyword = $keyword_data['keyword_name'];
				$rep_keyword = $rep_ary[$i];
				$tmp_new_keyword = str_replace_once($keyword,$rep_keyword,$tmp_keyword);
				$update_sql = "update keyword set keyword_name = '".$tmp_new_keyword."'"." where photo_id = ".$p_photo_id;
				try
				{
					$stmt2 = $db_link->prepare($update_sql);
					$result2 = $stmt2->execute();
					$result2 = true;
					if($result2)
					{
						$j = $j + 1;
						$str = "updatesql->".$update_sql.">>>OK";
						print $str . str_repeat(' ', 256);
						print "<br/>";
						flush();
					} else {
						$str = "updatesql->".$update_sql.">>>ERR";
						print $str . str_repeat(' ', 256);
						print "<br/>";
						flush();
					}
				} catch(Exception $e) {
					$msg = $e->getMessage();
					throw new Exception($msg);
				}
			}
			$str = $keyword.">>>".$j."件を処理しました。";
			print $str . str_repeat(' ', 256);
			print "<br/>";
			print "<br/>";
			print "<br/>";
			flush();
		}
	}
}
catch(Exception $e)
{
	$message= $e->getMessage();
	throw new Exception($message);
}

function str_replace_once($needle, $replace, $haystack)
{
    $pos = strpos($haystack, $needle);
    if ($pos === false) {
        // Nothing found
        return $haystack;
    }
    return substr_replace($haystack, $replace, $pos, strlen($needle));
}
?>