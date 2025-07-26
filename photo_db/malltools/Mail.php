<?php

if (PHP_SAPI === 'cli') {
    $root_path = str_replace("/malltools","",dirname(__FILE__));
} else {
    $root_path = "../";
}

class Mail
{
	private $mail;
	private $config;

	public function __construct()
	{
        global $root_path;

        try{
            $this->config = new Config($root_path.'/malltools/config/config.ini');

            $this->config = $this->config->readConfig('mail');
            $this->mail = new PHPMailer\PHPMailer\PHPMailer();
            $this->mail->isSMTP();
            $this->mail->Host = $this->config['host']; // SMTP服务器地址
            $this->mail->SMTPAuth = true;
            $this->mail->Username = $this->config['username']; // SMTP用户名
            $this->mail->Password = $this->config['password']; // SMTP密码
            $this->mail->SMTPSecure = 'ssl'; // SMTP加密方式，可以是tls或者ssl
            $this->mail->Port = $this->config['port']; // SMTP端口号
            $this->mail->CharSet = 'UTF-8';
        }catch(Exception $e){
            throw $e;
        }
	}

	public function sendMail($title,$content){
	    try{
            if($this->config['enable'] ==='run'){
                $this->mail->setFrom($this->config['username'], $this->config['username']); // 发件人邮箱和姓名
                $receiver_ary = explode(",",$this->config['receiver']);
                foreach($receiver_ary as $receiver){
                    $this->mail->addAddress($receiver,$receiver); // 收件人邮箱和姓名
                }
                // 设置邮件主题和内容
                $this->mail->Subject = $title;
                $this->mail->Body = $content;
                return $this->mail->send();
            }
            return false;
        }catch (Exception $e){
	        throw $e;
        }
	}
}
?>