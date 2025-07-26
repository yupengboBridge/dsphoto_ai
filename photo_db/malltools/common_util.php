<?php
class CommonUtil{
    /*
     * 関数名：trimSpace
     * 関数説明：文字列の前後の全角と半角スペースを削除する
     * パラメタ：
     * str：文字列
     * 戻り値：スペースを削除した文字列
     */
    public static function trimSpace($str)
    {
        return preg_replace('/^[ 　]*(.*?)[ 　]*$/u', '$1', $str);
    }

    public static function isBoolean($params){
        return in_array($params,[0,1]);
    }

    //-------半角数字チェック
    public static function checkNumber($num)
    {
        if (preg_match("/[^0-9]+/",$num))
        {
            return false;
        }else{
            return true;
        }
    }

    public static function resize_image($filename,$percent,$new_path)
    {
        $new_width = 0;
        $new_height = 0;

        if($percent == 1){
            $new_width = 470;
            $new_height = 750;
        }
        if($percent == 2){
            $new_width = 2600;
            $new_height = 1200;
        }
        if($percent == 3){
            $new_width = 1252;
            $new_height = 578;
        }

        if($new_width == 0 && $new_height == 0){
            return;
        }

        $a = count(explode('.',basename($filename)))-1;
        $b = explode('.',basename($filename));
        $ext = $b[$a];
        list($width, $height) = getimagesize($filename);


        $image_p = imagecreatetruecolor($new_width, $new_height);
        switch ($ext) {
            case 'jpg':
            case 'jpeg':
                $image = ImageCreateFromJpeg($filename);
                imagecopyresampled($image_p, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
                imagejpeg($image_p, $new_path);
                break;
            case 'png':
                $image = ImageCreateFromJpeg($filename);
                imagecopyresampled($image_p, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
                imagepng($image_p, $new_path);
                break;
            case 'gif':
                $image = ImageCreateFromGif($filename);
                imagecopyresampled($image_p, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
                imagegif($image_p, $new_path);
                break;
        }
    }

    public static function writeDeletePhotoImageLog($message,$dir_root){
        $log_file = $dir_root."/log/delete_photo_image_log.log";
        $log_message = date('Y-m-d H:i:s').":::".$message."\r\n";
        file_put_contents($log_file,$log_message,FILE_APPEND);
    }

    public static function writeUploadPhotoImageLog($message,$dir_root){
        $log_file = $dir_root."/log/upload_photo_image_log.log";
        $log_message = date('Y-m-d H:i:s').":::".$message."\r\n";
        file_put_contents($log_file,$log_message,FILE_APPEND);
    }
}
?>