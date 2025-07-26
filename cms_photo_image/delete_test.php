<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ja" lang="ja">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>画像の削除バッチ画面｜BUD PHOTO WEB</title>
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
		if (txtvalue.length > 0)
		{
			document.location.href = "./delete_image_batch.php?p_filename=" + txtvalue;
			return;
		}
	}
}
</script>
</head>
<body>
<label>ファイル名：<input type="text" id="p_filename" value="" /></label>
<input type="button" id="btn_ok" value="実行" onclick="submit();"/>
</body>
</html>
