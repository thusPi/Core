<?php 
    $succeeded = false;
    
    $device = @new \thusPi\Devices\Device($card['parameters']['id']);

    if($device->setValue([
        'value'       => floatval($card['parameters']['value']),
        'shown_value' => floatval($card['parameters']['shown_value']),
        'force_set'   => true
    ]) === true) {
        $succeeded = true;
    } 

    echo($succeeded);
    return;
?>