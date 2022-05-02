<?php set_time_limit(300);
    $pass = false;

    // If time specified matches current time
    if($parameters['hours'] == intval(date('H')) && $parameters['minutes'] == intval(date('i'))) {
        $pass = true;
    }

    echo($pass);
    return;
?>