<?php
function print_kikan_image($type,$img_data){
    if(strtolower($type)=="webp"){
        header("Content-Type:image/webp");
        $content=@file_get_contents($img_data);
        echo $content;
    }elseif(strtolower($type)=="byte"){
        header("Content-type: image/jpeg; charset=UTF-8");
        echo $img_data;
    }else{
        header("Content-type: image/jpeg; charset=UTF-8");
        $content=@file_get_contents($img_data);
        echo $content;
    }
}

function print_kikan_noimage(){
    header("Content-type: image/jpeg; charset=UTF-8");
    echo file_get_contents("./parts/noimage.gif");
}
?>