<?php
	chdir(__DIR__);
	include_once("{$_SERVER['DOCUMENT_ROOT']}/autoload.php");
?>
<?php 
    $response = [];

	$devices = \thusPi\Devices\get_all();

	foreach ($devices as $properties) {

		if(is_null($properties['value'])) {
			continue;
		}

		$response[$properties['id']] = [
			'name'        => $properties['name'],
			'value'       => $properties['value'],
			'shown_value' => $properties['shown_value'] ?? $properties['value']
		];
	}

	if(empty($response)) {
		\thusPi\response\error();
	} else {
		\thusPi\response\success(null, $response);
	}
?>