<?php
namespace nx\db;

/**
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
class builder{
	/**
	 * @var callable
	 */
	private $_dbcb =null;
	public  $table = null;//表名
	public  $primary = null;//主键

	private $params =[];
	private $args =[];

	private $where ='';
	private $where_params =[];

	public static function factory($Table, $Primary = 'id', $DB =null){
		return new static($Table, $Primary, $DB);
	}
	public function __construct($Table, $Primary = 'id', $DB=null){
		$this->table = $Table;
		$this->primary = $Primary;
		$this->_dbcb = $DB;
	}
	/**
	 * @return \PDO
	 */
	private function db(){
		$cb =$this->_dbcb;
		return $cb();
	}

	/**
	 * 直接插入方法
	 * @param $sql
	 * @param array $params
	 * @return bool|int
	 */
	public function insert($sql, $params=[]){
		if(!empty($params)){
			$ok =$this->db()->exec($sql);
		} else{
			$_first =current($params);
			if(!is_array($_first)){
				$_first =$params;
				$params =[$params];
			}
			$is_named =!isset($_first[0]);

			$ok =$this->_insert($sql, $params, $is_named);
		}
		if($ok) return $this->db()->lastInsertId();
		return false;
	}

	private function _insert($sql, $params =[], $named =false){
		$sth = $this->db()->prepare($sql);
		$ok =false;
		foreach($params as $_fields){
			if($named){
				$_var = [];
				foreach($_fields as $_key => $_val){
					$_var[':' . $_key] = $_val;
				}
			}else $_var = array_values($_fields);
			$ok = $sth->execute($_var);
		}
		return $ok;
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
		$is_named =!isset($_first[0]);

		$col =[];
		$prepare=[];
		foreach($_cols as $_col){
			$col[] ="`{$_col}`";
			$prepare[] =$is_named ?':'.$_col :'?';
		}
		$col =implode(", ", $col);
		$prepare =implode(", ", $prepare);
		$sql ="INSERT INTO {$this->table} ($col) VALUES ($prepare)";

		return $this->_insert($sql, $fields, $is_named);
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

	/**
	 * 更新记录
	 * ->update(['name'=>'vea', 'login'=>[1, '+'], 'count'=>['num', 'COUNT'], 'nickname'=>'`user.name`'])
	 * ->update('name', 'vea')
	 * ->update('sql', [param1, param2])
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
			$_set = implode(', ', $_set);

			if(!empty($this->args['filter'])){
				$_where =' WHERE ' .$this->args['filter'];
				$this->params =array_merge($this->params, $this->where_params);
				$this->args['filter'] ='';
				$this->where_params =[];
			} else $_where ='';

			//$_where = empty($this->args['filter']) ?'' :' WHERE ' .$this->args['filter'];
			$_limit = empty($this->args['limit']) ?'' :$this->args['limit'];

			$sql = "UPDATE `{$this->table}` SET {$_set}{$_where}{$_limit}";
		}
		$sth = $this->db()->prepare($sql);
		if($sth ===false) return false;
		$ok =$sth->execute(!empty($this->params) ?$this->params :null);
		$this->params =[];
		return $ok ?$sth->rowCount() :$ok;
	}

	/**
	 * 返回所有记录
	 * ->read('id', 'name')
	 * ->read(['id', 'name'=>'user', 'info.name', 'count'=>['count', '*']])
	 * ->read(['user'=>['id', 'name'], 'info'=>[]])
	 *
	 * @param array $fields
	 * @return array
	 */
	public function read($fields=[]){
		$_tables =[];
		if(func_num_args() <=1){
			if (is_array($fields) && !empty($fields)){
				if(is_array(current($Fields))) $_tables =$Fields;		//->select(['user'=>['id', 'name'], 'info'=>[]])
				else $_tables[$this->table] =$Fields;					//->select(['id', 'name'=>'user', 'info.name'])
			} elseif(is_string($fields)){
				if((strpos($fields, '`') !==false || strpos($fields, '(') !==false || strpos($fields, ',') !==false)){
					$this->args['select'] =$fields;						//->select("COUNT(*) `COUNT`, `name`")
					return $this;
				} else $_tables[$this->table]=func_get_args();			//->select('id')
			} else $_tables[$this->table]=['*'];							//->select(unknow)
		} else $_tables[$this->table] =func_get_args();					//->select('id', 'name')

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
						$_fs[] =isset($_field[1]) ?"{$_field[0]}(`{$_tab}`.`{$_field[1]}`) `{$_key}`" :"{$_field[0]}() `{$_key}`";
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
		$sql =$this->_buildSELECT($this->table, $this->args);

		$sth = $this->db()->prepare($sql);
		if($sth ===false) return false;
		$ok =$sth->execute(!empty($this->params) ?$this->params :null);
		$this->params =[];
		if($ok ===false) return false;
		return $sth->fetchAll();
	}
	/**
	 * 删除记录
	 * @return int|false		false 或删除的条目数
	 */
	public function delete(){
		return 0;
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
						/*
						if($is_named){
							$this->params[':'.$_col] =$_val[1];
							$_val =':'.$_col;
						} else {
							$this->params[] =$_val[1];
							$_val ='?';
						}*/
					}else{												// ['id'=>[1], 'stutas'=>[0]]
						$_opt = (isset($_val[1])) ?$_val[1] :$_opt;		// ['id'=>[1, '>'], 'stutas'=>[0, '=']]
						$_link =(isset($_val[2])) ?$_val[2] :$link;		//['id'=>[1, '>', 'or'], 'stutas'=>[0, '=', 'or']]
						$_val = $_val[0];
						/*
						if($is_named){
							$this->params[':'.$_col] =$_val[0];
							$_val =':'.$_col;
						} else {
							$this->params[] =$_val[0];
							$_val ='?';
						}*/
					}
				}														//['id'=>1, 'stutas'=>0]
				if(strpos($_col, '.') !==false) list($_tab, $_col) =explode('.', $_col);
				//if(is_string($_val)) $_val =$this->_value($_col, $_val);
				/*if(is_string($_val) && $_val[0] =='`' && $_val[strlen($_val)-1] =='`'){
					$_tab2 =$this->table;
					$_val =substr($_val, 1, -1);
					if(strpos($_val, '.') !==false) list($_tab2, $_val) =explode('.', $_val);
					$_val ="`{$_tab2}`.`{$_val}`";
				}*/
				//$_val =$this->_db()->quote($_val);
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
					case 'not':
					case 'NOT':
					case 'not in':
					case 'NOT IN':
						$_opt ='NOT IN';
					case 'in':
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
					case 'is':
					case 'IS':
						$_opt ='IS';
						$_val =strtoupper($_val);
						break;
					case 'LIKE':
					case 'like':
					case '%':
						$_opt ='LIKE';
						if($is_named){
							$this->params[$_val] ="%".$_val."%";
							$_val =':'.$_col;
						} else {
							$this->params[] ="%".$_val."%";
							$_val ='?';
						}
						//$_val = "%".$_val."%";
						//$_val =$this->_db()->quote($_val);
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
							//$_val =$this->_db()->quote($_val);
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

	private function _buildSELECT($table, $args){
		$get = empty($args['select']) ?"`{$table}`.*" :$args['select'];
		$sort = empty($args['sort']) ?'' :$args['sort'];
		$where = empty($args['filter']) ?'WHERE 1' :' WHERE '.$args['filter'];
		$limit = empty($args['limit']) ?'' :$args['limit'];
		$join = empty($args['join']) ?'' :$args['join'];
		if(is_array($join)){
			$join =[];
			foreach($args['join'] as $_joins){
				list($_join, $_get, $_where) =$_joins;
				$join[] =$_join;
				if(!empty($_get)) $get .=', '.$_get;
				if(!empty($_where)) $where .=' AND '.$_where;
			}
			$join =implode('', $join);
		}
		$group = empty($args['group']) ?'' :$args['group'];
		return "SELECT {$get} FROM `{$table}`{$join}{$where}{$group}{$sort}{$limit}";
	}
	private function _buildUPDATE($table, $args){
		$_where = empty($args['filter']) ?'' :' WHERE '.$args['filter'];
		$_limit = empty($args['limit']) ?'' :$args['limit'];
		return "UPDATE `{$table}` SET {$args['set']}{$_where}{$_limit}";
	}
	private function _buildDELETE($table, $args){
		$_where = empty($args['filter']) ?'' :' WHERE '.$args['filter'];
		$_limit = empty($args['limit']) ?'' :$args['limit'];
		return "DELETE FROM `{$table}`{$_where}{$_limit}";
	}

}
