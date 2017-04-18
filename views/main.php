<div class="top_groups">
	<ul class="groups" id="nav_groups">

		<?php if($site_stat):?><li class=selected><a href="#"><?php echo $site_stat;?></a><?php endif;?>
		<li <?php echo (!$is_group && !$site_stat) ? "class=selected":"";?>><a href="?">Все сайты <span class="count_sites"> <?php echo count($allsites);?> </span></a>
		<?php foreach($groups as $mygroup):?>
			<li <?php echo ($is_group==$mygroup['slug']) ? "class=selected":"";?>><a href="?group=<?php echo $mygroup['slug'];?>"><?php echo $mygroup['group'];?> <?php if(isset($mygroup['domains'])):?><span class="count_sites"> <?php echo count($mygroup['domains']);?> </span><?php endif;?></a>
		<?php endforeach;?>
		<li id="add_group"><a onclick="add_group();" style="cursor: pointer;" title="Добавить группу">+</a>
	</ul>
</div>

<div class="container">
<span style="font-family: Georgia;" class="second">Статистика посещаемости</span>

<ul class="nav">
	<?php
		$http = array();

		if($cat!="index") $http['cat']=$cat;
		if($site_stat) $http['site']=$site_stat;
		if($is_group) $http['group']=$is_group;
	?>
	<li <?php echo ($period=="") ? "class=selected":"";?>><a href="?<?php echo http_build_query($http);?>">По дням</a> |
	<li <?php echo ($period=="week") ? "class=selected":"";?>><a href="?<?php echo http_build_query(array_merge($http,array("period" => "week"))); ?>">По неделям</a> |
	<li <?php echo ($period=="month") ? "class=selected":"";?>><a href="?<?php echo http_build_query(array_merge($http,array("period" => "month"))); ?>">По месяцам</a>
</ul>


<div style="clear: both;"></div>

<hr noshade>

<div style="clear: both;"></div>


<div id="chart_div" style="width: 100%; height: 20%;"></div>

<ul class="nav" style="float: left; padding-top: 10px; padding-bottom: 10px;">
	<?php if($site_stat):?>
		<li><a href="?<?php echo ($cat!="index") ? "cat=$cat&" : "";?><?php echo ($period!="") ? "period=$period" : "";?>">Все сайты</a> &raquo; 
	<?php endif;?>

	<?php
		$http = array();

		if($site_stat) $http['site']=$site_stat;
		if($is_group) $http['group']=$is_group;
		if($period!="") $http['period'] = $period;
	?>

	<li <?php echo ($cat=="index") ? "class=selected" : "";?>><a href="?<?php echo http_build_query($http);?>" >Посещаемость</a> | 
	<li <?php echo ($cat=="searches") ? "class=selected" : "";?>><a href="?<?php echo http_build_query(array_merge($http,array("cat" => "searches"))); ?>" >Переходы с ПС</a> |
	<li <?php echo ($cat=="visitors") ? "class=selected" : "";?>><a href="?<?php echo http_build_query(array_merge($http,array("cat" => "visitors"))); ?>" >Аудитория</a> |
	<li <?php echo ($cat=="oses") ? "class=selected" : "";?>><a href="?<?php echo http_build_query(array_merge($http,array("cat" => "oses"))); ?>" >Устройства</a> |
	<li <?php echo ($cat=="searches2") ? "class=selected" : "";?>><a href="?<?php echo http_build_query(array_merge($http,array("cat" => "searches2"))); ?>" >Mail / Bing</a>
	
	<!--<li><a href="?cat=">Все переходы</a> |
	<li><a href="?cat=">Страницы</a> |
	<li><a href="?cat=">WAP траф</a>
	<li><a href="?do=seo" >SEO показатели</a>-->

</ul>
<div align="right" style="float:right; font-size: 10pt; margin-top: 10px;">
<em style="color: #333;" title="Обновлено <?php echo date('r');?>"><?php echo date("M d, G:i"); ?></em></div>

<!--
<div align="right" style="float:right; margin-top:-1px;margin-bottom:5px;"><a class="daolink" href="http://daodomains.com/" title="Регистрация доменов .ru/.рф/.com и т.д." target="_blank"><img src="styles/dao.gif" border="0" style="vertical-align:middle;" width=90 height=32></a></div>-->


