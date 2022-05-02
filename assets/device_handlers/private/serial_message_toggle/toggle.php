<?php 
	chdir(__DIR__);
	include_once("{$_SERVER['DOCUMENT_ROOT']}/autoload.php");
	include_once($f['load.php_functions']);
?>
<?php 
    include_class('Devices');

    if(count($argv) <= 3) {
		exit(json_output('Insufficient amount of arguments. Expected <value> <msg> <line>.'));
    }

    $value = $argv[1];
    $msg   = $argv[2];
    $line  = $argv[3];

    if($value != 'on' && $value != 'off') {
		exit(json_output('Invalid value. Expected on or off.'));
    }

    $Devices = new Devices();
    $devices = $Devices->list();

    foreach ($devices as $device_id => $device) {
        if($device['handler'] != 'serial_message_toggle') {
            continue;
        }

        if(!isset($device['options']["msg_{$value}"]) || $device['options']["msg_{$value}"] != $msg) {
            continue;
        }

        print_r('Found device '.$device_id." \n");

        $Device = new Device($device_id);
        $Device->setValue(['value' => $value, 'shown_value' => $value]);

        echo('Toggled device '.$device_id.' '.$value."\n");
    }
?>