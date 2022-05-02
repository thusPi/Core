<?php 
    $succeeded = false;

    $time = time();
    $at   = strtotime("{$card['parameters']['hours']}:{$card['parameters']['minutes']}", $time);
    $diff = $at - $time;

    if($diff >= 0 && $diff < (CONFIG['streams_interval'] * 60)) {
        $succeeded = true;
    }

    echo($succeeded);
?>