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
		if(!array_key_exists('db/pdo', $it->buffer)) $it->buffer['db/pdo']=['config'=>isset($it->setup['db/pdo']) ?$it->setup['db/pdo'] :[], 'handle'=>[],'timeout'=>[]];
	}
	/**
	 * @param string $name app->setup['db/pdo']
	 * @return \PDO
	 */
	public function db($name='default'){
		$it=is_a($this, 'nx\app') ?$this :$this->app;
		$db=&$it->buffer['db/pdo']['handle'];
		$timeout =&$it->buffer['db/pdo']['timeout'];
		if(!array_key_exists($name, $db) || (array_key_exists($name, $timeout) && $timeout[$name] <time())){
			$cfg=&$it->buffer['db/pdo']['config'];
			$config=[];
			if(array_key_exists($name, $cfg)) $config=is_array($cfg[$name]) ?$cfg[$name] :$cfg[$cfg[$name]];
			if(empty($config)) die('no db set.');
			$options=array_key_exists('options', $config) && is_array($config['options']) ?$config['options']+$this->_nx_db_pdo_options :$this->_nx_db_pdo_options;
			if(array_key_exists('timeout', $config) && $config['timeout'] >0) $timeout[$name] =time() +$config['timeout'];
			$db[$name]=new \PDO($config['dsn'], $config['username'], $config['password'], $options);
			$this->log('pdo: '.$config['dsn']);
		}
		return $db[$name];
	}
	/**
	 * @param \PDO $db
	 * @return bool
	 */
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
	 * @param string $config
	 * @return bool|string
	 */
	public function insertSQL($sql, array $params=[], $config='default'){
		$this->log('sql: '.$sql.' '.json_encode($params, JSON_UNESCAPED_UNICODE));
		$db =$this->db($config);
		$ok =false;
		if(0===count($params)){
			$ok=$db->exec($sql);
		}else{
			$sth=$db->prepare($sql);
			if(false===$sth) return $this->db_false($db);
			$_first=current($params);
			if(!is_array($_first)){
				$ok=$sth->execute($params);
			}else{
				foreach($params as $_fields){
					$ok=$sth->execute($_fields);
				}
			}
		}
		return $ok ?$db->lastInsertId() :$this->db_false($db);
	}
	/**
	 * 查找记录
	 * ->select('SELECT `cds`.* FROM `cds` WHERE `cds`.`id` = ?', [13])
	 * @param $sql
	 * @param array $params
	 * @param string $config
	 * @return array|bool
	 */
	public function selectSQL($sql, array $params=[], $config='default'){
		$this->log('sql: '.$sql.' '.json_encode($params, JSON_UNESCAPED_UNICODE));
		$db =$this->db($config);
		$sth=$db->prepare($sql);
		if(false===$sth) return $this->db_false($db);
		$ok=$sth->execute(count($params) ?$params :null);
		return (false===$ok) ?$this->db_false($db) :$sth->fetchAll();
	}
	/**
	 * 更新记录
	 * 删除记录
	 * ->update('UPDATE `cds` SET `interpret` =? WHERE `cds`.`id` = ?', ['vea', 14])
	 *
	 * @param $sql
	 * @param array $params
	 * @param string $config
	 * @return bool|int
	 */
	public function executeSQL($sql,array $params=[], $config='default'){
		$this->log('sql: '.$sql.' '.json_encode($params, JSON_UNESCAPED_UNICODE));
		$db =$this->db($config);
		$sth=$db->prepare($sql);
		if(false===$sth) return $this->db_false($db);
		$ok=$sth->execute(count($params) ?$params :null);
		return $ok ?$sth->rowCount() :$this->db_false($db);
	}
	/**
	 * 事务
	 * @param callable $fun arg[model:$this] return ===true is rollback
	 * @param string $config
	 * @return $this
	 */
	public function transaction(callable $fun, $config='default'){
		$this->log('transaction begin: '.json_encode($config, JSON_UNESCAPED_UNICODE));
		$db =$this->db($config);
		$db->beginTransaction();
		$rollback =$fun($this);
		if($rollback===true){
			$db->rollBack();
			$rollback =false;
		} else $db->commit();
		$this->log('transaction end.');
		return $rollback;
	}
}