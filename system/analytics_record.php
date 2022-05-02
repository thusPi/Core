<?php 
	chdir(__DIR__);
	include_once("{$_SERVER['DOCUMENT_ROOT']}/autoload.php");
	include_once($f['load.base']);
?>
<?php
	set_time_limit(120);

	$minutes_since_epoch = floor(time()/60);
	$succesful_runs = 0;
	$by_cli_or_cron = false;
	
	if(!isset($_SERVER['SERVER_NAME'])) {
		$by_cli_or_cron = true;
	}

	if(!$by_cli_or_cron) {echo('<pre>'); }

	output("Retrieving new data...");
	output("Minutes since epoch: {$minutes_since_epoch}", true, 1);

	$logs_dir = "{$d['analytics']}/historical";
	$logs = glob("{$logs_dir}/*/");
	$devices = get_device_list();

	$sensors = array_map(function($logs) {
		return basename($logs);
	}, $logs);

	foreach($sensors as $sensor_key) {

		// Check if device exists
		if(!isset($devices[$sensor_key])) {
			$LOG->write('Analytics', "{$sensor_key} was set to be recorded but does not exist.", 'warning');
			output("Device {$sensor_key} does not exist.", false, 1);
			continue;
		}
		$sensor = $devices[$sensor_key];

		output("Found device {$sensor_key} ({$sensor['name']})");

		// Check if handler is set
		if(!isset($sensor['handler'])) {
			$LOG->write('Analytics', "{$sensor['name']} ({$sensor_key}) was set to be recorded but a handler was not specified.", 'warning');
			output('A handler was not specified.', false, 1);
			continue;
		}

		// Check if device value needs to be logged
		if(
			!isset($sensor['log_interval']) ||
			$sensor['log_interval'] <= 0 || 
			!is_integer($sensor['log_interval']) ||
			$minutes_since_epoch % $sensor['log_interval'] !== 0
		) {
			output("Log interval of {$sensor_key} is every {$sensor['log_interval']} minute(s), skipping.", true, 1);
			continue;
		}

		// Check if handler exists
		if(($handler = @glob("{$d['assets']}/device_handlers/*/{$sensor['handler']}/_main.{php,py,sh,exec}", GLOB_BRACE)[0]) === false) {
			output("Failed to find handler {$sensor['handler']}");
			$LOG->write('Analytics', "Failed to find handler {$sensor['handler']} for {$sensor['name']} ({$sensor_key}).", 'error');
			continue;
		}

		$cmd = script_name_to_shell_cmd($handler, @shell_arg_encode($sensor['options']));

		output("Running command {$cmd}");

		if(execute($cmd, $json_output, 15) === false) {
			output('An output was not received.');
			continue;
		}

		output("Received output {$json_output}");
		if(($output = @json_decode($json_output, true)) === false) {
			$LOG->write('Analytics', "Failed to decode JSON data for {$sensor['name']} ({$sensor_key}): {$json_output}", 'error');
			output("Decoding recording failed: {$json_output}.", false, 1);
			continue;
		}
				
		if($output['success'] != true) {
			if(empty($output['message'])) {
				$output['message'] = 'unknown error';
			}
			$LOG->write('Analytics', "Failed to read data for {$sensor['name']} ({$sensor_key}): {$output['message']}.", 'error');
		}

		output('Recording succeeded.', true);

		# Alphabetically sort output message
		associative_array_sort($output['message']);

		if(isset($sensor['options']['filter'])) {
			# Only keep output that the user wants

			$output_filtered = [];

			$filter = explode('&', $sensor['options']['filter']);
			foreach ($filter as $filter_item) {
				$filter_items = explode('.', $filter_item);
				$filter_level = count($filter_items);

				$keys_str = '';
				for ($i=0; $i < $filter_level; $i++) { 
					$keys_str .= "['{$filter_items[$i]}']";
				}

				if(!$value = eval("return \$output['message']{$keys_str};")) {
					$value = 0;
				}

				array_push($output_filtered, $value);
			}

			$output['message'] = $output_filtered;
		}

		array_unshift($output['message'], time());
		$csv_string = str_putcsv($output['message']);

		$historical_logs_dir = "{$d['data']}/analytics/historical/{$sensor_key}";
		if(is_dir($historical_logs_dir)) {
			# Create new file every day to save SD-card write-cycles
			$log_file = date('Y-m-d').'.csv';

			if($by_cli_or_cron) {
				if(file_put_contents("{$historical_logs_dir}/{$log_file}", $csv_string.PHP_EOL, FILE_APPEND)) {
					$succesful_runs += 1;
					output("Saved to file {$historical_logs_dir}/{$log_file}!", true, 1);
				} else {
					$LOG->write('Analytics', "Failed to write to file {$historical_logs_dir}/{$log_file} while saving data for sensor {$sensor['name']} ({$sensor_key}).", 'error');
					output("Failed to write to file {$historical_logs_dir}/{$log_file}!", false, 1);
				}
			} else {
				output("Please run via CLI or cron in order to save data to file {$historical_logs_dir}/{$log_file}!", false, 1);
			}
		} 
	}

	if(!$by_cli_or_cron) {echo('</pre>'); }

	function output($str, $success = true, $whitespaces = 0) {
		global $by_cli_or_cron;
		$output_str = '';
		$whitespaces += 1;

		$output = ['time' => output_date(), 'message' => $str];

		if($by_cli_or_cron) {
			if(!empty($str)) {
				if($success == true) {
					$output_str = "[{$output['time']}] $str";
				} else {
					$output_str = "\033[01;31m[{$output['time']}]\033[0m $str";
				}
			}
			$output_str .= str_repeat(PHP_EOL, $whitespaces);
		} else {
			if(!empty($str)) {
				if($success == true) {
					$output_str = "[{$output['time']}] $str";
				} else {
					$output_str = "[<b>{$output['time']}</b>] $str";
				}
			}
			$output_str .= str_repeat('<br>', $whitespaces);
		}

		echo($output_str);
	}

	function output_date($format = 'H:i:Q') {
		$date = date($format);
		if(strpos($format, 'Q') !== false) {
			$microtime_of_second = number_format(abs(microtime(true)-time())+floatval(date('s')), 3);
			$str = str_pad($microtime_of_second, 6, '0', STR_PAD_LEFT);
			$date = str_replace('Q', $str, $date);
		}
		return $date;
	}
?>