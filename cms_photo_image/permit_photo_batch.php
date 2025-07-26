<BASE target=_self>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ja" lang="ja">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>管理番号入力</title>
<meta name="Description" content="" />
<meta http-equiv="content-style-type" content="text/css" />
<meta http-equiv="content-script-type" content="text/javascript" />
<script src="./js/common.js"  type="text/javascript"  charset="utf-8"></script>
    <SCRIPT   LANGUAGE="JavaScript">  
      <!--  
      function   centerWindow()   
      {  
              var   xMax   =   screen.width;
              var   yMax   =   screen.height;
            window.moveTo(xMax/2-100,yMax/2-100-80);
      }
      function form_submit(){
      	  var txtnode = document.getElementById("reg_photo_mno");
      	  if(txtnode)
      	  {
      	  	  if(txtnode.value != null)
      	  	  {
      	  	  	  if(trim(txtnode.value).length > 0)
      	  	  	  {
      	  	  	  	  document.permit_form.submit();
      	  	  	  } else {
      	  	  	  	  var err_obj = document.getElementById("err_obj");
      	  	  	  	  err_obj.innerHTML = "管理番号を入力して下さい。";
		      	  	  txtnode.focus();
		      	  	  return false;
      	  	  	  }
      	  	  }
      	  }
      }
		centerWindow();
      //-->  
    </SCRIPT>
</head>
<body>
	<form action="./permit_photo_batch_help.php" method="post" name="permit_form" id="permit_form">
		写真管理番号:
		<input name="reg_photo_mno" type="text" id="reg_photo_mno" value="" MaxLength="5" size="10" style="ime-mode:disabled" />
		<input type="button" id="permit_button" value="確認する" onclick="form_submit();" />
		<font color="red" size="2"><div>※半角の5桁数字/文字を入力して下さい。</div></font>
		<font color="red"><div style="font-color:red" id="err_obj"></div></font>
	</form>
</body>
</html>