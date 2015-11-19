<?php
namespace nx\db;

/**
 *
 * 读取所有 read[select] : get read getAll select all
 * 读取一列 first : getOne readOne first one
 * 一列字段 pluck =>readOne('col')
 * 读取某列 lists =>read('col1', 'col2') =>select('col1', 'col2')->get()
 * 查找    find where filter query
 * 添加    create[insert]
 * 删除    remove[delete]
 * 更新    save[update]
 * 翻页    limit page chunk skip&take
 * 连接    join
 * 分组    group groupBy
 * 总数    count
 * 顺序    order orderBy
 *
 *
 * $this->create([]);
 * $this->find(15)->get('id', 'name')
 * $this->find(['id'=>15])->select('id', 'name')->get()
 *
 * $buffer
 * $this->where($something)->limit()->sort()->read()
 *
 * $buffer[] =['where', [$something]]
 * $buffer[] =['limit']
 * $buffer[] =['sort']
 * $buffer[] =['read']
 *
 * $buffer => $cache
 *
 * Class sql
 * @package nx\db
 */
class sql{
	/**
	 * @var \nx\mvc\model
	 */
	private $_model =null;
	private $_config ='default';

	public  $table = null;//表名
	public  $primary = null;//主键

	private $params =[];
	private $args =[];

	private $where_params =[];
	static public $history =[];

	static public function factory($Table, $Primary = 'id', $config='default', $model =null){
		return new static($Table, $Primary, $config, $model);
	}
	public function __construct($Table, $Primary = 'id', $config='default', $model =null){
		$this->table = $Table;
		$this->primary = $Primary;

		$this->_config =$config;
		$this->_model =$model;
	}

	/**
	 * 添加新纪录， 只针对当前表进行操作
	 * ->create([field=>value, ...]);            标准调用方式
	 * ->create([[],[],[]]);    同时多条记录插入
	 *
	 * @param array $fields
	 * @return bool|int			最后插入id或false
	 */
	public function create($fields=[]){
		if(!is_array($fields) || empty($fields)){
			return false;
		}

		$_first =current($fields);
		if(!is_array($_first)){
			$_first =$fields;
			$fields =[$fields];
		}
		$_cols =array_keys($_first);
		//$is_named =!isset($_first[0]);
		$is_named =false;

		$col =[];
		$prepare=[];
		foreach($_cols as $_col){
			$col[] ="`{$_col}`";
			$prepare[] =$is_named ?':'.$_col :'?';
		}
		$col =implode(", ", $col);
		$prepare =implode(", ", $prepare);
		$sql ="INSERT INTO {$this->table} ($col) VALUES ($prepare)";
		$this->args =[];
		$params = [];
		foreach($fields as $_k => $_v){
			if(!$is_named){
				$params[$_k] =array_values($_v);
			} else {
				$_cols =[];
				foreach($_v as $_c => $_val){
					$_cols[':'.$_c] =$_val;
				}
				$params[$_k] =$_cols;
			}
		}
		$fields =$params;
		static::$history[] =$sql;
		$result =$this->_model->insertSQL($sql, $fields, $this->_config);
		$this->params =[];
		return $result;
	}

