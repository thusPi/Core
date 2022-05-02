<?php 
    chdir(__DIR__);
    include_once("../../../../autoload.php");
?>
<?php 
    if(count($argv) <= 3) {
        \thusPi\Response\error('Insufficient amount of arguments given.');
    }

    $value = $argv[1]; // On or off
    $msg   = $argv[2]; // Message device is being triggered by
    $port  = $argv[3]; // Serial port message was received from

    if($value != 'on' && $value != 'off') {
        \thusPi\Response\error('invalid_value', 'Invalid value. Expected on or off.');
    }

    $devices = \thusPi\Devices\get_all();


    foreach ($devices as $properties) {
        if($properties['handler'] != 'serial_message_toggle') {
            continue;
        }

        if(!isset($properties['options']["msg_{$value}"]) || $properties['options']["msg_{$value}"] != $msg ||
           !isset($properties['options']['port']) || $properties['options']['port'] != $port) {
            continue;
        }

        file_put_contents(DIR_ASSETS.'tmp.txt', date('H:i:s')."Device {$properties['id']} ({$properties['name']}) value changed to {$value}.\n", FILE_APPEND);


        $device = new \thusPi\Devices\Device($properties['id']);
        $device->setProperties(['value' => $value, 'shown_value' => $value]);
    }
?>