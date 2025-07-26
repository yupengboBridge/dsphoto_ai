<?php
class CommonPhotoImage{
    public static function getPhotoByBudPhotoNo($db_link,$par_bud_photo_no)
    {
        $sql = "select * from photoimg where bud_photo_no = '".$par_bud_photo_no."'";
        $stmt = $db_link->prepare($sql);
        $result = $stmt->execute();
        if($result){
            $image_info = $stmt->fetch(PDO::FETCH_ASSOC);
            if(isset($image_info['bud_photo_no']) && !empty($image_info['bud_photo_no'])){
                return $image_info;
            }
        }
        return null;
    }

    public static function getPhotoByMallNo($db_link,$par_mall_no)
    {
        $sql = "select * from photoimg where mall_no = '".$par_mall_no."'";
        $stmt = $db_link->prepare($sql);
        $result = $stmt->execute();
        if($result){
            $image_info = $stmt->fetch(PDO::FETCH_ASSOC);
            if(isset($image_info['mall_no']) && !empty($image_info['mall_no'])){
                return $image_info;
            }
        }
        return null;
    }

    public static function checkPhotoMno($db_link,$par_p_mno)
    {
        $sql = "select count(*) as cnt from photoimg where photo_mno = '".$par_p_mno."'";
        $stmt = $db_link->prepare($sql);
        $result = $stmt->execute();
        if ($result == true)
        {
            while($image_data = $stmt->fetch(PDO::FETCH_ASSOC))
            {
                $p_cnt = $image_data['cnt'];
                if($p_cnt > 0)
                {
                    return true;
                }
            }
        }
        return false;
    }

    public static function getCategoryNames($db_link, $param_category_ids){
        $sql = "SELECT GROUP_CONCAT( DISTINCT parent_id) AS parent_ids FROM category WHERE category_id IN(".$param_category_ids.") AND parent_id > 0";
        $stmt = $db_link->prepare($sql);
        $result = $stmt->execute();
        $category_parent_ids = "";
        if ($result == true){
            $category_info = $stmt->fetch(PDO::FETCH_ASSOC);
            $category_parent_ids = $category_info['parent_ids'];
        }

        $search_category_ids = $param_category_ids;
        if(!empty($category_parent_ids)){
            $search_category_ids = $category_parent_ids.",".$param_category_ids;
        }

        $sql = "SELECT GROUP_CONCAT(DISTINCT category_name SEPARATOR \" \") AS category_names FROM category WHERE category_id IN(".$search_category_ids.")";
        $stmt = $db_link->prepare($sql);
        $result = $stmt->execute();
        if ($result == true){
            $category_info = $stmt->fetch(PDO::FETCH_ASSOC);
            return $category_info["category_names"];
        }else{
            return "";
        }
    }

    public static function checkAdditionalConstraints1($db_link,$par_bud_photo_no,$new_additional_constraints_1){
        $photo_image_info = CommonPhotoImage::getPhotoByBudPhotoNo($db_link, $par_bud_photo_no);
        if(is_null($photo_image_info)){
            return false;
        }

        $additional_constraints1 = $photo_image_info['additional_constraints1'];
        $additional_constraints1 = str_replace("=_=","",$additional_constraints1);

        if(trim($additional_constraints1) != trim($new_additional_constraints_1)){
            return true;
        }else{
            return false;
        }
    }

    /*
    public static function deletePhotoImage(&$db_link,$par_bud_photo_no,$dir_root,&$ret_message){
        $photo_image_info = CommonPhotoImage::getPhotoByBudPhotoNo($db_link, $par_bud_photo_no);
        if(is_null($photo_image_info)){
            //$ret_message = "BUD_PHOTO_NO::".$par_bud_photo_no."より画像がみつかりませんでした。";
            return true;
        }

        // トランザクションを開始します。（オートコミットがオフになります。）
        $db_link->beginTransaction();

        $sql = "DELETE FROM photoimg WHERE photo_id = ".$photo_image_info['photo_id']." AND is_mall = 1";
        $stmt = $db_link->prepare($sql);
        $result = $stmt->execute();
        if ($result == false){
            // ロールバックします。
            $db_link->rollBack();
            $ret_message = "photo_id::".$photo_image_info['photo_id']."よりphotoimgテーブルから画像を削除できなかったです。";
            return false;
        }

        $sql = "DELETE FROM keyword WHERE photo_id=" . $photo_image_info['photo_id'];
        $stmt = $db_link->prepare($sql);
        $result = $stmt->execute();
        if ($result == false){
            // ロールバックします。
            $db_link->rollBack();
            $ret_message = "photo_id::".$photo_image_info['photo_id']."よりkeywordテーブルから画像を削除できなかったです。";
            return false;
        }

        $sql = "DELETE FROM registration_classification WHERE photo_id=" . $photo_image_info['photo_id'];
        $stmt = $db_link->prepare($sql);
        $result = $stmt->execute();
        if ($result == false){
            // ロールバックします。
            $db_link->rollBack();
            $ret_message = "photo_id::".$photo_image_info['photo_id']."よりregistration_classificationテーブルから画像を削除できなかったです。";
            return false;
        }

        $urlPath = parse_url($photo_image_info['photo_filename'], PHP_URL_PATH);
        if(is_file($dir_root.$urlPath)){
            $unlink_flag = @unlink($dir_root.$urlPath);
            if($unlink_flag==false){
                // ロールバックします。
                $db_link->rollBack();
                $ret_message = "photo_filename::".$photo_image_info['photo_filename']."より画像を削除できなかったです。";
                return false;
            }
        }


        for($i = 1;$i<=10;$i++){
            $urlPath = parse_url($photo_image_info['photo_filename_th'.$i], PHP_URL_PATH);
            if(is_file($dir_root.$urlPath)){
                $unlink_flag = @unlink($dir_root.$urlPath);
                if($unlink_flag==false){
                    // ロールバックします。
                    $db_link->rollBack();
                    $ret_message = "photo_filename_th".$i."::".$photo_image_info['photo_filename_th'.$i]."より画像を削除できなかったです。";
                    return false;
                }
            }
        }

        $db_link->commit();
        $ret_message = "BUD_PHOTO_NO::".$par_bud_photo_no."より画像が削除できました。";
        return true;
    }*/
    
    public static function deletePhotoImage(&$db_link,$par_mall_photo_no,$dir_root,&$ret_message){
        $sql = "select photo_id,photo_filename,photo_filename_th1,photo_filename_th2,photo_filename_th3,photo_filename_th4,";
        $sql .= "photo_filename_th5,photo_filename_th6,photo_filename_th7,photo_filename_th8,photo_filename_th9,photo_filename_th10";
        $sql .= " from photoimg where mall_no = '".$par_mall_photo_no."'";
        $stmt_del = $db_link->prepare($sql);
        $result = $stmt_del->execute();
        if($result){
            // トランザクションを開始します。（オートコミットがオフになります。）

            $db_link->beginTransaction();

            while($photo_image_info = $stmt_del->fetch(PDO::FETCH_ASSOC)) {
                $sql = "DELETE FROM photoimg WHERE photo_id = ".$photo_image_info['photo_id']." AND is_mall = 1";
                $stmt = $db_link->prepare($sql);
                $result = $stmt->execute();
                if ($result == false){
                    // ロールバックします。
                    $db_link->rollBack();
                    $ret_message = "photo_id::".$photo_image_info['photo_id']."よりphotoimgテーブルから画像を削除できなかったです。";
                    return false;
                }

                $sql = "DELETE FROM keyword WHERE photo_id=" . $photo_image_info['photo_id'];
                $stmt = $db_link->prepare($sql);
                $stmt->execute();
                // $result = $stmt->execute();
                // if ($result == false){
                //     // ロールバックします。
                //     $db_link->rollBack();
                //     $ret_message = "photo_id::".$photo_image_info['photo_id']."よりkeywordテーブルから画像を削除できなかったです。";
                //     return false;
                // }

                $sql = "DELETE FROM registration_classification WHERE photo_id=" . $photo_image_info['photo_id'];
                $stmt = $db_link->prepare($sql);
                $stmt->execute();
                // $result = $stmt->execute();
                // if ($result == false){
                //     // ロールバックします。
                //     $db_link->rollBack();
                //     $ret_message = "photo_id::".$photo_image_info['photo_id']."よりregistration_classificationテーブルから画像を削除できなかったです。";
                //     return false;
                // }

                $urlPath = parse_url($photo_image_info['photo_filename'], PHP_URL_PATH);
                if(is_file($dir_root.$urlPath)){
                    @unlink($dir_root.$urlPath);
                    // $unlink_flag = @unlink($dir_root.$urlPath);
                    // if($unlink_flag==false){
                    //     // ロールバックします。
                    //     $db_link->rollBack();
                    //     $ret_message = "photo_filename::".$photo_image_info['photo_filename']."より画像を削除できなかったです。";
                    //     return false;
                    // }
                }


                for($i = 1;$i<=10;$i++){
                    $urlPath = parse_url($photo_image_info['photo_filename_th'.$i], PHP_URL_PATH);
                    if(is_file($dir_root.$urlPath)){
                        @unlink($dir_root.$urlPath);
                        // $unlink_flag = @unlink($dir_root.$urlPath);
                        // if($unlink_flag==false){
                        //     // ロールバックします。
                        //     $db_link->rollBack();
                        //     $ret_message = "photo_filename_th".$i."::".$photo_image_info['photo_filename_th'.$i]."より画像を削除できなかったです。";
                        //     return false;
                        // }
                    }
                }
            }

            $db_link->commit();
            $ret_message = "MALL_PHOTO_NO::".$par_mall_photo_no."より画像が削除できました。";
            return true;
        }else{
            $ret_message = "MALL_PHOTO_NO::".$par_mall_photo_no."より画像検索がエラーになりました。";
            return false;
        }
        // $photo_image_info = CommonPhotoImage::getPhotoByBudPhotoNo($db_link, $par_bud_photo_no);
        // if(is_null($photo_image_info)){
        //     //$ret_message = "BUD_PHOTO_NO::".$par_bud_photo_no."より画像がみつかりませんでした。";
        //     return true;
        // }
    }

