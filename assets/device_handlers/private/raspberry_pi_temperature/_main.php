<?php 
	chdir(__DIR__);
	include_once("../../../../autoload.php");
?>
<?php 
	if(count($argv) <= 1) {
		\thusPi\Response\error('Insufficient amount of arguments given.');
	}

	$id = $argv[1];

	$device  = new \thusPi\Devices\Device($id);
	$options = $device->getProperty('options');

	if(!$units = @$options['units']) {
		$units = 'standard';
	}

	if(@execute('sudo vcgencmd measure_temp', $temperature_str, 2)) {
		$temperature = floatval(preg_replace('/[^0-9.]/', '', trim($temperature_str)));
	}

	$unit = substr($temperature_str, -1);

	// Convert to Kelvin
	switch($unit) {
		case 'F':
			$temperature = ($temperature - 32) * (5/9) + 273.15;
			break;

		case 'C':
			$temperature += 273.15;
			break;
	}

	// Convert to desired unit (metric, imperial, standard)
	switch($units) {
		case 'metric':
			$temperature -= 273.15;
			break;

		case 'imperial':
			$temperature = ($temperature - 273.15) * (9/5) + 32;
			break;
	}

	$temperature = round($temperature, 3);

	# Return temperature
	\thusPi\Response\success(null, ['temperature' => $temperature]);
?>