	/**
	 * 更新记录
	 * ->update(['name'=>'vea', 'login'=>[1, '+'], 'count'=>['num', 'COUNT'], 'nickname'=>'`user.name`'])
	 * ->update('name', 'vea')
	 *
	 * @param array $fields
	 * @param bool|false $value
	 * @return bool|int					false或修改的条目数
	 */
	public function update($fields=[], $value=false){
		$this->params =[];
		$is_named =false;
		$sql ='';

		$_fields =[];
		if(is_array($fields)){
			$_fields =$fields;					//->update(['name'=>'vea', 'login'=>[1, '+'], 'count'=>['num', 'COUNT'], 'nickname'=>'`user.name`'])
		} elseif(is_string($fields)){
			if(func_num_args() ==2){
				if(is_array($value)){
					$this->params =$value;
					$sql =$fields;
					$is_named =!isset($value[0]);
				} else $_fields[$fields] =$value;			//->update('name', 'vea')
			} else return false;
		} else return false;

		if(empty($sql)){
			$_set = [];
			foreach($_fields as $_field => $_value){
				if(!is_array($_value)){
					$_val = $this->_value($_field, $_value, $is_named);
				}else{
					list($_val, $_opt) = $_value;
					switch($_opt){
						case '+':
						case '-':
						case '*':
						case '/':
							$_val = $this->_value($_field, $_val, $is_named);
							$_val = "`{$this->table}`.`{$_field}` {$_opt} '$_val'";
							break;
						default:// sql function
							$_val = $this->_value($_field, $_val, $is_named);
							$_val = "{$_opt}({$_val})";
							break;
					}
				}
				$_set[] = "`{$_field}` ={$_val}";
			}
			if(empty($_set)) return false;
			$this->args['set'] = implode(', ', $_set);
			$sql =$this->_buildUPDATE();
		}
		static::$history[] =$sql;
		$result = $this->_model->executeSQL($sql, $this->params, $this->_config);
		$this->params =[];
		return $result;
	}

	/**
	 * 返回所有记录
	 * ->read('id', 'name')
	 * ->read(['id', 'name'=>'user', 'info.name', 'count'=>['count', '*']])
	 * //->read(['user'=>['id', 'name'], 'info'=>[]]) 废弃
	 *
	 * @param array $fields
	 * @return array
	 */
	public function read($fields=[]){
		$this->params =[];
		if(!empty($fields)) $this->_withSELECT(func_get_args(), func_num_args());
		$sql =$this->_buildSELECT();
		static::$history[] =$sql;
		$result =$this->_model->selectSQL($sql, $this->params, $this->_config);
		$this->params =[];
		return $result;
	}

	public function first($field =false){
		$this->params =[];
		$this->limit(1);
		if(!empty($field)) $this->select($field);
		$sql =$this->_buildSELECT();
		static::$history[] =$sql;
		$result =$this->_model->selectSQL($sql, $this->params, $this->_config);
		if($result ===false) return $result;
		$this->params =[];
		if(is_array($field)){
			if(count($field)==1){
				list($_col, $_val)=each($field);
				if($_col!==0) $field=$_col;
			} else $field =false;
		}
		$first =current($result);
		if($field!==false && isset($first[$field])) return $first[$field];
		return $first;
	}

	/**
	 * 删除记录
	 * @return int|false		false 或删除的条目数
	 */
	public function delete(){
		$this->params =[];
		$sql =$this->_buildDELETE();
		static::$history[] =$sql;
		$result = $this->_model->executeSQL($sql, $this->params, $this->_config);
		$this->params =[];
		return $result;
	}

	/**
	 * ->select('id', 'name')
	 * ->select(['id', 'name'=>'user', 'info.name', 'count'=>['count', '*']])
	 * ->select(['user'=>['id', 'name'], 'info'=>[]])
	 *
	 * @param array $fields
	 * @return $this
	 */
	public function select($fields =[]){
		$this->_withSELECT(func_get_args(), func_num_args());
		return $this;
	}

