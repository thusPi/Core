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
    $recording     = new \thusPi\Recordings\Analytic($recording_id, true);

    $recording->setHistorySelection(
        $_POST['selection']['x0'] ?? null,
        $_POST['selection']['x1'] ?? null
    );

    $rows         = $recording->getHistory($_POST['max_rows'] ?? 750);
    $size         = $recording->getHistorySize();

    foreach ($rows as &$row) {
        if(!isset($row['x']) || !isset($row['y'])) {
            unset($row);
        }

        $row['x_formatted'] = \thusPi\Locale\date_format('full,full', $row['x']);
    }

    \thusPi\Response\success(null, [ 
        'manifest' => $recording->getProperties(),
        'rows'     => $rows,
        'size'     => $size
    ]);
?>