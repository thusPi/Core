<?php 
    namespace thusPi\Devices;

	use \thusPi\Interfaces\defaultInterface;

    class Device extends defaultInterface {      
        public function __construct($id, $respect_permissions = false) {
			if($respect_permissions && !\thusPi\Users\CurrentUser::checkFlagItem('devices', $id)) {
                \thusPi\Response\error('no_permission', "User is not permitted to view device {$id}.");
            }

            $this->id = $id;
        }

        public function getProperties() {
			return \thusPi\Devices\get($this->id);
		}

		public function setProperties($new_properties, $doStreamsTrigger = true) {
            $db = \thusPi\Database\connect();

            if(isset($new_properties['options']) && is_array($new_properties['options'])) {
                $new_properties['options'] = @json_encode($new_properties['options']);
            }

            $db->where('id', $this->id);
            if(!$db->update('devices', $new_properties)) {
                return false;
            }

			// Trigger streams if value was changed
			if(isset($new_properties['value']) && $doStreamsTrigger) {
				\thusPi\Streams\trigger_all('device_value_change', ['id' => $this->id]);
			}

            return true;
        }

		public function setValue($args) {
            $db = \thusPi\Database\connect();

			$waypoints = new \thusPi\Debug\WaypointList('plain');
			$waypoints->disable();

			// Get function arguments
			if(!isset($args['value'])) {
				return 'argument_value_missing';
			}

			$args = array_replace(['shown_value' => $args['value'], 'cause' => 'manual', 'force_set' => false], $args);
			
			// Get device properties
			$properties = $this->getProperties();
			if($properties === false) {
				return 'loading_properties_failed';
			}

			// Don't try if device value is already set to this value and force_set is false
			if($args['value'] == $properties['value'] && $args['force_set'] != true) {
				return true;
			}
			
			// Obtain device options
			$options = $properties['options'];
			$additional_options = [];
				
			if($properties['control_type'] == 'search') {
				// Merge search result value with options
				if(($additional_options = $this->transformSearchResult($args['value'])) === false) {
					return 'transforming_search_result_failed';
				}
			}

			// Find handler location
			if(!($handler_path = @glob(DIR_ASSETS."/device_handlers/*/{$properties['handler']}/_main.{py,sh,php,exec}", GLOB_BRACE)[0])) {
				return 'handler_not_found';
			}

			$waypoints->printWaypoint('Found handler...');

			// Get handler script type
			$handler_type = pathinfo($handler_path, PATHINFO_EXTENSION);

			// Perform actions based on type
			switch($handler_type) {
				case 'exec': // Make PHP run a single shell command
					// Replace variables
					$cmd = file_get_contents($handler_path);
					$cmd = str_replace(
						[
							'{DEVICE_VALUE}',
							'{DEVICE_ID}'
						], 
						[
							$args['value'],
							$this->id
						], 
					$cmd);
					$cmd = escapeshellcmd($cmd);
					break;

				default:
					if(!$options = @array_merge($properties['options'], $additional_options)) {
						return 'merging_options_failed';
					}
					$options_str = shell_arg_encode($options);

					$tunnel_file = dirname($handler_path).'/_tunnels/'.pathinfo($handler_path, PATHINFO_FILENAME).'.json';
					$cmd = script_name_to_shell_cmd($handler_path, "{$tunnel_file} {$args['value']} {$options_str}");
					$cmd = escapeshellcmd($cmd);
					break;
			}

			$waypoints->printWaypoint('Waiting for process to finish...');

			$waypoints->printWaypoint('List of processes: ' . json_encode(\thusPi\Processes\get_all("handler_{$properties['handler']}")));

			// Wait until all processes for this handler have been finished
			if(!\thusPi\Processes\wait_for_finish("handler_{$properties['handler']}", 60000, 250)) {
				return 'handler_waiting_timed_out';
			}

			$waypoints->printWaypoint('Process finished, creating new process');

			// Create new process for this handler
			$handler_process = new \thusPi\Processes\Process("handler_{$properties['handler']}");

			$waypoints->printWaypoint('List of processes: ' . json_encode(\thusPi\Processes\get_all("handler_{$properties['handler']}")));

			// Run handler
			execute($cmd, $json, 15);

			// Mark handler process as finished
			$handler_process->status('finished');

			$waypoints->printWaypoint('Process marked as finished');

			if($handler_type == 'exec' || $json == '') {
				$response = ['success' => true];
			} else {
				if(!($response = @json_decode($json, true))) {
					return 'handler_invalid_response';
				}
			}
			
			if($response['success'] != true) {
				return 'handler_handled_unsuccesful';
			}

			// Save new value
			$this->setProperties(['value' => $args['value'], 'shown_value' => $args['shown_value']], false);

			$waypoints->printWaypoint('Properties set!');

			// Trigger devices/value_change hook
			\thusPi\Extensions\call_hooks_for_type('devices/value_change', [$this->id, $this->getProperty('value')]);

			// // Trigger streams
			// \thusPi\Streams\trigger_all('device_value_change', ['id' => $this->id]);

			// $waypoints->printWaypoint('Streams triggered, finished!');

			return true;
		}

		private function transformSearchResult($value) {
			$output = [];

			$search_handler = $this->getProperty('search_handler');

			if(!$script = glob(DIR_ASSETS."/device_handlers/*/{$search_handler}/_transform.{py,sh,php,exec}", GLOB_BRACE)[0]) {
				return false;
			}
				
			if(!($cmd = script_name_to_shell_cmd($script, $value))) {
				return false;
			}
			
			putenv('PULSE_SERVER=/run/user/'.getmyuid().'/pulse/native');
			execute($cmd, $json, 30);

			if(!$output = @json_decode($json, true)) {
				return false;
			}

			if($output['success'] != true || !isset($output['data'])) {
				return false;
			}

			return $output['data'];
		}
    }

	function get_handler_manifest($handler_name) {
		$manifest_fallback = [
			'name'              => \thusPi\Locale\translate('state.unknown'),
			'description'       => \thusPi\Locale\translate('state.unknown'),
			'category'          => 'appliances',
			'run_type'          => null,
			'control_support'   => true,
			'control_type'      => 'none',
			'analytics_support' => false,
			'options'           => []
		];

		$manifest_path = glob(DIR_ASSETS."/device_handlers/*/{$handler_name}/_manifest.json")[0];
		if(!$manifest = @json_decode(@file_get_contents($manifest_path), true)) {
			$manifest = [];
		}

		return array_replace_recursive($manifest_fallback, $manifest);
	}

    function get($id, $respect_permissions = false) {
		if($respect_permissions && !\thusPi\Users\CurrentUser::checkFlagItem('devices', $id)) {
            \thusPi\Response\error('no_permission', "User is not permitted to view device {$id}.");
            return null;
        }

        $db = \thusPi\Database\connect();

        $db->where('id', $id);
        $device = $db->getOne('devices');

		if(!isset($device)) {
			return null;
		}
        
        $device = array_replace([
            'id' => $id,
            'name' => '',
            'icon' => '',
            'category' => '',
            'family' => '',
            'control_type' => 'none',
            'control_support' => false,
            'handler' => '',
            'search_handler' => '',
            'value' => null,
            'shown_value' => null,
            'force_set' => false,
            'options' => []
        ], $device);

        if(is_string($device['options'])) {
            $device['options'] = @json_decode($device['options'], true);
        }

        return $device;
    }
	
    function get_all($respect_permissions = false) {
        $db = \thusPi\Database\connect();

        $devices = [];

        $ids = array_column($db->get('devices', null, 'id'), 'id');

        foreach ($ids as $id) {
			if($respect_permissions && !\thusPi\Users\CurrentUser::checkFlagItem('devices', $id)) {
				continue;
			}

            $device = \thusPi\Devices\get($id);

            if(!is_array($device)) {
                continue;
            } 

            $devices[] = $device;
        }

        return $devices;
    }
?>