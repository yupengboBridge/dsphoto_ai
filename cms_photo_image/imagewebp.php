<?php

#新建备份文件夹backup
function newFolderBackup($dir){
    $dirname = $dir.'//backup';
    if(file_exists($dirname)){
        echo '已有文件夹';
        chmod($dirname, 0777);
    }else{
        mkdir($dirname,0777);
    }
}
#将jpg图片复制到同目录下的backup文件夹中
#将jpg图片webp化
#删除jpg图片
function webpjpg_all ($dir){
    if (!is_dir($dir)){
        echo '目录不存在';
        return false;
    }
    #新建backup文件夹
    newFolderBackup($dir);
    $handle = opendir($dir);
    if ($handle) {
        while (($fl = readdir($handle)) !== false) {
            if ($fl != '.' && $fl != '..') {
                #筛选后缀为jpg的文件
                if(strpos($fl,'.jpg',1)){
                    #图片路径
                    $filename = $dir.'//'.$fl;
                    #备份后的图片路径
                    $backuppath = $dir.'//backup//'.$fl;
                    #备份图片
                    copy($filename,$backuppath);
                    #获取图片名称（去掉后缀）
                    $fl_name = basename($fl,'.jpg');
                    #webp化后图片的路径
                    $webpfile = $dir.'//'.$fl_name.'.webp';
                    #将图片webp化
                    $im = imagecreatefromjpeg($filename);
                    imagewebp($im, $webpfile);
                    imagedestroy($im);
                    #删除已webp化图片的原图
                    unlink($filename);
                    #echo PHP_EOL;
                }
                if(strpos($fl,'.png',1)){
                    #图片路径
                    $filename = $dir.'//'.$fl;
                    #备份后的图片路径
                    $backuppath = $dir.'//backup//'.$fl;
                    #备份图片
                    copy($filename,$backuppath);
                    #获取图片名称（去掉后缀）
                    $fl_name = basename($fl,'.png');
                    #webp化后图片的路径
                    $webpfile = $dir.'//'.$fl_name.'.webp';
                    #将图片webp化
                    $im = imagecreatefrompng($filename);
                    imagewebp($im, $webpfile);
                    imagedestroy($im);
                    #删除已webp化图片的原图
                    unlink($filename);
                    #echo PHP_EOL;
                }
            }
        }
    }
}
webpjpg_all('/usr/local/apache/htdocs/cms_photo_image/thumb4/3');