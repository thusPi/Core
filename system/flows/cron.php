<?php 
    chdir(__DIR__);
    include_once("../../autoload.php");
?>
<?php 
    set_time_limit(300);

    // Trigger all flows without arguments
    \thusPi\Flows\trigger_all('cron', []);
?>