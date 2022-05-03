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

    // Install the extension
    if(($err = \thusPi\Extensions\install($extension_id)) === true) {
        $extension = new \thusPi\Extensions\Extension($extension_id);
        \thusPi\Response\success('installed', $extension->getProperties());
    } else {
        \thusPi\Response\error('failed_to_install', $err);
    }
?>