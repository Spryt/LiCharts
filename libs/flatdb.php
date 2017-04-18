<?php
/**
 * Небольшая библиотека для хранения данных в файлах в виде массива
 *
 * @author     Spryt, <me@spryt.ru>, http://spryt.ru/
 * @link       http://spryt.ru/
 */

class Flatdb {

	private $db;
	private $datafile;

	function __construct($database,$path) {

		$this->datafile = $path.$database.".php";

		if(!file_exists($this->datafile))
			file_put_contents($this->datafile, "<?php/*");

		if(!is_readable($this->datafile) || !is_writable($this->datafile)) 
			die("<title>Ошибка</title><meta charset=\"utf-8\"><p style='text-align: center'>Файл ".$this->datafile." недоступен для чтения/записи. Проверьте права доступа к файлу и папке с файлами данных.");

		$this->open_db();
	}


	public function find_all() {
		return $this->db;
	}

	public function find($arr_find) {
		$el=$this->find_by($arr_find);

		return ($el!==false) ? $this->db[$el] : false;
	}

	public function select($arr_find) {
		$els=$this->find_by($arr_find,true);

		$return = array();
		foreach ($els as $val) {
			$return[]=$this->db[$val];
		}

		return $return;

		//return (count($return)>0) ? $return : false;
	}

	public function insert($array) {
		$this->db[]=$array;
		$this->save_db();
	}

	public function update($arr_find,$arr_update) {

		$el_id = (is_array($arr_find)) ? $this->find_by($arr_find) : $arr_find;
		$el = $this->db[$el_id];

		foreach($arr_update as $k=>$v) {

			//if(!isset($el[$k])) $el[$k] = $v;
			//$el[$k] = (is_array($el[$k])) ? array_unique(array_merge($el[$k],$v)) : $v;
			//$el[$k] = (is_array($el[$k])) ? array_unique(array_merge($el[$k],$v)) : $v;
			$el[$k] = $v;
		}

		$this->db[$el_id]=$el;
		$this->save_db();
	}

	public function delete($arr_find) {
		$el_id = (is_array($arr_find)) ? $this->find_by($arr_find) : $arr_find;
		unset($this->db[$el_id]);
		$this->save_db();
	}

	public function flush_db() {
		$this->db = array();
		$this->save_db();
	}

	#Поиск элементов в массиве с нужным ключем (только одним)
	private function find_by($arr, $return_array = false) {
		$key=key($arr);
		$find = $arr[$key];
		if($return_array) $return = array();

		foreach ($this->db as $k => $value) {
			foreach ($value as $kk => $v) {
				if($kk == $key && $v == $find) {
					if($return_array) 
						$return[]=$k;
					else 
						return $k;
				}
			}
		}

		return ($return_array) ? $return : false;
	}


	#Открытие файла БД и загрузка
	private function open_db() {
		$data = substr(file_get_contents($this->datafile),7);
		$db = unserialize($data);
		if(empty($db)) $db = array();
		$db = array_values($db);
		$this->db = $db;
	}

	private function save_db() {
		file_put_contents($this->datafile, "<?php/*".serialize($this->db));
	}
}