    public static function getClassificationNames($db_link,
                                                  $p_classification_name1,
                                                  $p_direction_name1,
                                                  $p_country_prefecture_name1,
                                                  $p_place_name1
    ){
        $ret_param_classification_names = "";
        $ret_classification_names = "";
        $classification_id1 = "";
        $direction_id1 = "";
        $country_prefecture_id1 = "";
        $place_id1 = "";
        //四つ（分類、方面、国、地名）登録の場合
        if ( (empty($p_classification_name1) || $p_classification_name1 == "") &&
            (empty($p_direction_name1) || $p_direction_name1 == "") &&
            (empty($p_country_prefecture_name1) || $p_country_prefecture_name1 == "") &&
            (!empty($p_place_name1) && strlen($p_place_name1) > 0)
        )
        {
            $ret_flag = CommonPhotoImage::get_id(
                $db_link,
                "0001",
                null,
                null,
                null,
                 $p_place_name1,
                $ret_classification_data);

            $classification_id1 = $ret_classification_data["classification_id1"];
            $direction_id1 = $ret_classification_data["direction_id1"];
            $country_prefecture_id1 = $ret_classification_data["country_prefecture_id1"];
            $place_id1 = $ret_classification_data["place_id1"];

            //データが見つからない場合
            if ($ret_flag == "無し")
            {
                $ret_param_classification_names = $p_place_name1;
            } elseif ($ret_flag == "1") {
                $ret_classification_names =
                    $ret_classification_data["classification_name1"]." ".
                    $ret_classification_data["direction_name1"] ." ".
                    $ret_classification_data["country_prefecture_name1"]." ".
                    $ret_classification_data["place_name1"] ;
            }
        }

        if ( (empty($p_classification_name1) || $p_classification_name1 == "") &&
            (empty($p_direction_name1) || $p_direction_name1 == "") &&
            (!empty($p_country_prefecture_name1) && strlen($p_country_prefecture_name1) > 0) &&
            (!empty($p_place_name1) && strlen($p_place_name1) > 0)
        )
        {
            $ret_flag = CommonPhotoImage::get_id(
                $db_link,
                "0011",
                null,
                null,
                $p_country_prefecture_name1,
                $p_place_name1,
                $ret_classification_data);

            $classification_id1 = $ret_classification_data["classification_id1"];
            $direction_id1 = $ret_classification_data["direction_id1"];
            $country_prefecture_id1 = $ret_classification_data["country_prefecture_id1"];
            $place_id1 = $ret_classification_data["place_id1"];

            //データが見つからない場合
            if ($ret_flag == "無し")
            {
                $ret_param_classification_names = $p_country_prefecture_name1." ".$p_place_name1;
            } elseif ($ret_flag == "1") {
                $ret_classification_names =
                    $ret_classification_data["classification_name1"]." ".
                    $ret_classification_data["direction_name1"] ." ".
                    $ret_classification_data["country_prefecture_name1"]." ".
                    $ret_classification_data["place_name1"] ;
            }
        }

        if ( (empty($p_classification_name1) || $p_classification_name1 == "") &&
            (!empty($p_direction_name1) && strlen($p_direction_name1) > 0) &&
            (empty($p_country_prefecture_name1) || $p_country_prefecture_name1 == "") &&
            (!empty($p_place_name1) && strlen($p_place_name1) > 0)
        )
        {
            $ret_flag = CommonPhotoImage::get_id(
                $db_link,
                "0101",
                null,
                $p_direction_name1,
                null,
                $p_place_name1,
                $ret_classification_data);

            $classification_id1 = $ret_classification_data["classification_id1"];
            $direction_id1 = $ret_classification_data["direction_id1"];
            $country_prefecture_id1 = $ret_classification_data["country_prefecture_id1"];
            $place_id1 = $ret_classification_data["place_id1"];

            //データが見つからない場合
            if ($ret_flag == "無し")
            {
                $ret_param_classification_names = $p_direction_name1." ".$p_place_name1;
            } elseif ($ret_flag == "1") {
                $ret_classification_names =
                    $ret_classification_data["classification_name1"]." ".
                    $ret_classification_data["direction_name1"] ." ".
                    $ret_classification_data["country_prefecture_name1"]." ".
                    $ret_classification_data["place_name1"] ;
            }
        }

        if ( (empty($p_classification_name1) || $p_classification_name1 == "") &&
            (!empty($p_direction_name1) && strlen($p_direction_name1) > 0) &&
            (!empty($p_country_prefecture_name1) && strlen($p_country_prefecture_name1) > 0) &&
            (!empty($p_place_name1) && strlen($p_place_name1) > 0)
        )
        {
            $ret_flag = CommonPhotoImage::get_id(
                $db_link,
                "0111",
                null,
                $p_direction_name1,
                $p_country_prefecture_name1,
                $p_place_name1,
                $ret_classification_data);

            $classification_id1 = $ret_classification_data["classification_id1"];
            $direction_id1 = $ret_classification_data["direction_id1"];
            $country_prefecture_id1 = $ret_classification_data["country_prefecture_id1"];
            $place_id1 = $ret_classification_data["place_id1"];

            //データが見つからない場合
            if ($ret_flag == "無し")
            {
                $ret_param_classification_names = $p_direction_name1." ".$p_country_prefecture_name1." ".$p_place_name1;
            } elseif ($ret_flag == "1") {
                $ret_classification_names =
                    $ret_classification_data["classification_name1"]." ".
                    $ret_classification_data["direction_name1"] ." ".
                    $ret_classification_data["country_prefecture_name1"]." ".
                    $ret_classification_data["place_name1"] ;
            }
        }

        if ( (!empty($p_classification_name1) && strlen($p_classification_name1) > 0) &&
            (empty($p_direction_name1) || $p_direction_name1 == "") &&
            (empty($p_country_prefecture_name1) || $p_country_prefecture_name1 == "") &&
            (!empty($p_place_name1) && strlen($p_place_name1) > 0)
        )
        {
            $ret_flag = CommonPhotoImage::get_id(
                $db_link,
                "1001",
                $p_classification_name1,
                null,
                null,
                $p_place_name1,
                $ret_classification_data);

            $classification_id1 = $ret_classification_data["classification_id1"];
            $direction_id1 = $ret_classification_data["direction_id1"];
            $country_prefecture_id1 = $ret_classification_data["country_prefecture_id1"];
            $place_id1 = $ret_classification_data["place_id1"];

            //データが見つからない場合
            if ($ret_flag == "無し")
            {
                $ret_param_classification_names = $p_classification_name1." ".$p_place_name1;
            } elseif ($ret_flag == "1") {
                $ret_classification_names =
                    $ret_classification_data["classification_name1"]." ".
                    $ret_classification_data["direction_name1"] ." ".
                    $ret_classification_data["country_prefecture_name1"]." ".
                    $ret_classification_data["place_name1"] ;
            }
        }

        if ( (!empty($p_classification_name1) && strlen($p_classification_name1) > 0) &&
            (empty($p_direction_name1) || $p_direction_name1 == "") &&
            (!empty($p_country_prefecture_name1) && strlen($p_country_prefecture_name1) > 0) &&
            (!empty($p_place_name1) && strlen($p_place_name1) > 0)
        )
        {
            $ret_flag = CommonPhotoImage::get_id(
                $db_link,
                "1011",
                $p_classification_name1,
                null,
                $p_country_prefecture_name1,
                $p_place_name1,
                $ret_classification_data);

            $classification_id1 = $ret_classification_data["classification_id1"];
            $direction_id1 = $ret_classification_data["direction_id1"];
            $country_prefecture_id1 = $ret_classification_data["country_prefecture_id1"];
            $place_id1 = $ret_classification_data["place_id1"];

            //データが見つからない場合
            if ($ret_flag == "無し")
            {
                $ret_param_classification_names = $p_classification_name1." ".$p_country_prefecture_name1." ".$p_place_name1;
            } elseif ($ret_flag == "1") {
                $ret_classification_names =
                    $ret_classification_data["classification_name1"]." ".
                    $ret_classification_data["direction_name1"] ." ".
                    $ret_classification_data["country_prefecture_name1"]." ".
                    $ret_classification_data["place_name1"] ;
            }
        }

        if ( (!empty($p_classification_name1) && strlen($p_classification_name1) > 0) &&
            (!empty($p_direction_name1) && strlen($p_direction_name1) > 0) &&
            (!empty($p_country_prefecture_name1) && strlen($p_country_prefecture_name1) > 0) &&
            (!empty($p_place_name1) && strlen($p_place_name1) > 0)
        )
        {
            $ret_flag = CommonPhotoImage::get_id(
                $db_link,
                "1111",
                $p_classification_name1,
                $p_direction_name1,
                $p_country_prefecture_name1,
                $p_place_name1,
                $ret_classification_data);

            $classification_id1 = $ret_classification_data["classification_id1"];
            $direction_id1 = $ret_classification_data["direction_id1"];
            $country_prefecture_id1 = $ret_classification_data["country_prefecture_id1"];
            $place_id1 = $ret_classification_data["place_id1"];

            //データが見つからない場合
            if ($ret_flag == "無し")
            {
                $ret_param_classification_names =
                    $p_classification_name1." ".
                    $p_direction_name1." ".
                    $p_country_prefecture_name1." ".
                    $p_place_name1;
            } elseif ($ret_flag == "1") {
                $ret_classification_names =
                    $ret_classification_data["classification_name1"]." ".
                    $ret_classification_data["direction_name1"] ." ".
                    $ret_classification_data["country_prefecture_name1"]." ".
                    $ret_classification_data["place_name1"] ;
            }
        }

        //三つ（分類、方面、国、地名）登録の場合
        if ( (empty($p_classification_name1) || $p_classification_name1 == "") &&
            (empty($p_direction_name1) || $p_direction_name1 == "") &&
            (!empty($p_country_prefecture_name1) && strlen($p_country_prefecture_name1) > 0) &&
            (empty($p_place_name1) || $p_place_name1 == "")
        )
        {
            $ret_flag = CommonPhotoImage::get_id(
                $db_link,
                "0010",
                null,
                null,
                $p_country_prefecture_name1,
                null,
                $ret_classification_data);

            $classification_id1 = $ret_classification_data["classification_id1"];
            $direction_id1 = $ret_classification_data["direction_id1"];
            $country_prefecture_id1 = $ret_classification_data["country_prefecture_id1"];
            $place_id1 = $ret_classification_data["place_id1"];

            //データが見つからない場合
            if ($ret_flag == "無し")
            {
                $ret_param_classification_names = $p_country_prefecture_name1;
            } elseif ($ret_flag == "1") {
                $ret_classification_names =
                    $ret_classification_data["classification_name1"]." ".
                    $ret_classification_data["direction_name1"] ." ".
                    $ret_classification_data["country_prefecture_name1"] ;
            }
        }

        if ( (empty($p_classification_name1) || $p_classification_name1 == "") &&
            (!empty($p_direction_name1) && strlen($p_direction_name1) > 0) &&
            (!empty($p_country_prefecture_name1) && strlen($p_country_prefecture_name1) > 0) &&
            (empty($p_place_name1) || $p_place_name1 == "")
        )
        {
            $ret_flag = CommonPhotoImage::get_id(
                $db_link,
                "0110",
                null,
                $p_direction_name1,
                $p_country_prefecture_name1,
                null,
                $ret_classification_data);

            $classification_id1 = $ret_classification_data["classification_id1"];
            $direction_id1 = $ret_classification_data["direction_id1"];
            $country_prefecture_id1 = $ret_classification_data["country_prefecture_id1"];
            $place_id1 = $ret_classification_data["place_id1"];

            //データが見つからない場合
            if ($ret_flag == "無し")
            {
                $ret_param_classification_names = $p_direction_name1." ".$p_country_prefecture_name1;
            } elseif ($ret_flag == "1") {
                $ret_classification_names =
                    $ret_classification_data["classification_name1"]." ".
                    $ret_classification_data["direction_name1"] ." ".
                    $ret_classification_data["country_prefecture_name1"] ;
            }
        }

        if ( (!empty($p_classification_name1) && strlen($p_classification_name1) > 0) &&
            (empty($p_direction_name1) || $p_direction_name1 == "") &&
            (!empty($p_country_prefecture_name1) && strlen($p_country_prefecture_name1) > 0) &&
            (empty($p_place_name1) || $p_place_name1 == "")
        )
        {
            $ret_flag = CommonPhotoImage::get_id(
                $db_link,
                "1010",
                $p_classification_name1,
                null,
                $p_country_prefecture_name1,
                null,
                $ret_classification_data);

            $classification_id1 = $ret_classification_data["classification_id1"];
            $direction_id1 = $ret_classification_data["direction_id1"];
            $country_prefecture_id1 = $ret_classification_data["country_prefecture_id1"];
            $place_id1 = $ret_classification_data["place_id1"];

            //データが見つからない場合
            if ($ret_flag == "無し")
            {
                $ret_param_classification_names = $p_classification_name1." ".$p_country_prefecture_name1;
            } elseif ($ret_flag == "1") {
                $ret_classification_names =
                    $ret_classification_data["classification_name1"]." ".
                    $ret_classification_data["direction_name1"] ." ".
                    $ret_classification_data["country_prefecture_name1"] ;
            }
        }

        if ( (!empty($p_classification_name1) && strlen($p_classification_name1) > 0) &&
            (!empty($p_direction_name1) && strlen($p_direction_name1) > 0) &&
            (!empty($p_country_prefecture_name1) && strlen($p_country_prefecture_name1) > 0) &&
            (empty($p_place_name1) || $p_place_name1 == "")
        )
        {
            $ret_flag = CommonPhotoImage::get_id(
                $db_link,
                "1110",
                $p_classification_name1,
                $p_direction_name1,
                $p_country_prefecture_name1,
                null,
                $ret_classification_data);

            $classification_id1 = $ret_classification_data["classification_id1"];
            $direction_id1 = $ret_classification_data["direction_id1"];
            $country_prefecture_id1 = $ret_classification_data["country_prefecture_id1"];
            $place_id1 = $ret_classification_data["place_id1"];

            //データが見つからない場合
            if ($ret_flag == "無し")
            {
                $ret_param_classification_names = $p_classification_name1." ".$p_direction_name1." ".$p_country_prefecture_name1;
            } elseif ($ret_flag == "1") {
                $ret_classification_names =
                    $ret_classification_data["classification_name1"]." ".
                    $ret_classification_data["direction_name1"] ." ".
                    $ret_classification_data["country_prefecture_name1"] ;
            }
        }

        //二つ（分類、方面、国、地名）登録の場合
        if ( (empty($p_classification_name1) || $p_classification_name1 == "") &&
            (!empty($p_direction_name1) && strlen($p_direction_name1) > 0) &&
            (empty($p_country_prefecture_name1) || $p_country_prefecture_name1 == "") &&
            (empty($p_place_name1) || $p_place_name1 == "")
        )
        {
            $ret_flag = CommonPhotoImage::get_id(
                $db_link,
                "0100",
                null,
                $p_direction_name1,
                null,
                null,
                $ret_classification_data);

            $classification_id1 = $ret_classification_data["classification_id1"];
            $direction_id1 = $ret_classification_data["direction_id1"];
            $country_prefecture_id1 = $ret_classification_data["country_prefecture_id1"];
            $place_id1 = $ret_classification_data["place_id1"];

            //データが見つからない場合
            if ($ret_flag == "無し")
            {
                $ret_param_classification_names = $p_direction_name1;
            } elseif ($ret_flag == "1") {
                $ret_classification_names =
                    $ret_classification_data["classification_name1"]." ".
                    $ret_classification_data["direction_name1"] ." ".
                    $ret_classification_data["country_prefecture_name1"] ;
            }
        }

        //一つ（分類、方面、国、地名）登録の場合
        if ( (!empty($p_classification_name1) && strlen($p_classification_name1) > 0) &&
            (empty($p_direction_name1) || $p_direction_name1 == "") &&
            (empty($p_country_prefecture_name1) || $p_country_prefecture_name1 == "") &&
            (empty($p_place_name1) || $p_place_name1 == "")
        )
        {
            $ret_flag = CommonPhotoImage::get_id(
                $db_link,
                "1000",
                $p_classification_name1,
                null,
                null,
                null,
                $ret_classification_data);

            $classification_id1 = $ret_classification_data["classification_id1"];
            $direction_id1 = $ret_classification_data["direction_id1"];
            $country_prefecture_id1 = $ret_classification_data["country_prefecture_id1"];
            $place_id1 = $ret_classification_data["place_id1"];

            //データが見つからない場合
            if ($ret_flag == "無し")
            {
                $ret_param_classification_names = $p_classification_name1;
            } elseif ($ret_flag == "1") {
                $ret_classification_names =
                    $ret_classification_data["classification_name1"]." ".
                    $ret_classification_data["direction_name1"] ." ".
                    $ret_classification_data["country_prefecture_name1"] ;
            }
        }

        return array(
            "ret_param_classification_names" => $ret_param_classification_names,
            "ret_classification_names" => $ret_classification_names,
            "classification_id1" => $classification_id1,
            "direction_id1" => $direction_id1,
            "country_prefecture_id1" => $country_prefecture_id1,
            "place_id1" => $place_id1
        );
    }