	/**
	 * 可多次调用，每次 AND()
	 * ->where(1)->where('id', 1)->where('user.id', 1, '>', 'or)
	 * ->where([['id', 1, '>', 'or'], ['stutas', 0, '=', 'or']])
	 * ->where(['id'=>1, 'stutas'=>[0, '>'], 'user.name'=>['a', 'like', 'or']], 'AND')
	 * @param $conds
	 * @return $this
	 */
	public function where($conds){
		return $this->_withWHERE(func_get_args(), func_num_args());
	}
	/**
	 * 继承 select, filter
	 * join(\nx\db\sql, ['id'])->join('user', ['id'=>'user_id'])->join('user', ['user.id'=>'editor.user_id'])
	 *
	 * @param string|\nx\db\sql $Table //表名
	 * @param null   $Conditions
	 * @param string $Join
	 * @return $this
	 */
	public function join($Table, $Conditions=null, $Join='LEFT'){
		$_table = (is_object($Table)) ?$Table->table :$Table;
		if(strpos($_table, ' ') !==false){
			list($_table, $_as) =explode(' ', $_table);
			$_as =" `{$_as}`";
		} else $_as ='';
		$s = " {$Join} JOIN `{$_table}`".$_as;
		if(is_array($Conditions)){
			$_c =[];
			foreach($Conditions as $Row => $As){
				$__table =$_table;
				$__row =is_numeric($Row) ?$As :$Row;
				if(strpos($Row, '.') !==false) list($__table, $__row) =explode('.',$Row);
				$__table2 =$this->table;
				$__as =$As;
				if(strpos($As, '.') !==false) list($__table2, $__as) =explode('.',$As);
				$_c[] = "`{$__table}`.`{$__row}` = `{$__table2}`.`{$__as}`";
			}
			$s .= " ON (".implode(' AND ', $_c).")";
		}
		elseif(is_string($Conditions)) $s .= $Conditions;

		$_join =[$s, '', ''];
		if(is_object($Table)){
			$_join[1] =empty($Table->args['select']) ?'' :$Table->args['select'];
			$_join[2] =empty($Table->args['filter']) ?'':$Table->args['filter'];
		}
		$this->args['join'][] = $_join;
		return $this;
	}
	/**
	 * ->sort()->sort(`create`, `desc`)->sort(['create'=>'desc', 'upload.last'->'asc'])
	 *
	 * @param bool $field
	 * @param bool $asc
	 * @return $this
	 */
	public function sort($field =false, $asc =true){
		$_sorts =[];
		if(is_array($field)) $_sorts =$field;
		else $_sorts[($field ===false) ?$this->primary :$field] =$asc;

		$_s =[];
		foreach($_sorts as $_field =>$_asc){
			$_tab =$this->table;
			$_sort ='ASC';
			if(strpos($_field, '.') !==false)  list($_tab, $_field) =explode('.', $_field);

			if(is_bool($_asc)) $_sort =($_asc) ?'ASC' :'DESC';
			elseif(is_string($_asc)){
				$_sort =(strtolower($_asc[0]) =='a') ?'ASC' :'DESC';
			}
			$_s[] =$_field[0]=='`' ?"{$_field} {$_sort}" :"`{$_tab}`.`{$_field}` {$_sort}";
		}
		$this->args['sort'] = " ORDER BY ".implode(", ", $_s);
		return $this;
	}
	/**
	 * 分页
	 * @param int $Rows 查询返回行数
	 * @param int  $Offset 查询起始行数
	 * @return $this
	 */
	public function limit($Rows =false, $Offset = 0){
		$this->args['limit'] = empty($Rows) ?''
			:((func_num_args() == 1) ?" LIMIT {$Rows}" :" LIMIT {$Offset}, {$Rows}");
		return $this;
	}
	/**
	 * @param $field
	 * @return $this
	 */
	public function group($field){
		$_tab =$this->table;
		if(strpos($field, '.') !==false) list($_tab, $field) =explode('.', $field);
		$this->args['group'] =" GROUP BY `{$_tab}`.`{$field}`";
		return $this;
	}

