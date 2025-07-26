<?php

require_once dirname(__FILE__).'/../config/RichCourseConfig.php';
require_once dirname(__FILE__).'/../repository/RichCourseIRepository.php';
require_once dirname(__FILE__).'/../repository/RichCourseDRepository.php';
require_once dirname(__FILE__).'/../repository/FreeplanImgIRepository.php';
require_once dirname(__FILE__).'/../repository/FreeplanImgDRepository.php';
require_once dirname(__FILE__).'/../service/Logger.php';
require_once dirname(__FILE__).'/../entity/csv/UtilCsv.php';
require_once dirname(__FILE__).'/../entity/csv/CountryUrlCsv.php';
require_once dirname(__FILE__).'/../entity/Course.php';
require_once dirname(__FILE__).'/../entity/RichCourse.php';

error_reporting(E_ALL & ~E_NOTICE);


/**
 * RegisterService
 * 専門店のタイトル画像をTBに登録するサービスクラス
 *
 */
class RegisterService {

	private $courses;
    private $courseId;
    private $hei;
    private $destination_code;
    private $course_name;
    private $course_staff_code;
    private $course_office_code;
    private $fst_nm;
    private $snd_nm;
    private $ex_nm;
    private $ident_day;
    private $domestic;
	private $repository;
	private $categorys;
    private $category;
    private $courseCnt = 0;
	private $country_url_array;
	private $mainbrand;
	private $csv_list;

    /**
     * RegisterService constructor.
     */
	public function __construct($list) {
    $this->config = new RichCourseConfig();
		$this->csv_list = $list;
		// TB初期化
    $repository = new FreeplanImgIRepository();
    Logger::info('initializing the freeplan_img_i table...');
    $repository->deleteAll();
    $repository = new FreeplanImgDRepository();
    Logger::info('Start initializing the freeplan_img_d table...');
    $repository->deleteAll();

	}

    /**
     * コース情報をDB登録
     * @return mixed
     */
	public function register() {
		Logger::info('Entering Csv data loading process...');
    $csv = new UtilCsv();
	
    // リストごと
		foreach ($this->csv_list as $naigai => $naigai_array) {
			$this->repository = $naigai == 'i' ? new FreeplanImgIRepository() : new FreeplanImgDRepository();
			foreach ($naigai_array as $img_array) {
				$img_array['updated_at'] =  date("Y-m-d H:i:s");

        // CSVファイルに混在する空行対策
        if(!empty($img_array['写真URL'])){
            // Logger::info('  Csv data loaded p_country_name: '. $img_array['p_country_name']);
            // デバッグ用カウント
            $this->courseCnt++;
            // TB登録
            Logger::info('  Updating database...');
            $this->updateDatabase($img_array);
        }
			}
		}

		Logger::info('Success');
        Logger::info('CourseCount: ' . $this->courseCnt);
	}

	/**
	 * courseId、hei、内外を取得し、不要な列を削除
	 * @return mixed
	 */
	private function replace($replacer) {
		// コースID
		$this->courseId = $replacer["p_course_id"][0];
		// hei
		$this->hei = $replacer["p_area_code"][0];
		// 内外
		$this->domestic = $replacer["p_domestic"][0];
		// 方面
		$this->destination_code = $replacer["p_destination_code"][0];
		// 以下からの値も差分CSVファイル作成のため、下記情報もTBに登録
		// コース名
		$this->course_name = $replacer["p_course_name"][0];
		// スタッフコード
		$this->course_staff_code = $replacer["p_course_staff_code"][0];
		// オフィスコード
		$this->course_office_code = $replacer["p_course_office_code"][0];
		// 不要な列を削除
		if($this->domestic == 1) {
			// 海外
			array_splice($replacer, 0, $this->config->csvMainField);
		} else {
			// 国内
			array_splice($replacer, 0, $this->config->csvMainField - 1);
		}
		// p_country_name,p_city_name,p_brand_nameを削除
		array_splice($replacer, -3);
		return $replacer;
	}

