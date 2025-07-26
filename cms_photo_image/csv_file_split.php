<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ja" lang="ja">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>ファイルの分割｜BUD PHOTO WEB</title>
<meta name="Keywords" content="キーワードが入ります" />
<meta name="Description" content="" />
<meta http-equiv="content-style-type" content="text/css" />
<meta http-equiv="content-script-type" content="text/javascript" />
<script type="text/javascript">
function submit()
{
	var txtobj = document.getElementById('p_filename');
	if (txtobj)
	{
		var txtvalue = txtobj.value;
		if (txtvalue == null)
		{
			alert('ファイル名を入力してください。');
			return;
		} else if (txtvalue.length <= 0) {
			alert('ファイル名を入力してください。');
			return;
		}
	}

	var p_line_no = document.getElementById("p_s_line_no").value;
	var result1 = p_line_no.match(/[^0-9]/);
	if (!p_line_no || result1)
	{
		alert("開始行目は0～9の数字だけ入力してください");
		return;
	}

	var p_file_cnt = document.getElementById("p_file_cnt").value;
	var result = p_file_cnt.match(/[^0-9]/);
	if (!p_file_cnt || result)
	{
		alert("ファイルの行数は1～9の数字だけ入力してください");
		return;
	}

	var i_tmp = parseInt(p_file_cnt);
	if (i_tmp > 150)
	{
		alert("ファイルの行数は150以降の数字だけ入力してください");
		return;
	}

	document.location.href = "./batch_file_split.php?p_filename=" + txtvalue + "&start_line_no="+p_line_no + "&group_cnt=" + p_file_cnt;
}
</script>
</head>
<body>
<label>ファイル名：&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<input type="text" id="p_filename" value="" /></label><br/><br/>

<label>開始行目(0から)：&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<input type="text" id="p_s_line_no" value="" /></label><br/><br/>

<label>ファイルの行数(1-150)：<input type="text" id="p_file_cnt" value="" /></label><br/><br/>
<input type="button" id="btn_ok" value="実行" onclick="submit();"/>
</body>
</html>