<table id='table' class="sites">
<thead>
	<tr>
		<th rowspan=2>Сайт
		<?php if($cat=="index"):?><th colspan=2>Сегодня<?php endif;?>
		<th colspan=2 class="second">
			<?php
			if($period=="week") echo "Прошлая неделя";
			elseif($period=="month") echo "Прошлый месяц";
			else echo "Вчера";
			?>
		
		<th rowspan=2 class="second">&nbsp;
	<tr>
		<?php if($cat=="index"):?>
		<th>Посетителей 
		<th>Просмотров 

		<th class="second">Посетителей
		<th class="second">Просмотров
		<?php endif;?>

		<?php if($cat=="searches"):?>
		<th>С Google
		<th>С Яндекса
		<?php endif;?>

		<?php if($cat=="searches2"):?>
		<th>С Mail.ru
		<th>С Bing
		<?php endif;?>

		<?php if($cat=="visitors"):?>
		<th>Ядро
		<th>Постоянные
		<?php endif;?>

		<?php if($cat=="oses"):?>
		<th>Настольные
		<th>Мобильные
		<?php endif;?>

</thead>


<?php

$day_vis=0; $day_hit=0; $today_vis=0; $today_hit=0; $visitors=0; $views=0; $diff_visitors=0; $diff_views=0;

#Подсчитываем суммарные значения
foreach ($sites as $site) {
	$domain = $site['domain'];

	$max=count($csv[$domain])-1;
	$pre=count($csv[$domain])-2;

	if($max>=0 && $pre>=0) {
		$day_vis+=$csv[$domain][$max]['visitors'];
		$day_hit+=$csv[$domain][$max]['views'];
		$visitors+=$csv[$domain][$pre]['visitors'];
		$views+=$csv[$domain][$pre]['views'];
	}
	if($today) {
		$today_vis+=$today[$domain]['visitors'];
		$today_hit+=$today[$domain]['views'];
		$diff_visitors+=$today[$domain]['diff_visitors'];
		$diff_views+=$today[$domain]['diff_views'];
	}
}

if(count($sites)>=5) {
	echo "<tfoot><tr><td><strong>Всего:</strong>";

	if($today) {
	echo "	<td class=\"second\"><strong>".number_format($today_vis)." ".$this->diff($diff_visitors,0)."</strong>
			<td class=\"second\"><strong>".number_format($today_hit)." ".$this->diff($diff_views,0)."</strong>";
	}

	echo "	<td><strong>".number_format($day_vis)." ".$this->diff($day_vis,$visitors)."</strong>
			<td><strong>".number_format($day_hit)." ".$this->diff($day_hit,$views)."</strong>
	<td class=\"second\">&nbsp;</tfoot>";
}


echo '<tbody class="list">';

foreach ($sites as $site) {
	$domain = $site['domain'];

	$max=count($csv[$domain])-1;
	$pre=count($csv[$domain])-2;




	echo "<tr id=\"tr_$domain\"><td><a href=\"http://$domain/\" target=_blank><img class=fav src=\"http://favicon.yandex.net/favicon/$domain\" width=16 height=16></a> <a href=\"?site=$domain"; 
	echo ($cat!="index") ? "&cat=$cat" : "";
	echo ($period!="") ? "&period=$period" : "";

	echo "\">$domain</a> <small style=\"float: right;\"><a href=\"https://www.liveinternet.ru/stat/$domain/\" target=_blank>LI</a></small>";
	$class_td="";
	if($today) {
		echo "
			<td>".number_format($today[$domain]['visitors'])." ".$this->diff($today[$domain]['diff_visitors'],0)."
			<td>".number_format($today[$domain]['views'])." ".$this->diff($today[$domain]['diff_views'],0);
			$class_td=" class=\"second\"";
	}
	if($max>=0 && $pre>0) {
		echo "<td{$class_td}>".number_format($csv[$domain][$max]['visitors'])." ".$this->diff($csv[$domain][$max]['visitors'],$csv[$domain][$pre]['visitors'])."
		<td{$class_td}>".number_format($csv[$domain][$max]['views'])." ".$this->diff($csv[$domain][$max]['views'],$csv[$domain][$pre]['views']);
	} else {
		echo "<td>0<td>0";
	}

	
	echo "<td class=\"second\"><a style=\"cursor: pointer; color: red;\" title=\"Удалить сайт\" onclick=\"del('$domain');\">&times;</a>";

	


}

echo "</tbody><tfoot><tr><td><strong>Всего:</strong>";

if($today) {
echo "	<td><strong>".number_format($today_vis)." ".$this->diff($diff_visitors,0)."</strong>
		<td><strong>".number_format($today_hit)." ".$this->diff($diff_views,0)."</strong>";
}

echo "	<td{$class_td}><strong>".number_format($day_vis)." ".$this->diff($day_vis,$visitors)."</strong>
		<td{$class_td}><strong>".number_format($day_hit)." ".$this->diff($day_hit,$views)."</strong>
