<?php

require_once dirname(__FILE__).'/../config/RichCourseConfig.php';
require_once dirname(__FILE__).'/../repository/RichCourseIRepository.php';
require_once dirname(__FILE__).'/../repository/RichCourseDRepository.php';
require_once dirname(__FILE__).'/../service/LoggerDiff.php';
require_once dirname(__FILE__).'/../entity/Course.php';
require_once dirname(__FILE__).'/../entity/RichCourse.php';

/**
 * DifferenceService
 * 差分CSVファイルを作成するサービスクラス
 *
 */
class DifferenceService {

    private $hei;
    private $destination_code;
    private $course_name;
    private $course_staff_code;
    private $course_office_code;
    private $fst_nm;
    private $snd_nm;
    private $ex_nm;
	private $repository;
	private $categorys;
    private $courseCnt = 0;
    private $data_i;
    private $data_d;
    private $list_array;
    // 海外(p_domestic)
    private $i = 1;
    // 国内(p_domestic)
    private $d = 0;
    private $key;
    private $csvfilepath;

    /**
     * DifferenceService constructor.
     */
	public function __construct() {
        $this->config = new RichCourseConfig();
        // 生成ファイルパスの雛形取得
        $this->csvfilepath = $this->config->difcsvFilePath;
        // 過去分CSVファイル削除
        LoggerDiff::info('Start deleting old CSV file');
        $this->deleteOldCsvFile();
        LoggerDiff::info('Finish deletion of old CSV file');
        // 差分情報をDBから取得（海外）
        $this->repository = new RichCourseIRepository();
        LoggerDiff::info('Start search the rich_course_i table...');
        $this->data_i = $this->repository->getCsvDataCourseList();
        LoggerDiff::info('End search the rich_course_i table...');
        // 差分情報をDBから取得（国内）
        $this->repository = new RichCourseDRepository();
        LoggerDiff::info('Start search the rich_course_d table...');
        $this->data_d = $this->repository->getCsvDataCourseList();
        LoggerDiff::info('End search the rich_course_d table...');
	}

    /**
     * 差分コース情報CSVを作成
     * @return mixed
     */
    public function create()
    {
        LoggerDiff::info('Entering Csv data creating process...');
        // 差分コース情報を配列化（海外）
        $data_i_res = $this->createCSVList($this->data_i, $this->i);
        // 差分コース情報を配列化（国内）
        $data_d_res = $this->createCSVList($this->data_d, $this->d);
        // 海外・国内配列をマージ
        $data_res = array_merge($data_i_res, $data_d_res);

        if(!empty($data_res)){
            // ヘッダー行を追加
            $header_array = $this->searchCSVList();
            array_unshift($data_res, $header_array);
            // 配列からCSVファイルを作成
            $this->createCSVFile($data_res);
            LoggerDiff::info('Success');
        } else {
            LoggerDiff::info('DifferenceCourseCount: 0');
            LoggerDiff::info('End Csv data creating process...');
        }
    }

    /**
     * 差分コース情報を配列化
     * @return mixed
     */
	public function createCSVList($data, $i_or_d) {
        $contents = array();
        foreach ($data as $row) {
            // hei + course_idで一意の配列のキーを作成
            $this->key = implode('_', array($row->hei, $row->course_id));
            // 基本情報系フィールド取得処理
            $contents[$this->key]['p_course_id'] = $row->course_id;
            $contents[$this->key]['p_domestic'] = $i_or_d;
            $contents[$this->key]['p_area_code'] = $row->hei;
            $contents[$this->key]['p_destination_code'] = $row->destination_code;
            $contents[$this->key]['p_course_name'] = $row->course_name;
            $contents[$this->key]['p_course_staff_code'] = $row->course_staff_code;
            $contents[$this->key]['p_course_office_code'] = $row->course_office_code;
            // 画像ファイル系フィールド取得処理
            $contents[$this->key][$this->getFilename($row)]
                .= $this->sortFilename($row, $this->searchCSVList());
        }
        // CSVのフィールド一覧を取得
        $this->list_array = array_map(array($this, 'deleteVal'), $this->searchCSVList());
        $result = array_map(array($this, 'mergeList'), $contents);
        // 末尾の文字列削除
        $result = $this->trimFileName($result);
        return $result;
	}

    /**
     * 差分コース情報CSVファイルを作成
     * @return mixed
     */
    public function createCSVFile($data)
    {
        // ファイル名取得
        $filepath = $this->config->difcsvPath . $this->createTodayCSVFileName();
        // ファイル開く
        if (!($fp = @fopen($filepath,"w"))) {
            // ファイルが開けない場合はバッチ終了
            LoggerDiff::error('Sorry. Failed to create the file. Please start up the batch again.');
            exit;
        }
        LoggerDiff::info('file open: ' . $filepath);
        // ヘッダー判定用カウント
        $hc = 0;
        // ファイル書き込み
        foreach ($data as $fields) {

            $hc++;
            // ヘッダー以外のコース情報をログに表示 $hc === 1 はヘッダー
            if($hc !== 1) {
                // デバッグ用カウント
                $this->courseCnt++;
                LoggerDiff::info('  Csv data writing hei: ' . $fields['p_area_code'] . ' courseId: ' . $fields['p_course_id']);
            }
            //fputs($fp, implode(',', $fields) . PHP_EOL);
            //fputcsv($fp, $fields);
            $this->mb_fputcsv($fp, $fields);
        }
        // ファイル閉じる
        fclose($fp);
        LoggerDiff::info('DifferenceCourseCount: ' . $this->courseCnt);
    }