	/*--------------- build ----------------------------------------------------------*/
	/**
	 *
	 * todo: bug need fix (1 "." in $_col 2 the last $_val
	 *
	 * @param $Args
	 * @param $Num
	 * @return $this
	 */
	private function _withWHERE($Args, $Num){
		$is_named =false;

		if($Num ==0) return $this;
		$conds =$Args[0];
		if(!isset($this->args['filter']))$this->args['filter'] ='';
		$_conds =[];
		$link ='AND';

		if(is_array($conds)){
			$_conds =$conds;
			if($Num >1) $link =$Args[1];
		} else{
			switch($Num){
				case 0:
					return $this;
					break;
				case 1://(1)
					if(strpos($conds, '`')!==false || strpos($conds, '=')!==false || strpos($conds, '(')!==false){
						$this->args['filter'] .=empty($this->args['filter']) ?"({$conds})" :" {$link} ({$conds})";
						return $this;
					}
					$_conds =[$this->primary=>$conds];
					break;
				default://(id, 1) ('id', 1, '>') ('id', 1, '>', 'or)
					$_conds[] =$Args;
					if($Num ==4) $link =$Args[3];
					break;
			}
		}
		if(!empty($_conds)){
			$_where ='';
			foreach($_conds as $_col => $_val){
				$_opt = '=';
				$_link =$link;
				$_tab =$this->table;
				if(is_array($_val)){
					if(is_numeric($_col)){								//[['id', 1], ['stutas', 0]]
						$_opt = (isset($_val[2])) ?$_val[2] :$_opt;		//[['id', 1, '>'], ['stutas', 0, '=']]
						$_col = $_val[0];
						$_link =(isset($_val[3])) ?$_val[3] :$link;		//[['id', 1, '>', 'or'], ['stutas', 0, '=', 'or']]
						$_val = $_val[1];
					}else{												// ['id'=>[1], 'stutas'=>[0]]
						$_opt = (isset($_val[1])) ?$_val[1] :$_opt;		// ['id'=>[1, '>'], 'stutas'=>[0, '=']]
						$_link =(isset($_val[2])) ?$_val[2] :$link;		//['id'=>[1, '>', 'or'], 'stutas'=>[0, '=', 'or']]
						$_val = $_val[0];
					}
				}														//['id'=>1, 'stutas'=>0]
				if(strpos($_col, '.') !==false) list($_tab, $_col) =explode('.', $_col);
				$_opt =strtoupper($_opt);
				switch($_opt){
					case '+':
					case '-':
					case '*':
					case '/':
					case '+=':
					case '-=':
					case '*=':
					case '/=':
						$_opt = "=`{$_tab}`.`{$_col}` {$_opt[0]}";
						$_val =$this->_value($_col, $_val);
						break;
					//case 'not':
					case 'NOT':
					//case 'not in':
					case 'NOT IN':
						$_opt ='NOT IN';
					//case 'in':
					case 'IN':
						$__val =[];
						foreach($_val as $_k =>$_v){
							if($is_named){
								$__val[] =':in'.$_k;
								$this->params[':in'.$_k] =$_v;
							} else {
								$__val[] ='?';
								$this->params[] =$_v;
							}
						}
						$_val = " ('".implode("','", $__val)."')";
						break;
					//case 'is':
					case 'IS':
						$_opt ='IS';
						$_val =strtoupper($_val);
						break;
					case 'LIKE':
					//case 'like':
					case '%':
						$_opt ='LIKE';
						if($is_named){
							$this->params[$_val] ="%".$_val."%";
							$_val =':'.$_col;
						} else {
							$this->params[] ="%".$_val."%";
							$_val ='?';
						}
						break;
					default:
						if(strpos($_val, '(') === false){
							if($is_named){
								$this->params[$_col] =$_val;
								$_val =':'.$_col;
							} else {
								$this->params[] =$_val;
								$_val ='?';
							}
						}
						break;
				}
				if(!empty($_where)) $_where .=' '.strtoupper($_link).' ';
				$_where .= (is_numeric($_col))
					?$_val											//['id >1', 'stutas =0']
					:"`{$_tab}`.`{$_col}` {$_opt} {$_val}";
			}
			if(!empty($_where))$this->args['filter'] .=empty($this->args['filter']) ?"({$_where})" :" {$link} ({$_where})";
			$this->where_params =$this->params;
		}
		return $this;
	}
	private function _withSELECT($args =[], $nums){
		$_tables =[];

		switch($nums){
			case 0:
				$_tables[$this->table]=['*'];								//->select()
				break;
			case 1:
				$fields =$args[0];
				if (is_array($fields) && !empty($fields)){
					//if(is_array(current($fields))) $_tables =$fields;		//->select(['user'=>['id', 'name'], 'info'=>[]])
					//else
					$_tables[$this->table] =$fields;					//->select(['id', 'name'=>'user', 'info.name'])
				} elseif(is_string($fields)){
					if((strpos($fields, '`') !==false || strpos($fields, '(') !==false || strpos($fields, ',') !==false)){
						$this->args['select'] =$fields;						//->select("COUNT(*) `COUNT`, `name`")
						return $this;
					} else $_tables[$this->table]=$args;					//->select('id')
				}
				break;
			default:
				$_tables[$this->table] =$args;								//->select('id', 'name')
				break;
		}

		$_fs =[];
		foreach($_tables as $_table =>$_fields){						//[tab1=>fields, tab2=>fields]
			foreach($_fields as $_key =>$_field){						//$_fields =['tab.field', 'field']
				$_tab =$_table;
				if(is_numeric($_key)){
					if(strpos($_field, '.') !==false) list($_tab, $_field) =explode('.', $_field); //['tab.field']
					$_field =($_field =='*') ?$_field :"`{$_field}`";
					$_fs[] ="`{$_tab}`.{$_field}";
				} else{
					if(is_array($_field)){								//$_fields =['count'=>['count', '*']]
						if(isset($_field[2])) $_tab =$_field[2];
						if($_field[1] =='*' || $_field[1][0] =='`'){
							$_col =$_field[1];
						} else $_col ="`{$_tab}`.`{$_field[1]}`)";
						$_fs[] =isset($_field[1]) ?"{$_field[0]}({$_col}) `{$_key}`" :"{$_field[0]}() `{$_key}`";
					}else{												//$_fields =['tab.field'=>'field', 'COUNT(*)'=>'field']
						if(strpos($_key, '(') !== false){
							$_fs[] = "{$_key} `{$_field}`";
						} else{
							if(strpos($_key, '.') !== false) list($_tab, $_key) = explode('.', $_key);
							$_fs[] = "`{$_tab}`.`{$_key}` `{$_field}`";
						}
					}
				}
			}
		}
		$this->args['select'] =implode(', ', $_fs);
		//return $this;
	}

