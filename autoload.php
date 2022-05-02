<?php
	// Make script automatically exit after one minute
	set_time_limit(60);

	// Start session
	session_start();

	// Set timezone
	date_default_timezone_set('UTC');

	// Constant directories
	define('SERVER_ROOT',     rtrim(__DIR__, '/'));
	define('DIR_ASSETS',      SERVER_ROOT.'/assets');
	define('DIR_CONFIG',      SERVER_ROOT.'/config');
	define('DIR_DATA',        SERVER_ROOT.'/data');
	define('DIR_EXTENSIONS',  SERVER_ROOT.'/extensions');
	define('DIR_PAGES',       SERVER_ROOT.'/pages');
	define('DIR_SYSTEM',      SERVER_ROOT.'/system');
	define('DIR_NAMESPACES',  DIR_ASSETS.'/php/namespaces');
	define('DIR_CLASSES',     DIR_ASSETS.'/php/classes');

	// Constant files
	define('FIL_FUNCTIONS',   DIR_ASSETS.'/php/load/functions.php');

	// Include composer autoload
	include_once(SERVER_ROOT.'/vendor/autoload.php');

	// Include base functions and classes
	include_once(FIL_FUNCTIONS);

	// Load config
	define('CONFIG',          include_configs());

	if(!class_exists('mysqli')) {
		exit('Class mysqli does not exist. Try installing the mysqli extension.');
	} 

	// Include database support
	include_classes('MysqliDb');

	// Load namespaces
	include_namespaces(
		'Debug',
		'Log',
		'Response',
		'Config', 
		'Authorization', 
		'Database',
		'Interfaces',
		'Processes',
		'Extensions',
		'Widgets',
		'Categories',
		'Devices', 
		'Users',
		'Streams',
		'Analytics',
		'Dashboard',
		'Locale',
		'Frontend'
	);
?>