    /*
     * 関数名：get_id
     * 関数説明：分類、方面、国、都市を取得する
     * パラメタ：
     * strflg:フラグ
     * p_c:分類
     * p_d:方面
     * p_cp:国
     * p_p:都市
     * 戻り値："1"（正常）/"-1"（エラー）/"無し（データ無し）"
     */
    public static function get_id($db_link, $classification_code, $p_c = "", $p_d = "", $p_cp = "", $p_p = "", &$ret_data)
    {
        $ret_data = array(
            "classification_id1" => "",// 分類ID
            "classification_name1" => "",// 分類
            "direction_id1" => "",//方面ID
            "direction_name1" => "",// 方面
            "country_prefecture_id1" => "",// 国・都道府県ID
            "country_prefecture_name1" => "",// 国・都道府県
            "place_id1" => "",// 地名ID
            "place_name1" => "",// 地名
        );

        $sql = "SELECT classification.classification_id, classification_name,";
        $sql .= "direction.direction_id, direction_name, country_prefecture.country_prefecture_id,";
        $sql .= "country_prefecture_name,place_id,place_name";
        $sql .= " FROM classification, direction, country_prefecture, place";

        $sql_where = " WHERE 1=1 ";
        if ($p_c != null && !empty($p_c))
        {
            if (strlen($p_c) > 0) $sql_where .= " AND classification_name = '".$p_c."'";
        }

        if ($p_d != null && !empty($p_d))
        {
            if (strlen($p_d) > 0)
            {
                $sql_where .= " AND direction_name = '".$p_d."'";
                $sql_where .= " AND direction.classification_id = classification.classification_id";
            }
        }

        if ($p_cp != null && !empty($p_cp))
        {
            if (strlen($p_cp) > 0)
            {
                $sql_where .= " AND country_prefecture_name = '".$p_cp."'";
                $sql_where .= " AND country_prefecture.direction_id = direction.direction_id";
                if ($p_d == null || empty($p_d) || strlen($p_d) <= 0)
                {
                    $sql_where .= " AND direction.classification_id = classification.classification_id";
                }
            }
        }

        if ($p_p != null && !empty($p_p))
        {
            if (strlen($p_p) > 0)
            {
                $sql_where .= " AND place_name = '".$p_p."'";
                $sql_where .= " AND place.country_prefecture_id = country_prefecture.country_prefecture_id";
                if ($p_cp == null || empty($p_cp) || strlen($p_cp) <= 0)
                {
                    $sql_where .= " AND country_prefecture.direction_id = direction.direction_id";
                }
                if ($p_d == null || empty($p_d) || strlen($p_d) <= 0)
                {
                    $sql_where .= " AND direction.classification_id = classification.classification_id";
                }
            }
        }

        if ($sql_where != " WHERE 1=1 ")
        {
            $sql_where .= " LIMIT 1";
            $sql .= $sql_where;
        } else {
            return false;
        }

        $stmt = $db_link->prepare($sql);
        // SQLを実行します。
        $result = $stmt->execute();

        // 実行結果をチェックします。
        if ($result == true)
        {
            // 実行結果がOKの場合の処理です。
            $icount = $stmt->rowCount();
            if ($icount > 0)
            {
                $reg_c = $stmt->fetch(PDO::FETCH_ASSOC);

                // 分類IDなどを保存します。
                if ($classification_code == "0001" || $classification_code == "0011" || $classification_code == "0101" || $classification_code == "0111" ||
                    $classification_code == "1001" || $classification_code == "1011" || $classification_code == "1111")
                {
                    $ret_data["classification_id1"] =  $reg_c['classification_id'];
                    $ret_data["classification_name1"] = $reg_c['classification_name'];

                    $ret_data["direction_id1"] = $reg_c['direction_id'];
                    $ret_data["direction_name1"] = $reg_c['direction_name'];

                    $ret_data["country_prefecture_id1"] = $reg_c['country_prefecture_id'];
                    $ret_data["country_prefecture_name1"] = $reg_c['country_prefecture_name'];

                    $ret_data["place_id1"] = $reg_c['place_id'];
                    $ret_data["place_name1"] = $reg_c['place_name'];
                } elseif ($classification_code == "0010" || $classification_code == "0110" || $classification_code == "1010" || $classification_code == "1110") {
                    $ret_data["classification_id1"] =  $reg_c['classification_id'];
                    $ret_data["classification_name1"] = $reg_c['classification_name'];

                    $ret_data["direction_id1"] = $reg_c['direction_id'];
                    $ret_data["direction_name1"] = $reg_c['direction_name'];

                    $ret_data["country_prefecture_id1"] = $reg_c['country_prefecture_id'];
                    $ret_data["country_prefecture_name1"] = $reg_c['country_prefecture_name'];
                } elseif ($classification_code == "0100") {
                    $ret_data["classification_id1"] =  $reg_c['classification_id'];
                    $ret_data["classification_name1"] = $reg_c['classification_name'];

                    $ret_data["direction_id1"] = $reg_c['direction_id'];
                    $ret_data["direction_name1"] = $reg_c['direction_name'];
                } elseif ($classification_code == "1000") {
                    $ret_data["classification_id1"] =  $reg_c['classification_id'];
                    $ret_data["classification_name1"] = $reg_c['classification_name'];
                } else {
                    return "無し";
                }
                return "1";
            } else {
                //都市を設定した場合、都市より検索しない場合

                if ($classification_code == "0011")
                {
                    //国よりもう一度検索する、検索した場合、都市は登録しない
                    $retval = CommonPhotoImage::get_id2($db_link, $classification_code, "", "", $p_cp, "", $ret2_data);
                    $ret_data = array(
                        "classification_id1" => $ret2_data["classification_id1"],// 分類ID
                        "classification_name1" => $ret2_data["classification_name1"],// 分類
                        "direction_id1" => $ret2_data["direction_id1"],//方面ID
                        "direction_name1" => $ret2_data["direction_name1"],// 方面
                        "country_prefecture_id1" => $ret2_data["country_prefecture_id1"],// 国・都道府県ID
                        "country_prefecture_name1" => $ret2_data["country_prefecture_name1"],// 国・都道府県
                        "place_id1" => $ret2_data["place_id1"],// 地名ID
                        "place_name1" => $ret2_data["place_name1"],// 地名
                    );
                    if ($retval == "無し")
                    {
                        return "無し";
                    } elseif ((int)$retval > 0) {
                        $ret_data["place_id1"] = "";
                        $ret_data["place_name1"] = "";

                        return "1";
                    } else {
                        return "-1";
                    }
                } elseif ($classification_code == "0101") {
                    //方面よりもう一度検索する、検索した場合、国と都市は登録しない
                    $retval = CommonPhotoImage::get_id2($db_link, $classification_code, "", $p_d, "", "", $ret2_data);
                    $ret_data = array(
                        "classification_id1" => $ret2_data["classification_id1"],// 分類ID
                        "classification_name1" => $ret2_data["classification_name1"],// 分類
                        "direction_id1" => $ret2_data["direction_id1"],//方面ID
                        "direction_name1" => $ret2_data["direction_name1"],// 方面
                        "country_prefecture_id1" => $ret2_data["country_prefecture_id1"],// 国・都道府県ID
                        "country_prefecture_name1" => $ret2_data["country_prefecture_name1"],// 国・都道府県
                        "place_id1" => $ret2_data["place_id1"],// 地名ID
                        "place_name1" => $ret2_data["place_name1"],// 地名
                    );
                    if ($retval == "無し")
                    {
                        return "無し";
                    } elseif ((int)$retval > 0) {
                        $ret_data["country_prefecture_id1"] = "";
                        $ret_data["country_prefecture_name1"] = "";
                        $ret_data["place_id1"] = "";
                        $ret_data["place_name1"] = "";

                        return "1";
                    } else {
                        return "-1";
                    }
                } elseif ($classification_code == "0111") {
                    //国と方面よりもう一度検索する、検索した場合、都市は登録しない
                    $retval = CommonPhotoImage::get_id2($db_link, $classification_code, "", $p_d, $p_cp, "", $ret2_data);
                    $ret_data = array(
                        "classification_id1" => $ret2_data["classification_id1"],// 分類ID
                        "classification_name1" => $ret2_data["classification_name1"],// 分類
                        "direction_id1" => $ret2_data["direction_id1"],//方面ID
                        "direction_name1" => $ret2_data["direction_name1"],// 方面
                        "country_prefecture_id1" => $ret2_data["country_prefecture_id1"],// 国・都道府県ID
                        "country_prefecture_name1" => $ret2_data["country_prefecture_name1"],// 国・都道府県
                        "place_id1" => $ret2_data["place_id1"],// 地名ID
                        "place_name1" => $ret2_data["place_name1"],// 地名
                    );
                    if ($retval == "無し")
                    {
                        return "無し";
                    } elseif ((int)$retval > 0) {
                        $ret_data["place_id1"] = "";
                        $ret_data["place_name1"] = "";

                        return "1";
                    } else {
                        return "-1";
                    }
                } elseif ($classification_code == "1001") {
                    //分類よりもう一度検索する、検索した場合、方面と国と都市は登録しない
                    $retval = CommonPhotoImage::get_id2($db_link, $classification_code, $p_c , "", "", "", $ret2_data);
                    $ret_data = array(
                        "classification_id1" => $ret2_data["classification_id1"],// 分類ID
                        "classification_name1" => $ret2_data["classification_name1"],// 分類
                        "direction_id1" => $ret2_data["direction_id1"],//方面ID
                        "direction_name1" => $ret2_data["direction_name1"],// 方面
                        "country_prefecture_id1" => $ret2_data["country_prefecture_id1"],// 国・都道府県ID
                        "country_prefecture_name1" => $ret2_data["country_prefecture_name1"],// 国・都道府県
                        "place_id1" => $ret2_data["place_id1"],// 地名ID
                        "place_name1" => $ret2_data["place_name1"],// 地名
                    );
                    if ($retval == "無し")
                    {
                        return "無し";
                    } elseif ((int)$retval > 0) {
                        $ret_data["direction_id1"] = "";
                        $ret_data["direction_name1"] = "";
                        $ret_data["country_prefecture_id1"] = "";
                        $ret_data["country_prefecture_name1"] = "";
                        $ret_data["place_id1"] = "";
                        $ret_data["place_name1"] = "";

                        return "1";
                    } else {
                        return "-1";
                    }
                } elseif ($classification_code == "1011") {
                    //国と分類よりもう一度検索する、検索した場合、都市は登録しない
                    $retval = CommonPhotoImage::get_id2($db_link, $classification_code, $p_c , "", $p_cp, "", $ret2_data);
                    $ret_data = array(
                        "classification_id1" => $ret2_data["classification_id1"],// 分類ID
                        "classification_name1" => $ret2_data["classification_name1"],// 分類
                        "direction_id1" => $ret2_data["direction_id1"],//方面ID
                        "direction_name1" => $ret2_data["direction_name1"],// 方面
                        "country_prefecture_id1" => $ret2_data["country_prefecture_id1"],// 国・都道府県ID
                        "country_prefecture_name1" => $ret2_data["country_prefecture_name1"],// 国・都道府県
                        "place_id1" => $ret2_data["place_id1"],// 地名ID
                        "place_name1" => $ret2_data["place_name1"],// 地名
                    );
                    if ($retval == "無し")
                    {
                        return "無し";
                    } elseif ((int)$retval > 0) {
                        $ret_data["place_id1"] = "";
                        $ret_data["place_name1"] = $p_p;

                        return "1";
                    } else {
                        return "-1";
                    }
                } elseif ($classification_code == "1111") {
                    $retval = CommonPhotoImage::get_id2($db_link, $classification_code, $p_c , $p_d, $p_cp, "", $ret2_data);
                    $ret_data = array(
                        "classification_id1" => $ret2_data["classification_id1"],// 分類ID
                        "classification_name1" => $ret2_data["classification_name1"],// 分類
                        "direction_id1" => $ret2_data["direction_id1"],//方面ID
                        "direction_name1" => $ret2_data["direction_name1"],// 方面
                        "country_prefecture_id1" => $ret2_data["country_prefecture_id1"],// 国・都道府県ID
                        "country_prefecture_name1" => $ret2_data["country_prefecture_name1"],// 国・都道府県
                        "place_id1" => $ret2_data["place_id1"],// 地名ID
                        "place_name1" => $ret2_data["place_name1"],// 地名
                    );
                    if ($retval == "無し")
                    {
                        return "無し";
                    } elseif ((int)$retval > 0) {
                        $ret_data["place_id1"] = "";// 地名ID
                        $ret_data["place_name1"] = "";// 地名

                        return "1";
                    } else {
                        return "-1";
                    }
                } else {
                    return "無し";
                }
            }
        } else {
            return "-1";
        }
    }