	private function _buildSELECT(){
		$get = empty($this->args['select']) ?"`{$this->table}`.*" :$this->args['select'];
		$sort = empty($this->args['sort']) ?'' :$this->args['sort'];
		//$where = empty($this->args['filter']) ?'' :' WHERE '.$this->args['filter'];
		if(!empty($this->args['filter'])){
			$where =' WHERE ' .$this->args['filter'];
			$this->params =array_merge($this->params, $this->where_params);
			$this->args['filter'] ='';
			$this->where_params =[];
		} else $where ='';
		$limit = empty($this->args['limit']) ?'' :$this->args['limit'];
		$join = empty($this->args['join']) ?'' :$this->args['join'];
		if(is_array($join)){
			$join =[];
			foreach($this->args['join'] as $_joins){
				list($_join, $_get, $_where) =$_joins;
				$join[] =$_join;
				if(!empty($_get)) $get .=', '.$_get;
				if(!empty($_where)) $where .=' AND '.$_where;
			}
			$join =implode('', $join);
		}
		$group = empty($this->args['group']) ?'' :$this->args['group'];
		$this->args=[];
		return "SELECT {$get} FROM `{$this->table}`{$join}{$where}{$group}{$sort}{$limit}";
	}
	private function _buildUPDATE(){
		if(!empty($this->args['filter'])){
			$_where =' WHERE ' .$this->args['filter'];
			$this->params =array_merge($this->params, $this->where_params);
			$this->args['filter'] ='';
			$this->where_params =[];
		} else $_where ='';
		//$_where = empty($args['filter']) ?'' :' WHERE '.$args['filter'];
		$_limit = empty($this->args['limit']) ?'' :$this->args['limit'];
		$_set =$this->args['set'];
		$this->args=[];
		return "UPDATE `{$this->table}` SET {$_set}{$_where}{$_limit}";
	}
	private function _buildDELETE(){
		if(!empty($this->args['filter'])){
			$_where =' WHERE ' .$this->args['filter'];
			$this->params =array_merge($this->params, $this->where_params);
			$this->args['filter'] ='';
			$this->where_params =[];
		} else $_where ='';
		$_limit = empty($this->args['limit']) ?'' :$this->args['limit'];
		$this->args =[];
		return "DELETE FROM `{$this->table}`{$_where}{$_limit}";
	}

