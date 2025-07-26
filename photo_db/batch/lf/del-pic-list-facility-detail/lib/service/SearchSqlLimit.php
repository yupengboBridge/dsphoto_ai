<?php

require_once 'SqlBuilder.php';

// require_once dirname(__FILE__).'/../../ChromePhp.php';

class SearchSql {

	private $table;
	private $table_option;
	private $expirationDays;
	private $rpp;

	public function __construct($table, $expirationDays, $rpp) {
		$this->table = $table;
		// 方面と国テーブルを決める
		$this->table_option = $this->table == 'rich_course_i' ? 'rich_course_i_option' : 'rich_course_d_option' ;
		$this->expirationDays = $expirationDays;
		$this->rpp = $rpp;
	}

	public function createCountSql($courseId = null, $dest = null, $country = null, $mainbrand = null) {
		if($dest == null && $mainbrand == null){
	        return SqlBuilder::create()
	            ->select(array(
	                'count(t1.course_id) as count'
	            ))
	            ->from(SqlBuilder::create()
	                ->select(array('r.course_id'))
//	                 ->from($this->table . ' as r')
//	                 ->leftJoin('PIC_DELETE_INFO_LIMIT as ds', 'r.photo_no=ds.DS_PHOTO_NO')
					->from('PIC_DELETE_INFO_LIMIT as ds')
	                ->leftJoin($this->table . ' as r', 'r.photo_no=ds.DS_PHOTO_NO')
					->where('(((ds.DELETION_DATE BETWEEN :today AND :expirationDate) OR (ds.DELETION_DATE IS NULL)) OR r.ident_day = :today)')
	                ->andWhere('r.hei = :hei')
	                ->andWhere($courseId ? 'r.course_id LIKE :courseId': null)
	                ->groupBy('r.course_id')
	                ->orderBy('r.course_id ASC')
	                ->buildAsSubQuery('t1')
	            )
	            ;
		}else{
			// 方面の有無
			$where_string = $dest == null ? 'where' : 'andWhere' ;
			$subQuery2 = SqlBuilder::create()
				->select(array('course_id'))
				->from($this->table_option . ' as r3')
	            ->where($dest ? 'r3.p_dest_code LIKE :dest': null)
				->andWhere($country ? 'r3.p_country_code LIKE :country': null)
				->$where_string($mainbrand ? 'r3.p_mainbrand  LIKE :mainbrand': null)
				->groupBy('r3.course_id')
			;
	        return SqlBuilder::create()
	            ->select(array(
	                'count(t1.course_id) as count'
	            ))
	            ->from(SqlBuilder::create()
	                ->select(array('r.course_id'))
//	                ->from($this->table . ' as r')
//	                ->leftJoin('PIC_DELETE_INFO_LIMIT as ds', 'r.photo_no=ds.DS_PHOTO_NO')
					->from('PIC_DELETE_INFO_LIMIT as ds')
	                ->leftJoin($this->table . ' as r', 'r.photo_no=ds.DS_PHOTO_NO')
	                ->where('(((ds.DELETION_DATE BETWEEN :today AND :expirationDate) OR (ds.DELETION_DATE IS NULL)) OR r.ident_day = :today)')
	                ->andWhere('r.hei = :hei')
	                ->andWhere($courseId ? 'r.course_id LIKE :courseId': null)
					->andWhere('r.course_id IN ' . SqlBuilder::create()
							->select(array('t2.course_id'))
							->from($subQuery2->buildAsSubQuery('t2'))
							->buildAsSubQuery()
					)
	                ->groupBy('r.course_id')
	                ->orderBy('r.course_id ASC')
	                ->buildAsSubQuery('t1')
	            )
	            ;
		}
	}

