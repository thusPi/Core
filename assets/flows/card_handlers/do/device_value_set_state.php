<?php 
    $succeeded = false;
    
    $device = @new \thusPi\Devices\Device($card['parameters']['id']);

    if($device->setValue([
        'value'       => $card['parameters']['value'],
        'shown_value' => $card['parameters']['shown_value'] ?? null,
        'force_set'   => true
    ]) === true) {
        $succeeded = true;
    } 

    echo($succeeded);
    return;
?>