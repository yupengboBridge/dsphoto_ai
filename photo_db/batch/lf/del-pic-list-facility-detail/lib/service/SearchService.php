<?php

require_once dirname(__FILE__).'/../config/RichCourseConfig.php';
require_once dirname(__FILE__).'/../repository/RichCourseIRepository.php';
require_once dirname(__FILE__).'/../repository/RichCourseDRepository.php';
require_once dirname(__FILE__).'/../repository/FacilityDetailImgDRepository.php';
require_once dirname(__FILE__).'/../repository/FacilityDetailImgIRepository.php';

require_once dirname(__FILE__).'/../entity/csv/CountryUrlCsv.php';

require_once dirname(__FILE__).'/Logger.php';

require_once dirname(__FILE__).'/../../ChromePhp.php';


class SearchService {

	private $config;
	private $type;
	private $country_url_array;

	public function __construct(RichCourseConfig $config) {
		$this->config = $config;
	}

	public function search($hei, $page = 1, $type = 'i', $country) {
		$data = new stdClass();
		$this->type = $type;
		$repository = $type === 'i' ?
			new FacilityDetailImgIRepository($this->config->expirationDays, $this->config->rpp) :
			new FacilityDetailImgDRepository($this->config->expirationDays, $this->config->rpp);
		//$repository = new RichCourseIRepository($this->config->expirationDays, $this->config->rpp);
		$data->totalCount = $repository->countExpirations($hei, $country);
		$data->contents = $this->createContents($repository->searchExpirations($hei, $page, $country));
		return $data;
	}

    public function del($hei, $courseId, $img, $fstNm, $sndNm, $exNm, $type = 'i', $ctgId) {
        $data = new stdClass();
        $repository = $type === 'i' ?
            new RichCourseIRepository($this->config->expirationDays, $this->config->rpp) :
            new RichCourseDRepository($this->config->expirationDays, $this->config->rpp);
        $repository->update($hei, $courseId, $fstNm, $sndNm, $exNm, $img, $ctgId);
        $data->deletionDate = $this->createDeletionDate($repository->searchDeletionDate($img, $type));
        return $data;
    }

    // public function destsel($hei, $courseId, $page = 1, $type = 'i', $dest, $mainbrand) {
    //     $repository = $type === 'i' ?
    //         new RichCourseIRepository($this->config->expirationDays, $this->config->rpp) :
    //         new RichCourseDRepository($this->config->expirationDays, $this->config->rpp);
    //     $data = $this->createDestSel($repository->destSelExpirations($hei, $courseId, $page, $dest, $mainbrand), $type);
    //     return $data;
    // }

	public function countrysel($hei,$page = 1, $type = 'i') {
		$repository = $type === 'i' ?
			new FacilityDetailImgIRepository($this->config->expirationDays, $this->config->rpp) :
			new FacilityDetailImgDRepository($this->config->expirationDays, $this->config->rpp);
		$data = $this->createCountrySel($repository->countrySelExpirations($hei, $page), $type);
		return $data;
	}

	/**
	 * format
	 *   hei_courseId (eg: '10_UT70501')
	 *     0
	 *       id
	 *       hei
	 *       courseId
	 *       richCourseCategory
	 *       photoNo
	 *       overview
	 *       deletionDate
	 *     1
	 *       ...snip...
	 *
	 * @param $result
	 * @return array
	 */
	private function createContents($result) {
		$contents = array();

		// 国の配列
		$country_url_csv = new CountryUrlCsv();
		$this->country_url_array = $country_url_csv->readCountryUrl();

		foreach ($result as $row) {
			$key = $this->createKey($row);
			if (empty($contents[$key])) {
				$contents[$key] = new stdClass();
			}

			$contents[$key]->photo[] = $this->createPhoto($row);
			$contents[$key]->basicInfo = $this->createBasicInfo($row);
		}
		return $contents;
	}

	private function createKey($row) {
		return $row->ACCOMMODATION_CODE;
	}

	private function createPhoto($row) {
		$photo = new stdClass();
		$photo->p_img_filepath = $row->IMAGE_FILE_NAME;
		$photo->p_img_caption = $row->EXPOSITION;
		$photo->deletionDate = $row->deletionDate;
		return $photo;
	}

	private function createBasicInfo($row) {
		$basicInfo = new stdClass();
		$basicInfo->type = $this->type;
		$basicInfo->accommodation_name = $row->ACCOMMODATION_NAME;
		$basicInfo->accommodation_code = $row->ACCOMMODATION_CODE;
		if($this->type == 'i'){
			$basicInfo->accommodation_url = 'https://www-cms.hankyu-travel.com/freeplan-i/hotel/detail/h'.$row->HOTEL_CODE.'.php';
			$basicInfo->country_name = $this->country_url_array[$this->type][$row->COUNTRY_CODE]['country_name'];
		}else{
			$basicInfo->accommodation_url = 'https://www.hankyu-travel.com/freeplan-d/facility/detail/htl'.$row->ACCOMMODATION_CODE.'.php';
			$basicInfo->country_name = $this->country_url_array[$this->type][$row->PREFECTURE_CODE]['country_name'];
		}

		return $basicInfo;
	}

    private function createDeletionDate($result) {
        $contents = array();
        $contents = new stdClass();
        $contents = $result->deletionDate;
        return $contents;
    }

    private function createDestSel($result, $type) {
        $contents = new stdClass();
        foreach ($result as $row) {
            if(!empty($row->p_dest_code)){
                $contents->destination_code[$row->p_dest_code] = $row->p_dest_name;
            }
        }
        return $contents;
    }

	private function createCountrySel($result, $type) {
		// 国の配列
		$country_url_csv = new CountryUrlCsv();
		$this->country_url_array = $country_url_csv->readCountryUrl();

		$contents = new stdClass();
		foreach ($result as $row) {
			if($type == 'i'){
				if(!empty($row->COUNTRY_CODE)){
					$contents->country_code[$row->COUNTRY_CODE] = $this->country_url_array[$type][$row->COUNTRY_CODE]['country_name'];
				}
			}else{
				if(!empty($row->PREFECTURE_CODE)){
					$contents->country_code[$row->PREFECTURE_CODE] = $this->country_url_array[$type][$row->PREFECTURE_CODE]['country_name'];
				}
			}
		}
		return $contents;
	}
}
