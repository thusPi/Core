<?php
	chdir(__DIR__);
	include_once("{$_SERVER['DOCUMENT_ROOT']}/autoload.php");
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="IE=edge;chrome=1" />
	<link href="/favicon.ico" rel="icon">
	<link href="https://pro.fontawesome.com/releases/v5.15.0/css/all.css" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css?family=Material+Icons+Outlined" rel="stylesheet">
	<link href="https://cdn.jsdelivr.net/npm/@mdi/font@6.4.95/css/materialdesignicons.min.css" rel="stylesheet">

	<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js" integrity="sha512-894YE6QWD5I59HgZOGReFYm4dnWc1Qt5NtvYSaNcOP+u1T9qYdvdihz0PPSiiqn/+/3e7Jo4EaG7TubfWGUrMQ==" crossorigin="anonymous" referrerpolicy="no-referrer" type="text/javascript"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js" integrity="sha512-uto9mlQzrs59VwILcLiRYeLKPPbS/bT71da/OEBYEwcdNUk8jYIy+D176RYoop1Da+f9mvkYrmj5MCLZWEtQuA==" crossorigin="anonymous" referrerpolicy="no-referrer" type="text/javascript"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui-touch-punch/0.2.3/jquery.ui.touch-punch.min.js" integrity="sha512-0bEtK0USNd96MnO4XhH8jhv3nyRF0eK87pJke6pkYf3cM0uDIhNJy9ltuzqgypoIFXw3JSuiy04tVk4AjpZdZw==" crossorigin="anonymous" referrerpolicy="no-referrer" type="text/javascript"></script>
	<?php 
		\thusPi\Frontend\print_scripts();
		\thusPi\Frontend\print_stylesheets();
		\thusPi\Frontend\print_theme();
		\thusPi\Frontend\print_category_css();
	?>
	<title>thusPi</title>
</head>
<body data-reduced-motion="<?php echo(bool_to_str(\thusPi\Users\CurrentUser::getSetting('reduced_motion'))); ?>">
	<?php \thusPi\Frontend\print_element('sidenav'); ?>
	<main class="container"></main>
	<div class="page-status" data-status="animating_loading">
		<i class="far fa-spinner-third fa-spin fa-3x text-info mb-2"></i>
		<h2 class="d-block px-2"><?php echo(\thusPi\Locale\translate('generic.loading')); ?></h2>
	</div>
	<div class="page-status" data-status="error">
		<i class="far fa-exclamation-circle fa-3x text-danger mb-2"></i>
		<h2 class="d-block px-2"><?php echo(\thusPi\Locale\translate('generic.error')); ?></h2>
	</div>
	<div class="message-area"></div>
</body>
</html>