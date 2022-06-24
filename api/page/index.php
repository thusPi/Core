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
	$page_path    = trim($url['path'], '/');
	$script_src   = DIR_PAGES."/{$page_path}.php";
	$manifest_src = DIR_PAGES."/{$page_path}.json";
	
	// Load manifest
	if(!$manifest = file_get_json($manifest_src)) {
		\thusPi\response\error('error_loading_manifest');
	}

	if(isset($manifest['admin_only']) && $manifest['admin_only'] == true && \thusPi\Users\CurrentUser::getFlag('is_admin') !== true) {
		\thusPi\response\error('no_permission');
	}
	
	// Load page content
	if(!$html = get_script_output($script_src)) {
		\thusPi\response\error('error_loading_content');
	}

	// Generate page title
	$page_path_split = explode('/', $page_path);
	$page_title = [];

	foreach ($page_path_split as $level => $subpage) {
		if($subpage == 'main') {
			continue;
		}

		$translate_key            = implode('.', array_slice($page_path_split, 0, $level+1));
		$subpage_title_translated = \thusPi\Locale\translate("generic.page.{$translate_key}.title");

		array_push($page_title, $subpage_title_translated);
	}

	$seperator = \thusPi\Locale\translate('generic.page_seperator');

	$response = [
		'manifest' => $manifest,
		'html'     => $html,
		'title'    => implode(" {$seperator} ", $page_title)
	];

	\thusPi\response\success('success_loaded', $response);
?>