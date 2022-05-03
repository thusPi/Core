<?php
	chdir(__DIR__);
	include_once("{$_SERVER['DOCUMENT_ROOT']}/autoload.php");
?>
<?php 
    // Check if user has permission to manage extensions
    if(\thusPi\Users\CurrentUser::getFlag('is_admin') !== true) {
        \thusPi\Response\error('no_permission', 'You don\'t have permission to manage extensions settings');
    }
    
    // Check if extension id is given
	if(!isset($_POST['id'])) {
		\thusPi\Response\error('request_field_missing', 'Field id is missing.');
	}
    $extension_id = basename($_POST['id']);

    $extension = new \thusPi\Extensions\Extension($extension_id);

    // Store properties in a variable because they won't be accessible
    // anymore when the extension is uninstalled
    $properties = $extension->getProperties();

    // Return an error if extension is not installed
    if(!isset($properties)) {
        \thusPi\Response\error('extension_not_installed', $properties);
    }

    if($extension->uninstall()) {
        \thusPi\Response\success('uninstalled', $properties);
    }

    \thusPi\Response\error('error_uninstalling', $properties);
?>