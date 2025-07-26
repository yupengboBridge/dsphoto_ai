<?php
require_once ('../../config.php');
require_once ('../../lib.php');
require_once ('../Config.php');
const CONFIGPATH = '../config/';

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

$config_instance = new Config(CONFIGPATH.'config.ini');

$crop_config = $config_instance->readConfig('crop');
$mail_config = $config_instance->readConfig('mail');

//提交保存
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        checkConfigParams($_POST);
    }catch (Exception $e){
        echo '<script>alert("保存に失敗しました");</script>';
        header_out($_SERVER['PHP_SELF']);
        return;
    }

    $data = $_POST;
    $mailChange = [];
    $mailChange['enable'] = $_POST['mail_enable'];
    $mailChange['host'] = $_POST['mail_host'];
    $mailChange['port'] = $_POST['mail_port'];
    $mailChange['username'] = $_POST['mail_username'];
    $mailChange['passowrd'] = $_POST['mail_passowrd'];
    $mailChange['receiver'] = $_POST['mail_receiver'];
    foreach ($mailChange as $key=>$value){
        $config_instance->updateConfig('mail',$key,$value);
    }
    echo '<script>alert("保存に成功しました");</script>';
    header_out($_SERVER['PHP_SELF']);
}
function checkConfigParams($params){
    if($params['mail_enable'] !=='stop' && $params['mail_enable'] !== 'run'){
        throw new Exception('不正な引数');
    }
    return true;
}
    // 这里处理POST请求的逻辑
// ＤＢへ接続します。

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ja" lang="ja">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>MALLへ連携</title>
    <meta name="Keywords" content="キーワードが入ります" />
    <meta name="Description" content="" />
    <meta http-equiv="content-style-type" content="text/css" />
    <meta http-equiv="content-script-type" content="text/javascript" />
    <!--CSSリンク　ここから-->
    <link rel="stylesheet" href="../../css/master.css" type="text/css"
          media="all" />
    <!--CSSリンク　ここまで-->
    <!--javascript ここから -->
    <script src="../../js/jquery.min.js" type="text/javascript" charset="utf-8"></script>
<!--    <script src="../../js/common.js" type="text/javascript" charset="utf-8"></script>-->
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
<form id="mall_setting_form" action="<?php echo $_SERVER['PHP_SELF'];?>" method="post" onsubmit="return sendform(this);">
    <div id="zentai">
        <div id="contents">
            <div id="registration">
                <div class="pickup_contents">
                  <dl class="album_registering" style="font-size: 12px">
                    <dt>CSVの保存先ディレクトリ：<?php echo dirname(dirname(__FILE__)).'/download/'.'mall_dsphoto_list.csv' ?></dt>
                    <dt>画像の保存先ディレクトリ：<?php echo dirname(dirname(__FILE__)).'/download/'.'image/' ?></dt>
                    <dt>CSVテンプレートのダウンロード：<a href="<?php echo str_replace('pages/tasksetting.php','demo/mall_dsphoto_list.csv',$_SERVER['REQUEST_URI']) ?>">ダウンロードする</a></dt>
                  </dl>
                </div>
                <div>
                    <h2>メール通知設定</h2>
                    <dl class="reg_division reg_clear">
                        <dt>クロップを有効にするか</dt>
                        <dd>
                            <label><input name="mail_enable" id="crop_enable0" type="radio" value="run"  <?php  echo ($mail_config['enable']==='run'?'checked':'')?>>有効</label>
                            <label><input name="mail_enable" id="crop_enable1" type="radio" value="stop" <?php  echo ($mail_config['enable']!=='run'?'checked':'')?>>無効</label>
                        </dd>
                    </dl>
                    <dl class="reg_customer_info reg_clear reg_list_none_top">
                        <dt>ホストアドレス</dt>
                        <dd>
                            <input name="mail_host" type="text" size="25" value="<?php echo ($mail_config['host'])?>">
                        </dd>
                    </dl>
                    <dl class="reg_customer_info reg_clear reg_list_none_top">
                        <dt>ポート</dt>
                        <dd>
                            <input name="mail_port" type="text" size="25" value="<?php echo ($mail_config['port'])?>">
                        </dd>
                    </dl>
                    <dl class="reg_customer_info reg_clear reg_list_none_top">
                        <dt>ユーザー名</dt>
                        <dd>
                            <input name="mail_username" type="text" size="25" value="<?php echo ($mail_config['username'])?>">
                        </dd>
                    </dl>
                    <dl class="reg_customer_info reg_clear reg_list_none_top">
                        <dt>パスワード</dt>
                        <dd>
                            <input name="mail_passowrd" type="text" size="25" value="<?php echo ($mail_config['password'])?>">
                        </dd>
                    </dl>
                    <dl class="reg_customer_info reg_clear reg_list_none_top">
                        <dt>送信先</dt>
                        <dd>
                            <input name="mail_receiver" type="text" size="25" value="<?php echo ($mail_config['receiver'])?>">
                        </dd>
                    </dl>
                </div>
                <div class="reg_search_btn">
                    <ul>
                        <li class="bt_reg_confirm">
                            <p><button type="submit" title="保存する">保存する</button></p>
                        </li>
                    </ul>
                </div>
              <div>
                <h2>手動でCSVをアップロード</h2>
                <dl class="reg_division reg_clear">
                  <dt>アップロードしてください</dt>
                  <dd>
                    <label><button type="button" title="手動でCSVをアップロード" onclick="startTask();" id="task_button">手動でCSVファイルを取り込む</button></label>
                  </dd>
                </dl>
              </div>
            </div>
        </div>
    </div>
</form>

</body>
<script>
    function sendform(form){
        var d = {};
        var t = $(form).serializeArray();
        $.each(t, function() {
            d[this.name] = this.value;
        });
        return true;
    }
    function startTask() {
      _loading();
      $.ajax({
        type: 'GET',
        url: '../../mall_task.php',
        data: ({source:'web_page'}),
        success: function(response){
          $res = JSON.parse(response);
          if(!$res.isCrash && !$res.isError){
            alert('success:' + 'タスクが正常に実行されました');
          }else{
            alert('error:' + 'タスクが失敗しました、ログファイルまたはメールを確認してください');
          }
          _stopLoading();
        },
        error: function (response){
          alert('error:' + 'タスクが失敗しました、ログファイルまたはメールを確認してください');
          _stopLoading();
        }
      });
    }
    function _loading() {
      $("#task_button").html('読み込み中.....');
      $("#task_button").attr('disabled','disabled');
    }
    function _stopLoading() {
      $("#task_button").html('手動でCSVをアップロード');
      $("#task_button").removeAttr('disabled');
    }
</script>
</html>
