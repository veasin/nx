<?php

namespace nx\db;

/**
 * Class pdo
 * @trait app
 * @package nx\db
 */
trait pdo{

	private $_nx_db_pdo_options =[
		\PDO::ATTR_DEFAULT_FETCH_MODE =>\PDO::FETCH_ASSOC,
		\PDO::ATTR_STRINGIFY_FETCHES =>false,
		\PDO::ATTR_EMULATE_PREPARES =>false,
	];
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
			$options =isset($config['options']) ?$config['options']+$this->_nx_db_pdo_options :$this->_nx_db_pdo_options;
			$db[$name] = new \PDO($config['dsn'], $config['username'], $config['password'], $options);
		}
		return $db[$name];
	}

	/**
	 * 直接插入方法
	 * ->insert('INSERT INTO cds (`interpret`, `titel`) VALUES (?, ?)', ['veas', 'new cd']);
	 * @param $sql
	 * @param array $params
	 * @return bool|int
	 */
	public function insertSQL($sql, $params=[], $config='default'){
		if(!empty($params)){
			$ok =$this->db($config)->exec($sql);
		} else{
			$sth = $this->db($config)->prepare($sql);
			$ok =false;
			$_first =current($params);
			if(!is_array($_first)){
				$ok = $sth->execute($params);
			} else {
				foreach($params as $_fields){
					$ok = $sth->execute($_fields);
				}
			}
		}
		if($ok) return $this->db()->lastInsertId();
		return false;
	}
	/**
	 * 查找记录
	 * ->select('SELECT `cds`.* FROM `cds` WHERE `cds`.`id` = ?', [13])
	 * @param $sql
	 * @param array $params
	 * @return array|bool
	 */
	public function selectSQL($sql, $params=[], $config='default'){
		$sth = $this->db($config)->prepare($sql);
		if($sth ===false) return false;
		$ok =$sth->execute(!empty($params) ?$params :null);
		if($ok ===false) return false;
		return $sth->fetchAll();
	}
	/**
	 * 更新记录
	 * 删除记录
	 * ->update('UPDATE `cds` SET `interpret` =? WHERE `cds`.`id` = ?', ['vea', 14])
	 *
	 * @param $sql
	 * @param array $params
	 * @return bool|int
	 */
	public function executeSQL($sql, $params=[], $config='default'){
		$sth = $this->db($config)->prepare($sql);
		if($sth ===false) return false;
		$ok =$sth->execute(!empty($params) ?$params :null);
		return $ok ?$sth->rowCount() :$ok;
	}

}