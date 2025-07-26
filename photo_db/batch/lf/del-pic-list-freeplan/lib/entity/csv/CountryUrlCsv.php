<?php

error_reporting(E_ALL & ~E_NOTICE);

/*
*  CSV読み込みクラス
*/
class CountryUrlCsv
{
    public function readCsv($file)
    {
        $CsvAry = array();
        if (file_exists($file)) {
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
                    // // 2行目は日本語での説明行なので省く
                    // if ($num == 1) {
                    //     ++$num;
                    //     continue;
                    // }
                    // 1行取り出す
                    $data = explode(",", $buffer);

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
                            $csvdata[$key] = isset($data[$no]) ? $data[$no] : '';
                        }

                        $CsvAry[$csvdata['p_country_name_en']] = $csvdata;

                    }
                }
                fclose($handle);
            }
    	}


        return $CsvAry;
    }

    public function readCountryUrl(){

        $array = array();
//        $array['i'] = $this->readCsv(dirname(__FILE__).'/file/i_dest_country.csv');
        $array['d'] = $this->readCsv(dirname(__FILE__).'/file/d_country_url.csv');


        return $array;
    }

}