    /*
     * 関数名：get_id2
     * 関数説明：分類、方面、国、都市を取得する
     * パラメタ：
     * $classification_code:フラグ
     * p_c:分類
     * p_d:方面
     * p_cp:国
     * p_p:都市
     * 戻り値："1"（正常）/"-1"（エラー）/"無し（データ無し）"
     */
    public static function get_id2($db_link, $classification_code, $p_c = "", $p_d = "", $p_cp = "", $p_p = "", &$ret_data)
    {
        global $db_link;

        $ret_data = array(
            "classification_id1" => "",// 分類ID
            "classification_name1" => "",// 分類
            "direction_id1" => "",//方面ID
            "direction_name1" => "",// 方面
            "country_prefecture_id1" => "",// 国・都道府県ID
            "country_prefecture_name1" => "",// 国・都道府県
            "place_id1" => "",// 地名ID
            "place_name1" => "",// 地名
        );

        $sql = "SELECT classification.classification_id, classification_name,";
        $sql .= "direction.direction_id, direction_name, country_prefecture.country_prefecture_id,";
        $sql .= "country_prefecture_name,place_id,place_name";
        $sql .= " FROM classification, direction, country_prefecture, place";

        $sql_where = " WHERE 1=1 ";
        if ($p_c != null && !empty($p_c))
        {
            if (strlen($p_c) > 0) $sql_where .= " AND classification_name = '".$p_c."'";
        }

        if ($p_d != null && !empty($p_d))
        {
            if (strlen($p_d) > 0)
            {
                $sql_where .= " AND direction_name = '".$p_d."'";
                $sql_where .= " AND direction.classification_id = classification.classification_id";
            }
        }

        if ($p_cp != null && !empty($p_cp))
        {
            if (strlen($p_cp) > 0)
            {
                $sql_where .= " AND country_prefecture_name = '".$p_cp."'";
                $sql_where .= " AND country_prefecture.direction_id = direction.direction_id";
                if ($p_d == null || empty($p_d) || strlen($p_d) <= 0)
                {
                    $sql_where .= " AND direction.classification_id = classification.classification_id";
                }
            }
        }

        if ($p_p != null && !empty($p_p))
        {
            if (strlen($p_p) > 0)
            {
                $sql_where .= " AND place_name = '".$p_p."'";
                $sql_where .= " AND place.country_prefecture_id = country_prefecture.country_prefecture_id";
                if ($p_cp == null || empty($p_cp) || strlen($p_cp) <= 0)
                {
                    $sql_where .= " AND country_prefecture.direction_id = direction.direction_id";
                }
                if ($p_d == null || empty($p_d) || strlen($p_d) <= 0)
                {
                    $sql_where .= " AND direction.classification_id = classification.classification_id";
                }
            }
        }

        if ($sql_where != " WHERE 1=1 ")
        {
            $sql_where .= " LIMIT 1";
            $sql .= $sql_where;
        } else {
            return false;
        }

        $stmt = $db_link->prepare($sql);
        // SQLを実行します。
        $result = $stmt->execute();

        // 実行結果をチェックします。
        if ($result == true)
        {
            // 実行結果がOKの場合の処理です。
            $icount = $stmt->rowCount();
            if ($icount > 0)
            {
                $reg_c = $stmt->fetch(PDO::FETCH_ASSOC);

                // 分類IDなどを保存します。
                if ($classification_code == "0001" || $classification_code == "0011" || $classification_code == "0101" || $classification_code == "0111" ||
                    $classification_code == "1001" || $classification_code == "1011" || $classification_code == "1111")
                {
                    $ret_data["classification_id1"] =  $reg_c['classification_id'];
                    $ret_data["classification_name1"] = $reg_c['classification_name'];

                    $ret_data["direction_id1"] = $reg_c['direction_id'];
                    $ret_data["direction_name1"] = $reg_c['direction_name'];

                    $ret_data["country_prefecture_id1"] = $reg_c['country_prefecture_id'];
                    $ret_data["country_prefecture_name1"] = $reg_c['country_prefecture_name'];

                    $ret_data["place_id1"] = $reg_c['place_id'];
                    $ret_data["place_name1"] = $reg_c['place_name'];
                } elseif ($classification_code == "0010" || $classification_code == "0110" || $classification_code == "1010" || $classification_code == "1110") {
                    $ret_data["classification_id1"] =  $reg_c['classification_id'];
                    $ret_data["classification_name1"] = $reg_c['classification_name'];

                    $ret_data["direction_id1"] = $reg_c['direction_id'];
                    $ret_data["direction_name1"] = $reg_c['direction_name'];

                    $ret_data["country_prefecture_id1"] = $reg_c['country_prefecture_id'];
                    $ret_data["country_prefecture_name1"] = $reg_c['country_prefecture_name'];
                } elseif ($classification_code == "0100") {
                    $ret_data["classification_id1"] =  $reg_c['classification_id'];
                    $ret_data["classification_name1"] = $reg_c['classification_name'];

                    $ret_data["direction_id1"] = $reg_c['direction_id'];
                    $ret_data["direction_name1"] = $reg_c['direction_name'];
                } elseif ($classification_code == "1000") {
                    $ret_data["classification_id1"] =  $reg_c['classification_id'];
                    $ret_data["classification_name1"] = $reg_c['classification_name'];
                } else {
                    return "無し";
                }
                return "1";
            } else {
                return "無し";
            }
        } else {
            return "-1";
        }
    }

