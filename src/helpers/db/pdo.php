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
	public $link=null;
	private $setup=[];
	/**
	 * @var callable
	 */
	private $_log =null;
	public function __construct($setup=[]){
		if(\nx\app::$instance) $this->_log =\nx\app::$instance->log;
		$this->setup=$setup ??[];
		$this->timeout =$this->setup['timeout'] ?? 0;
		$this->setup['options']=($this->setup['options'] ?? []) + $this->_nx_db_pdo_options;
	}
	/**
	 * @return \PDO
	 */
	private function db():\PDO{
		$now=time();
		if(null === $this->link || ($this->timeout > 0 && $this->timeout < $now)){
			$this->link=new \PDO($this->setup['dsn'], $this->setup['username'], $this->setup['password'], $this->setup['options']);
			$this->timeout=($this->setup['timeout'] ?? 0 > 0) ?$now + $this->setup['timeout'] :0;
		}
		return $this->link;
	}
	public function setLog(callable $logger){
		$this->_log =$logger;
	}
	/**
	 * @param string $template
	 * @param array  $data
	 */
	private function log(string $template, array $data=[]){
		if(null !==$this->_log) call_user_func($this->_log, sprintf($template, ...$data));
	}
	/**
	 * @return null
	 */
	private function failed(){
		$this->log('sql error: %s %s %s', $this->link->errorInfo());
		return null;
	}
	/**
	 * 直接插入方法
	 * ->insert('INSERT INTO cds (`interpret`, `titel`) VALUES (?, ?)', ['veas', 'new cd']);
	 * @param string $sql
	 * @param array  $params
	 * @return null|int
	 */
	public function insert(string $sql, array $params=[]):?int{
		$this->log('sql: %s [%s]', [$sql, json_encode($params, JSON_UNESCAPED_UNICODE)]);
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
		return $ok ?(int)$db->lastInsertId() :$this->failed();
	}
	/**
	 * 选择记录
	 * ->select('SELECT `cds`.* FROM `cds` WHERE `cds`.`id` = ?', [13])
	 * @param string     $sql
	 * @param array|null $params
	 * @return array|null
	 */
	public function select(string $sql, array $params=null):?array{
		$this->log('sql: %s [%s]', [$sql, json_encode($params, JSON_UNESCAPED_UNICODE)]);
		$db=$this->db();
		$sth=$db->prepare($sql);
		if(false === $sth) return $this->failed();
		$ok=$sth->execute($params ??[]);
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
	public function execute(string $sql, array $params=null):?int{
		$this->log('sql: %s [%s]', [$sql, json_encode($params, JSON_UNESCAPED_UNICODE)]);
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
		$this->log('sql transaction begin:');
		$db=$this->db();
		$db->beginTransaction();
		$rollback=$fun($this);
		if($rollback === true){
			$db->rollBack();
			$rollback=null;
		}else $db->commit();
		$this->log('sql transaction end.');
		return $rollback;
	}
	/**
	 * 返回table对象
	 * @param string $tableName
	 * @param string $primary
	 * @return \nx\helpers\db\sql
	 */
	public function from(string $tableName, string $primary='id'):\nx\helpers\db\sql{
		return new sql($tableName, $primary, $this);
	}
}