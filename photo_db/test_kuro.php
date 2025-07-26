<?php
# FileName="Connection_php_mysql.htm"
# Type="MYSQL"
# HTTP="true"
$hostname_client = "10.254.2.63";
$database_client = "ximage";
$username_client = "ximage";
$password_client = "kCK!7wu4";
$client = mysql_pconnect($hostname_client, $username_client, $password_client) or trigger_error(mysql_error(),E_USER_ERROR); 

mysql_select_db($database_client, $client);
$query = "SELECT * FROM photoimg";
$client_arr = mysql_query($query, $client) or die(mysql_error());
$row_bridal_client = mysql_fetch_assoc($bridal_client);

print_r($client_arr);
echo 'aaaaaaaaaaaa';
?>

