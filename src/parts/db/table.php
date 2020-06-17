<?php
namespace nx\parts\db;

use nx\helpers\db\sql;

trait table{
	protected $tableName='';
	protected $dbConfig='default';
	protected $tablePrimary ='id';
	/**
	 * @param string|null $tableName
	 * @param string|null $primary
	 * @param string|null $config
	 * @return \nx\helpers\db\sql
	 */
	protected function table(string $tableName=null, string $primary=null, string $config=null):sql{
		return $this->db($config ?? $this->dbConfig)->from($tableName ?? $this->tableName, $primary ?? $this->tablePrimary ?? 'id');
	}
}