    public static function getTakePictureTime($param_take_picture_time_id){
        $rad_kisetu = 0;
        $time2 = "";

        // 撮影時期２
        if ($param_take_picture_time_id == "春")
        {
            $rad_kisetu = 1;
        } elseif ($param_take_picture_time_id == "夏") {
            $rad_kisetu = 2;
        } elseif ($param_take_picture_time_id == "秋") {
            $rad_kisetu = 3;
        } elseif ($param_take_picture_time_id == "冬") {
            $rad_kisetu = 4;
            // 撮影時期１
        } else {
            $take_picture_time_id = mb_convert_kana($param_take_picture_time_id,"a","UTF-8");
            $ipos1 = strpos($take_picture_time_id,"月");
            $ipos2 = strpos($take_picture_time_id,"年");
            $istart = -1;
            if ($ipos2 > 0) $istart = $ipos2 + 3;

            if ((int)$ipos1 > 0)
            {
                if ($istart > 0)
                {
                    $take_picture_time_id1 = substr($take_picture_time_id,$istart);
                    $itmp_pos = strpos($take_picture_time_id1,"月");
                    $tmp_t = substr($take_picture_time_id1,0,(int)$itmp_pos);
                    $take_picture_time_id1 = $tmp_t;
                } else {
                    $take_picture_time_id1 = substr($take_picture_time_id,0,(int)$ipos1);
                }

                if (!empty($take_picture_time_id1) && strlen($take_picture_time_id1) > 0)
                {
                    if (CommonUtil::checkNumber($take_picture_time_id1))
                    {
                        $time2 = (int)$take_picture_time_id1;
                        if($time2 >=1 && $time2 <=3){
                            $rad_kisetu = 1;
                        }elseif($time2 >=4 && $time2 <=6){
                            $rad_kisetu = 2;
                        }elseif($time2 >=7 && $time2 <=9) {
                            $rad_kisetu = 3;
                        }elseif($time2 >=10 && $time2 <=12) {
                            $rad_kisetu = 4;
                        }
                    }
                }
            } else {
                $tmp1 = (int)$take_picture_time_id;
                if ($tmp1 >= 1 && $tmp1 <= 12)
                {
                    $time2 = $tmp1;
                    if($time2 >=1 && $time2 <=3){
                        $rad_kisetu = 1;
                    }elseif($time2 >=4 && $time2 <=6){
                        $rad_kisetu = 2;
                    }elseif($time2 >=7 && $time2 <=9) {
                        $rad_kisetu = 3;
                    }elseif($time2 >=10 && $time2 <=12) {
                        $rad_kisetu = 4;
                    }
                }
            }
        }

        return array(
            "rad_kisetu" => $rad_kisetu,
            "time2" => $time2
        );
    }

