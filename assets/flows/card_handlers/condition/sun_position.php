<?php 
    $pass = false;

    global $main_config;

    $time = time();

    $sunrise_at = strtotime(date_sunrise($time, SUNFUNCS_RET_STRING, $main_config['location_latitude'], $main_config['location_longitude'], 90, $main_config['gmt_offset']), $time);
    $sunset_at  = strtotime(date_sunset($time, SUNFUNCS_RET_STRING, $main_config['location_latitude'], $main_config['location_longitude'], 90, $main_config['gmt_offset']), $time);
    $sunpos     = ($sunrise_at < $time && $time < $sunset_at ? 'up' : 'down');

    if($parameters['position'] == $sunpos) {
        $pass = true;
    }

    echo($pass);
    return;
?>