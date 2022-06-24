<?php
	chdir(__DIR__);
	include_once("{$_SERVER['DOCUMENT_ROOT']}/autoload.php");
?>
<!DOCTYPE html>
<html lang="en" data-theme="<?php echo(\thusPi\Users\CurrentUser::getSetting('theme')); ?>">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="IE=edge;chrome=1" />
	<link href="/favicon.ico" rel="icon">
	<link href="https://fonts.googleapis.com/css?family=Material+Icons+Outlined" rel="stylesheet">
	<link href="https://cdn.jsdelivr.net/npm/@mdi/font@6.4.95/css/materialdesignicons.min.css" rel="stylesheet">

	<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js" integrity="sha512-894YE6QWD5I59HgZOGReFYm4dnWc1Qt5NtvYSaNcOP+u1T9qYdvdihz0PPSiiqn/+/3e7Jo4EaG7TubfWGUrMQ==" crossorigin="anonymous" referrerpolicy="no-referrer" type="text/javascript"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js" integrity="sha512-uto9mlQzrs59VwILcLiRYeLKPPbS/bT71da/OEBYEwcdNUk8jYIy+D176RYoop1Da+f9mvkYrmj5MCLZWEtQuA==" crossorigin="anonymous" referrerpolicy="no-referrer" type="text/javascript"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui-touch-punch/0.2.3/jquery.ui.touch-punch.min.js" integrity="sha512-0bEtK0USNd96MnO4XhH8jhv3nyRF0eK87pJke6pkYf3cM0uDIhNJy9ltuzqgypoIFXw3JSuiy04tVk4AjpZdZw==" crossorigin="anonymous" referrerpolicy="no-referrer" type="text/javascript"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.8.0/chart.min.js" integrity="sha512-sW/w8s4RWTdFFSduOTGtk4isV1+190E/GghVffMA9XczdJ2MDzSzLEubKAs5h0wzgSJOQTRYyaz73L3d6RtJSg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
	
	<?php 
		\thusPi\Frontend\load_scripts();
		\thusPi\Frontend\load_stylesheets();
		\thusPi\Frontend\load_themes();
		\thusPi\Frontend\load_category_css();
	?>
	<title>thusPi</title>
</head>
<body data-reduced-motion="<?php echo(bool_to_str(\thusPi\Users\CurrentUser::getSetting('reduced_motion'))); ?>">
	<?php \thusPi\Frontend\print_element('sidenav'); ?>
	<main class="container"></main>
	<div class="page-status" data-status="animating_loading">
		<i class="far fa-spinner-third fa-spin fa-3x text-blue mb-2"></i>
		<h2 class="d-block px-2"><?php echo(\thusPi\Locale\translate('generic.loading')); ?></h2>
	</div>
	<div class="page-status" data-status="error">
		<i class="far fa-exclamation-circle fa-3x text-red mb-2"></i>
		<h2 class="d-block px-2"><?php echo(\thusPi\Locale\translate('generic.error')); ?></h2>
	</div>
	<div class="message-area"></div>
</body>
</html>