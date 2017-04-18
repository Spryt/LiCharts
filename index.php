<?php
/**
 * Li Charts v1.1
 *
 * LiCharts - группировка статистики LiveInternet для сайтов
 *
 * @author     Spryt, <me@spryt.ru>, http://spryt.ru/
 * @copyright  2015
 * @version    1.1
 * @link       http://licharts.ru/
 */

include "libs/li_charts.php";
include "libs/flatdb.php";
#include "libs/cache.class.php";
include "libs/RollingCurl.php";
include "libs/kagura.php";
include "main_controller.php";

define('START_TIME', microtime(true));

$config = array(
	"path_db" => "data/"
);

#Роутинг версия lite
$method = (isset($_GET['do'])) ? $_GET['do'] : "main";

#Основной класс
$kagura = new Main_controller($config);

#И нужный метод
$ref= new ReflectionMethod($kagura, $method);

try {
    $ref->invoke($kagura);
} catch (Exception $e) {
    $kagura->show_error("Ошибка при выполнении запроса <p><pre>$e</pre>");
}