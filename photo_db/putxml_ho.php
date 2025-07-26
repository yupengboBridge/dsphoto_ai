<?php
echo "SCP PUT START\r\n";
$xRootPath = dirname(__FILE__) . '/';

$private_key = "~/.ssh/id_xhankyu_nopass";
$account = "itec";
$ipaddress = "27.121.60.36";
$remot_csv_dir = "/var/wb/export/protected/users/itec/master_data";
$remot_csv_dir2 = "/var/wb/export/protected/users/itec/master_data/xml";

//$local_csv_dir = "/home2/chroot/home/xhankyu/public_html/photo_db/csv";
//$local_xml_dir = "/home2/chroot/home/xhankyu/public_html/photo_db/xml";
//$local_xml_dir2 = "/home2/chroot/home/xhankyu/public_html/photo_db/xml/hotel";
//$local_csv_dir = "/home/xhankyu/public_html/photo_db/csv";
//$local_xml_dir = "/home/xhankyu/public_html/photo_db/xml";
//$local_xml_dir2 = "/home/xhankyu/public_html/photo_db/xml/hotel";

$local_csv_dir = $xRootPath . "csv";
$local_xml_dir = $xRootPath . "xml";
$local_xml_dir2 = $xRootPath . "xml/hotel";



$com ="scp -i ".$private_key." -r ".$local_xml_dir." ".$account."@".$ipaddress.":".$remot_csv_dir;
exec($com.' 2>&1',$ans);

$com ="scp -i ".$private_key." -r ".$local_xml_dir2." ".$account."@".$ipaddress.":".$remot_csv_dir2;
exec($com.' 2>&1',$ans);

$com ="scp -i ".$private_key." -r ".$local_csv_dir." ".$account."@".$ipaddress.":".$remot_csv_dir;
exec($com.' 2>&1',$ans);


//scp -i "/home/xhankyu/.ssh/id_xhankyu_test_nopass" -r /home/xhankyu/public_html/photo_db/xml itec-test@27.121.60.36:/var/wb/export_staging/protected/users/itec-test/master_data/xml

$private_key = "~/.ssh/id_xhankyu_test_nopass";
//$private_key = "/home/xhankyu/.ssh/id_xhankyu_test_nopass";
$account = "itec-test";
$ipaddress = "27.121.60.36";
$remot_csv_dir = "/var/wb/export_staging/protected/users/itec-test/master_data";
$remot_csv_dir2 = "/var/wb/export_staging/protected/users/itec-test/master_data/xml";

//$local_csv_dir = "/home2/chroot/home/xhankyu/public_html/photo_db/makexml_for_test/csv";
//$local_xml_dir = "/home2/chroot/home/xhankyu/public_html/photo_db/makexml_for_test/xml";
//$local_xml_dir2 = "/home2/chroot/home/xhankyu/public_html/photo_db/makexml_for_test/xml/hotel";

//$local_csv_dir = "/home2/chroot/home/xhankyu/public_html/photo_db/csv";
//$local_xml_dir = "/home2/chroot/home/xhankyu/public_html/photo_db/xml";
//$local_xml_dir2 = "/home2/chroot/home/xhankyu/public_html/photo_db/xml/hotel";
//$local_csv_dir = "/home/xhankyu/public_html/photo_db/csv";
//$local_xml_dir = "/home/xhankyu/public_html/photo_db/xml";
//$local_xml_dir2 = "/home/xhankyu/public_html/photo_db/xml/hotel";
$local_csv_dir = $xRootPath . "csv";
$local_xml_dir = $xRootPath . "xml";
$local_xml_dir2 = $xRootPath . "xml/hotel";

$com ="scp -i ".$private_key." -r ".$local_xml_dir." ".$account."@".$ipaddress.":".$remot_csv_dir;
exec($com.' 2>&1',$ans);

$com ="scp -i ".$private_key." -r ".$local_xml_dir2." ".$account."@".$ipaddress.":".$remot_csv_dir2;
exec($com.' 2>&1',$ans);

$com ="scp -i ".$private_key." -r ".$local_csv_dir." ".$account."@".$ipaddress.":".$remot_csv_dir;
exec($com.' 2>&1',$ans);


echo "SCP PUT END\r\n";
?>