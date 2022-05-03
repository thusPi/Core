<?php
	chdir(__DIR__);
	include_once("../../../autoload.php");
?>
<?php 
    if(count($argv) <= 1) {
		return false;
	}

	// Arguments
	list($feature_path, $extension_id, $feature_type, $feature_name) = $argv;
	
    // Save the extension id as a constant
    define('EXTENSION_ID', $extension_id);
	define('TRANSLATION_PREFIX', "features.{$feature_type}.{$feature_name}.");

	// Make only the constant accessible
	unset($extension_id);
?>