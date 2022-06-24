<?php 
	chdir(__DIR__);
	include_once("../../../../autoload.php");
?>
<?php 
	if(count($argv) <= 1) {
		\thusPi\Response\error('Insufficient number of arguments given.');
	}

	$id = $argv[1];

	$device  = new \thusPi\Devices\Device($id);
	$options = $device->getProperty('options');

	if(!$filter = @$options['filter']) {
		$filter = 'temperature';
	}

	if(!$units = @$options['units']) {
		$units = 'standard';
	}
	
	if(!$gpio_pin = @$options['gpio_pin']) {
		\thusPi\Response\error('option_gpio_pin_missing', 'Option gpio_pin was not specified in device options.');
	}

	$cmd = "/usr/bin/python3 inner.py {$gpio_pin}";

	if(!execute(escapeshellcmd($cmd, $output_json, 10)) {
		exit('Failed to run script.');
	}

	if(($output = @json_decode($output_json, true)) === false) {
		exit(json_output('Failed to decode data returned from script.'));
	}
			
	if($output['success'] != true || !isset($output['humidity']) || !isset($output['temperature'])) {
		exit(json_output('Failed to decode data returned from script.'));
	}

	switch($units) {
		case 'metric':
			$output['temperature'] -= 273.15;
			break;

		case 'imperial':
			$output['temperature'] = ($output['temperature'] - 273.15) * 9/5 + 32;
			break;
	}

	$output['temperature'] = round($output['temperature'], 3);
	$output['humidity'] = round($output['humidity'], 3);

	# Return DHT22 output
	exit(json_output($output, 'success'));
?>