<?php 
	chdir(__DIR__);
	include_once("{$_SERVER['DOCUMENT_ROOT']}/autoload.php");
?>
<?php
	if(!isset($_POST['url'])) {
		\thusPi\Response\error('request_field_missing', 'Field url is missing.');
	}

	if($_POST['url'] == 'home/main') {
		$_POST['url'] = \thusPi\Users\CurrentUser::getSetting('home') ?? 'dashboard/main';
	}

	$url = array_replace(
		['path' => '', 'query' => ''], 
		parse_url($_POST['url'])
	);

	// Obtain url query and modify $_GET variable
	parse_str($url['query'], $query);
	$_GET = $query;

	// Get page path
	$page_path     = trim($url['path'], '/');
	$content_path  = DIR_PAGES."/{$page_path}.php";
	$manifest_path = DIR_PAGES."/{$page_path}.json";
	
	// Load manifest
	if(!$manifest = file_get_json($manifest_path)) {
		\thusPi\response\error('error_loading_manifest');
	}
	
	// Load page content
	if(!$content = get_script_output($content_path)) {
		\thusPi\response\error('error_loading_content');
	}

	$response = [
		'manifest' => $manifest,
		'html'     => $content
	];

	\thusPi\response\success('success_loaded', $response);
?>