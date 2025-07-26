<?php

require_once dirname(__FILE__) . '/../../service/Logger.php';

abstract class AbstractCsv {

	private $filename;
	private $containsColumnHeader;
	private $updatedAt;
	protected $columns;
	protected $rows;
	protected $categoryColumnName;

	public function __construct($filename, $containsColumnHeader = true) {
		$this->filename = $filename;
		$this->containsColumnHeader = $containsColumnHeader;
		$this->rows = array();
		$this->parse();
	}

	private function parse() {
		if ($this->rows) {
			return $this->rows;
		}
		$fp = @fopen($this->filename, 'r');
		$rows = array();
		if ($fp) {
			Logger::debug('    fopen() success: filename: "' . $this->filename . '"');
			$this->retrieveUpdatedAt(stream_get_meta_data($fp));
			$columnCreated = false;
			while (!feof($fp)) {
				$row = fgetcsv($fp, 0, "\t");
				if (!$columnCreated && $this->containsColumnHeader) {
					$row = fgetcsv($fp, 0, "\t");
					$this->columns = $this->createColumns($row);
					$columnCreated = true;
					continue;
				}
				if ($row) {
					$rows[] = $this->split($row);
				}
			}
			fclose($fp);
		} else {
			Logger::warn('    fopen() failure: filename: "' . $this->filename . '"');
		}
		$this->rows = $rows;
		return $rows;
	}

	protected function createColumns($row) {
		$columns = array();
		foreach ($row as $index => $columnName) {
			if ($columnName) {
				$columns[$columnName] = $index;
			}
		}
		return $columns;
	}

	private function split($row) {
		if (count($this->columns) === 0) {
			return $row;
		}
		$record = array();
		foreach ($this->columns as $columnName => $index) {
			$record[$columnName] = empty($row[$index]) ? '' : $row[$index];
		}
		return $record;
	}

	public function fetch($category, $group = null) {
		if ($group) {
			return $this->fetchWithCategoryAndGroup($category, $group);
		} else {
			return $this->fetchWithCategory($category);
		}
	}

	public function updatedAt() {
		return $this->updatedAt;
	}

	private function fetchWithCategoryAndGroup($category, $group) {
		$fetched = $this->findByCategory($category);
		foreach ($fetched as $row) {
			if ($group == $row['GROUP']) {
				return $row;
			}
		}
		return null;
	}

	private function fetchWithCategory($category) {
		$fetched = $this->findByCategory($category, 0, 1);
		return empty($fetched[0]) ? '' : $fetched[0];
	}

	protected function findByCategory($categoryName, $offset = 0, $limit = 0) {
		$filtered = array();
		foreach ($this->rows as $key => $row) {
			if ($row[$this->categoryColumnName] === $categoryName) {
				$filtered[$key] = $row;
			}
		}
		return $this->slice($filtered, $offset, $limit);
	}

	private function slice($rows, $offset = 0, $limit = 0) {
		if ($offset && $limit) {
			return array_slice($rows, $offset, $limit);
		} else if ($offset && !$limit) {
			return array_slice($rows, $offset);
		} else if (!$offset && $limit) {
			return array_slice($rows, 0, $limit);
		}
		return $rows;
	}

	private function retrieveUpdatedAt($meta) {
		if (empty($meta['wrapper_data'])) {
			return null;
		}
		foreach ($meta['wrapper_data'] as $response) {
			if (substr( strtolower($response), 0, 15) === 'last-modified: ') {
				$this->updatedAt = date('Y/m/d H:i:s', strtotime(substr($response, 15)));
				break;
			}
		}
	}
}
