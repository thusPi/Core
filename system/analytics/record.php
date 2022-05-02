<?php 
	chdir(__DIR__);
	include_once('../../autoload.php');
?>
<?php 
    if(count($argv) <= 1) {
        \thusPi\Response\error('Insufficient amount of arguments given.');
    }

    $id = trim($argv[1]);

    $analytic   = new \thusPi\Analytics\Analytic($id);
    $device     = new \thusPi\Devices\Device($id);
    $properties = $device->getProperties();

    // Find handler location
    if(!($handler_path = @glob(DIR_ASSETS."/device_handlers/*/{$properties['handler']}/_main.{py,sh,php,exec}", GLOB_BRACE)[0])) {
        return 'handler_not_found';
    }

    $handler_cmd = script_name_to_shell_cmd($handler_path)." {$id}";

    execute($handler_cmd, $output_json, 60);

    if($output_json == '') {
        \thusPi\Log\write('Analytics', "Handler of device {$id} was found, but didn't return anything.", 'error');
        \thusPi\Response\error('error_recording', "Handler of device {$id} was found, but didn't return anything.");
    }

    if(!$output = @json_decode($output_json, true)) {
        \thusPi\Log\write('Analytics', "Failed to decode recording output of device {$id}.");
        \thusPi\Response\error('error_decoding_output', "Failed to decode recording output of device {$id}.");
    }

    if(isset($properties['options']['filter'])) {
        // Only keep output that the user wants

        $output_filtered = [];

        $filter = explode('&', $properties['options']['filter']);
        foreach ($filter as $filter_item) {
            $filter_items = explode('.', $filter_item);
            $filter_level = count($filter_items);

            $keys_str = '';
            for ($i=0; $i < $filter_level; $i++) { 
                $keys_str .= "['{$filter_items[$i]}']";
            }

            if(is_null($value = @eval("return \$output['data']{$keys_str};"))) {
                $value = 0;
            }
            
            array_push($output_filtered, $value);
        }

        $output['data'] = $output_filtered;
    }

    // Prepend recording time to array
    array_unshift($output['data'], time());

    if($analytic->saveRecording($output['data'])) {
        \thusPi\Response\success();
    } else {
        \thusPi\Response\error();
    }
?>