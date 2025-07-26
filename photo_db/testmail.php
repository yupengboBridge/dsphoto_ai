<?php
    // 複数のファイルを添付してメールで送信する例
    
    //if (!extension_loaded('fileinfo')) {        die("fileinfo 拡張モジュールがインストールされていません");    }    // 送信するメールに関するの情報
    //  ↓ 送信先メールアドレス
    $to      = "yupengbo@bridge.vc";
    //  ↓  送信元
    $from    = "yupengbo@bridge.vc";
    //  ↓  件名
    $subject = "添付メールのサンプル";
    //  ↓  メール本文
    $body    = <<< __EOT
    こんにちは。
    
    あなたが選んだファイルを添付しますので、ご確認ください。
    もし添付ファイルが開けない場合は、お手数ですがご連絡ください。
__EOT;
    
    // ダウンロードさせたいファイルを配列にセット
    $aFilenames = array('/home2/chroot/home/xhankyu/public_html/photo_db/testaa.php');
    
    // 文字化けする場合は明示的に文字エンコーディングを指定してください
    $encoding = mb_detect_encoding($body, "SJIS,EUC-JP,JIS,UTF-8");

    // 添付ファイルのMultiPartを作成する
    function getAttatchFile($aFile, $body) {
        $sLine = "--";
        $sMultipartLine = '_'.uniqid(b, true).'_powered_by_php.to_';
        $sMultipartLine = "-----------moemoe";
        $sContentType = "multipart/mixed; boundary=\"$sMultipartLine\"";

        $out  = "$sLine$sMultipartLine\nContent-Type: text/plain; charset=\"iso-2022-jp\"\n";
        $out .= "Content-Transfer-Encoding: 7bit\n\n";
        $out .= "$body\n";
        foreach($aFile as $filename) {
            $fn = basename($filename);
            if (trim($fn) && file_exists($filename)) {
                //$finfo = finfo_open(FILEINFO_MIME, "/usr/share/file/magic");
                //$mime = finfo_file($finfo, $filename);
                //finfo_close($finfo);
                $mime = "text/plain";
                //
                $out .= "\n$sLine$sMultipartLine\n";
                $out .= sprintf("Content-Type: %s; name=\"%s\"\n", $mime, $fn);
                $out .= sprintf("Content-Disposition: attachment; filename=\"%s\"\n", $fn);
                $out .= "Content-Transfer-Encoding: base64\n\n";
                $out .= chunk_split(base64_encode(file_get_contents($filename))) . "\n";
            }
        }
        $out .= $sLine . $sMultipartLine . $sLine;
        return array($sContentType, "This is a multi-part message in MIME format.\n".$out);
    }


    //
    if ($encoding != "JIS") {
        //echo $encoding;
        $subject = mb_convert_encoding($subject, "JIS", $encoding);
        $body = mb_convert_encoding($body, "JIS", $encoding);
    }
    $subject = base64_encode($subject);
    $subject = '=?ISO-2022-JP?B?' . $subject . '?=';
    list($content_type, $body) = getAttatchFile($aFilenames, $body);

$header = <<< __EOT
From: $from
MIME-Version: 1.0
Content-Type: $content_type
X-Mailer: php.to tips sample mailer [see http://php.to/tips/]
Content-Transfer-Encoding: 7bit
$additional_header

__EOT;

    echo mail($to, $subject, $body, ereg_replace("\r\n|\r|\n","\n", trim($header)));
?>
