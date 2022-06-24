<?php 
    $pass = false;

    global $main_config;

    $time       = time();
    $sunset_at  = strtotime(date_sunset($time, SUNFUNCS_RET_STRING, $main_config['location_latitude'], $main_config['location_longitude'], 90, $main_config['gmt_offset']));
    $diff_comp  = $parameters['duration'] * $parameters['period'];

    if($parameters['moment'] == 'after') {
        $diff       = ($time - $sunset_at);

        if($parameters['compare'] == 'less') {
            $pass = ($diff <= $diff_comp && $diff >= 0 ? true : false);
        } else if($parameters['compare'] == 'more') {
            $pass = ($diff >= $diff_comp && $diff >= 0 ? true : false);
        }
    } else if($parameters['moment'] == 'before') {
        $diff       = ($sunset_at - $time);

        if($parameters['compare'] == 'less') {
            $pass = ($diff <= $diff_comp && $diff >= 0 ? true : false);
        } else if($parameters['compare'] == 'more') {
            $pass = ($diff >= $diff_comp && $diff >= 0 ? true : false);
        }
    }

    echo($pass);
    return;
?>