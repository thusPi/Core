<?php
	chdir(__DIR__);
	include_once("{$_SERVER['DOCUMENT_ROOT']}/autoload.php");
?>
<?php 
    if(\thusPi\Users\CurrentUser::getFlag('is_admin') !== true) {
        \thusPi\Response\error('no_permission', \thusPi\Locale\translate('generic.error.no_page_access'));
    }

    $top      = intval($_POST['top'] ?? null) ?? 25;
    $min_time = intval($_POST['min_time'] ?? null) ?? 0;

    $messages = \thusPi\Log\read($top, $min_time);

    if(is_array($messages)) {
        \thusPi\Response\success(null, $messages);
    } else {
        \thusPi\Response\error('no_messages_found');
    }
?>