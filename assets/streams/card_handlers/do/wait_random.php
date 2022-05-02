<?php set_time_limit(300);
    $pass = false;

    $duration = (mt_rand($parameters['duration_min'] * $parameters['period'] * 1000000, $parameters['duration_max'] * $parameters['period'] * 1000000) / 1000000);

    if($duration <= 120) { // Less than two minutes
        $pass = true;

        if($duration > 0) {
            $seconds = floor($duration);
            $micros  = ($duration - $seconds)*1000000;
    
            sleep($seconds);
            usleep($micros);
        }
    }

    echo($pass);
    return;
?>