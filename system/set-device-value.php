<?php
	chdir(__DIR__);
	include_once("{$_SERVER['DOCUMENT_ROOT']}/autoload.php");
	include_once($f['load.php_functions']);
?>
<?php
	if(count($argv) > 2) { // Verify if enough information is given
		if(@set_device_value($argv[1], $argv[2], $argv[3], $argv[4], $argv[5], $argv[6])) {
			exit(json_output('Device value changed.', 'success'));
		}
	} else {
		exit(json_output("Insufficient query parameters were given: device_id and value are required."));
	}
	exit(json_output('Something went wrong.'));
?>