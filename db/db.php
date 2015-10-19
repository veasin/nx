<?php
namespace nx\db;

class db{

	/**
	 * @return bool
	 */
	static public function close(){
		return false;
	}

	/**
	 * $results = db::select('select * from users where id = ?', [1]);
	 * $results = db::select('select * from users where id = :id', ['id' => 1]);
	 *
	 * @param $sql
	 * @param array $params
	 * @return array
	 */
	static public function select($sql, $params=[]){

		return [];
	}

	/**
	 * db::insert('insert into users (id, name) values (?, ?)', [1, 'Dayle']);
	 *
	 * @param $sql
	 * @param array $params
	 * @return int
	 */
	static public function insert($sql, $params=[]){
		return 0;
	}

	/**
	 * DB::update('update users set votes = 100 where name = ?', ['John']);
	 *
	 * @param $sql
	 * @param array $params
	 * @return int
	 */
	static public function update($sql, $params=[]){
		return 0;
	}

	/**
	 * DB::delete('delete from users');
	 *
	 * @param $sql
	 * @param array $params
	 * @return int
	 */
	static public function delete($sql, $params=[]){
		return 0;
	}

	/**
	 * DB::statement('drop table users');
	 *
	 * @param $sql
	 * @return bool
	 */
	static public function statement($sql){
		return true;
	}

	/**
	 * DB::transaction(function(){}}
	 *
	 * @param callable $callback
	 */
	static public function transaction(Callable $callback){

	}

}