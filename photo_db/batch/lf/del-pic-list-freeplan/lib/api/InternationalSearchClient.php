<?php

require_once 'SearchClient.php';

class InternationalSearchClient extends SearchClient {

	const DATA_KIND_FACET = 1;
	const DATA_KIND_SEARCH = 2;
	const DATA_KIND_FACET_SEARCH = 3;
	const DATA_KIND_CALENDAR = 4;

	protected $defaults = array(
		'p_data_kind' => self::DATA_KIND_SEARCH,
	);

}
