<?php

require_once 'SqlBuilder.php';

require_once dirname(__FILE__).'/../../ChromePhp.php';

class SearchSql {

	private $table;
	private $table_accom;
	private $country_column;
	private $expirationDays;
	private $rpp;

	public function __construct($table, $expirationDays, $rpp) {
		$this->table = $table;
		// 方面と国テーブルを決める
		$this->table_accom = $this->table == 'wb_csv_hotel_ab_04' ? 'wb_csv_hotel_ab_01' : 'M_ACCOMMODATION' ;
		$this->country_column = $this->table == 'wb_csv_hotel_ab_04' ? 'COUNTRY_CODE' : 'PREFECTURE_CODE' ;
		$this->expirationDays = $expirationDays;
		$this->rpp = $rpp;
	}

	public function createCountSql($country = null) {
        return SqlBuilder::create()
            ->select(array(
                'count(t1.ACCOMMODATION_CODE) as count'
            ))
            ->from(SqlBuilder::create()
                ->select(array('r.ACCOMMODATION_CODE'))
                ->from($this->table . ' as r')
								->leftJoin($this->table_accom . ' as accmo', 'r.ACCOMMODATION_CODE=accmo.ACCOMMODATION_CODE')
                ->leftJoin('PIC_DELETE_INFO as ds', 'r.IMAGE_FILE_NAME=ds.DS_PHOTO_NO')
                ->leftJoin('PIC_DELETE_INFO_CMS as cms', 'r.IMAGE_FILE_NAME=cms.DS_PHOTO_NO')
                ->where('((((ds.DELETION_DATE BETWEEN :today AND :expirationDate) OR (cms.DELETION_DATE BETWEEN :today AND :expirationDate)) OR ((ds.DELETION_DATE IS NULL AND cms.DELETION_DATE IS NULL))))')
								->andWhere($country ? 'accmo.'.$this->country_column.' LIKE :country': null)
                ->groupBy('r.ACCOMMODATION_CODE')
              //  ->orderBy('r.p_img_title ASC')
                ->buildAsSubQuery('t1')
            )
            ;
	}

