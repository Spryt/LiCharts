<?php
class Main_controller extends Kagura {

	function __construct($config) {
		parent::__construct($config);

		#Проверяем, авторизирован ли пользователь
		$user = new Flatdb("user",$this->config['path_db']);
		$this->user = $user;

		$login = $this->user->find_all();

		#Если пароля нет - ставим 123
		if(!$login) {
			$this->user->insert(array("hash" => "202cb962ac59075b964b07152d234b70"));
			$login = $user->find_all();
		}

		#Проверяем, правильный ли хеш в куке
		if(!isset($_COOKIE['hash']) || $_COOKIE['hash']!=$login[0]['hash']) {
			$this->logged = false;
			$this->login();
			die();
		} else {
			$this->logged = true;
		}

		#Подгружаем остальные базы
		$sites = new Flatdb("sites",$this->config['path_db']);
		$this->sites = $sites;

		$groups = new Flatdb("groups",$this->config['path_db']);
		$this->groups = $groups;
	}

	

	public function main($set_group=false) {

		if(count($this->sites->find_all())==0) {
			$this->data['sites'] = false;
			$this->data['group'] = false;
			$this->data['groups'] = false;
			$this->data['is_group'] = false;
			$this->template("no_sites");
			die();
		}

		#Параметры выборок
		$period 	= (isset($_GET['period'])) 	? $_GET['period'] 	: "";
		$cat 		= (isset($_GET['cat'])) 	? $_GET['cat'] 		: "index";
		$site_stat 	= (isset($_GET['site'])) 	? $_GET['site'] 	: false;
		$is_group 	= (isset($_GET['group'])) 	? $_GET['group'] 	: false;

		if($set_group!=false) $is_group = $set_group;

		$li_charts = new Li_charts($this->sites);
		$sites= ($site_stat) ? $this->sites->select(array("domain"=>$site_stat)) : $this->sites->find_all();

		#Если выборка для группы
		if($is_group) {
			$sites = array();
			$group = $this->groups->find(array("slug"=>$is_group));

			if(isset($group['domains'])):
				foreach ($group['domains'] as $k=>$domain) {
					$site = $this->sites->find(array("domain"=>$domain));
					if($site) 
						$sites[] = $site;
					else {
						#Удаляем из группы несуществующие сайты
						unset($group['domains'][$k]);
						$this->groups->update(array("slug"=>$is_group),array("domains"=>$group['domains']));
					}
				}
			endif;

			if(count($sites)==0) {
				$this->data['sites'] = $this->sites->find_all();
				$this->data['group'] = $group;
				$this->data['groups'] = $this->groups->find_all();
				$this->data['is_group'] = $is_group;
				$this->template("no_sites");
				die();
			}
		} else $group = false;

		#Парсинг всей статистики (графика и сегодняшних)
		$li_data = $li_charts->parse_stats($sites,$period,$cat);
		$csv = $li_data['csv'];
		$today = $li_data['today'];


		#Суммируем все данные по всем сайтам (кроме даты)
		$stat=array();

		foreach ($csv as $domain => $val) {
			foreach ($val as $i => $data) {
				foreach ($data as $key => $value) {
					$dd = $data['date'];
					if(!isset($stat[$dd][$key])) $stat[$dd][$key]=0;
					if($key!='date') $stat[$dd][$key]+=$value; else $stat[$dd][$key]=$value;
				}
			}
		}

		ksort($stat);
		
		#Данные для шаблона
		$data = array(
			'cat' 		=> $cat, 
			'period' 	=> $period,
			'groups'	=> $this->groups->find_all(),
			'is_group'	=> $is_group,
			'site_stat' => $site_stat,
			'stat' 		=> $stat,
			'csv' 		=> $csv,
			'today'		=> $today,
			'sites' 	=> $sites,
			'group'		=> $group,
			'allsites' 	=> $this->sites->find_all()
		);

		$this->data=$data;
		$this->template("main");

	}

