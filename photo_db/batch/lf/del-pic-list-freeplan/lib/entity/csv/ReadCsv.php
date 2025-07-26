<?php
require_once dirname(__FILE__) . '/../../service/Logger.php';
/*
*  CSV読み込みクラス
*/
class ReadCsv
{
    public function basicReadCsv($file)
    {
        $file_headers = @get_headers($file);
        if($file_headers[0] == 'HTTP/1.0 404 Not Found' || $file_headers[0] == 'HTTP/1.1 404 Not Found'){
            return false;
        }
        $CsvAry = array();
        // CSVファイルをオープン
        $handle = fopen($file, "r");
        $csvdata = array();
        if ($handle) {
            $num = 0;

            // すべての行を読み込む
            while (!feof($handle)) {

                $buffer = rtrim(fgets($handle, 9999));
                $buffer = str_replace('"', '', $buffer);

                if (empty($buffer)) {
                    continue;
                }
                // 2行目は日本語での説明行なので省く
                // if ($num == 1) {
                //     ++$num;
                //     continue;
                // }
                // 1行取り出す
                $data = explode("\t", $buffer);

                // 1行目の時
                if ($num == 0) {
                    $keyAry = array();
                    // keyに使用する
                    foreach ($data as $no => $val) {
                        if (empty($val)) {
                            continue;
                        }
                        $keyAry[$no] = $val;
                    }

                    ++$num;
                } else {
                    foreach ($keyAry as $no => $key) {
                        if($key == '写真URL'){
                          $imgPath = '';
                          if(!empty($data[$no])){
                            if(preg_match('/p_photo_mno=(.*)/', $data[$no], $match)){
                              $imgPath = $match[1];
                            }else{
                              $imgPath = $data[$no];
                            }
                          }
                          $csvdata[$key] = $imgPath;
                        }else{
                          $csvdata[$key] = isset($data[$no]) ? $data[$no] : '';
                        }
                    }

                    if($csvdata['表示非表示フラグ'] != '1') continue;

                    // 配列にCSVの項目を入れていく
                    $CsvAry[] = $csvdata;
                }
            }
            fclose($handle);
        }


        return $CsvAry;
    }

}
