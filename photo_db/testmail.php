<?php
    // �����̃t�@�C����Y�t���ă��[���ő��M�����
    
    //if (!extension_loaded('fileinfo')) {        die("fileinfo �g�����W���[�����C���X�g�[������Ă��܂���");    }    // ���M���郁�[���Ɋւ���̏��
    //  �� ���M�惁�[���A�h���X
    $to      = "yupengbo@bridge.vc";
    //  ��  ���M��
    $from    = "yupengbo@bridge.vc";
    //  ��  ����
    $subject = "�Y�t���[���̃T���v��";
    //  ��  ���[���{��
    $body    = <<< __EOT
    ����ɂ��́B
    
    ���Ȃ����I�񂾃t�@�C����Y�t���܂��̂ŁA���m�F���������B
    �����Y�t�t�@�C�����J���Ȃ��ꍇ�́A���萔�ł������A�����������B
__EOT;
    
    // �_�E�����[�h���������t�@�C����z��ɃZ�b�g
    $aFilenames = array('/home2/chroot/home/xhankyu/public_html/photo_db/testaa.php');
    
    // ������������ꍇ�͖����I�ɕ����G���R�[�f�B���O���w�肵�Ă�������
    $encoding = mb_detect_encoding($body, "SJIS,EUC-JP,JIS,UTF-8");

    // �Y�t�t�@�C����MultiPart���쐬����
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