    public static function getCategories($category){
        $category_tmp_ary = explode(" ",$category);
        $categories_str = "";
        for ($i = 0; $i < count($category_tmp_ary); $i++)
        {
            if ($category_tmp_ary[$i] == "風景" ||
                $category_tmp_ary[$i] == "自然・植物" || $category_tmp_ary[$i] == "植物・自然"
            )
            {
                if (!empty($categories_str))
                {
                    $tmp = "__".$categories_str;
                    if (strpos($tmp,"自然・植物") > 0)
                    {
                    } else {
                        $categories_str .= " 自然・植物";
                    }
                } else {
                    $categories_str = "自然・植物";
                }
            }

            if ($category_tmp_ary[$i] == "海" || $category_tmp_ary[$i] == "海・ビーチ" ||
                $category_tmp_ary[$i] == "ビーチ・海"
            )
            {
                if (!empty($categories_str))
                {
                    $tmp = "__".$categories_str;
                    if (strpos($tmp,"自然・植物") > 0)
                    {
                        $categories_str .= " 海・ビーチ";
                    } else {
                        $categories_str .= " 自然・植物 海・ビーチ";
                    }
                } else {
                    $categories_str = "自然・植物 海・ビーチ";
                }
            }

            if ($category_tmp_ary[$i] == "山" || $category_tmp_ary[$i] == "川" ||
                $category_tmp_ary[$i] == "滝" || $category_tmp_ary[$i] == "木" ||
                $category_tmp_ary[$i] == "桜" || $category_tmp_ary[$i] == "紅葉")
            {
                if (!empty($categories_str))
                {
                    $tmp = "__".$categories_str;
                    if (strpos($tmp,"自然・植物") > 0)
                    {
                        $categories_str .= " ".$category_tmp_ary[$i];
                    } else {
                        $categories_str .= " 自然・植物 ".$category_tmp_ary[$i];
                    }
                } else {
                    $categories_str = "自然・植物 ".$category_tmp_ary[$i];
                }
            }

            if ($category_tmp_ary[$i] == "花" || $category_tmp_ary[$i] == "草" ||
                $category_tmp_ary[$i] == "花・草" || $category_tmp_ary[$i] == "草・花"
            )
            {
                if (!empty($categories_str))
                {
                    $tmp = "__".$categories_str;
                    if (strpos($tmp,"自然・植物") > 0)
                    {
                        $categories_str .= " 花・草";
                    } else {
                        $categories_str .= " 自然・植物 花・草";
                    }
                } else {
                    $categories_str = "自然・植物 花・草";
                }
            }

            if ($category_tmp_ary[$i] == "湖・沼" || $category_tmp_ary[$i] == "沼・湖" ||
                $category_tmp_ary[$i] == "湖" || $category_tmp_ary[$i] == "沼"
            )
            {
                if (!empty($categories_str))
                {
                    $tmp = "__".$categories_str;
                    if (strpos($tmp,"自然・植物") > 0)
                    {
                        $categories_str .= " 湖沼";
                    } else {
                        $categories_str .= " 自然・植物 湖沼";
                    }
                } else {
                    $categories_str = "自然・植物 湖沼";
                }
            }

            if ($category_tmp_ary[$i] == "建物" || $category_tmp_ary[$i] == "建造物")
            {
                if (!empty($categories_str))
                {
                    $tmp = "__".$categories_str;
                    if (strpos($tmp,"建造物") > 0)
                    {
                    } else {
                        $categories_str .= " 建造物";
                    }
                } else {
                    $categories_str = "建造物";
                }
            }

            if ($category_tmp_ary[$i] == "寺社" || $category_tmp_ary[$i] == "教会" ||
                $category_tmp_ary[$i] == "城" || $category_tmp_ary[$i] == "橋" ||
                $category_tmp_ary[$i] == "塔" || $category_tmp_ary[$i] == "遺跡" ||
                $category_tmp_ary[$i] == "像")
            {
                if (!empty($categories_str))
                {
                    $tmp = "__".$categories_str;
                    if (strpos($tmp,"建造物") > 0)
                    {
                        $categories_str .= " ".$category_tmp_ary[$i];
                    } else {
                        $categories_str .= " 建造物 ".$category_tmp_ary[$i];
                    }
                } else {
                    $categories_str = "建造物 ".$category_tmp_ary[$i];
                }
            }

            if ($category_tmp_ary[$i] == "モニュメント" || $category_tmp_ary[$i] == "モニュメント（記念碑）")
            {
                if (!empty($categories_str))
                {
                    $tmp = "__".$categories_str;
                    if (strpos($tmp,"建造物") > 0)
                    {
                        $categories_str .= " モニュメント（記念碑）";
                    } else {
                        $categories_str .= " 建造物 モニュメント（記念碑）";
                    }
                } else {
                    $categories_str = "建造物 モニュメント（記念碑）";
                }
            }

            if ($category_tmp_ary[$i] == "美術館" || $category_tmp_ary[$i] == "博物館" ||
                $category_tmp_ary[$i] == "美術館・博物館" || $category_tmp_ary[$i] == "博物館・美術館")
            {
                if (!empty($categories_str))
                {
                    $tmp = "__".$categories_str;
                    if (strpos($tmp,"施設") > 0)
                    {
                        $categories_str .= " 美術館・博物館 ";
                    } else {
                        $categories_str .= " 施設 美術館・博物館";
                    }
                } else {
                    $categories_str = "施設 美術館・博物館";
                }
            }

            if ($category_tmp_ary[$i] == "自然公園" || $category_tmp_ary[$i] == "公園・庭園" ||
                $category_tmp_ary[$i] == "公園" || $category_tmp_ary[$i] == "庭園" ||
                $category_tmp_ary[$i] == "庭園・公園"
            )
            {
                if (!empty($categories_str))
                {
                    $tmp = "__".$categories_str;
                    if (strpos($tmp,"施設") > 0)
                    {
                        $categories_str .= " 公園・庭園";
                    } else {
                        $categories_str .= " 施設 公園・庭園";
                    }
                } else {
                    $categories_str = "施設 公園・庭園";
                }
            }

            if ($category_tmp_ary[$i] == "遊園地等" || $category_tmp_ary[$i] == "動物園" ||
                $category_tmp_ary[$i] == "水族館" || $category_tmp_ary[$i] == "遊園地" ||
                $category_tmp_ary[$i] == "動物園・遊園地" || $category_tmp_ary[$i] == "動物園・遊園地" ||
                $category_tmp_ary[$i] == "動物園・水族館" || $category_tmp_ary[$i] == "水族館・動物園" ||
                $category_tmp_ary[$i] == "水族館・遊園地" || $category_tmp_ary[$i] == "遊園地・水族館"
            )
            {
                if (!empty($categories_str))
                {
                    $tmp = "__".$categories_str;
                    if (strpos($tmp,"施設") > 0)
                    {
                        $categories_str .= " 動物園・水族館・遊園地";
                    } else {
                        $categories_str .= " 施設 動物園・水族館・遊園地";
                    }
                } else {
                    $categories_str = "施設 動物園・水族館・遊園地";
                }
            }

            if ($category_tmp_ary[$i] == "ゴルフ" || $category_tmp_ary[$i] == "ゴルフ場")
            {
                if (!empty($categories_str))
                {
                    $tmp = "__".$categories_str;
                    if (strpos($tmp,"施設") > 0)
                    {
                        $categories_str .= " ゴルフ場";
                    } else {
                        $categories_str .= " 施設 ゴルフ場";
                    }
                } else {
                    $categories_str = "施設 ゴルフ場";
                }
            }

            if ($category_tmp_ary[$i] == "スキー" || $category_tmp_ary[$i] == "スキー場")
            {
                if (!empty($categories_str))
                {
                    $tmp = "__".$categories_str;
                    if (strpos($tmp,"施設") > 0)
                    {
                        $categories_str .= " スキー場";
                    } else {
                        $categories_str .= " 施設 スキー場";
                    }
                } else {
                    $categories_str = "施設 スキー場";
                }
            }

            if ($category_tmp_ary[$i] == "レストラン")
            {
                if (!empty($categories_str))
                {
                    $tmp = "__".$categories_str;
                    if (strpos($tmp,"施設") > 0)
                    {
                        $categories_str .= " レストラン";
                    } else {
                        $categories_str .= " 施設 レストラン";
                    }
                } else {
                    $categories_str = "施設 レストラン";
                }
            }

            if ($category_tmp_ary[$i] == "店" || $category_tmp_ary[$i] == "店舗")
            {
                if (!empty($categories_str))
                {
                    $tmp = "__".$categories_str;
                    if (strpos($tmp,"施設") > 0)
                    {
                        $categories_str .= " 店舗";
                    } else {
                        $categories_str .= " 施設 店舗";
                    }
                } else {
                    $categories_str = "施設 店舗";
                }
            }

            if ($category_tmp_ary[$i] == "ホテル" || $category_tmp_ary[$i] == "宿泊施設")
            {
                if (!empty($categories_str))
                {
                    $categories_str .= " 宿泊施設";
                } else {
                    $categories_str = "宿泊施設";
                }
            }

            if ($category_tmp_ary[$i] == "外観" || $category_tmp_ary[$i] == "室内" ||
                $category_tmp_ary[$i] == "風呂"
            )
            {
                if (!empty($categories_str))
                {
                    $tmp = "__".$categories_str;
                    if (strpos($tmp,"宿泊施設") > 0)
                    {
                        $categories_str .= " スキー場".$category_tmp_ary[$i];
                    } else {
                        $categories_str .= " 宿泊施設 ".$category_tmp_ary[$i];
                    }
                } else {
                    $categories_str = "宿泊施設 ".$category_tmp_ary[$i];
                }
            }

            if ($category_tmp_ary[$i] == "街" || $category_tmp_ary[$i] == "街並み")
            {
                if (!empty($categories_str))
                {
                    $categories_str .= " 街並み";
                } else {
                    $categories_str = "街並み";
                }
            }

            if ($category_tmp_ary[$i] == "乗り物")
            {
                if (!empty($categories_str))
                {
                    $tmp = "__".$categories_str;
                    if (strpos($tmp,"乗り物") > 0)
                    {
                    } else {
                        $categories_str .= " 乗り物";
                    }
                } else {
                    $categories_str = "乗り物";
                }
            }

            if ($category_tmp_ary[$i] == "航空" || $category_tmp_ary[$i] == "飛行機")
            {
                if (!empty($categories_str))
                {
                    $tmp = "__".$categories_str;
                    if (strpos($tmp,"乗り物") > 0)
                    {
                        $categories_str .= " 飛行機";
                    } else {
                        $categories_str .= " 乗り物 飛行機";
                    }
                } else {
                    $categories_str = "乗り物 飛行機";
                }
            }

            if ($category_tmp_ary[$i] == "鉄道" || $category_tmp_ary[$i] == "バス" ||
                $category_tmp_ary[$i] == "船")
            {
                if (!empty($categories_str))
                {
                    $tmp = "__".$categories_str;
                    if (strpos($tmp,"乗り物") > 0)
                    {
                        $categories_str .= " ".$category_tmp_ary[$i];
                    } else {
                        $categories_str .= " 乗り物 ".$category_tmp_ary[$i];
                    }
                } else {
                    $categories_str = "乗り物 ".$category_tmp_ary[$i];
                }
            }

            if ($category_tmp_ary[$i] == "人物")
            {
                if (!empty($categories_str))
                {
                    $categories_str .= " ".$category_tmp_ary[$i];
                } else {
                    $categories_str = $category_tmp_ary[$i];
                }
            }

            if ($category_tmp_ary[$i] == "動物" || $category_tmp_ary[$i] == "生物")
            {
                if (!empty($categories_str))
                {
                    $categories_str .= " 生物";
                } else {
                    $categories_str = "生物";
                }
            }

            if ($category_tmp_ary[$i] == "食品" || $category_tmp_ary[$i] == "飲食物")
            {
                if (!empty($categories_str))
                {
                    $tmp = "__".$categories_str;
                    if (strpos($tmp,"飲食物") > 0)
                    {
                    } else {
                        $categories_str .= " 飲食物";
                    }
                } else {
                    $categories_str = "飲食物";
                }
            }

            if ($category_tmp_ary[$i] == "料理")
            {
                if (!empty($categories_str))
                {
                    $tmp = "__".$categories_str;
                    if (strpos($tmp,"飲食物") > 0)
                    {
                        $categories_str .= " 料理";
                    } else {
                        $categories_str .= " 飲食物 料理";
                    }
                } else {
                    $categories_str = "飲食物 料理";
                }
            }

            if ($category_tmp_ary[$i] == "製品")
            {
                if (!empty($categories_str))
                {
                    $tmp = "__".$categories_str;
                    if (strpos($tmp,"品物") > 0)
                    {
                    } else {
                        $categories_str .= " 品物";
                    }
                } else {
                    $categories_str = "品物";
                }
            }

            if ($category_tmp_ary[$i] == "美術" || $category_tmp_ary[$i] == "美術品")
            {
                if (!empty($categories_str))
                {
                    $tmp = "__".$categories_str;
                    if (strpos($tmp,"品物") > 0)
                    {
                        $categories_str .= " 美術品";
                    } else {
                        $categories_str .= " 品物 美術品";
                    }
                } else {
                    $categories_str = "品物 美術品";
                }
            }

            if ($category_tmp_ary[$i] == "イベント")
            {
                if (!empty($categories_str))
                {
                    $tmp = "__".$categories_str;
                    if (strpos($tmp,"イベント") > 0)
                    {
                    } else {
                        $categories_str .= " イベント";
                    }
                } else {
                    $categories_str = "イベント";
                }
            }

            if ($category_tmp_ary[$i] == "花火" || $category_tmp_ary[$i] == "祭り" ||
                $category_tmp_ary[$i] == "花火・祭り" || $category_tmp_ary[$i] == "祭り・花火"
            )
            {
                if (!empty($categories_str))
                {
                    $tmp = "__".$categories_str;
                    if (strpos($tmp,"イベント") > 0)
                    {
                        $categories_str .= " 花火・祭り";
                    } else {
                        $categories_str .= " イベント 花火・祭り";
                    }
                } else {
                    $categories_str = "イベント 花火・祭り";
                }
            }

            if ($category_tmp_ary[$i] == "クリスマス")
            {
                if (!empty($categories_str))
                {
                    $tmp = "__".$categories_str;
                    if (strpos($tmp,"イベント") > 0)
                    {
                        $categories_str .= " クリスマス";
                    } else {
                        $categories_str .= " イベント クリスマス";
                    }
                } else {
                    $categories_str = "イベント クリスマス";
                }
            }

            if ($category_tmp_ary[$i] == "芸能" || $category_tmp_ary[$i] == "芸能鑑賞")
            {
                if (!empty($categories_str))
                {
                    $tmp = "__".$categories_str;
                    if (strpos($tmp,"イベント") > 0)
                    {
                        $categories_str .= " 芸能鑑賞";
                    } else {
                        $categories_str .= " イベント 芸能鑑賞";
                    }
                } else {
                    $categories_str = "イベント 芸能鑑賞";
                }
            }

            if ($category_tmp_ary[$i] == "世界遺産")
            {
                if (!empty($categories_str))
                {
                    $categories_str .= " ".$category_tmp_ary[$i];
                } else {
                    $categories_str = $category_tmp_ary[$i];
                }
            }

            if ($category_tmp_ary[$i] == "夕景" || $category_tmp_ary[$i] == "夕方（夕景）")
            {
                if (!empty($categories_str))
                {
                    $tmp = "__".$categories_str;
                    if (strpos($tmp,"時間") > 0)
                    {
                        $categories_str .= " 夕方（夕景）";
                    } else {
                        $categories_str .= " 時間 夕方（夕景）";
                    }
                } else {
                    $categories_str = "時間 夕方（夕景）";
                }
            }

            if ($category_tmp_ary[$i] == "夜景" || $category_tmp_ary[$i] == "夜（夜景）")
            {
                if (!empty($categories_str))
                {
                    $tmp = "__".$categories_str;
                    if (strpos($tmp,"時間") > 0)
                    {
                        $categories_str .= " 夜（夜景）";
                    } else {
                        $categories_str .= " 時間 夜（夜景）";
                    }
                } else {
                    $categories_str = "時間 夜（夜景）";
                }
            }

            if ($category_tmp_ary[$i] == "朝")
            {
                if (!empty($categories_str))
                {
                    $tmp = "__".$categories_str;
                    if (strpos($tmp,"時間") > 0)
                    {
                        $categories_str .= " 朝";
                    } else {
                        $categories_str .= " 時間 朝";
                    }
                } else {
                    $categories_str = "時間 朝";
                }
            }

            if ($category_tmp_ary[$i] == "日中")
            {
                if (!empty($categories_str))
                {
                    $tmp = "__".$categories_str;
                    if (strpos($tmp,"時間") > 0)
                    {
                        $categories_str .= " 日中";
                    } else {
                        $categories_str .= " 時間 日中";
                    }
                } else {
                    $categories_str = "時間 日中";
                }
            }

            if ($category_tmp_ary[$i] == "スポーツ")
            {
                if (!empty($categories_str))
                {
                    $categories_str .= " スポーツ";
                } else {
                    $categories_str = "スポーツ";
                }
            }

            if ($category_tmp_ary[$i] == "ウインタースポーツ")
            {
                if (!empty($categories_str))
                {
                    $tmp = "__".$categories_str;
                    if (strpos($tmp,"スポーツ") > 0)
                    {
                        $categories_str .= " ウインタースポーツ";
                    } else {
                        $categories_str .= " スポーツ ウインタースポーツ";
                    }
                } else {
                    $categories_str = "スポーツ ウインタースポーツ";
                }
            }

            if ($category_tmp_ary[$i] == "温泉")
            {
                if (!empty($categories_str))
                {
                    $categories_str .= " 温泉";
                } else {
                    $categories_str = "温泉";
                }
            }

            if ($category_tmp_ary[$i] == "書類等" || $category_tmp_ary[$i] == "印刷物")
            {
                if (!empty($categories_str))
                {
                    $categories_str .= " 印刷物";
                } else {
                    $categories_str = "印刷物";
                }
            }

            if ($category_tmp_ary[$i] == "イラスト")
            {
                if (!empty($categories_str))
                {
                    $categories_str .= " イラスト";
                } else {
                    $categories_str = "イラスト";
                }
            }
        }

        return $categories_str;
    }

