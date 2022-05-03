<?php
	chdir(__DIR__);
	include_once("../../../autoload.php");
?>
<?php 
    if(count($argv) <= 1) {
		return false;
	}

	// Arguments
	list($feature_path, $extension_id, $feature_type, $feature_name, $user_locale) = $argv;
	
    // Save the extension id as a constant
    define('EXTENSION_ID', $extension_id);
	define('TRANSLATION_PREFIX', "features.{$feature_type}.{$feature_name}.");
	define('EXTENSIONS_OVERRIDE_LOCALE', $user_locale);

	// Make only the constants and session accessible
	unset($feature_path, $extension_id, $feature_type, $feature_name, $user_locale);
?>