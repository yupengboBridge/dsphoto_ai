<?php
	try
	{
		$connection = ssh2_connect('27.121.60.36', 22,array('hostkey'=>'ssh-rsa'));

		if (ssh2_auth_pubkey_file($connection, 'itec-test',
		                          '~/.ssh/id_xhankyu_test.pub',
		                          '~/.ssh/id_xhankyu_test', '#budinter$')) {
		  echo "Public Key Authentication Successful\n";
		} else {
		  die('Public Key Authentication Failed');
		}

//		$ssh2 = ssh2_connect('e-mon.vc', 22);
//		if(ssh2_auth_password($ssh2, 'root', '090-9880-8633-yuichi'))
//		{
//			echo "Authentication Successful\n";
//		} else {
//			die('Authentication Failed');
//		}

//		$connection = ssh2_connect('e-mon.vc', 22,array('hostkey'=>'ssh-rsa'));
//
//		if (ssh2_auth_pubkey_file($connection, 'root',
//		                          './secretkey.ppk',
//		                          '/root/.ssh/authorized_keys', '090-9880-8633-yuichi')) {
//		  echo "Public Key Authentication Successful\n";
//		} else {
//		  die('Public Key Authentication Failed');
//		}

//		$connection = ssh2_connect('e-mon.vc', 22);
//		$methods = ssh2_methods_negotiated($connection);
//
//		echo "Encryption keys were negotiated using: {$methods['kex']}\n";
//		echo "Server identified using an {$methods['hostkey']} with ";
//		echo "fingerprint: " . ssh2_fingerprint($connection) . "\n";
//
//		echo "Client to Server packets will use methods:\n";
//		echo "\tCrypt: {$methods['client_to_server']['crypt']}\n";
//		echo "\tComp: {$methods['client_to_server']['comp']}\n";
//		echo "\tMAC: {$methods['client_to_server']['mac']}\n";
//
//		echo "Server to Client packets will use methods:\n";
//		echo "\tCrypt: {$methods['server_to_client']['crypt']}\n";
//		echo "\tComp: {$methods['server_to_client']['comp']}\n";
//		echo "\tMAC: {$methods['server_to_client']['mac']}\n";

	}
	catch(Exception $cla)
	{
		// 異常を出力する
		$msg[] = $cla->getMessage();
		error_exit($msg);
	}

?>
