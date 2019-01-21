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
	protected $pdo_dbs=[];//缓存
	protected $pdo_timeout =[];//超时
	/**
	 * @param string $name app->setup['db/pdo']
	 * @return \PDO
	 */
	public function db($name='default'){
		$now =time();
		$timeout =$this->pdo_timeout[$name] ?? 0;
		if(!array_key_exists($name,$this->pdo_dbs) || ($timeout >0 && $timeout <$now)){
			$config =$this->container->get('db') ?? [];
			$setup=$config[$config[0] ?? $name];
			$new=$setup[0] ?? '\pdo';
			$options =($setup[1]['options'] ??[]) +$this->_nx_db_pdo_options;
			$set =$setup[1];
			$this->pdo_dbs[$name]=new $new($set['dsn'] ?? '', $set['username'] ?? 'root', $set['password'] ?? '', $options);
			$this->pdo_timeout[$name] =($set['timeout']??0) ? $set['timeout'] +$now :0;
		}
		return $this->pdo_dbs[$name];
	}
	/**
	 * @param \PDO $db
	 * @return bool
	 */
	private function db_false($db){
		$err =$db->errorInfo();
		$this->logger->debug(sprintf('pdo error: %s, %s, %s', $err[0], $err[1], $err[2]));
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
		$this->logger->debug('sql: '.$sql.' '.json_encode($params, JSON_UNESCAPED_UNICODE));
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
		$this->logger->debug('sql: '.$sql.' '.json_encode($params, JSON_UNESCAPED_UNICODE));
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
		$this->logger->debug('sql: '.$sql.' '.json_encode($params, JSON_UNESCAPED_UNICODE));
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
		$this->logger->debug('transaction begin: '.json_encode($config, JSON_UNESCAPED_UNICODE));
		$db =$this->db($config);
		$db->beginTransaction();
		$rollback =$fun($this);
		if($rollback===true){
			$db->rollBack();
			$rollback =false;
		} else $db->commit();
		$this->logger->debug('transaction end.');
		return $rollback;
	}
}