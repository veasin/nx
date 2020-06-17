<?php
namespace nx\helpers\model;

use nx\parts\callApp;
use nx\parts\db\table;
use nx\parts\model\cache;

/**
 * 群组数据
 * @package life\models\content
 */
class multiple{
	use callApp, cache, table;

	protected $single=null;
	/**
	 * 私有方法 返回单条数据
	 * @param array $conditions 查询条件
	 * @param array $options
	 * @return array|null
	 */
	protected function _find(array $conditions=[], array $options=[]):?array{
		$table=$this->table()->select()->where($conditions);
		if(array_key_exists('FIND', $options) && is_callable($options['FIND'])) call_user_func($options['FIND'], $table, $conditions, $options);
		return $table->execute()->first($this->single);
	}
	/**
	 * 私有方法 返回多条数据
	 * @param array $conditions
	 * @param array $options
	 * @return array
	 */
	protected function _list(array $conditions=[], array $options=[]):array{
		$table=$this->table();
		$table->select($table::COUNT('*')->as('COUNT'));
		if(count($conditions)) $table->where($conditions);
		if(array_key_exists('COUNT', $options) && is_callable($options['COUNT'])) call_user_func($options['COUNT'], $table, $conditions, $options);
		$ok=$table->execute()->first();
		$count=null !== $ok ?$ok['COUNT'] :0;
		if($count > 0){
			if(array_key_exists('sort', $options)) $table->sort($options['sort'], 'DESC');
			if(array_key_exists('page', $options)) $table->page($options['page'] ?? 1, $options['max'] ?? 10);
			$table->select(array_key_exists('select', $options) ?$options['select'] :[]);
			if(array_key_exists('LIST', $options) && is_callable($options['LIST'])) call_user_func($options['LIST'], $table, $conditions, $options);
			if(array_key_exists('MAP', $options) && is_callable($options['MAP'])){
				$list=$table->execute()->fetchMap($options['MAP']);
			}else $list=$table->execute()->fetchAll();
		}else $list=[];
		return ['count'=>$count, 'list'=>$list];
	}
	/**
	 * 返回数据列表
	 * @param array $conditions 查询条件
	 * @param array $options    支持 sort 排序参数 page 翻页 [page, max]
	 * @return array
	 */
	public function list($conditions=[], $options=[]):array{
		return $this->_list($conditions, $options);
	}
}