    /**
     * フィールド名取得
     * @return mixed
     */
    private function sortFilename($data, $list) {
        $this->ex_nm = $data->ex_nm;
        array_splice($list, 0, $this->config->csvMainField);
        foreach ($list as $val) {
            if(strcmp($val, $this->getFilename($data)) === 0){
                return $this->createExtFileName($data->photo_no . "、");
            }
        }
    }

    /**
     * フィールド名作成
     * @return mixed
     */
    private function getFilename($data) {
        // カテゴリごとのファイル名取得
        $this->categorys = $this->searchCategoryFile();
        // 置き換える数字を配列で返す
        $replacer = $this->createDefaultReplacer($data);
        // フィールド名作成
        return str_replace(
            array_keys($replacer),
            array_values($replacer),
            $this->categorys[$data->rich_course_category_id]
        );
    }

    /**
     * CSVのフィールド一覧を配列で返す
     * @return mixed
     */
    public function searchCSVList() {
        return json_decode(json_encode($this->config->csvList) , true);
    }

    /**
     * カテゴリごとのファイル名を配列で返す
     * @return mixed
     */
    public function searchCategoryFile() {
        return json_decode(json_encode($this->config->categoryFileNameList) , true);
    }

    /**
     * 置き換える数字を配列で返す
     * @return mixed
     */
    private function createDefaultReplacer($data) {
        return array(
            '{fst_nm}' => empty($data->fst_nm) ? '' : $data->fst_nm,
            '{snd_nm}' => empty($data->snd_nm) ? '' : $data->snd_nm,
        );
    }

    /**
     * フィールド配列をマージする
     * @return mixed
     */
    private function mergeList($array) {
        $merge_array = array();
        foreach ($array as $val) {
            $merge_array = array_merge($this->list_array, $array);
        }
        return $merge_array;
    }

    /**
     * フィールド配列を空にする
     * @return mixed
     */
    private function deleteVal() {
        return "";
    }

    /**
     * 画像ファイル名末尾の「、」を削除
     * @return mixed
     */
    private function trimFileName($array) {
        $ary = array();
        // 末尾の文字列削除
        foreach ($array as $key => $val) {
            foreach ($val as $k => $v) {
                $ary[$key][$k] = rtrim($v, '、');
            }
        }
        return $ary;
    }

    /**
     * 数字（旅行日、段数）付きファイル名取得
     * @return mixed
     */
    private function createExtFileName($name) {
        if($this->ex_nm != 0){
            $name = $this->ex_nm . "_" . $name;
        }
        return $name;
    }

    /**
     * 前日日付CSVファイル名取得
     * @return mixed
     */
    private function createTodayCSVFileName() {
        //return date('Ymd') . '.csv';
        return date('Ymd', strtotime('-1 day')) . '.csv';
    }

    /**
     * 過去分のCSVファイルを削除
     * @return mixed
     */
    public function deleteOldCsvFile() {
        $keepDays = $this->config->csvKeepDays;
        $alives = array();
        for ($i = 0; $i < $keepDays; $i++) {
            $alives[] = $this->createCSVFilename($i);
        }
        $files = array();
        $dir = scandir(dirname($this->createCSVFilename()));
        foreach ($dir as &$file) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            $files[] = dirname($this->createCSVFilename()).'/'.$file;
        }
        foreach ($files as $file) {
            if (!in_array($file, $alives)) {
                unlink($file);
            }
        }
    }

    /**
     * 過去分のCSVファイル名取得
     * @return mixed
     */
    private function createCSVFilename($days = 0) {
        $time = time() - 86400 * $days;
        $replacer = array(
            '{phpdir}' => dirname(__FILE__),
            '{Y}' => date('Y', $time),
            '{m}' => date('m', $time),
            '{d}' => date('d', $time),
        );
        return str_replace(array_keys($replacer), array_values($replacer), $this->csvfilepath);
    }

    /**
     * フィールドをダブルクォートで囲む
     * @return mixed
     */
    private function mb_fputcsv($fp=null, $fields=null, $delimiter=',', $enclosure='"', $rfc=false) {
        $str=null;
        $chk=true;

        if($chk) {
            $cnt=0;
            $last=count($fields);
            foreach($fields as $val) {
                $cnt++;
                if(!$rfc) {  // fputcsv()の挙動
                    $val = preg_replace('/(?<!\\\\\\\\)\"/u', '""', $val);
                }else {      // RFC4180に準拠
                    $val = preg_replace('/\"/u', '""', $val);
                }
                $str.= '"'. $val. '"';
                if($cnt!=$last) $str.= ',';
            }
        }
        if($chk) $chk = fwrite($fp, $str);
        if($chk) $chk = fwrite($fp, "\r\n");

        return $chk;
    }

}
