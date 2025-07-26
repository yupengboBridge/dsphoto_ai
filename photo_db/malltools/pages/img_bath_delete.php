<?php
require_once ('../../config.php');
require_once ('../../lib.php');

set_time_limit(3600);
date_default_timezone_set('Asia/Tokyo');

// セッション管理をスタートします。
session_start();

$s_login_id = array_get_value($_SESSION, 'login_id', "");
$s_login_name = array_get_value($_SESSION, 'user_name', "");
$s_security_level = array_get_value($_SESSION, 'security_level', "");
$comp_code = array_get_value($_SESSION, 'compcode', "");
$s_group_id = array_get_value($_SESSION, 'group', "");
$s_user_id = array_get_value($_SESSION, 'user_id', "");
if (empty($s_login_id) || $s_security_level != 4 || $s_security_level != "4")
{
    // ログイン後のTOPページへリダイレクトします。
    header_out($logout_page);
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ja" lang="ja">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>批量上传CSV-删除</title>
    <meta name="Keywords" content="キーワードが入ります" />
    <meta name="Description" content="" />
    <meta http-equiv="content-style-type" content="text/css" />
    <meta http-equiv="content-script-type" content="text/javascript" />
    <!--CSSリンク　ここから-->
    <link rel="stylesheet" href="../../css/master.css" type="text/css"
          media="all" />
    <!--CSSリンク　ここまで-->
    <!--javascript ここから -->
    <script src="http://code.jquery.com/jquery-2.1.1.min.js"></script>
    <!-- javascript ここまで -->
    <style type="text/css">
        div.reg_search_btn ul li.bt_reg_confirm button {
            display: block;
            width: 85px;
            height: 20px;
        }
        button:visited {
            color: #224272;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div id="zentai">
        <div id="contents">
            <div id="registration">
                <div class="pickup_contents">
                  <dl class="album_registering" style="font-size: 12px">
                    <dt>CSVテンプレートのダウンロード：<a href="<?php echo str_replace('pages/img_bath_delete.php','demo/mall_dsphoto_delete.csv',$_SERVER['REQUEST_URI']) ?>">ダウンロードする</a></dt>
                  </dl>
                </div>
                <div>
                    <h2>CSVより一括削除</h2>
                  <dl class="reg_customer_info reg_clear reg_list_none_top">
                      <dt>CSVファイルをアップロードしてください</dt>
                      <dd>
                          <input name="delete_csv" type="file" accept=".csv" id="csv_file">
                      </dd>
                  </dl>
                  <dl class="reg_customer_info reg_clear reg_list_none_top" id="effect_img" style="display: none">
                    <dt>
                    <dt>削除済み画像のBUDPHOTO番号</dt>
                    </dt>
                    <dd>
                      <select id='csvLine' style='width: 400px' multiple='true' size='10'>
                      </select>
                    </dd>
                  </dl>
                </div>
                <div class="reg_search_btn" style="text-align: center">
                  <button id="uploadButton" onclick="uploadDeleteCsv()" title="上传" style="padding: 0 30px">手動でCSVファイルを取り込む</button>
                </div>
            </div>
        </div>
    </div>
    <script type="application/javascript">
      function uploadDeleteCsv() {
        var fileInput = document.getElementById("csv_file");
        var files = fileInput.files;
        if(!files.length){
          alert("CSVファイルが存在しません");
          return false;
        }
        var data = new FormData();
        data.append('csv_file', files[0]);
        _loading();
        $.ajax({
          url: '../batch_delete.php',
          type: 'POST',
          // Remove contentType and processData settings
          contentType: false,
          processData: false,
          cache: false,
          data: data,
          success: function (response) {
            res = JSON.parse(response);
            console.log(JSON.parse(response));
            if(res.status === 'success'){
              $.each(res.msg, function(i, option) {
                $('#csvLine').append($('<option></option>').val(option).text(option));
                $('#effect_img').css('display','block');
              });
              alert('タスクが正常に実行されました');
              _stopLoading();
            }else{
              alert(res.msg);
              _stopLoading();
            }
            // Handle successful response here
          },
          error: function (response) {
            res = JSON.parse(response);
            alert(res.msg);
            _stopLoading();
          },
          finally:function () {
            _stopLoading();
          }
        });
      }
      function _loading() {
        $("#uploadButton").html('加载中.....');
        $("#uploadButton").attr('disabled','disabled');
      }
      function _stopLoading() {
        $("#uploadButton").html('終了');
      }
    </script>
</body>
</html>
