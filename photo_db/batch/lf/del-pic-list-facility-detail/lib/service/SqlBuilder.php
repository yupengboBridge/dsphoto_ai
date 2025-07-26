<?php

class SqlBuilder {

	private $select;
	private $from;
	private $leftJoin;
	private $where;
	private $groupBy;
	private $orderBy;
	private $limit;
	private $offset;

	private function __construct() {
		$this->leftJoin = array();
	}

	public function select($select) {
		if (empty($select)) {
			return $this;
		}
		$this->select = $select;
		return $this;
	}

	public function from($from) {
		if (empty($from)) {
			return $this;
		}
		$this->from = $from;
		return $this;
	}

	public function leftJoin($table, $condition) {
		$this->leftJoin[] = array(
			'table' => $table,
			'condition' => $condition,
		);
		return $this;
	}

	public function where($where) {
		if (empty($where)) {
			return $this;
		}
		$this->where[] = $where;
		return $this;
	}

	public function andWhere($where) {
		if (empty($where)) {
			return $this;
		}
		$this->where[] = 'AND';
		$this->where[] = $where;
		return $this;
	}

	public function orWhere($where) {
		if (empty($where)) {
			return $this;
		}
		$this->where[] = 'OR';
		$this->where[] = $where;
		return $this;
	}

	public function orderBy($orderBy) {
		if (empty($orderBy)) {
			return $this;
		}
		$this->orderBy = $orderBy;
		return $this;
	}

	public function groupBy($groupBy) {
		if (empty($groupBy)) {
			return $this;
		}
		$this->groupBy = $groupBy;
		return $this;
	}

	public function limit($limit) {
		if (empty($limit)) {
			return $this;
		}
		$this->limit = $limit;
		return $this;
	}

	public function offset($offset) {
		if (empty($offset)) {
			return $this;
		}
		$this->offset = $offset;
		return $this;
	}

	public function build() {
		$sql = array();
		$sql = array_merge($sql, $this->createSelect());
		if (!empty($this->from)) {
			$sql = array_merge($sql, $this->createFrom());
		}
		if (!empty($this->leftJoin)) {
			$sql = array_merge($sql, $this->createLeftJoin());
		}
		if (!empty($this->where)) {
			$sql = array_merge($sql, $this->createWhere());
		}
		if (!empty($this->groupBy)) {
			$sql = array_merge($sql, $this->createGroupBy());
		}
		if (!empty($this->orderBy)) {
			$sql = array_merge($sql, $this->createOrderBy());
		}
		if (!empty($this->limit)) {
			$sql = array_merge($sql, $this->createLimit());
		}
		if (!empty($this->offset)) {
			$sql = array_merge($sql, $this->createOffset());
		}
		$trimmed = array();
		foreach ($sql as $token) {
			$trimmed[] = trim($token);
		}
		return implode(' ', $trimmed);
	}

	public function buildAsSubQuery($name = null) {
		$query = '(' . $this->build() . ')';
		$query .= $name ? ' as ' . $name : null;
		return $query;
	}

	private function createSelect() {
		$sql = array();
		$sql[] = 'SELECT';
		return array_merge($sql, $this->select);
	}

	private function createLeftJoin() {
		$sql = array();
		foreach ($this->leftJoin as $leftJoin) {
			$sql[] = 'LEFT JOIN';
			$sql[] = $leftJoin['table'];
			$sql[] = 'ON';
			$sql[] = $leftJoin['condition'];
		}
		return $sql;
	}

	private function createFrom() {
		$sql = array();
		$sql[] = 'FROM';
		$sql[] = $this->from;
		return $sql;
	}

	private function createWhere() {
		$sql = array();
		$sql[] = 'WHERE';
		return array_merge($sql, $this->where);
	}

	private function createGroupBy() {
		$sql = array();
		$sql[] = 'GROUP BY';
		$sql[] = $this->groupBy;
		return $sql;
	}

	private function createOrderBy() {
		$sql = array();
		$sql[] = 'ORDER BY';
		$sql[] = $this->orderBy;
		return $sql;
	}

	private function createLimit() {
		$sql = array();
		$sql[] = 'LIMIT';
		$sql[] = $this->limit;
		return $sql;
	}

	private function createOffset() {
		$sql = array();
		$sql[] = 'OFFSET';
		$sql[] = $this->offset;
		return $sql;
	}

	public static function create() {
		return new self();
	}
}
