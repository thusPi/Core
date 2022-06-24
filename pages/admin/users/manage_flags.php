<?php 
    // Verify if a uuid was specified
    if(!isset($_GET['uuid'])) exit(); 
    $user = @new \thusPi\Users\User($_GET['uuid']);
    $properties = $user->getProperties();

    // Verify if a user with the specified uuid exits
    if(!isset($properties) || empty($properties)) {
        exit();
    }

    $default_user = @new \thusPi\Users\User('default');
    $default_properties = $default_user->getProperties();

    $properties = array_replace($properties, $default_properties);
?>
<?php var_dump($properties); ?>
<div class="flex-column">
    <div class="tile">
        <h3 class="tile-title"></h3>
        <div class="tile-content"></div>
    </div>
</div>