<?php 
	chdir(__DIR__);
	include_once('../../autoload.php');
?>
<?php 
    $minutes_since_epoch = floor(time()/60);

    $recordings = \thusPi\Recordings\get_all();

    foreach ($recordings as $properties) {
        $analytic = new \thusPi\Recordings\Recording($properties['id']);

        // Record if crontab pattern matches current time
        if(parse_crontab($properties['crontab'])) {
            $analytic->record(true);
        }
    }
?>