    /**
     * ファイル名の頭の「〇〇_（旅行日、段数）」削除
     * @return mixed
     */
    private function deleteHeadNum($replacer) {
        // 旅行日、段数の取得
        $this->ex_nm = preg_match('/^([0-9]+)_/', $replacer, $res) ? $res[1] : 0;
        // 旅行日、段数の削除
        return preg_replace('/^[0-9]*_/', '', $replacer);
    }

    /**
     * ファイル名（カラム）の数字取得
     * @return mixed
     */
    private function getFileNum($replacer) {
        $nm_ary = array();
        // 数字を取り出す
        $nm_ary = preg_match_all('/([0-9]+)_/', $replacer, $res) ? $res[1] : '';
        list($this->fst_nm, $this->snd_nm) = $nm_ary;
    }

    /**
     * 動画ファイル判定
     * @return mixed
     */
    private function checkMovie($filename) {
        $result = false;
        // 数値のみの場合は動画ファイル
        if((boolean)preg_match("/^[0-9]+$/",$filename)){
            $result = true;
        }
        return $result;
    }

    /**
     * 画像ファイルフォーマット修正
     * 「http://」などおかしなファイル名が配列に入らないように制御
     * @return mixed
     */
    private function scrapeImage($path) {
        preg_match('/[0-9a-zA-Z\\-_]*\\.(jpg|gif|png)$/', $path, $matches);
        return empty($matches[0]) ? '' : $matches[0];
    }

    /**
     * DB登録用にデータ整形
     * @return mixed
     */
    protected function createRichCourse($imgKey, $key) {
        // ファイル名（カラム）の数字取得
        $this->getFileNum($key);
        // 画像ファイルフォーマット修正
        $img = $this->scrapeImage(empty($imgKey) ? '' : trim($imgKey));
        // 画像のキャプションは廃止
        $text = "";
        // updatedAtはTB更新時を入れる
        $updatedAt = date("Y-m-d H:i:s");
        // 識別用更新日(ident_day)には初期では何も入れない
        $this->ident_day = "";

        return new RichCourse(
            $this->hei,
            $this->courseId,
            $this->destination_code,
            $this->course_name,
            (String)$this->course_staff_code,
            $this->course_office_code,
            $this->category,
            $this->convertNull($this->fst_nm),
            $this->convertNull($this->snd_nm),
            $this->convertNull($this->ex_nm),
            $img,
            $text,
            $this->ident_day,
            $updatedAt
        );
    }

	/**
	 * DB登録用にデータ整形
	 * @return mixed
	 */
	protected function createRichCourseOption($country_name) {
		$table_array = array();
		$naigai = $this->domestic === '1' ? 'i' : 'd';
		$table_array = $this->dest_country_array[$naigai][$country_name];
		$table_array['course_id'] = $this->courseId;
		$table_array['p_mainbrand'] = $this->mainbrand;
		// updatedAtはTB更新時を入れる
		$table_array['updated_at'] =  date("Y-m-d H:i:s");

		return $table_array;
	}


    /**
     * カテゴリ別振り分け
     * @return mixed
     */
    private function patternCategory($pattern, $keys) {
        // カテゴリのキーワードに合致するか判定
        return (boolean)preg_match("/" . $pattern . "/i", $keys);
    }

    /**
     * カテゴリを配列で返す
     * @return mixed
     */
    public function searchCategory() {
        return json_decode(json_encode($this->config->categoryList) , true);
    }

    /**
     * nullの場合0に変換
     * @return mixed
     */
    public function convertNull($num) {
        return is_null($num) ? 0 : $num;
    }

    /**
     * DB登録
     * @return mixed
     */
	private function updateDatabase($richCourses) {
		$this->repository->save($richCourses);
	}

	/**
	 * DB登録　rich_course_i_option,rich_course_d_option
	 * @return mixed
	 */
	private function updateDatabaseOptionTable($richCourses) {
		$this->repository->saveOption($richCourses);
	}
}