    /**
     * 国名を取得する
     *
     * @param unknown_type $p_cp_name
     */
    public static function getCountryPrefectureName($p_cp_name)
    {
        global $db_link;
        $ret_cp_name = $p_cp_name;
        if(!empty($p_cp_name))
        {
            $where = " WHERE ";
            $where .= "country_name_case0 = '".$p_cp_name."'";
            $where .= " OR country_name_case1 = '".$p_cp_name."'";
            $where .= " OR country_name_case2 = '".$p_cp_name."'";
            $where .= " OR country_name_case3 = '".$p_cp_name."'";
            $where .= " OR country_name_case4 = '".$p_cp_name."'";
            $where .= " OR country_name_case5 = '".$p_cp_name."'";
            $where .= " OR country_name_case6 = '".$p_cp_name."'";
            $where .= " OR country_name_case7 = '".$p_cp_name."'";
            $where .= " OR country_name_case8 = '".$p_cp_name."'";
            $where .= " OR country_name_case9 = '".$p_cp_name."'";
            $where .= " OR country_name_case10 = '".$p_cp_name."'";

            $sql = "SELECT country_name_case0 FROM country_case ".$where;
            $stmt = $db_link->prepare($sql);
            // SQLを実行します。
            $result = $stmt->execute();
            // 実行結果をチェックします。
            if ($result == true)
            {
                // 実行結果がOKの場合の処理です。
                $icount = $stmt->rowCount();
                if ($icount > 0)
                {
                    $registration_country_name = $stmt->fetch(PDO::FETCH_ASSOC);
                    $ret_cp_name = $registration_country_name['country_name_case0'];
                }
            }
        }
        return $ret_cp_name;
    }

