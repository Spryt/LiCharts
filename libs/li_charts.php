<?php
/**
 * Класс для работы с LiveInternet - авторизация, получение статистики из csv, дневной из pda-версии
 *
 * @author     Spryt, <me@spryt.ru>, http://spryt.ru/
 * @link       http://spryt.ru/
 */
class Li_charts {

	public $site;
	static $csv;
	static $today;
	static $sites;

	function __construct($db) {
		self::$sites = new Flatdb("sites","data/");
	}

	#Новая функция для многопточного парсинга статистики из LI
	#Слегка запутанные коллбеки
	public function parse_stats($sites,$period,$cat) {

		#Инициализирум два потока - один для csv-графиков, другой для статы за день
		$rc_csv = new RollingCurl(array("li_charts","get_csv_stats"));
		$rc_csv->options = array(CURLOPT_USERAGENT => "Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:24.0) Gecko/20100101 Firefox/24.0");
		$rc_csv->window_size = 5;

		if($cat=="index") {
			$rc_today = new RollingCurl(array("li_charts","get_today_stat"));
			$rc_today->options = array(CURLOPT_USERAGENT => "Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:24.0) Gecko/20100101 Firefox/24.0");
			$rc_today->window_size = 5;
		}

		foreach ($sites as $site) {
			$domain = $site['domain'];

			$liperiod = ($period!="") ? $liperiod=";period=".$period : "";

			switch ($cat) {
				case 'index': $ids="id=8;id=0;"; break;
				case 'searches': $ids="id=4;id=13;"; break;
				case 'searches2': $ids="id=5;id=8;"; break;
				case 'visitors': $ids="id=2;id=4;"; break;
				case 'oses': $ids="id=2;id=15;id=4;id=11;id=total;"; break;
				default: $ids="";
			}

			$url_stat = (isset($site['alias'])) ? $site['alias'] : $site['domain'];

			#Формируем урл
			$get_url = "http://www.liveinternet.ru/stat/$url_stat/$cat.csv?{$ids}graph=csv$liperiod&total=no";

			if($cat=="searches2") $get_url = "http://www.liveinternet.ru/stat/$url_stat/searches.csv?{$ids}graph=csv$liperiod&total=no";

			#Отправляем запрос с данными
			$request = new RollingCurlRequest($get_url,"GET",array("site"=>$site, "period" => $period, "cat" => $cat));
			$request->options = array(CURLOPT_COOKIE => $site['cookie']);
			$rc_csv->add($request);

			if($cat=="index") {

				$site = self::$sites->find(array("domain"=>$domain)); #На случай, если куки была получена заново

				#Аналогично - для сегодняшних данных
				$today_url="http://pda.liveinternet.ru/stat/$url_stat/index.html?nograph=yes";

				$request2 = new RollingCurlRequest($today_url,"GET",array("site"=>$site));
				$request2->options = array(CURLOPT_COOKIE => $site['cookie']);
				$rc_today->add($request2);
			
			} else self::$today = false;
		}

		#Запускаем оба потока
		$rc_csv->execute();
		if($cat=="index") $rc_today->execute();

		#Не нашел более удобного способа обмениваться данными с вызываемыми коллбеком функциями, кроме как через статичную переменную
		$data = array("csv" => self::$csv, "today" => self::$today);
		return $data;
	}
	
	#Получение статистики из csv
	public function get_csv_stats($response, $info, $request) {
			
		foreach($request->args as $k=>$v) $$k=$v;
		$data = $response;


		if(strpos($data, "Your browser seems to be a robot") !== FALSE) {
			echo "<br>LI заблокировал ваш IP";
			return false;
		}

		#На случай факапа - обновление через старые синхронные функции
		if(trim($data) == '"статистика сайта";"обновлено {date} в {time}"') {
			$cookie = self::auth_li($site['domain'],$site['pass']);

			$data= self::get_data($info['url'],$cookie);
			if(trim($data) == '"статистика сайта";"обновлено {date} в {time}"') {
				echo "<br>Статистика сайта <strong>{$site['domain']}</strong> недоступна - его нет в LI, или нужен пароль, или пароль неправильный";
				return false;
			} else {
				self::$sites->update(array("domain"=>$site['domain']),array("cookie"=>$cookie));
			}
		}
		
		
		$strings = explode("\n", $data);

		if($strings[0]=='"Дата";"Android";"iOS iPhone";"SymbianOS";"Windows Phone";"всего"') {
			$cat="oses";
		}

		unset($strings[0]);

		$max=count($strings);
		unset($strings[$max]);

		if(strlen($strings[1])==0 ) {
			$csv[0]['visitors']=0;
			$csv[0]['views']=0;
			$csv[0]['date'] = 0;
			$domain = $site['domain'];
			self::$csv[$domain] = $csv;
			return false;
		}

		$i=0;
		foreach ($strings as $key => $value) {
			
			$arr=explode(";", $value);

			$date = $arr[0];

			#Если сайт новый
			if($date=='"Просмотры"' || $date=='"Просмотров на посетителя"') {
				$csv[$i]['visitors']=0;
				$csv[$i]['views']=0;
				$csv[$i]['date'] = 0;
				break;
			}

			$date=str_replace("\"", "", $date);
			
			if($period=="month") {
				$date="1 ".$date;
				$date=mb_strtolower($date,'UTF-8');
				$date=str_replace(" 1", " 201", $date);
			} 

			if($period=="week") {

				list($day,$mon) = explode(" ", $date);

				$mon = str_replace(
					array("янв","фев","мар","апр","мая","июн","июл","авг","сен","окт","ноя","дек"),
					array('1','2','3','4','5','6','7 ','8','9','10','11','12'),
				$mon);

				//Выставляем правильный год
				if($mon > date("n") ) $year=date("Y")-1; else $year=date("Y");

				$date.=" ".$year;
			}


			$date = str_replace(
				array("янв","фев","мар","апр","мая","июн","июл","авг","сен","окт","ноя","дек"),
				array('January','February','March','April','May','June','July ','August','September','October','November','December'),
				$date);

			if ($period==""){

				if(!isset($first)) $first = $date;

				list($first_day,$first_month) = explode(" ", $first);
				list($this_day,$this_month) = explode(" ", $date);


				if($first_month == "December" && $this_month == "December" && $first_day>=2) {
					$date.=" ".date("Y")-1;
				} else {
					$date.=" ".date("Y");
				}

			}


			$csv[$i]['date']=strtotime($date);

			
			if(isset($arr[1])) $csv[$i]['visitors']=floor($arr[1]); else $csv[$i]['visitors']=0;
			if(isset($arr[2])) $csv[$i]['views']=floor($arr[2]); else $csv[$i]['views']=0;

			if($cat=="oses") {
				$csv[$i]['views']=floor($arr[1]+$arr[2]+$arr[3]+$arr[4]);
				$csv[$i]['visitors']=floor($arr[5]-$csv[$i]['views']);
			}

			
			

			$i++;
		}


		$domain = $site['domain'];
		self::$csv[$domain] = $csv;
	}



