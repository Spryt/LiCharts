<div class="container">

<h2 style="border-bottom: 2px #ccc solid; padding-bottom: 5px; margin-top: 5px;">Авторизируйтесь для входа:</h2>
	<?php if(isset($msg)) echo "<p style='color: brown;'>$msg"; ?>


<form method=POST action="?do=login" style=''>
	<p><strong>Пароль:</strong> <input type='password' name='password' style="margin-left: 15px;"> <input type='submit' style="margin-left: 15px;" value='Войти'>
	<p style="color: gray;">(пароль по умолчанию: 123)
	
</form>
