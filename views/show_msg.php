<?php 
if(isset($msg)) {
	echo "<div style='color: brown; text-align: center; margin-top: 20px; margin-bottom: 20px;'>";
	echo implode("<p>",$msg); 
	echo "</div>";
}
?>