	public function createSearchSql($page = 1,$country = null) {

		$subQuery = SqlBuilder::create()
            ->select(array('r2.ACCOMMODATION_CODE'))
            ->from($this->table . ' as r2')
						->leftJoin($this->table_accom . ' as accmo', 'r2.ACCOMMODATION_CODE=accmo.ACCOMMODATION_CODE')
            ->leftJoin('PIC_DELETE_INFO as ds2', 'r2.IMAGE_FILE_NAME=ds2.DS_PHOTO_NO')
            ->leftJoin('PIC_DELETE_INFO_CMS as cms2', 'r2.IMAGE_FILE_NAME=cms2.DS_PHOTO_NO')
            ->where('((((ds2.DELETION_DATE BETWEEN :today AND :expirationDate) OR (cms2.DELETION_DATE BETWEEN :today AND :expirationDate)) OR ((ds2.DELETION_DATE IS NULL
        AND cms2.DELETION_DATE IS NULL))))')
						->andWhere($country ? 'accmo.'.$this->country_column.' LIKE :country': null)
            ->groupBy('r2.ACCOMMODATION_CODE')
						//->orderBy('CAST(accmo.ACCOMMODATION_NAME_READ AS CHAR) ASC')
        ;


        $this->pagination($subQuery, $this->rpp, $page);
        return SqlBuilder::create()
            ->select(array(
				'r.*,accmo.*,',
                'CASE WHEN ds.DELETION_DATE IS NOT NULL THEN ds.DELETION_DATE ELSE cms.DELETION_DATE END as deletionDate',
            ))
            ->from($this->table . ' as r')
						->leftJoin($this->table_accom . ' as accmo', 'r.ACCOMMODATION_CODE=accmo.ACCOMMODATION_CODE')
            ->leftJoin('PIC_DELETE_INFO as ds', 'r.IMAGE_FILE_NAME=ds.DS_PHOTO_NO')
            ->leftJoin('PIC_DELETE_INFO_CMS as cms', 'r.IMAGE_FILE_NAME=cms.DS_PHOTO_NO')
            ->where('r.ACCOMMODATION_CODE IN ' . SqlBuilder::create()
                    ->select(array('t1.ACCOMMODATION_CODE'))
                    ->from($subQuery->buildAsSubQuery('t1'))
                    ->buildAsSubQuery()
            )
            ->andWhere('((((ds.DELETION_DATE BETWEEN :today AND :expirationDate) OR (cms.DELETION_DATE BETWEEN :today AND :expirationDate)) OR ((ds.DELETION_DATE IS NULL
        AND cms.DELETION_DATE IS NULL))))')
						->andWhere($country ? 'accmo.'.$this->country_column.' LIKE :country': null)
            //->orderBy('CAST(accmo.ACCOMMODATION_NAME_READ AS CHAR) ASC')
						->groupBy('r.IMAGE_FILE_NAME')
      	;
	}

	public function createSearchSqlAll($country = null) {

        return SqlBuilder::create()
            ->select(array(
				'r.*,accmo.*,',
                'CASE WHEN ds.DELETION_DATE IS NOT NULL THEN ds.DELETION_DATE ELSE cms.DELETION_DATE END as deletionDate',
            ))
            ->from($this->table . ' as r')
						->leftJoin($this->table_accom . ' as accmo', 'r.ACCOMMODATION_CODE=accmo.ACCOMMODATION_CODE')
            ->leftJoin('PIC_DELETE_INFO as ds', 'r.IMAGE_FILE_NAME=ds.DS_PHOTO_NO')
            ->leftJoin('PIC_DELETE_INFO_CMS as cms', 'r.IMAGE_FILE_NAME=cms.DS_PHOTO_NO')
            ->where('((((ds.DELETION_DATE BETWEEN :today AND :expirationDate) OR (cms.DELETION_DATE BETWEEN :today AND :expirationDate)) OR ((ds.DELETION_DATE IS NULL
        AND cms.DELETION_DATE IS NULL))))')
						->andWhere($country ? 'accmo.'.$this->country_column.' LIKE :country': null)
						->groupBy('r.IMAGE_FILE_NAME')
      	;
	}


	public function createCountrySelSql( $page = 1) {
		return SqlBuilder::create()
			->select(array(
				'accmo.'.$this->country_column,
			))
			->from($this->table . ' as r')
			->leftJoin($this->table_accom . ' as accmo', 'r.ACCOMMODATION_CODE=accmo.ACCOMMODATION_CODE')
			->leftJoin('PIC_DELETE_INFO as ds', 'r.IMAGE_FILE_NAME=ds.DS_PHOTO_NO')
			->leftJoin('PIC_DELETE_INFO_CMS as cms', 'r.IMAGE_FILE_NAME=cms.DS_PHOTO_NO')
			->where('((((ds.DELETION_DATE BETWEEN :today AND :expirationDate) OR (cms.DELETION_DATE BETWEEN :today AND :expirationDate)) OR ((ds.DELETION_DATE IS NULL
				AND cms.DELETION_DATE IS NULL))))')
			->groupBy('accmo.'.$this->country_column)
			// ->orderBy('r.p_country_code ASC')
			;

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

	public function createParameters($hei, $country) {
		$parameters = array(
			'today' => $this->createToday(),
			'expirationDate' => $this->createExpirationDate(),
			'hei' => $hei,
		);
		// if ($courseId) {
		// 	$parameters = array_merge($parameters, array(
		// 		'courseId' => '%' . $courseId . '%',
		// 	));
		// }
    //     if ($dest) {
    //         $parameters = array_merge($parameters, array(
    //             'dest' => '%' . $dest . '%',
    //         ));
    //     }
		if ($country) {
			$parameters = array_merge($parameters, array(
				'country' => '%' . $country . '%',
			));
		}
		// if ($mainbrand) {
		// 	$parameters = array_merge($parameters, array(
		// 		'mainbrand' => '%' . $mainbrand . '%',
		// 	));
		// }
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

	private function createToday() {
		return date('Y-m-d');
	}

    private function createYesterday() {
        return date('Y-m-d', strtotime('-1 day'));
    }

	private function createExpirationDate() {
		return date('Y-m-d', time() + 86400 * $this->expirationDays);
	}
}
