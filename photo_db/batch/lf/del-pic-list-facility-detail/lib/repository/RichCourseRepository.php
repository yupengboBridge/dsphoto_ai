<?php

require_once 'RepositoryBase.php';
require_once dirname(__FILE__) . '/../service/Logger.php';
	require_once dirname(__FILE__) . '/../service/SearchSql.php';



abstract class RichCourseRepository extends RepositoryBase {

	private $searchSql;

	protected $table = '';

	public function __construct($expirationDays = null, $rpp = null) {
		$this->searchSql = new SearchSql($this->table, $expirationDays, $rpp);
	}

	public function countExpirations($hei,$country = null) {
		$sql = $this->searchSql->createCountSql($country);
		$parameters = $this->searchSql->createParameters($hei, $country);
		$count = ORM::for_table($this->table)->raw_query($sql->build(), $parameters)->find_one();
		return $count->count;
	}

	public function searchExpirations($hei, $page = 1, $country = null) {
		$sql = $this->searchSql->createSearchSql($page, $country);
		$parameters = $this->searchSql->createParameters($hei, $country);
		$expirations = ORM::for_table($this->table)->raw_query($sql->build(), $parameters)->find_many();
		return $expirations;
	}

    /**
     * 方面プルダウン用にTBから取得
     * @return mixed
     */
    public function destSelExpirations($hei, $courseId = null, $page = 1, $dest = null, $mainbrand = null) {
        $sql = $this->searchSql->createDestSelSql($courseId, $page, $dest, $mainbrand);
        $parameters = $this->searchSql->createParameters($hei, $courseId, $dest, $country = null, $mainbrand);
        $expirations = ORM::for_table($this->table)->raw_query($sql->build(), $parameters)->find_many();
        //print_r(ORM::for_table($this->table)->raw_query($sql->build(), $parameters));
        return $expirations;
    }

	public function countrySelExpirations($hei, $courseId = null, $page = 1, $dest = nul, $mainbrand = null) {
		$sql = $this->searchSql->createCountrySelSql($courseId, $page, $dest, $mainbrand);
		$parameters = $this->searchSql->createParameters($hei, $courseId, $dest, $country  = null, $mainbrand);
		$expirations = ORM::for_table($this->table)->raw_query($sql->build(), $parameters)->find_many();
		//print_r(ORM::for_table($this->table)->raw_query($sql->build(), $parameters));
		return $expirations;
	}

	public function searchHeiCourseIds() {
		$sql = $this->searchSql->createHeiCourseIdsSql();
		return ORM::for_table($this->table)->raw_query($sql->build())->find_many();
	}

    public function searchDeletionDate($img, $type) {
        $sql = $this->searchSql->searchDeletionDate($img, $type);
        //return var_dump(ORM::for_table($this->table)->raw_query($sql->build()));
        return ORM::for_table($this->table)->raw_query($sql->build())->find_one();
    }

    /**
     * 差分CSVファイル用にTBから取得
     * @return mixed
     */
    public function getCsvDataCourseList() {
        $sql = $this->searchSql->getCsvDataCourseListSql();
        $parameters = $this->searchSql->createParameterYesterday();
        return ORM::for_table($this->table)->raw_query($sql->build(), $parameters)->find_many();
    }

    public function save($richCourses) {
//		foreach ($richCourses as $richCourse) {
			if (empty($richCourses)) {
				continue;
			}
			try {
//				Logger::debug('  Creating: ' . json_encode($richCourse));
				$entity = ORM::for_table($this->table)->create();
				$entity->set(array(
					'p_img_title' => $richCourses['写真タイトル'],
					'p_img_filepath' => $richCourses['写真URL'],
					'p_img_caption' => $richCourses['写真ALT'],
					'updated_at' => $richCourses['updated_at']
				));
				$entity->save();
				Logger::debug('    Success');
			} catch (Exception $e) {
            	Logger::error('    Failure: ' . $e->getMessage());
        	}
//		}
	}

	/**
     * テーブルrich_course_i_option,rich_course_d_optionに登録
     * @return mixed
     */
	public function saveOption($richCourses) {
		foreach ($richCourses as $richCourse) {
			if (empty($richCourse)) {
				continue;
			}
			try {
				Logger::debug('  Creating: ' . json_encode($richCourse));
				$entity = ORM::for_table($this->table)->create();
				$entity->set(array(
					'course_id' => $richCourse['course_id'],
					'p_dest_code' => $richCourse['p_dest_code'],
					'p_dest_name' => $richCourse['p_dest_name'],
					'p_country_code' => $richCourse['p_country_code'],
					'p_country_name' => $richCourse['p_country_name'],
					'p_mainbrand' => $richCourse['p_mainbrand'],
					'updated_at' => $richCourse['updated_at']
				));
				$entity->save();
				 //Logger::debug('    Success');
			} catch (Exception $e) {
			// Logger::error('    Failure: ' . $e->getMessage());
		}
		}
	}

    /**
     * 画像ファイルを更新
     * @return mixed
     */
    public function update($hei, $courseId, $fstNm, $sndNm, $exNm, $img, $richCourseCategoryId) {
        try {
            $where_array = array($courseId, $fstNm, $sndNm, $exNm, $hei, $richCourseCategoryId);
            $where_query = 'course_id like ? AND fst_nm = ? AND snd_nm = ? AND ex_nm = ? AND hei = ? AND rich_course_category_id = ?';
            $entity = ORM::for_table($this->table)->where_raw($where_query, $where_array)->find_one();
            if (!$entity) $entity = ORM::for_table($this->table)->create();
            $entity->set(array('ident_day' => date('Y-m-d'), 'photo_no' => $img));
            $entity->save();

        } catch (Exception $e) {
           Logger::error('    Failure: ' . $e->getMessage());
           return $e->getCode();
        }
    }

	public function delete($richCourses) {
		$heiCourseIds = array();
		foreach ($richCourses as $richCourse) {
			$heiCourseId = $richCourse->hei . '_' . $richCourse->courseId;
			if ($richCourse->hei && $richCourse->courseId && array_search($heiCourseId, $heiCourseIds) === false) {
				$heiCourseIds[] = $heiCourseId;
			}
		}
		$this->deleteHeiCourseIds($heiCourseIds);
	}

    /**
     * 該当テーブルを空にする
     * @return mixed
     */
    public function deleteAll() {
        //該当テーブルを空にする
        $records = ORM::for_table($this->table)->delete_many();
    }

	public function dispose($noHits) {
		$this->deleteHeiCourseIds($noHits);
	}

	private function deleteHeiCourseIds($heiCourseIds) {
		foreach ($heiCourseIds as $heiCourseId) {
			list($hei, $courseId) = explode('_', $heiCourseId);
			try {
				Logger::debug('    Deleting: hei:"' . $hei . '"' . ', courseId:"'. $courseId .'"');
				ORM::for_table($this->table)
					->where_equal('hei', $hei)
					->where_equal('course_id', $courseId)
					->delete_many()
				;
				Logger::debug('    Success');
			} catch (Exception $e) {
				Logger::error('    Failure: ' . $e->getMessage());
			}
		}
	}
}
