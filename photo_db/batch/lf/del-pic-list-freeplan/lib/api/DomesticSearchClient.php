<?php

require_once 'SearchClient.php';

class DomesticSearchClient extends SearchClient {

	const DATA_KIND_FACET = 1;
	const DATA_KIND_SEARCH = 2;
	const DATA_KIND_FACET_SEARCH = 3;
	const DATA_KIND_CALENDAR = 4;
	const DATA_KIND_HOTEL_FREEWORD = 5;
	const DATA_KIND_ACCOMMODATION_FREEWORD = 6;

	protected $defaults = array(
		'p_data_kind' => self::DATA_KIND_SEARCH,
		//'p_kikan_min' => 2,
		//'p_conductor' => 1,
	);

}
