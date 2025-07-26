<?php
    echo "Connexion SSH <br/>";

//	$ssh2 = ssh2_connect('203.133.238.102', 22);

//  $stdout_stream = ssh2_exec($connection, "/usr/bin/scp -i /home/xhankyu/.ssh/id_xhankyu_nopass itec-test@150.48.8.105:/var/wb/export_staging/protected/users/itec-test/test_kensyo.txt ./");
//	if (!$stdout_stream)
//	{
//			echo "[FAILED]\n";
//			exit(1);
//	}

$stdout = "/usr/bin/scp -i /home/xhankyu/.ssh/id_xhankyu_nopass itec-test@150.48.8.105:/var/wb/export_staging/protected/users/itec-test/test_kensyo.txt ./";

echo $stdout."<br/>";

echo exec( $stdout );
//	$err_stream = ssh2_fetch_stream($stdout_stream, SSH2_STREAM_STDERR);
//	stream_set_blocking($err_stream, true);
//	$result_err = stream_get_contents($err_stream);
//	if ($result_err)
//	{
//			echo $result_err."\n";
//			exit(1);
//	}
//
//	$dio_stream = ssh2_fetch_stream($stdout_stream, SSH2_STREAM_STDIO);
//	stream_set_blocking($dio_stream, true);
//	$result_dio = stream_get_contents($dio_stream);
//	echo $result_dio."\n";

//	$time_end=time();
//	echo $time_end-$time_start."\n";
?>