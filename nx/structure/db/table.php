<?php
namespace nx\structure\db;

/**
 * Class table
 * @trait model
 * @package nx\db
 */
trait table{
	/**
	 * @param $name
	 * @param string $primary
	 * @param string $config
	 * @return \nx\db\sql
	 */
	public function table($name, $primary='id', $config='default'){
		$db =$this->db($config);
		return \nx\db\sql::factory($name, $primary, $db);
	}
	/**
	 * [[key, val, oth],[key, val, oth]...]
	 * (key, val)=>[key=>val],
	 * (key, fun)=>[key=>fun(val)]
	 * (key,false)=>[key=>[key, val, oth]],
	 * (null, val)=>[val, val],
	 * (null, fun)=>[fun(val)]
	 * (null, false) =>$array
	 * @param     $array
	 * @param int $key
	 * @param int $value
	 * @return array
	 */
	public function table_map($array, $key=0, $value=1){
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
	}
	/**
	 * 对查询结果集进行排序
	 * @param array $array 查询结果
	 * @param string $field 排序的字段名
	 * @param string $sortby 排序类型  asc正向排序 desc逆向排序 nat自然排序
	 * @return array
	 */
	public function table_sort($array, $field, $sortby='asc'){
		if(!is_array($array)) return $array;
		$sort=$r=[];
		foreach($array as $i=>$data) $sort[$i]= &$data[$field];
		switch($sortby){
			case 'asc': // 正向排序
				asort($sort);
				break;
			case 'desc':// 逆向排序
				arsort($sort);
				break;
			case 'nat': // 自然排序
				natcasesort($sort);
				break;
		}
		foreach($sort as $key=>$val) $r[]= &$array[$key];
		return $r;
	}
}