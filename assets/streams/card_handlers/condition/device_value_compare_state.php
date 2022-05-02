<?php 
    $succeeded = false;
    
    $device       = @new \thusPi\Devices\Device($card['parameters']['id']);
    $properties   = $device->getProperties();

    if($properties['value'] == $card['parameters']['value'] || $properties['shown_value'] == $card['parameters']['shown_value']) {
        $succeeded = true;
    }

    echo($succeeded);
?>