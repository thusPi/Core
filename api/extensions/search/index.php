<?php
	chdir(__DIR__);
	include_once("{$_SERVER['DOCUMENT_ROOT']}/autoload.php");
?>
<?php 
    // Check if user has permission to manage extensions
    if(\thusPi\Users\CurrentUser::getFlag('is_admin') !== true) {
        \thusPi\Response\error('no_permission', 'You don\'t have permission to manage extensions settings');
    }

    $_POST['query'] = $_POST['query'] ?? null;
    $_POST['category'] = $_POST['category'] ?? null;

    \thusPi\Response\success(null, \thusPi\Extensions\get_all_from_server($_POST['category'], $_POST['query']));
?>