<?php
/**
 * Created by PhpStorm.
 * User: Vea
 * Date: 2018/12/18 018
 * Time: 13:44
 */
namespace nx\structure;

/**
 * Trait call
 * @package origin\traits
 * @method log($any, $template=false) 输出日志
 * @method main(array $route) 执行默认控制方法
 * @method router() 执行路由方法
 * @method string i18n() 返回对应语言文本
 * @method array|string|null config(string $word, $params=null) 读取配置
 * @method filter($value, $def=null, $filter=null, ...$pattern) 根据filter来对$value进行过滤，默认返回$def
 * @method \nx\db\pdo db($name='default') 根据$app->setup['db/pdo'] 的配置创建pdo对象
 * @method int|false insertSQL($sql, array $params=[], $config='default') 执行插入数据动作->insertSQL('INSERT INTO cds (`interpret`, `titel`) VALUES (?, ?)', ['veas', 'new cd']);
 * @method array|false selectSQL($sql, array $params=[], $config='default') 执行查询数据方法->selectSQL('SELECT `cds`.* FROM `cds` WHERE `cds`.`id` = ?', [13])
 * @method false|int executeSQL($sql, array $params=[], $config='default') 执行默认控制方法->executeSQL('UPDATE `cds` SET `interpret` =? WHERE `cds`.`id` = ?', ['vea', 14])
 * @method \nx\db\sql table($name, $primary='id', $config='default') 返回一个sql对象
 * @method true|array|int transaction(callable $fun) 返回事务
 * @method \object di($name, ...$args) 返回一个注入对象
 * @method in() 返回全部输入内容
 * @method out(string $string) 设置默认输出方法
 * @method initTraits() 初始化引用的trait
 * @property \nx\input  $in  输入
 * @property \nx\output $out 输出
 * @property \nx\container $container 容器
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