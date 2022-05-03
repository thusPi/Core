<?php
	chdir(__DIR__);
	include_once("{$_SERVER['DOCUMENT_ROOT']}/autoload.php");
?>
<?php    
    // Check if feature is given
	if(!isset($_POST['feature'])) {
		\thusPi\Response\error('request_field_missing', 'Field feature is missing.');
	}
    
    $features = \thusPi\Extensions\list_all_features($_POST['feature']);
?>