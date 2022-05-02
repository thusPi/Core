<?php 
	chdir(__DIR__);
	include_once("{$_SERVER['DOCUMENT_ROOT']}/autoload.php");
	include_once($f['load.php_functions']);
?>
<?php 
    exit(json_output('Device value was changed.', 'success'));
?>