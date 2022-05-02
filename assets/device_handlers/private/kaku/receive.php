<?php 
	chdir(__DIR__);
	include_once("../../../../autoload.php");
?>
<?php 
	$devices_changed = [];

	if(count($argv) <= 4) {
		\thusPi\Response\error('Insufficient amount of arguments given. Expected <address> <unit> <state> <pulse length>.');
	}
	
	$address      = $argv[1];
	$unit         = $argv[2];
	$state        = $argv[3];
	$pulse_length = $argv[4];

	\thusPi\Log\write('KlikAanKlikUit', "Received address={$address},unit={$unit},state={$state},pulse_length={$pulse_length}.", 'debug');

	$devices = \thusPi\Devices\get_all();
	
	foreach($devices as $properties) {
		if(!isset($properties['handler']) || $properties['handler'] != 'kaku') {
			continue;
		}

		if(!isset($properties['options']['address']) || !isset($properties['options']['unit'])) {
			continue;
		}

		if($properties['options']['address'] != $address || $properties['options']['unit'] != $unit) {
			continue;
		}

		$device = new \thusPi\Devices\Device($properties['id']);

		array_push($devices_changed, $properties['id']);

		if($state == 0) {
			$device->setProperties(['value' => 'off', 'shown_value' => 'off']);
		} else {
			$device->setProperties(['value' => 'on', 'shown_value' => 'on']);
		}
	}

	if(count($devices_changed) > 0) {
		exit(json_output('Device(s) '.implode(', ', $devices_changed).' was/were changed.', 'success'));
	}
	exit(json_output('A matching device was not found.'));
?>?>