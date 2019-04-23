<?php
/**
 * Created by PhpStorm.
 * User: Vea
 * Date: 2019/04/17 017
 * Time: 11:37
 */
declare(strict_types=1);
namespace nx\helpers\db;

class pdo{
	private $_nx_db_pdo_options=[
		\PDO::ATTR_DEFAULT_FETCH_MODE=>\PDO::FETCH_ASSOC,
		\PDO::ATTR_STRINGIFY_FETCHES=>false,
		\PDO::ATTR_EMULATE_PREPARES=>false,
	];
	private $timeout=0;//超时
	/**
	 * @var \PDO
	 */
	public $PDO=null;
	/**
	 * @var \nx\app;
	 */
	private $app=null;
	private $setup=[];
	public function __construct($setup=[]){
		$this->app=\nx\app::$instance;
		$this->setup=$setup;
		$this->setup['options']=($this->setup['options'] ?? []) + $this->_nx_db_pdo_options;
	}
	/**
	 * @return \PDO
	 */
	private function db(){
		$now=time();
		if(null === $this->PDO || ($this->timeout > 0 && $this->timeout < $now)){
			$this->PDO=new \PDO($this->setup['dsn'], $this->setup['username'], $this->setup['password'], $this->setup['options']);
			$this->timeout=($this->setup['timeout'] ?? 0 > 0) ?$now + $this->setup['timeout'] :0;
		}
		return $this->PDO;
	}
	/**
	 * @return null
	 */
	private function failed(){
		if($this->app) $this->app->log(sprintf('sql error: %s %s %s', $this->PDO->errorInfo()));
		return null;
	}
	/**
	 * 直接插入方法
	 * ->insert('INSERT INTO cds (`interpret`, `titel`) VALUES (?, ?)', ['veas', 'new cd']);
	 * @param string $sql
	 * @param array  $params
	 * @return null|int
	 */
	public function insert(string $sql, array $params=[]){
		if($this->app) $this->app->log(sprintf('sql: %s %s', $sql, json_encode($params, JSON_UNESCAPED_UNICODE)));
		$db=$this->db();
		$ok=false;
		if(0 === count($params)){
			$ok=$db->exec($sql);
		}else{
			$sth=$db->prepare($sql);
			if(false === $sth) return $this->failed();
			$_first=current($params);
			if(!is_array($_first)){
				$ok=$sth->execute($params);
			}else{
				foreach($params as $_fields){
					$ok=$sth->execute($_fields);
				}
			}
		}
		return $ok ?$db->lastInsertId() :$this->failed();
	}
	/**
	 * 查找记录
	 * ->select('SELECT `cds`.* FROM `cds` WHERE `cds`.`id` = ?', [13])
	 * @param string     $sql
	 * @param array|null $params
	 * @return array|null
	 */
	public function select(string $sql, array $params=null){
		if($this->app) $this->app->log(sprintf('sql: %s %s', $sql, json_encode($params, JSON_UNESCAPED_UNICODE)));
		$db=$this->db();
		$sth=$db->prepare($sql);
		if(false === $sth) return $this->failed();
		$ok=$sth->execute($params);
		return (false === $ok) ?$this->failed() :$sth->fetchAll();
	}
	/**
	 * 更新记录
	 * 删除记录
	 * ->update('UPDATE `cds` SET `interpret` =? WHERE `cds`.`id` = ?', ['vea', 14])
	 * @param string     $sql
	 * @param array|null $params
	 * @return int|null
	 */
	public function execute(string $sql, array $params=null){
		if($this->app) $this->app->log(sprintf('sql: %s %s', $sql, json_encode($params, JSON_UNESCAPED_UNICODE)));
		$db=$this->db();
		$sth=$db->prepare($sql);
		if(false === $sth) return $this->failed();
		$ok=$sth->execute($params);
		return $ok ?$sth->rowCount() :$this->failed();
	}
	/**
	 * 事务
	 * @param callable $fun arg[model:$this] return ===true is rollback
	 * @return null|mixed
	 */
	public function transaction(callable $fun){
		if($this->app) $this->app->log('sql transaction begin:');
		$db=$this->db();
		$db->beginTransaction();
		$rollback=$fun($this);
		if($rollback === true){
			$db->rollBack();
			$rollback=null;
		}else $db->commit();
		if($this->app) $this->app->log('sql transaction end.');
		return $rollback;
	}
	public function sql():\nx\helpers\db\sql{
		return new sql($this);
	}
	public function from():\nx\helpers\db\sql{
		return new sql($this);
	}
}