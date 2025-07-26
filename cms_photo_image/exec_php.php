<?php
 
echo "SCP START";
//[/dev/null &]でバックグランドで実行> [/dev/null]:標準出力やエラーなどが
//$com ="scp -i ~/.ssh/id_xhankyu_test_nopass itec-test@150.48.237.194:/var/wb/export_staging/protected/users/itec-test/master_data/test_scp.html /home/xhankyu/public_html/photo_db/.";

//$com ="scp -i ~/.ssh/id_xhankyu_test_nopass test_scp.html itec-test@150.48.237.194:/var/wb/export_staging/protected/users/itec-test/master_data/.";

//$com ="scp /var/www/html/travelcom_up/rsync.php budinter@10.129.166.14:/var/www/html/hankyu-travel.com/test003/.";
$com  = "php scp_test.php";
exec($com.' 2>&1',$ans);

echo "SCP END";


?>