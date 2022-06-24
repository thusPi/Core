<?php
	// Make script automatically exit after one minute
	set_time_limit(60);

	// Start session
	session_start();

	// Set timezone to UTC
	date_default_timezone_set('UTC');

	// Constant directories
	define('SERVER_ROOT',     rtrim(__DIR__, DIRECTORY_SEPARATOR));
	define('DIR_ASSETS',      SERVER_ROOT.'/assets');
	define('DIR_CONFIG',      SERVER_ROOT.'/config');
	define('DIR_DATA',        SERVER_ROOT.'/data');
	define('DIR_EXTENSIONS',  SERVER_ROOT.'/extensions');
	define('DIR_PAGES',       SERVER_ROOT.'/pages');
	define('DIR_SYSTEM',      SERVER_ROOT.'/system');
	define('DIR_NAMESPACES',  DIR_ASSETS.'/php/namespaces');
	define('DIR_LIBRARIES',   DIR_ASSETS.'/php/libraries');

	// Constant files
	define('FIL_FUNCTIONS',   DIR_ASSETS.'/php/load/functions.php');

	// Include composer autoload
	include_once(SERVER_ROOT.'/vendor/autoload.php');

	// Include some standard functions
	include_once(FIL_FUNCTIONS);

	// Load config
	define('CONFIG',          include_configs());

	if(!class_exists('mysqli')) {
		exit('Class mysqli does not exist. Try installing the mysqli extension.');
	} 

	// Include libraries
	include_libraries(
		'MysqliDb',
		'SVGGraph/autoloader'
	);

	// Load namespaces
	include_namespaces(
		'thusPi/Debug',
		'thusPi/Log',
		'thusPi/Response',
		'thusPi/Config', 
		'thusPi/Authorization', 
		'thusPi/Database',
		'thusPi/Interfaces',
		'thusPi/Processes',
		'thusPi/Extensions',
		'thusPi/Widgets',
		'thusPi/Categories',
		'thusPi/Devices', 
		'thusPi/Users',
		'thusPi/Flows',
		'thusPi/Recordings',
		'thusPi/Dashboard',
		'thusPi/Locale',
		'thusPi/Frontend'
	);

	// Try to authorize the user. If this fails,
	// authorize the user as guest
	if(!\thusPi\Authorization\authorize()) {
		$_SESSION['thusPi_uuid'] = '__GUEST__';
	}
?>