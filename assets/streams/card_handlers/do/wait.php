<?php set_time_limit(300);
    $succeeded = false;

    $duration = floatval($card['parameters']['duration']) * floatval($card['parameters']['period']);

    if($duration > 0 && $duration <= 120) { // If less than two minutes
        sleep($duration);
        $succeeded = true;
    }

    echo($succeeded);
    return;
?>