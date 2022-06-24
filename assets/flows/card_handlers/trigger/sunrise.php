<?php 
    $succeeded = false;

    $time      = time();
    $latitude  = CONFIG['env_latitude'];
    $longitude = CONFIG['env_longitude'];
    $sunrise   = strtotime(date_sunrise($time, SUNFUNCS_RET_STRING, $latitude, $longitude, 90));
    $diff      = $sunrise - $time;

    if($diff >= 0 && $diff < (CONFIG['streams_interval'] * 60)) {
        $succeeded = true;
    }

    echo($succeeded);
?>