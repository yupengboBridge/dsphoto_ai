<?php
   $time_start=time();
    header("Content-type: text/plain\n\n");
    echo "Connexion SSH ";

	if(!$connection=ssh2_connect('150.48.8.105',22,array('hostkey' =>'ssh-rsa')))
	{
		echo "[FAILED]\n";
		exit(1);
	}
	echo "[OK]\nAuthentification ";


	if(ssh2_auth_pubkey_file($connection,'itec-test','./id_xhankyu_test.pub','./id_xhankyu_test', "#budinter$" ))
	 {
	    echo "OK!";
	 }
	 
	else
	{
	   echo "[FAILED]\n";
	   exit(1); 
	}

     $stdout_stream = ssh2_exec($connection, "/bin/ls -la ");
	if (!$stdout_stream)
	{
			echo "[FAILED]\n";
			exit(1);
	}
	$err_stream = ssh2_fetch_stream($stdout_stream, SSH2_STREAM_STDERR);
	stream_set_blocking($err_stream, true);
	$result_err = stream_get_contents($err_stream);
	if ($result_err)
	{
			echo $result_err."\n";
			exit(1);
	}

	$dio_stream = ssh2_fetch_stream($stdout_stream, SSH2_STREAM_STDIO);
	stream_set_blocking($dio_stream, true);
	$result_dio = stream_get_contents($dio_stream);
	echo $result_dio."\n";

	$time_end=time();
	echo $time_end-$time_start."\n";
?>