    /*
     * 関数名：select_user
     * 関数説明：申請者管理番号よりユーザーを検索する
     * パラメタ：
     * p_mno:申請者管理番号
     * p_userid:ユーザーID
     * p_username:ユーザー名前
     * 戻り値：無し
     */
    public static function setUserInfo($db_link,$p_mno,&$p_userid,&$p_username)
    {
        try
        {
            // ユーザー情報をDBより取得します。
            // 取得するためのSQLを作成します。
            $sql = "select login_id,user_name from `user` where compcode = ?";
            $stmt = $db_link->prepare($sql);
            $stmt->bindParam(1, $p_mno);

            // SQLを実行します。
            $result = $stmt->execute();

            // 実行結果をチェックします。
            if ($result == true)
            {
                // 実行結果がOKの場合の処理です。
                $row_count = $stmt->rowCount();
                if ($row_count == 1)
                {
                    // 正常にデータの取得ができたときの処理です。
                    $user = $stmt->fetch(PDO::FETCH_ASSOC);
                    $p_userid = $user['login_id'];
                    $p_username = $user['user_name'];
                }
            }
        }
        catch(Exception $e)
        {
            $p_userid = "";
            $p_username = "";
        }
    }

    public static function getUserInfo($db_link,$p_username)
    {
        $ret_data = array(
            "user_id" => 0,
            "compcode" => "0000",
            "login_id" => "",
            "user_name" => $p_username
        );

        try
        {
            // ユーザー情報をDBより取得します。
            // 取得するためのSQLを作成します。
            $sql = "select user_id,login_id,user_name,compcode from `user` where user_name = ?";
            $stmt = $db_link->prepare($sql);
            $stmt->bindParam(1, $p_username);

            // SQLを実行します。
            $result = $stmt->execute();

            // 実行結果をチェックします。
            if ($result == true)
            {
                // 実行結果がOKの場合の処理です。
                $row_count = $stmt->rowCount();
                if ($row_count == 1)
                {
                    // 正常にデータの取得ができたときの処理です。
                    $user = $stmt->fetch(PDO::FETCH_ASSOC);
                    if(isset($user['user_id']) && !empty($user['user_id'])){
                        $ret_data["user_id"] = $user['user_id'];
                        $ret_data["login_id"] = $user['login_id'];
                    }
                }
            }
        }
        catch(Exception $e)
        {
        }

        return $ret_data;
    }
}