<?php
	chdir(__DIR__);
	include_once("../../../autoload.php");
?>
<?php 
    if(count($argv) <= 3) {
		return false;
	}

	// Decode arguments
	$hook_args = decodeshellargarray($argv[4]);
	
	// Split the arguments to seperate variables
	list($extension_id, $hook_type, $hook_name) = array_slice($argv, 1);

	class Hook {
		// These properties are always present
		public $hookType, $hookGroup, $hookName, $extensionId, $extension;

		// The presence of these properties depend on the hook group
		public $deviceId, $deviceValue, $deviceShownValue, $recordingData, $pageId;

		public function __construct($extension_id, $hook_type, $hook_name, $hook_args = []) {
			$this->hookType     = $hook_type;
			$this->hookGroup    = strtok($this->hookType, '/');
			$this->hookName     = $hook_name;
			$this->extensionId  = $extension_id;
			$this->extension    = new \thusPi\Extensions\Extension($this->extensionId);
	
			switch($this->hookGroup) {
				case 'devices':
					list($this->deviceId, $this->deviceValue, $this->deviceShownValue) = $hook_args;
					break;

				case 'recordings':
					list($this->deviceId, $this->recordingData) = $hook_args;
					break;

				case 'pages':
					list($this->pageId) = $hook_args;
					break;
			}
		}
	}

	$HOOK = new Hook($extension_id, $hook_type, $hook_name, $hook_args);

	if(!isset($HOOK)) {
		exit();
	}

	// Unset all unnessecary variables so the actual handler can't access them
	unset($hook_type, $hookGroup, $hook_args, $extension, $extension_id, $extension_data, $argv, $arg);
?>