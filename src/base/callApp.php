<?php
/**
 * Created by PhpStorm.
 * User: Vea
 * Date: 2018/12/18 018
 * Time: 13:44
 */
namespace nx\base;

/**
 * Trait callApp
 * @package nx\base
 * @method log($any, $template=false) 输出日志
 * @method string i18n() 返回对应语言文本
 * @method array|string|null config(string $word, $params=null) 读取配置
 * @method filter($vars=[], $options=[]) 过滤器，对输入进行过滤。可指定输入内容来源或设置来源数组。('id', ['int', 'default']) (['id'=>['int', 'default']])
 * @method \PDO db($name='default') 根据$app->setup['db/pdo'] 的配置创建pdo对象
 * @method int|false insertSQL($sql, array $params=[], $config='default') 执行插入数据动作->insertSQL('INSERT INTO cds (`interpret`, `titel`) VALUES (?, ?)', ['veas', 'new cd']);
 * @method array|false selectSQL($sql, array $params=[], $config='default') 执行查询数据方法->selectSQL('SELECT `cds`.* FROM `cds` WHERE `cds`.`id` = ?', [13])
 * @method false|int executeSQL($sql, array $params=[], $config='default') 执行默认控制方法->executeSQL('UPDATE `cds` SET `interpret` =? WHERE `cds`.`id` = ?', ['vea', 14])
 * @method \nx\db\table\sql table($name, $primary='id', $config='default') 返回一个sql对象
 * @method true|array|int transaction(callable $fun) 返回事务
 * @property \nx\request request 输入对象
 * @property \nx\response response 输出对象
 * @method request() 返回全部输入内容
 * @method response(array|string $string) 设置默认输出方法
 * @method in() 返回全部输入内容
 * @method out(array|string $string) 设置默认输出方法
 * @property \nx\input in 输入对象
 * @property \nx\output out 输出对象
 * @method string getPath(string $subPath) 获取相对app的目录
 * @method string getUUID() 获取唯一id
 */
trait callApp{
	/**
	 * 魔术方法，属性转发到app上 $this->something
	 * @param string $name
	 * @return mixed
	 */
	public function __get($name){
		$this->$name=&\nx\app::$instance->$name;
		return $this->$name;
	}
	/**
	 * 魔术方法，所有未知方法调用都转发到app上 $this->something()
	 * @param string $name
	 * @param array  $args
	 * @return mixed
	 */
	public function __call($name, $args){
		return call_user_func_array([\nx\app::$instance, $name], $args);
	}
}