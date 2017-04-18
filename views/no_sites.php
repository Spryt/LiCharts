<div class="top_groups">
	<ul class="groups" id="nav_groups">

		<li <?php echo (!$is_group) ? "class=selected":"";?>><a href="?">Все сайты <span class="count_sites"><?php echo count($sites);?></span></a>
		<?php if($groups):?>
		<?php foreach($groups as $mygroup):?>
			<li <?php echo ($is_group==$mygroup['slug']) ? "class=selected":"";?>><a href="?group=<?php echo $mygroup['slug']?>"><?php echo $mygroup['group']?> <?php if(isset($mygroup['domains'])):?><span class="count_sites"><?php echo count($mygroup['domains']);?></span><?php endif;?></a>
		<?php endforeach;?>
		<li id="add_group"><a onclick="add_group();" style="cursor: pointer;" title="Добавить группу">+</a>
		<?php endif;?>
	</ul>
</div>

<div class="container">

<div id="container_add_no_site">

	<h2 style="margin-top: 0;">Вы еще не добавили <?php if($group) echo "в группу {$group['group']}";?> ни одного сайта</h2>

	<?php if($group):?>

		<hr noshade>
		<form method=POST action="?do=add_sites_group">
			<ul class="words">
			<?php foreach($sites as $k=>$site):?>
			<li><input type="checkbox" name="sites[]"  value="<?php echo $site['domain'];?>"> <?php echo $site['domain'];?>
			<?php endforeach;?>
			<li><input type="button" id="toggle" value="Выбрать все" onClick="do_this()" />
			<input type="hidden" name="group" value="<?php echo $group['slug'];?>"> 
			<li style="padding: 10px;"><input type="submit" value="Добавить сайты в группу">
			</ul>
			
		</form>

		<div style="clear: both;"></div>


	<?php endif;?>

	<hr noshade>

	<p>Добавить (каждый сайт с новой строчки, если есть пароль - то site.ru:password):

	<form method=post action="?do=add_sites" class="add_no_site">
		<textarea name='sites'></textarea>
		<?php if($group):?> <input type="hidden" name="group" value="<?php echo $group['slug'];?>">  <?php endif;?>
		<p><input type='submit' value='Добавить сайты'>
	</form>

	<?php if($group):?>
	<p style="text-align: right;"><a href="?do=delete_group&group=<?php echo $group['slug']?>" onclick="return confirm('Вы уверены, что хотите удалить группу?')" class="delete_group">Удалить группу</a>
	<?php endif;?>


</div>