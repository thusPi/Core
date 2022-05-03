<?php
	chdir(__DIR__);
	include_once("{$_SERVER['DOCUMENT_ROOT']}/autoload.php");
?>
<?php
	// Check if device id is given
	if(!isset($_POST['id'])) {
		\thusPi\Response\error('request_field_missing', 'Field id is missing.');
	}

	// Check if device value is given
	if(!isset($_POST['value'])) {
		\thusPi\Response\error('request_field_missing', 'Field value is missing.');
	}

	// Set default shown value to value
	if(!isset($_POST['shown_value'])) {
		$_POST['shown_value'] = $_POST['value'];
	}

	// Set default cause
	if(!isset($_POST['cause'])) {
		$_POST['cause'] = 'manual';
	}

	// Don't force set by default
	if(!isset($_POST['force_set'])) {
		$_POST['force_set'] = false;
	}

	$device = new \thusPi\Devices\Device($_POST['id'], true);

	try {
		$result = $device->setValue([
			'value'       => $_POST['value'], 
			'shown_value' => $_POST['shown_value'],
			'force_set'   => str_to_bool($_POST['force_set']), 
			'cause'       => $_POST['cause']
		]);

		if($result === true) {
			\thusPi\Response\success();
		} else {
			throw new Exception($result);
		}
	} catch(Exception $e) {
		\thusPi\Response\error(null, $e->getMessage());
	}
?>