<?php
	chdir(__DIR__);
	include_once("{$_SERVER['DOCUMENT_ROOT']}/autoload.php");
?>
<?php 
    // Check if analytic id is given
	if(!isset($_POST['id'])) {
		\thusPi\Response\error('request_field_missing', 'Field id is missing.');
	}

    $analytic_id  = $_POST['id'];
    $analytic     = new \thusPi\Analytics\Analytic($analytic_id, true);

    $analytic->setHistorySelection(
        $_POST['selection']['x0'] ?? null,
        $_POST['selection']['y0'] ?? null,
        $_POST['selection']['x1'] ?? null,
        $_POST['selection']['y1'] ?? null,
    );

    $rows         = $analytic->getHistory($_POST['max_rows'] ?? 750);
    $size         = $analytic->getHistorySize();

    foreach ($rows as &$row) {
        if(!isset($row['x']) || !isset($row['y'])) {
            unset($row);
        }

        $row['x_formatted'] = \thusPi\Locale\date_format('full,full', $row['x']);
    }

    \thusPi\Response\success(null, [ 
        'manifest' => $analytic->getProperties(),
        'rows'     => $rows,
        'size'     => $size
    ]);
?>