<td class=\"second\">";
if(!$group && !$site_stat) {
	echo "
	<form method=POST action=\"?do=flush\">
	<input type=hidden name=delete value=all>
	<input type=submit class=flush title=\"Удалить все сайты\" value=\"&sum;\" onclick=\"return confirm('Вы уверены, что хотите удалить все сайты?')\">
	</form>";
	} 
	else echo "&nbsp;";
?>
</tfoot>
</table>

<p style="float: right;"><a href="#" onclick="alert('<?php foreach($sites as $site) echo "http://{$site['domain']}/\\n";?>'); return false;" class="export">Экспорт сайтов</a>

<?php if($group):?>
<p style="float: right;"><a href="?do=delete_group&group=<?php echo $group['slug'];?>" onclick="return confirm('Вы уверены, что хотите удалить группу?')" class="delete_group">Удалить группу</a>
<?php endif;?>





<?php if(!$site_stat):?>
<p><a onclick="toggle(add_sites)" class="jslink"><?php echo ($group) ? "Управление сайтами" : "Добавить сайты";?> &darr;</a>


<div id="add_sites" style="display: none;">
	<?php if($group):?>
		<hr noshade>
		<form method=POST action="?do=add_sites_group">
			<ul class="words">
			<?php foreach($allsites as $k=>$site):?>
				<li><input type="checkbox" name="sites[]" value="<?php echo $site['domain'];?>" <?php echo (in_array($site['domain'], $group['domains'])) ? "checked" : "";?>> <?php echo $site['domain'];?>
			<?php endforeach;?>
			<li><input type="button" id="toggle" value="Выбрать все" onClick="do_this()" />
			<input type="hidden" name="group" value="<?php echo $group['slug'];?>"> 
			<li style="padding: 10px;"><input type="submit" value="Добавить сайты в группу">
			</ul>

			
		</form>

		<div style="clear: both;"></div>

	<?php endif;?>

	<p style="color: #333; text-align: center;">Каждый сайт с новой строчки, если есть пароль - то site.ru:password
	<form method=post action="?do=add_sites" class="add_no_site">
		<textarea name='sites'></textarea>
		<?php if($group):?> <input type="hidden" name="group" value="<?php echo $group['slug'];?>">  <?php endif;?>
		<p><input type='submit' value='Добавить сайты'>
	</form>
</div>

<?php endif;?>

<div style="clear: both;"></div>


<script type="text/javascript" src="https://www.google.com/jsapi"></script>
<script type="text/javascript">

// Load the Visualization API library and the piechart library.
google.load('visualization', '1.0', {'packages':['corechart']});
google.setOnLoadCallback(drawChart);
   // ... draw the chart...
 
function drawChart() {
 
    // Create the data table.
    var data = new google.visualization.DataTable();
        data.addColumn('date', 'Дата');
    <?php if($cat=="index"):?>
    	data.addColumn('number', 'Посетителей');
    	data.addColumn('number', 'Просмотров');
    <?php endif;?>
    <?php if($cat=="searches"):?>
    	data.addColumn('number', 'Переходов с Google');
    	data.addColumn('number', 'Переходов с Яндекса');
    <?php endif;?>

    <?php if($cat=="searches2"):?>
    	data.addColumn('number', 'Переходов с Mail.ru');
    	data.addColumn('number', 'Переходов с Bing');
    <?php endif;?>

    <?php if($cat=="visitors"):?>
    	data.addColumn('number', 'Ядро');
    	data.addColumn('number', 'Постоянные');
    <?php endif;?>

     <?php if($cat=="oses"):?>
    	data.addColumn('number', 'Настольные');
    	data.addColumn('number', 'Мобильные');
    <?php endif;?>

    data.addRows([
    <?php
    foreach($stat as $v) {	
		$date = (isset($v['date'])) ? $v['date']*1000 : 0;
		unset($v['date']);
		if($date>0)	echo "[new Date($date), ".implode(", ", $v)."],\n";
    }
    ?>
]);
        var options = {
        colors: ['blue',<?php echo ($cat=="searches") ? "'red'" : "'green'";?>],
        'legend':{'position':'none'},
        'titleTextStyle':{'fontName':'Georgia','fontSize':18,'bold':false},
        <?php if($period==""):?>hAxis: {format: 'd MMMM'},<?php endif;?>
        chartArea: {width: '100%', height: '90%', top: 0},
        series: {0 :  {areaOpacity:0.15}},
        vAxis: {textPosition: 'in',minValue: 0},          
    };
 
    // Instantiate and draw our chart, passing in some options.
    var chart = new google.visualization.AreaChart(document.getElementById('chart_div'));
    chart.draw(data, options);


}


if (document.addEventListener) {
    window.addEventListener('resize', drawChart);
}
else if (document.attachEvent) {
    window.attachEvent('onresize', drawChart);
}
else {
    window.resize = drawChart;
}
</script>