	public function createSearchSql($courseId = null, $page = 1, $dest = null, $country = null, $mainbrand = null) {
		if($dest == null && $mainbrand == null){

			$subQuery = SqlBuilder::create()
	            ->select(array('r2.course_id'))
	            // ->from($this->table . ' as r2')
	            // ->leftJoin('PIC_DELETE_INFO_LIMIT as ds2', 'r2.photo_no=ds2.DS_PHOTO_NO')
				->from('PIC_DELETE_INFO_LIMIT as ds2')
	            ->leftJoin($this->table . ' as r2', 'r2.photo_no=ds2.DS_PHOTO_NO')
	            ->where('(((ds2.DELETION_DATE BETWEEN :today AND :expirationDate) OR (ds2.DELETION_DATE IS NOT NULL)) OR r2.ident_day = :today)')
				->andWhere('r2.hei = :hei')
	            ->andWhere($courseId ? 'r2.course_id LIKE :courseId': null)
	            ->groupBy('r2.course_id')
	            ->orderBy('r2.course_id ASC, r2.rich_course_category_id ASC, r2.fst_nm ASC, r2.snd_nm ASC, r2.ex_nm ASC')
	        ;
		}else{
			$subQuery = SqlBuilder::create()
	            ->select(array('r2.course_id'))
	            // ->from($this->table . ' as r2')
	            // ->leftJoin('PIC_DELETE_INFO_LIMIT as ds2', 'r2.photo_no=ds2.DS_PHOTO_NO')
				->from('PIC_DELETE_INFO_LIMIT as ds2')
	            ->leftJoin($this->table . ' as r2', 'r2.photo_no=ds2.DS_PHOTO_NO')
				->leftJoin($this->table_option . ' as r3', 'r2.course_id=r3.course_id')
	            ->where('(((ds2.DELETION_DATE BETWEEN :today AND :expirationDate) OR (ds2.DELETION_DATE IS NULL)) OR r2.ident_day = :today)')
	            ->andWhere('r2.hei = :hei')
	            ->andWhere($courseId ? 'r2.course_id LIKE :courseId': null)
				->andWhere($dest ? 'r3.p_dest_code LIKE :dest': null)
				->andWhere($country ? 'r3.p_country_code LIKE :country': null)
				->andWhere($mainbrand ? 'r3.p_mainbrand  LIKE :mainbrand': null)
	            ->groupBy('r2.course_id')
	            ->orderBy('r2.course_id ASC, r2.rich_course_category_id ASC, r2.fst_nm ASC, r2.snd_nm ASC, r2.ex_nm ASC')
	        ;
		}

        $this->pagination($subQuery, $this->rpp, $page);
        return SqlBuilder::create()
            ->select(array(
                'r.id as id,',
                'r.hei as hei,',
                'r.course_id as courseId,',
                'c.name as richCourseCategory,',
                'r.rich_course_category_id as richCourseCategoryId,',
                'r.photo_no as photoNo,',
                'r.fst_nm as fstNm,',
                'r.snd_nm as sndNm,',
                'r.ex_nm as exNm,',
                'r.ident_day as identDay,',
                'r.overview as overview,',
                'CASE WHEN ds.DELETION_DATE IS NOT NULL THEN ds.DELETION_DATE END as deletionDate',
            ))
            // ->from($this->table . ' as r')
            // ->leftJoin('PIC_DELETE_INFO_LIMIT as ds', 'r.photo_no=ds.DS_PHOTO_NO')
			->from('PIC_DELETE_INFO_LIMIT as ds')
            ->leftJoin($this->table . ' as r', 'r.photo_no=ds.DS_PHOTO_NO')
			->leftJoin('rich_course_category as c', 'r.rich_course_category_id=c.id')
            ->where('r.course_id IN ' . SqlBuilder::create()
                    ->select(array('t1.course_id'))
                    ->from($subQuery->buildAsSubQuery('t1'))
                    ->buildAsSubQuery()
            )
            ->andWhere('(((ds.DELETION_DATE BETWEEN :today AND :expirationDate) OR (ds.DELETION_DATE IS NULL)) OR r.ident_day = :today)')
            ->orderBy('r.course_id ASC, r.rich_course_category_id ASC, r.fst_nm ASC, r.snd_nm ASC, r.ex_nm ASC');
        ;
	}

    public function createDestSelSql($courseId = null, $page = 1, $dest = null, $mainbrand = null) {
		$subQuery = SqlBuilder::create()
			->select(array('r2.course_id'))
			->from('PIC_DELETE_INFO_LIMIT as ds2')
			->leftJoin($this->table . ' as r2', 'r2.photo_no=ds2.DS_PHOTO_NO')
			->where('(((ds2.DELETION_DATE BETWEEN :today AND :expirationDate) OR (ds2.DELETION_DATE IS NULL)) OR r2.ident_day = :today)')
			->andWhere('r2.hei = :hei')
			->groupBy('r2.course_id')
			->orderBy('r2.course_id ASC, r2.rich_course_category_id ASC, r2.fst_nm ASC, r2.snd_nm ASC, r2.ex_nm ASC')
		;
		return SqlBuilder::create()
            ->select(array(
                'ro.*',
            ))
			->from($this->table_option . ' as ro')
			->leftJoin($this->table . ' as r', 'ro.course_id=r.course_id')
            ->leftJoin('PIC_DELETE_INFO_LIMIT as ds', 'r.photo_no=ds.DS_PHOTO_NO')
            ->where('(((ds.DELETION_DATE BETWEEN :today AND :expirationDate) OR (ds.DELETION_DATE IS NULL)) OR r.ident_day = :today)')
            ->andWhere('r.hei = :hei')
            ->andWhere($courseId ? 'r.course_id LIKE :courseId': null)
			->andWhere($mainbrand ? 'ro.p_mainbrand LIKE :mainbrand': null)
			->andWhere('ro.course_id IN ' . SqlBuilder::create()
					->select(array('t1.course_id'))
					->from($subQuery->buildAsSubQuery('t1'))
					->buildAsSubQuery()
			)
			->groupBy('ro.p_dest_code');

    }

