<?php 
	chdir(__DIR__);
	include_once("../../autoload.php");
?>
<?php 
    $ticks_run = [];
    $tick_manifest_fallback = [
        'options' => [],
        'run_same_repeatedly' => false
    ];

    $devices = \thusPi\Devices\get_all();

    foreach ($devices as $properties) {
        if(!isset($properties['handler'])) {
            continue;
        }

        $tick_path          = @glob(DIR_ASSETS."/device_handlers/*/{$properties['handler']}/_tick.{py,sh,php,exec}", GLOB_BRACE)[0];
        $tick_manifest_path = @glob(DIR_ASSETS."/device_handlers/*/{$properties['handler']}/_tick.json")[0];
        
        if(!file_exists($tick_path)) {
            continue;
        }
        
        if(($tick_manifest = @file_get_json($tick_manifest_path)) === false) {
            $tick_manifest = [];
        }

        $tick_manifest = array_replace($tick_manifest_fallback, $tick_manifest);

        // Append requested arguments (device options)
        $tick_argv = '';
        if(isset($tick_manifest['options'])) {
            foreach($tick_manifest['options'] as $option_key) {
                if(isset($properties['options'][$option_key])) {
                    $tick_argv .= str_replace(' ', '_', $properties['options'][$option_key]).' ';
                }
            }
            $tick_argv = trim($tick_argv);
        }
        
        // Run script
        $tick_cmd = script_name_to_shell_cmd($tick_path, $tick_argv);
        if(!isset($ticks_run[$tick_cmd])) {
            $ticks_run[$tick_cmd] = true;
            shell_exec("{$tick_cmd} > /dev/null 2>&1 &");
        }
    }
?>