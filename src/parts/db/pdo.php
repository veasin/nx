<?php
declare(strict_types=1);
namespace nx\parts\db;

/**
 * Class pdo
 * @trait app
 * @package nx\db
 */
trait pdo{
	protected $db_pdos=[];//缓存
	/**
	 * @param string $name app->setup['db/pdo']
	 * @return \nx\helpers\db\pdo
	 */
	public function db($name='default'){
		if(!array_key_exists($name, $this->db_pdos)){
			$config =($this->setup['db/pdo'] ??[])[$name] ?? null;
			if(null ===$config) $this->throw("db[{$name}] config error.");
			$this->db_pdos[$name]= new \nx\helpers\db\pdo($config);
		}
		return $this->db_pdos[$name];
	}
}