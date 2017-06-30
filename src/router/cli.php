<?php
/**
 * Created by PhpStorm.
 * User: Vea
 * Date: 2017/06/29 029
 * Time: 17:28
 */

namespace nx\router;

trait cli{
	/**
	 * 设定执行参数解析
	 * shell> php example.php -f "value for f" -v -a --required value --optional="optional value" --option
	 * ['f'=>'value for f', 'v'=>false, 'a'=>false, 'required'=>'value', 'optional'=>'optional value', 'option'=>false]
	 *
	 * @param string $options a-zA-Z0-9 f: Required value; v:: Optional value; abc These options do not accept values
	 * @param array  $longopts required:Required value; optional:: Optional value; option No value
	 *
	 * @return $this
	 */
	public function args(string $options='', array $longopts=[]){
		$this->buffer['router/cli']=['args'=>$options, 'long'=>$longopts];
		return $this;
	}
	public function router(){
		if(!isset($this->buffer['router/cli'])) $this->buffer['router/cli']=isset($this->setup['router/cli'])
			?$this->setup['router/cli']
			:['args'=>'', 'long'=>[]];

		$set =&$this->buffer['router/cli'];
		$args =array_merge($this->request['params'], getopt($set['args'], $set['long']));
		return call_user_func_array([$this, 'control'], [$args]);
	}
}