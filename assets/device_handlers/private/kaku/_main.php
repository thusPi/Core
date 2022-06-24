<?php 
	chdir(__DIR__);
	include_once("../../../../autoload.php");
?>
<?php 
	\thusPi\Devices\handler_handle(__DIR__, $argv, 'send.py');
?>