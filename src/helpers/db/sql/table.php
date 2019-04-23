<?php
/**
 * Created by PhpStorm.
 * User: Vea
 * Date: 2019/04/17 017
 * Time: 12:14
 */
declare(strict_types=1);
namespace nx\helpers\db\sql;

class table implements \ArrayAccess{
	public $table ='';
	public $tableAS ='';
	public $primary ='id';
	public function __construct(string $tableName, string $primary='id'){
		$n =explode(' ', $tableName, 2);
		$this->table =$n[0];
		$this->tableAS =$n[1] ?? '';

		$this->primary =$primary;
	}
	/**
	 * 设置别名
	 * @param string $name
	 * @return \nx\helpers\db\sql\part
	 */
	public function as(string $name):table{
		$this->as =$name;
		return $this;
	}
	public function formatField($name=null){
		$table =$this->tableAS ?"`{$this->tableAS}`" :"`{$this->table}`";
		$field =\nx\helpers\db\sql::formatField($name??$this->primary);
		return "{$table}.{$field}";
	}
	public function getFormatName($withAS =true){
		if($withAS) return $this->tableAS ?"`{$this->table}` `{$this->tableAS}`" : "`{$this->table}`";
		else return "`{$this->table}`";
	}
	//-------------------------------------------------------------------------------------------------------------
	public function __toString():string{
		return $this->getFormatName();
	}
	public function __invoke($value):part{
		return new part($value, 'value', $this);
	}
	//-------------------------------------------------------------------------------------------------------------
	public function offsetSet($offset, $value){
		//
	}
	public function offsetExists($offset) {
		//
	}
	public function offsetUnset($offset) {
		//
	}
	/**
	 * @param mixed $offset
	 * @return \nx\helpers\db\sql\part
	 */
	public function offsetGet($offset):part{
		return new part($offset, 'field',$this);
	}
}