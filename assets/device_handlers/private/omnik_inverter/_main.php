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

	// Check if inverter_ip was specified
	if(!isset($options['inverter_ip'])) {
		\thusPi\Response\error('option_inverter_ip_missing', 'Option inverter_ip was not specified in device options.');
	}
	$ip = $options['inverter_ip'];

	// Check if wifi_kit_sn was specified
	if(!isset($options['wifi_kit_sn'])) {
		\thusPi\Response\error('option_wifi_kit_sn_missing', 'Option wifi_kit_sn was not specified in device options.');
	}
	$wifi_sn = $options['wifi_kit_sn'];

	// Check if port was specified
	if(!isset($options['port'])) {
		\thusPi\Response\error('option_port_missing', 'Option port was not specified in device options.');
	}
	$port = $options['port'];
	
	$cmd = "/usr/bin/python3 inner.py {$ip} {$port} {$wifi_sn}";

	if(!@execute(escapeshellcmd($cmd), $output_json, 10)) {
		\thusPi\Response\error('error_running_script', 'Failed to run handler script.');
	}
	
	if(($output = @json_decode($output_json, true)) === false) {
		\thusPi\Response\error('error_decoding_data', 'Failed to decode data returned from handler script.');
	}
	
	if($output['success'] != true || !isset($output['pv']) || !is_array($output['pv'])) {
		\thusPi\Response\error('error_parsing_data', 'Failed to parse data returned from handler script.');
	}

 	// Return inverter data
	\thusPi\Response\success(null, $output);
?>