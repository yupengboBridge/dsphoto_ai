<?php
	//require necessary lib
	include_once ("./mail_lib/class.phpmailer.php");
	require_once ('./config.php');
	require_once ('./lib.php');
	
	//set the timezone
	date_default_timezone_set ( 'Asia/Tokyo' );
	
	check_expird_data();
	
	function check_expird_data()
	{
		try
		{
			// ＤＢへ接続します。
			$db_link = db_connect();
			$pi = new PhotoImageDB();
			$data = $pi->select_expired_data($db_link);
			
			$mails = array();
			$mail_data = array();
			//if the data is not empty
			if(count($data)>0)
			{
				foreach($data as $val)
				{
					if(!in_array($val['email'],$mails))
					{
						$mails[] = $val['email'];
						$mail_names[$val['email']] = $val['registration_person'];
					}
					$mail_data[$val['email']]['photo_id'][] = $val['photo_id'];
					$mail_data[$val['email']]['photo_mno'][] = $val['photo_mno'];
					$mail_data[$val['email']]['photo_name'][] = $val['photo_name'];
					$mail_data[$val['email']]['dfrom'][] = $val['dfrom'];
					$mail_data[$val['email']]['dto'][] = $val['dto'];
				}
			}
			//if the mails is not empty
			if(count($mails)>0)
			{
				foreach($mails as $mail)
				{
					//set the file content
					$file_content = "DB管理番号	写真管理番号	写真名	開始日付	終了日付\r\n";
					foreach($mail_data[$mail]['photo_id'] as $key=>$val)
					{
						$file_content .= $val."	";
						$file_content .= $mail_data[$mail]['photo_mno'][$key]."	";
						$file_content .= $mail_data[$mail]['photo_name'][$key]."	";
						$file_content .= $mail_data[$mail]['dfrom'][$key]."	";
						$file_content .= $mail_data[$mail]['dto'][$key]."\r\n";
					}
					//create the file
					$file_name = write_file($file_content);
					//set mail attachment info
					$files['path'] = "./".$file_name;
					$files['name'] = $file_name;
					//set the mail body
					$body = "添付ファイルの画像期限が切れてしまいました。";
					//send the mail
					if(SendMail($body,$files,$mail,$mail_names[$mail]))
					{
						echo('yes');
						//delete the attachment
						unlink($file_name);
					}
					else 
					{
						echo('no');
						//delete the attachment
						unlink($file_name);
					}
				}
			}
		}
		catch(Exception $cla)
		{
			// 異常を出力する
			$msg[] = $cla->getMessage();
			error_exit($msg);
		}
	}
	/**
	 * Send the mail to Someones' mailbox.
	 * @param string $body The content of the mail.
	 * @param array $files The File you want sent.
	 * @param string $to The Email which you want send to.
	 * @param string $name The name which you want send to.
	 * @return bool
	 */
	function SendMail($body,$files,$to,$name) 
	{
		global $mail_config;	
		//define the mail class.
		$mail = new PHPMailer ();
		
		//set the base information.
		$mail->From = $mail_config ["from"];
		$mail->FromName = $mail_config ["from_name"];
		$mail->Username = $mail_config ["user"];
		$mail->Password = $mail_config ["password"];
		$mail->CharSet  = $mail_config ["char"];
		$mail->SMTPAuth = $mail_config ["auth"];
		$mail->Host     = $mail_config ["server"];
		$mail->Mailer   = $mail_config ["mailer"];
		$mail->Port     = $mail_config ["port"];
		$mail->Subject  = $mail_config ["subject"];
		$mail->Body 	= $body;
		
		//set the file you want send.
		$mail->AddAttachment ( iconv ( "UTF-8", "CP932", $files['path']), $files['name']);
		
		//set the address you want send to.
		$mail->AddAddress($to,$name);
		
		//send the mail.
		if (! $mail->Send ()) {
			return false;
		} else {
			return true;
		}
	}
	
	/**
	 * Write the string to file.
	 * @param string $body The content of the mail.
	 * @param array $files The File you want sent.
	 * @param string $to The Email which you want send to.
	 * @param string $name The name which you want send to.
	 * @return bool
	 */
	function write_file($string)
	{
		$file = date('Ymd').".txt";
		
		if(file_exists($file))
		{
			unlink($file);
		}
		$fh = fopen($file, 'w') or die("can't open file");
		fwrite($fh,$string);
		fclose($fh);
		return $file;
	}