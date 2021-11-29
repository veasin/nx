<?php
namespace nx\helpers\model;

use nx\parts\callApp;
use nx\parts\db\table;
use nx\parts\model\cache;
use nx\parts\model\plugin;

class single{
	use callApp, plugin, cache, table;

	public $id=0;
	protected $data=[];
	protected $_data=[];//初始化数据
	protected $tombstone=false; //逻辑删除
	protected $field_of_created ='create_time';
	protected $field_of_updated ='update_time';
	protected $field_of_deleted ='delete_time';
	public function __construct(array $data=[]){
		$this->data=$this->_data=$data ?? [];
		$this->id=$this->data['id'] ?? 0;
		if(0 === $this->id) $this->default($data);
	}
	/**
	 * 设置默认属性值
	 * @param array $data
	 */
	protected function default($data=[]){
		$this->plugin('default', $data);
	}
	/**
	 * 保存当前对象中的数据，如不存在即添加
	 * @return bool
	 */
	public function save():bool{
		if($this->id){
			$update=array_diff_assoc($this->data, $this->_data);//对比出需要更新数据
			if(empty($update)) return true;//如果无须更新返回成功
			if($this->field_of_updated) $update[$this->field_of_updated]=time();
			$ok=$this->table()->where(['id'=>$this->id])->update($update)->execute()->ok();
			$this->_data=$this->data;
			if($ok) $this->plugin('update');
		}else{
			if($this->field_of_created) $this->data[$this->field_of_created]=time();
			$this->plugin('before_create');
			$id=$this->table()->create($this->data)->execute()->lastInsertId();
			$ok=$id > 0;
			if($ok){
				$this->data=$this->_data=$this->table()->where(['id'=>$id])->select()->execute()->first();
				$this->id=$this->data['id'];
				$this->plugin('create');
				$this->save();//触发二次保存逻辑
			}
		}
		return $ok;
	}
	/**
	 * 删除当前对象本身的数据，如未记录直接忽略
	 * @return bool
	 */
	public function delete():bool{
		if($this->id > 0){
			$this->_data=$this->data;
			$table=$this->table()->where(['id'=>$this->id]);
			if($this->tombstone && $this->field_of_deleted){//逻辑删除
				$table->update([$this->field_of_deleted=>time()]);
			}else $table->delete();
			$ok=$table->execute()->ok();
			if($ok) $this->plugin('delete');
			return $ok;
		}else{
			$this->id=0;
			return true;
		}
	}
	/**
	 * 更新自身数据
	 * @param array $data
	 * @return $this
	 */
	public function update(array $data):self{
		foreach($data as $key=>$value){
			if(array_key_exists($key, $this->_data)) $this->data[$key]=$value;
		}
		return $this;
	}
	public function output($options=[]):array{
		return [] + $this->data;
	}
}
