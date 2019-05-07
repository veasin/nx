<?php
/**
 * Created by PhpStorm.
 * User: Vea
 * Date: 2019/05/05 005
 * Time: 17:30
 */
declare(strict_types=1);
namespace nx\helpers\db\pdo;

class result{
	/**
	 * @var \PDOStatement
	 */
	protected $sth;
	/**
	 * @var \PDO
	 */
	protected $pdo;
	/**
	 * @var bool
	 */
	protected $result =false;
	public function __construct(bool $result, \PDOStatement $sth, \PDO $pdo){
		$this->sth =$sth;
		$this->pdo =$pdo;
		$this->result =$result;
	}
	public function ok():bool{
		return $this->result;
	}
	/**
	 * $db->update() <=false
	 *      ->affectedRows();
	 *
	 */
	public function rowCount():?int{
		if(false ===$this->result) return null;
		else return $this->sth->rowCount();
	}
	public function lastInsertId():?int{
		if(false ===$this->result) return null;
		else return (int)$this->pdo->lastInsertId();
	}
	public function first($className=null, ...$args){
		if(false ===$this->result) return null;
		elseif(null ===$className) return $this->fetch($this->pdo::FETCH_ASSOC, $this->pdo::FETCH_ORI_FIRST);
		else{
			$o =$this->sth->fetchObject($className, $args);
			return false !==$o ?$o :null;
		}
	}
	public function all($className=null, ...$args){
		if(false ===$this->result) return null;
		elseif(null ===$className) return $this->fetchAll($this->pdo::FETCH_ASSOC);
		else return $this->fetchAll($this->pdo::FETCH_CLASS, $className, $args);
	}
	/**
	 * DATA:[[key, val, oth],[key, val, oth]...]
	 * ARGS:(key, val)=>[key=>val],
	 *      (key, fun)=>[key=>fun(val)]
	 *      (key,false)=>[key=>[key, val, oth]],
	 *      (null, val)=>[val, val],
	 *      (null, fun)=>[fun(val)]
	 *      (null, false) =>$array
	 * @param int $key
	 * @param int $value
	 * @return array
	 */
	public function map($key=0, $value=1){
		$callback =function(...$array) use($key, $value){
			if(!is_array($array)) return $array;
			$r=[];
			if(is_null($key)){
				if($value===false) return $array;
				foreach($array as $_key=>$_value){
					$r[]=is_callable($value) ?$value($_value, $_key) :$_value[$value];
				}
			}else{
				foreach($array as $_key=>$_value){
					$r[$_value[$key]]=($value===false)
						?$_value :(is_callable($value) ?$value($_value, $_key) :$_value[$value]);
				}
			}
			return $r;
		};
		return $this->fetchMap($callback);
	}
	public function fetchMap($callback){
		return $this->fetchAll($this->pdo::FETCH_FUNC, $callback);
	}
	public function fetch(...$args):?array{
		if(false ===$this->result) return null;
		else{
			$r =$this->sth->fetch(...$args);
			return false !==$r ?$r:null;
		}
	}
	public function fetchAll(...$args):?array{
		if(false ===$this->result) return null;
		else {
			$r =$this->sth->fetchAll(...$args);
			return false !==$r ?$r:null;
		}
	}
}