<?php
/**
 * Каркас для маленьких скриптов, с шаблонизатором
 *
 * @author     Spryt, <me@spryt.ru>, http://spryt.ru/
 * @link       http://spryt.ru/
 */
class Kagura {
	protected $data = array();
	protected $layout = "layout";
	protected $uri = array();

	function __construct($config) {

		$this->config = $config;
	}

	#Загрузка шаблона
	public function template($view) {

		foreach($this->data as $k=>$v) $$k=$v;

		$content_main = $this->load_template($view);
		
		if(file_exists("views/{$this->layout}.php"))
			include "views/{$this->layout}.php";	
	}

	#Подгрузка шаблона в шаблон, yo dawg
	public function load_template($view) {

		foreach($this->data as $k=>$v) $$k=$v;

		ob_start();
			if(file_exists("views/$view.php"))
				include "views/$view.php";

		$template = ob_get_contents();
		ob_end_clean();
		return $template;
	}

	public function diff($start,$end) {
		$diff = $start - $end;

		$color = ($diff>0) ? "green" : "red";
		$plus = ($diff>0) ? "+" : "";

		if($diff!=0) 
			return "<span style='color: $color; font-size: 10pt;'>$plus".number_format($diff)."</span>";
	}

	public function show_error($msg, $code = "") {

		if($code==404) header("HTTP/1.1 404 Not Found");

		$this->data['msg']=$msg;
		$this->data['title'] = "Ошибка";
		$this->template('error');
		
		die();
	}
}