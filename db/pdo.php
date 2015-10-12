<?php

namespace nx\db;

/**
 * Class pdo
 * @trait app
 * @package nx\db
 */
trait pdo{

	protected function nx_db_pdo(){
		$this->buffer['db_pdo'] =[
			'config' =>isset($this->setup['db.pdo']) ?$this->setup['db.pdo'] :[],
			'handle' =>[],
		];
	}

	/**
	 * @param string $name app->setup['db.pdo']
	 * @return \PDO
	 */
	public function db($name ='default'){
		$db =&$this->buffer['db_pdo']['handle'];
		if(!isset($db[$name])) {
			$cfg =&$this->buffer['db_pdo']['config'];
			$config = false;
			if (isset($cfg[$name])) $config = is_array($cfg[$name]) ? $cfg[$name] : $cfg[$cfg[$name]];
			if (empty($config)) die('no db set.');
			$db[$name] = new \PDO($config['dsn'], $config['username'], $config['password'], $config['options']);
		}
		return $db[$name];
	}

}