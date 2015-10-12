<?php
namespace nx;

class o2 implements \ArrayAccess, \Countable, \IteratorAggregate{
	protected $data = [];
	protected $_hasSet =false;
	public function __construct($data = []){
		$this->data = $data;
		$this->_hasSet =(func_num_args()>0);
	}
	//function __destruct(){ }
	public function set($data){
		$this->data = $data;
		$this->_hasSet =true;
		return $this;
	}
	public function get(){return $this->data;}
	public function has($name = null){ return is_null($name) ?!empty($this->data) :isset($this->data[$name]); }
	public function merge($data){ $this->data = array_merge($this->data, $data); }
	public function clear(){$this->data = [];return $this;}
	//Countable
	public function count(){return count($this->data);} //->count($this)
	//IteratorAggregate
	public function getIterator() {return new ArrayIterator($this->data);} //foreach($this as ..)
	//ArrayAccess
	public function offsetSet($offset, $value){
		$this->data[$offset] = $value;
		$this->_hasSet =true;
	}		//$this['xx'] ='xx'
	public function &offsetGet($offset){ return $this->data[$offset]; }				//=$this['zz']
	public function offsetExists($offset){ return isset($this->data[$offset]); }		//isset($this['xx']
	public function offsetUnset($offset){ unset($this->data[$offset]); }				//unset($this['xx']
	//Serializable
	public function __sleep(){ return ['data']; }										//serialize($this)
	//php5.2+?
	public function __toString(){ return !empty($this->data) ?json_encode($this->data, JSON_UNESCAPED_UNICODE) :'';} //echo $this
	//Class overloading
	//public function &__get($offset){ return $this->offsetGet($offset); }				// ->name
	//public function __set($offset, $value){ $this->offsetSet($offset, $value); }		// ->name =xxx
	//public function __isset($offset){ return $this->offsetExists($offset); }			//isset(->name) empty(->name)
	//public function __unset($offset){ $this->offsetUnset($offset); }					//unset(->name)
	/*
	public function __call($name, $args){												//->x('y')->a('b')-c()
		switch(count($args)){
			case 0:
				return $this->offsetGet($name);											//$this->name()
			case 1:
				$this->offsetSet($name, $args[0]);										//$this->name($value)
				return $this;
			default:
				$this->offsetSet($name, $args);											//$this->name(value1, ...)
				return $this;
		}
	} // name(), name(value), name(value1, ...)
	//php5.3
	public function __invoke(){
		switch(func_num_args()){
			case 0:
				return $this->data;
			case 1:
				$this->offsetGet(func_get_arg(0));
				return $this;
			default:
				$this->offsetSet(func_get_arg(0), func_get_arg(1));
				return $this;
		}
	} //(), (name), (name, value)
	*/
	//php5.1
	static public function __set_state($data){
		return new static($data);
	} //var_export
	//php5.6
	public function __debugInfo(){ return $this->data; } //php5.6 var_dump
}
