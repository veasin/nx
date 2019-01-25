<?php
/**
 * Created by PhpStorm.
 * User: Vea
 * Date: 2019/1/21 021
 * Time: 11:02
 */
namespace nx\structure\db;

/**
 * Class pdo
 * @trait app
 * @package nx\db
 */
trait pdo{
	protected $pdo_dbs=[];//缓存
	/**
	 * @param string $name app->setup['db/pdo']
	 * @return \PDO
	 */
	public function db($name='default'){
		if(!array_key_exists($name, $this->pdo_dbs)) $this->pdo_dbs[$name]=$this->container->create('db');
		return $this->pdo_dbs[$name];
	}
}