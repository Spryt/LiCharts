<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="styles/favicon2.png" rel="shortcut icon" />

    <title>Статистика Li Charts</title>

    <link rel="stylesheet" href="styles/style.css" type="text/css" media="screen" />

    <script type="text/javascript" src="styles/main.js"></script>

    <script type="text/javascript" src="styles/gs_sortable.js"></script>
	<script type="text/javascript">
		<!--
		var TSort_Data = new Array ('table', 's', 'n', 'n','n','n');
		var TSort_Initial = new Array ('1D');
		tsRegister();
		// -->
	</script>

  </head>

  <body>

	<?php echo $content_main;?>



<?php if($this->logged):?><p style="font-size: 10pt; float: left;"><a href="?do=logout">Выйти</a> | <span id="change_password"><a class="jslink" onclick="change_password();">Сменить пароль</a></span><?php endif;?>
<p style="color: gray; font-size: 10pt; float: right;"><a href="http://licharts.ru">Li Charts</a> v1.3, &copy; <a href="http://spryt.ru/">Spryt</a>, 2015-<?php echo date("Y"); ?>

</div>



<p>
	<br><br><?php 
  	$mem=round(memory_get_usage()/1024/1024, 2);
	$exec_time = round(microtime(true) - START_TIME,2);

	//echo "<p style='text-align: center;'>$mem mb , $exec_time секунд";

  	?>



</body>
</html>