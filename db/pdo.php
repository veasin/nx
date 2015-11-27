<?php

namespace nx\db;

/**
 * Class pdo
 * @trait app
 * @package nx\db
 */
trait pdo{
	private $_nx_db_pdo_options=[
		\PDO::ATTR_DEFAULT_FETCH_MODE=>\PDO::FETCH_ASSOC,
		\PDO::ATTR_STRINGIFY_FETCHES=>false,
		\PDO::ATTR_EMULATE_PREPARES=>false,
	];
	protected function nx_db_pdo(){
		$it=is_a($this, 'nx\app') ?$this :$this->app;
		if(!isset($it->buffer['db/pdo'])) $it->buffer['db/pdo']=['config'=>isset($it->setup['db/pdo']) ?$it->setup['db/pdo'] :[], 'handle'=>[],];
	}
	/**
	 * @param string $name app->setup['db/pdo']
	 * @return \PDO
	 */
	public function db($name='default'){
		$it=is_a($this, 'nx\app') ?$this :$this->app;
		$db=&$it->buffer['db/pdo']['handle'];
		if(!isset($db[$name])){
			$cfg=&$it->buffer['db/pdo']['config'];
			$config=false;
			if(isset($cfg[$name])) $config=is_array($cfg[$name]) ?$cfg[$name] :$cfg[$cfg[$name]];
			if(empty($config)) die('no db set.');
			$options=isset($config['options']) ?$config['options']+$this->_nx_db_pdo_options :$this->_nx_db_pdo_options;
			$db[$name]=new \PDO($config['dsn'], $config['username'], $config['password'], $options);
			$this->log('pdo dsn:'.$config['dsn']);
		}
		return $db[$name];
	}
	private function db_false($db){
		$err =$db->errorInfo();
		$this->log(sprintf('pdo error: %s, %s, %s', $err[0], $err[1], $err[2]));
		return false;
	}
	/**
	 * 直接插入方法
	 * ->insert('INSERT INTO cds (`interpret`, `titel`) VALUES (?, ?)', ['veas', 'new cd']);
	 * @param $sql
	 * @param array $params
	 * @return bool|int
	 */
	public function insertSQL($sql, $params=[], $config='default'){
		$this->log('sql: '.$sql.' '.json_encode($params, JSON_UNESCAPED_UNICODE));
		$db =$this->db($config);
		if(empty($params)){
			$ok=$db->exec($sql);
		}else{
			$sth=$db->prepare($sql);
			if(empty($sth)) return $this->db_false($db);
			$_first=current($params);
			if(!is_array($_first)){
				$ok=$sth->execute($params);
			}else{
				foreach($params as $_fields){
					$ok=$sth->execute($_fields);
				}
			}
		}
		if($ok) return $db->lastInsertId();
		return $this->db_false($db);
	}
	/**
	 * 查找记录
	 * ->select('SELECT `cds`.* FROM `cds` WHERE `cds`.`id` = ?', [13])
	 * @param $sql
	 * @param array $params
	 * @return array|bool
	 */
	public function selectSQL($sql, $params=[], $config='default'){
		$this->log('sql: '.$sql.' '.json_encode($params, JSON_UNESCAPED_UNICODE));
		$db =$this->db($config);
		$sth=$db->prepare($sql);
		if($sth===false) return $this->db_false($db);
		$ok=$sth->execute(!empty($params) ?$params :null);
		if($ok===false) return $this->db_false($db);
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
		$this->log('sql: '.$sql.' '.json_encode($params, JSON_UNESCAPED_UNICODE));
		$db =$this->db($config);
		$sth=$db->prepare($sql);
		if($sth===false) return $this->db_false($db);
		$ok=$sth->execute(!empty($params) ?$params :null);
		return $ok ?$sth->rowCount() :$this->db_false($db);
	}
	/**
	 * 事务
	 * @param callable $fun
	 * @param string $config
	 * @return $this
	 */
	public function transaction(callable $fun, $config='default'){
		$this->log('transaction begin: '.json_encode($config, JSON_UNESCAPED_UNICODE));
		$db =$this->db($config);
		$db->beginTransaction();
		$rollback =$fun($this);
		if($rollback) $db->rollBack();
		else $db->commit();
		$this->log('transaction end.');
		return $rollback;
	}
}