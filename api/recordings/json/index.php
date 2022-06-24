<?php
	chdir(__DIR__);
	include_once("{$_SERVER['DOCUMENT_ROOT']}/autoload.php");
?>
<?php 
    // Check if recording id is given
	if(!isset($_POST['id'])) {
		\thusPi\Response\error('request_field_missing', 'Field id is missing.');
	}

    $recording_id  = $_POST['id'];
    $interval      = $_POST['interval'] ?? null;
    $recording     = new \thusPi\Recordings\Recording($recording_id, true);

    $datasets = $recording->getDataSets([
        'max_rows' => min($_POST['max_rows'] ?? 750, 5000), 
        'interval' => $interval,
        'x_start'  => $_POST['x_start'] ?? null,
        'x_end'    => $_POST['x_end'] ?? null
    ]);

    \thusPi\Response\success(null, [ 
        'manifest' => $recording->getProperties(),
        'datasets' => $datasets
    ]);
?>