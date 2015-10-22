<?php
namespace nx\db;

/**
 * Class table
 * @trait model
 * @package nx\db
 */
trait table{

	protected function nx_db_table(){
		if(isset($this->buffer)){
			if(!isset($this->buffer['table'])) $this->buffer['table'] = [];
		}
	}

	/**
	 * @param $name
	 * @param null $primary
	 * @param string $config
	 * @return \nx\db\sql
	 */
	public function table($name, $primary = 'id', $config = 'default'){
		return \nx\db\sql::factory($name, $primary, $config, $this);
	}

}