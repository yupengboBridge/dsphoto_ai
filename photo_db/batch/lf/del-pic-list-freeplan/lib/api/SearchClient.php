<?php

abstract class SearchClient {

	private $api;

	protected $defaults = array(
	);

	public function __construct($api) {
		$this->api = $api;
	}

	public function request($params) {
		$query = $this->query($params);
		$curl = curl_init();
		try {
			return $this->curl($curl, $query);
		} catch (Exception $e) {
			curl_close($curl);
			throw $e;
		}
	}

	protected function query($params) {
		return $params + $this->defaults;
	}

	private function curl($curl, $query) {
		$ua = 'XML Agent';
		curl_setopt($curl, CURLOPT_URL, $this->api);
		curl_setopt($curl, CURLOPT_USERAGENT, $ua);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
		curl_setopt($curl, CURLOPT_ENCODING, 'gzip');

		$headers = array();

		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($curl, CURLOPT_FAILONERROR, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($query));
		curl_setopt($curl, CURLOPT_POST, true);

		$response = curl_exec($curl);
		$err_no = curl_errno($curl);
		if ($err_no > 0) {
			throw new RuntimeException(sprintf('curl_err_no: %s %s', $err_no, curl_error($curl)));
		}

		curl_close($curl);

		return $response;
	}
}