	public function createCountrySelSql($courseId = null, $page = 1, $dest = null, $mainbrand = null) {
		$subQuery = SqlBuilder::create()
			->select(array('r2.course_id'))
			->from('PIC_DELETE_INFO_LIMIT as ds2')
			->leftJoin($this->table . ' as r2', 'r2.photo_no=ds2.DS_PHOTO_NO')
			->where('(((ds2.DELETION_DATE BETWEEN :today AND :expirationDate) OR (ds2.DELETION_DATE IS NULL)) OR r2.ident_day = :today)')
			->andWhere('r2.hei = :hei')
			->groupBy('r2.course_id')
			->orderBy('r2.course_id ASC, r2.rich_course_category_id ASC, r2.fst_nm ASC, r2.snd_nm ASC, r2.ex_nm ASC')
		;
		return SqlBuilder::create()
			->select(array(
				'ro.*',
			))
			->from($this->table_option . ' as ro')
			->leftJoin($this->table . ' as r', 'ro.course_id=r.course_id')
			->leftJoin('PIC_DELETE_INFO_LIMIT as ds', 'r.photo_no=ds.DS_PHOTO_NO')
			->where('(((ds.DELETION_DATE BETWEEN :today AND :expirationDate) OR (ds.DELETION_DATE IS NULL)) OR r.ident_day = :today)')
			->andWhere('r.hei = :hei')
			->andWhere($courseId ? 'r.course_id LIKE :courseId': null)
			->andWhere($dest ? 'ro.p_dest_code LIKE :dest': null)
			->andWhere($mainbrand ? 'ro.p_mainbrand LIKE :mainbrand': null)
			->andWhere('ro.course_id IN ' . SqlBuilder::create()
					->select(array('t1.course_id'))
					->from($subQuery->buildAsSubQuery('t1'))
					->buildAsSubQuery()
			)
//           ->groupBy('r.course_id');
			->groupBy('ro.p_country_code');

	}


    public function createHeiCourseIdsSql() {
		return SqlBuilder::create()
			->select(array(
				'r.hei as hei,',
				'r.course_id as courseId',
			))
			->from($this->table . ' as r')
			->groupBy('r.hei, r.course_id')
			->orderBy('r.hei ASC, r.course_id ASC')
		;
	}

    /**
     * 差分CSVファイル用にTBから取得
     * @return mixed
     */
    public function getCsvDataCourseSql() {
        return SqlBuilder::create()
            ->select(array('*'))
            ->from($this->table . ' as r')
            ->where('ident_day like ' . '%' . $this->createToday() . '%')
            ->groupBy('r.hei, r.course_id')
            ->orderBy('r.hei ASC, r.course_id ASC')
            ;
    }

    /**
     * 差分CSVファイル用にTBから取得
     * @return mixed
     */
    public function getCsvDataCourseListSql() {
        $subQuery = SqlBuilder::create()
            ->select(array('course_id'))
            ->from($this->table . ' as r2')
            ->where('r2.ident_day = :yesterday')
            ->orderBy('r2.course_id ASC')
        ;
        return SqlBuilder::create()
            ->select(array('*'))
            ->from($this->table . ' as r1')
            ->where('r1.course_id IN ' . SqlBuilder::create()
                    ->select(array('t1.course_id'))
                    ->from($subQuery->buildAsSubQuery('t1'))
                    ->buildAsSubQuery()
            )
            ->orderBy('r1.course_id ASC')
            ;
    }

    public function searchDeletionDate($img, $type) {
        return SqlBuilder::create()
            ->select(array(
                'DELETION_DATE as deletionDate'
            ))
            ->from('PIC_DELETE_INFO_DS_CMS')
            ->Where('DS_PHOTO_NO like '. "'%$img%'" . '')
            ;
    }

	private function pagination(SqlBuilder $sql, $rpp, $page) {
		$offset = $rpp * ($page - 1);
		if ($offset < 0) {
			$offset = 0;
		}
		$sql->limit($rpp);
		$sql->offset($offset);
		return $sql;
	}

	public function createParameters($hei, $courseId, $dest, $country, $mainbrand) {
		$parameters = array(
			'today' => $this->createToday(),
			'expirationDate' => $this->createExpirationDate(),
			'hei' => $hei,
		);
		if ($courseId) {
			$parameters = array_merge($parameters, array(
				'courseId' => '%' . $courseId . '%',
			));
		}
        if ($dest) {
            $parameters = array_merge($parameters, array(
                'dest' => '%' . $dest . '%',
            ));
        }
		if ($country) {
			$parameters = array_merge($parameters, array(
				'country' => '%' . $country . '%',
			));
		}
		if ($mainbrand) {
			$parameters = array_merge($parameters, array(
				'mainbrand' => '%' . $mainbrand . '%',
			));
		}
		return $parameters;
	}

    public function createParameterToday() {
        $parameters = array(
            'today' => $this->createToday()
        );
        return $parameters;
    }

    public function createParameterYesterday() {
        $parameters = array(
            'yesterday' => $this->createYesterday()
        );
        return $parameters;
    }

	// todayだが期限なしにする
	private function createToday() {
		return date('1970-01-01');
	}

    private function createYesterday() {
        return date('Y-m-d', strtotime('-1 day'));
    }

	private function createExpirationDate() {
//		return date('Y-m-d', time() + 86400 * $this->expirationDays);
		return date('2100-01-01');
	}
}
