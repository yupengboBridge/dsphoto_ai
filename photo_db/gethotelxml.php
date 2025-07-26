<?php
echo "SCP GET START\r\n";
$xRootPath = dirname(__FILE__) . '/';

$private_key = "~/.ssh/id_xhankyu_nopass";
$account = "itec";
$ipaddress = "27.121.60.36";
$remot_xml_dir = "/var/wb/export/protected/users/itec/master_data/xml/hotel/*.xml";
$local_xml_dir = $xRootPath . "hotelxml";
$local_csv_dir = $xRootPath . "hotelxml";

$com ="scp -i ".$private_key." ".$account."@".$ipaddress.":".$remot_xml_dir." ".$local_xml_dir;

exec($com.' 2>&1',$ans);

echo "SCP GET END\r\n";
?>