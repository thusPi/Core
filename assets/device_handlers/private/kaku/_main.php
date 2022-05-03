<?php 
	chdir(__DIR__);
	include_once("../../../../autoload.php");
?>
<?php 
	if(count($argv) <= 3) {
		\thusPi\Response\error('Insufficient amount of arguments given.');
	}

	$options = decodeshellargarray($argv[3]);
	
	$state = $argv[2] == 'on' ? 1 : 0;

	if(!isset($options['unit'])) {
		\thusPi\Response\error('option_unit_missing');
	}

	if(!isset($options['address'])) {
		\thusPi\Response\error('option_address_missing');
	}

	if(!isset($options['port'])) {
		\thusPi\Response\error('option_port_missing');
	}

	if(!isset($options['baud'])) {
		\thusPi\Response\error('option_baud_missing');
	}

	if(!isset($options['pulse_length'])) {
		\thusPi\Response\error('option_pulse_length_missing');
	}

	$cmd = "/usr/bin/python3 send.py {$options['address']} {$options['unit']} {$state} {$options['pulse_length']} {$options['port']} {$options['baud']}";
	if(!@execute($cmd, $output_json, 10)) {
		\thusPi\Response\error();
	}

	if(($output = json_decode($output_json, true)) === false) {
		\thusPi\Response\error('invalid_output');
	}

	if($output['success'] === true) {
		\thusPi\Response\success();
	} else if(isset($output['data'])) {
		\thusPi\Response\error($output['data']);
	} else {
		\thusPi\Response\error();
	}

	\thusPi\Log\write('KlikAanKlikUit', "Sent address={$address},unit={$unit},state={$state},pulse_length={$pulse_length}.", 'debug');
?>