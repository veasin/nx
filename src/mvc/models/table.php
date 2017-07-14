<?php
/**
 * Created by PhpStorm.
 * User: Vea
 * Date: 2017/07/14 014
 * Time: 10:41
 */

namespace nx\mvc\models;

class table extends \nx\mvc\model{
	/**
	 * 绑定表名
	 * @var string
	 */
	public $table_name ='';
	/**
	 * 绑定表主键
	 * @var string
	 */
	public $table_primary ='id';
	/**
	 * 更新时间戳字段
	 * @var string
	 */
	public $update_field ='update_time';
	/**
	 * 创建时间戳字段
	 * @var string
	 */
	public $create_field ='create_time';
	/**
	 * 返回一个table
	 * @return \nx\helpers\sql
	 */
	private function _table(){
		return $this->table($this->table_name);
	}
	/**
	 * 添加一行数据
	 * @param $data
	 * @return bool|int
	 */
	public function add($data){
		if(!empty($this->create_field)) $data[$this->create_field] =time();
		return $this->_table()->create($data);
	}
	/**
	 * 根据主键获取一行数据
	 * @param      $id
	 * @param bool $fields
	 * @return mixed
	 */
	public function get($id, $fields=false){
		return $this->_table()->where($id)->first($fields);
	}
	/**
	 * 根据主键更新一行数据
	 * @param       $id
	 * @param array $data
	 * @return bool|int
	 */
	public function update($id, $data=[]){
		if(!empty($this->update_field)) $data[$this->update_field] =time();
		return $this->_table()->where($id)->update($data);
	}
	/**
	 * 根据主键删除一行数据
	 * @param $id
	 * @return false|int
	 */
	public function delete($id){
		return $this->_table()->where($id)->delete();
	}
	/**
	 * 返回全部列表
	 * @param int $page
	 * @param int $max
	 * @param array $setting
	 * @return array
	 */
	public function all($page=1, $max=20, $setting=[]){
		$where =$setting['where'] ??false;
		$orderBy =$setting['orderBy'] ??false;
		$sort =$setting['sort'] ??'desc';
		$fields =$setting['fields'] ??[];
		$withCount =$setting['withCount'] ??false;
		$_tab =$this->_table();
		if(!empty($where)) $_tab->where($where);
		if(false ===$withCount) return $_tab->sort([(empty($orderBy) ?$this->table_primary:$orderBy)=>$sort])->page($page, $max)->read($fields);
		$count =$_tab->count();
		$data =$count>0 ?$_tab->sort([(empty($orderBy) ?$this->table_primary:$orderBy)=>$sort])->page($page, $max)->read($fields) : [];
		return ['count'=>$count, 'data'=>$data];
	}
	/**
	 * 返回总数
	 * @return int
	 */
	public function count(){
		return $this->_table()->count();
	}
}