	public function add_sites() {

		$li_charts = new Li_charts($this->sites);

		$post_data=explode("\n", trim($_POST['sites']));
		$group = (isset($_POST['group'])) ? $_POST['group'] : false;

		foreach ($post_data as $v) {
			$url=trim($v);
			$site=array();

			$url = str_replace(array("http://","https://","/"),array("","",""),$url);

			$arr = explode(":", $url);
			$domain = strtolower($arr[0]);

			if($domain=="" || strlen($domain)==0) continue;

			//Если есть пароль - получем куку
			$cookie = (isset($arr[1])) ? $li_charts->auth_li($domain,$arr[1]) : "";

			$data=$li_charts->get_data("http://www.liveinternet.ru/stat/$domain/index.csv?graph=csv",$cookie);

			#Проверяем, доступна ли статистика
			if(trim($data) == '"статистика сайта";"обновлено {date} в {time}"') {
				$this->data['msg'][]="Статистика сайта <strong>$url</strong> недоступна - его нет в LI, или нужен пароль, или пароль неправильный";
				continue;
			} 

			#Отрабатываем алиас
			if(strpos($data,"<TITLE>302 Found</TITLE>") !== FALSE){
				preg_match('|<A HREF="http:\/\/www.liveinternet.ru/stat/([^/]+)/index.csv\?graph=csv">here</A>|', $data,$rows);
				$site['alias'] = $rows[1];
			} 

			$site['domain'] = $domain;
			$site['pass'] = (isset($arr[1])) ? $arr[1] : "";
			$site['cookie'] = $cookie;

			#Если домен уже существует - обновляем, если нет - добавляем
			if($this->sites->find(array("domain" => $domain))) 
				$this->sites->update(array("domain" => $domain),$site);
			 else 
				$this->sites->insert($site);

			#Если добавляется из группы - добавляем в группу
			if($group) {
				$gr = $this->groups->find(array("slug" => $group));

				if(isset($gr['domains']))
					$domains = array_merge($gr['domains'],array($domain));
				else
					$domains = array($domain);

				$this->groups->update(array("slug"=>$group),array("domains"=>$domains));
			}
					
		}

		echo $this->load_template("show_msg");
		$this->main($group);
	}

	#Удаление сайта
	public function delete_site() {
		
		$domain = $_GET['domain'];
		$this->sites->delete(array("domain"=>$domain));
	}

	#Удаление всех сайтов
	public function flush() {
		if($_POST['delete']=="all") {
			$this->sites->flush_db();
			header("Location: ?");
		}
	}

	#Добавление группы
	public function add_group() {
		
		include "libs/URLify.php";

		$group = trim($_GET['group']);
		$slug = URLify::filter($group);

		if(strlen($group) > 0 && strlen($slug)> 0):

			$find_group = $this->groups->find(array("group"=>$group));
			$find_slug = $this->groups->find(array("slug"=>$slug));

			if(!$find_group && !$find_slug) {
				$this->groups->insert(array("group"=>$group,"slug"=>$slug));
				echo "<a href=\"?group=$slug\">$group</a>";
			}

		endif;
	}

	#Удаление группы
	public function delete_group() {
		$slug=$_GET['group'];
		$this->groups->delete(array("slug"=>$slug));
		header("Location: ?");
	}

	#Добавление сайта в группу
	public function add_sites_group() {
		$sites = (isset($_POST['sites']) && is_array($_POST['sites'])) ? $_POST['sites'] : array();
		$slug = $_POST['group'];
		
		#Одним ударом двух зайцев - и добавление сайтов в группу, и удаление их оттуда
		$this->groups->update(array("slug"=>$slug),array("domains"=>$sites));

		header("Location: ?group=$slug");
	}

	#Форма логина
	public function login() {
		if(isset($_POST['password'])) {
			$pass_hash = md5($_POST['password']);
			$login = $this->user->find_all();
			
			if($pass_hash == $login[0]['hash']) {
				setcookie("hash", $pass_hash,time()+12*2592000,"/",".".$_SERVER['HTTP_HOST']);
				header("Location: ?");
			} else {
				$this->data['msg'] = "Неправильный пароль";
				$this->template("login");
			}
		} else {
			$this->template("login");
		}
	}


	public function logout() {
		setcookie("hash", "",time()+12*2592000,"/",".".$_SERVER['HTTP_HOST']);
		header("Location: ?");
	}

	#AJAX смена пароля
	public function change_password() {
		$pass = trim($_GET['pass']);
		$hash = md5($pass);

		$this->user->flush_db();
		$this->user->insert(array("hash" => $hash));
		setcookie("hash", $hash,time()+12*2592000,"/",".".$_SERVER['HTTP_HOST']);
	}

}