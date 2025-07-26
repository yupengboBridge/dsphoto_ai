<?php
echo "SCP GET START\r\n";
$xRootPath = dirname(__FILE__) . '/';

$private_key = "~/.ssh/id_xhankyu_nopass";
$account = "itec";
$ipaddress = "27.121.60.36";
$remot_csv_dir = "/var/wb/export/protected/users/itec/optlist/*.csv";
$local_csv_dir = $xRootPath . "csv";
//$local_csv_dir = "csv";

$com ="scp -i ".$private_key." ".$account."@".$ipaddress.":".$remot_csv_dir." ".$local_csv_dir;

exec($com.' 2>&1',$ans);

echo "SCP GET END\r\n";
?>