	/**
	 * 解析赋值 "val", "`col`", "`tab.col`"
	 * @param $field
	 * @param $value
	 * @param bool|false $is_named
	 * @return string
	 */
	private function _value($field, $value, $is_named =false){
		$value =(string)$value;		// =>string
		$_len =strlen($value);
		if($_len >0){
			if($value[0] =='`' && $value[$_len-1] =='`'){
				$_tab2 =$this->table;
				$_val =substr($value, 1, -1);
				if(strpos($value, '.') !==false) list($_tab2, $_val) =explode('.', $value, 2);
				$_val ="`{$_tab2}`.`{$_val}`";
			} else {
				if($is_named){
					$_val = ':' . $field;
					$this->params[$field] = $value;
				} else {
					$_val ='?';
					$this->params[] =$value;
				}
			}
		} else $_val="''";
		return $_val;
	}
	/*--------------- 别称 或快捷方法 ----------------------------------------------------------*/
	/**
	 * 返回记录总数
	 * @param string $field
	 * @return int
	 */
	public function count($field='*'){
		return (int)$this->first(['count'=>['count', $field]]);
	}
	/**
	 * 排序
	 *  0:$this->table.primary ASC
	 *  1:string:$this->table.{1} ASC
	 *  2:string,string:$this->table.{1} {2}
	 *  3:string,string,string:{1}.{2} {3}
	 *  1:array:
	 *    [0=>[$k=>$v],1=>[$k=>$v],...]:$this->table.$k $v
	 *    [$t=>[$k=>$v],$t=>[$k=>$v],...]:$t.$k $v
	 * ?2：string,array:[0=>[$k=>$v],1=>[$k=>$v],...]:{1}.$k $v
	 *
	 * @param bool|false $field 省却为按照主键排序，
	 * @param bool|true $asc
	 * @return sql
	 */
	public function orderBy($field =false, $asc =true){
		return $this->sort($field, $asc);
	}
	/**
	 * 分页
	 * @param int $Page 从第1页开始
	 * @param int $Max 每页条数
	 * @return sql
	 */
	public function page($Page = 1, $Max = 15){
		$Rows = $Max;
		$Offset = ($Page - 1)*$Max;
		return $this->limit($Rows, $Offset);
	}
	/**
	 * 过滤
	 * @param unknown $Conds 过滤条件 (1), ('id', 1), ('id', 1, '>'), (['id', 1], ['id', 1, '>'], 'id >1', 'id'=>1, 'id'=>[1, '>'])
	 *                                    1个参数
	 *                                        非数组设置主键为此值
	 *                                        数组按照key为字段名value为值依次过滤
	 *                                    2个参数
	 *                                        1为字段名 2为此字段值
	 * @return sql
	 */
	public function filter($Conds){
		return $this->_withWHERE(func_get_args(), func_num_args());
	}
	/**
	 * @param string $Fields
	 * @return sql
	 */
	public function groupBy($Fields){
		return $this->group($Fields);
	}
}