	public function get_today_stat($response, $info, $request) {

		foreach($request->args as $k=>$v) $$k=$v;
		$data = $response;

		$stat = array('visitors' =>0, 'views' => 0, 'diff_visitors' => 0, 'diff_views' => 0);

		if(strpos($data, "Для доступа к этой странице необходимо ввести пароль") !== FALSE) {
			$cookie = self::auth_li($site['domain'],$site['pass']);

			$data=self::get_data($info['url'],$cookie);
			if(strpos($data, "Для доступа к этой странице необходимо ввести пароль") !== FALSE) {
				echo "<br>Статистика сайта <strong>{$site['domain']}</strong> недоступна - его нет в LI, или нужен пароль, или пароль неправильный";
			} else {
				self::$sites->update(array("domain"=>$site['domain']),array("cookie"=>$cookie));
			}
		}
	

		$data = str_replace("\r\n","",$data);
		$arr1=array(" ",",","+");
		$arr2=array("","","");

		#Проверяем, есть ли данные

		if(strpos($data, "За последние 24 часа на сайте были 0 посетителей") !== FALSE) {
			$stat['visitors'] = 0;
			$stat['views'] = 0;
			$stat['diff_visitors'] = 0;
			$stat['diff_views'] = 0;
		} else {

			#ToDo: Сделать один корректный RegEx для обоих случаев (когда есть дифф и когда его нет)
			preg_match_all('|<td align=left>([^<]+)</td><td>([^<]+)<br><a([^>]+)><font color="([^"]+)">(.*?)</font></a></td>|',$data,$rows);

			if(isset($rows[2][1])) {
				$stat['visitors'] = str_replace($arr1,$arr2,$rows[2][1]);
				$stat['views'] = str_replace($arr1,$arr2,$rows[2][0]);
				$stat['diff_visitors'] = str_replace($arr1,$arr2,$rows[5][1]);
				$stat['diff_views'] = str_replace($arr1,$arr2,$rows[5][0]);
			} else {
				preg_match_all('|<td><input type=checkbox name=id value="([^"]+)" checked></td><td align=left>([^<]+)</td><td>([^<]+)</td>|',$data,$rows);
				$stat['visitors'] = str_replace($arr1,$arr2,$rows[3][1]);
				$stat['views'] = str_replace($arr1,$arr2,$rows[3][0]);
				$stat['diff_visitors'] = 0;
				$stat['diff_views'] = 0;
			}
		}

		$domain = $site['domain'];
		self::$today[$domain] = $stat;
	}

	#Авторизация и получение куки в LI
	public function auth_li($domain,$pass) {

		$c = curl_init("http://www.li.ru/stat/");
		curl_setopt($c, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($c, CURLOPT_POST, TRUE);
		curl_setopt($c, CURLOPT_TIMEOUT, 10);
		curl_setopt($c, CURLOPT_USERAGENT, "Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:24.0) Gecko/20100101 Firefox/24.0");
		curl_setopt($c, CURLOPT_POSTFIELDS, "url=$domain&password=$pass");
		curl_setopt($c, CURLOPT_HEADER, TRUE);
		//curl_setopt($c, CURLOPT_FOLLOWLOCATION, true);
		$data = curl_exec($c);
		if($data === false) echo "\nCurl error: ".curl_error($c);
		
		$header=substr($data,0,curl_getinfo($c,CURLINFO_HEADER_SIZE));
		curl_close($c);

		preg_match_all("/Set-Cookie: (.*?)=(.*?);/i",$header,$res);
		$cookie='';
		foreach ($res[1] as $key => $value) $cookie.= $value.'='.$res[2][$key].'; ';

		if(strlen($cookie)==0) $cookie=FALSE;

		return $cookie;
	}

	public function get_data($url,$cookie="") {

		$c = curl_init($url);
		curl_setopt($c, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($c, CURLOPT_USERAGENT, "Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:24.0) Gecko/20100101 Firefox/24.0");
		curl_setopt($c, CURLOPT_TIMEOUT, 10);
		curl_setopt($c, CURLOPT_COOKIE, $cookie);
		curl_setopt($c, CURLOPT_HEADER, FALSE);
		//curl_setopt($c, CURLOPT_FOLLOWLOCATION, TRUE);
		$data = curl_exec($c);
		if($data === false) echo "\nCurl error: ".curl_error($c);
		curl_close($c);

		return $data;
	}
}
