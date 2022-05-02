<?php
	chdir(__DIR__);
	include_once("{$_SERVER['DOCUMENT_ROOT']}/autoload.php");
?>
<?php 
	
    if(!isset($_POST['username'])) {
		$_POST['username'] = '';
	}

	if(!isset($_POST['password'])) {
		$_POST['password'] = '';
	}

	if(!\thusPi\Authorization\login_verify($_POST['username'], $_POST['password'])) {
		\thusPi\response\error('error_credentials');
	}
	
	\thusPi\response\success('success_login');
?>