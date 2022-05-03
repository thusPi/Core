<?php 
	chdir(__DIR__);
	include_once("../../../../autoload.php");
?>
<?php 
    set_time_limit(70);

	if(count($argv) <= 1) {
		\thusPi\Response\error('Insufficient amount of arguments given.');
	}

	$id = $argv[1];

	$device  = new \thusPi\Devices\Device($id);
	$options = $device->getProperty('options');

	// Check if dsmr_version was specified
	if(!isset($options['dsmr_version'])) {
		\thusPi\Response\error('option_dsmr_version_missing', 'Option dsmr_version was not specified in device options.');
	}
	$dsmr_version = $options['dsmr_version'];

	// Check if port was specified
	if(!isset($options['port'])) {
		\thusPi\Response\error('option_port_missing', 'Option port was not specified in device options.');
	}
	$port = $options['port'];

    $cmd = "/usr/bin/python3 inner.py {$port} {$dsmr_version}";

    if(!execute(escapeshellcmd($cmd), $output_json, 65)) {
		\thusPi\Response\error('error_running_script', 'Failed to run handler script.');
	}

    if(($output = @json_decode($output_json, true)) === false) {
		\thusPi\Response\error('error_decoding_data', 'Failed to decode data returned from handler script.');
	}
	
	if(!isset($output['success']) || $output['success'] != true || !isset($output['electricity']) || !isset($output['gas'])) {
		\thusPi\Response\error('error_parsing_data', 'Failed to parse data returned from handler script.');
	}

 	// Return DSM data
	\thusPi\Response\success(null, $output);
?>