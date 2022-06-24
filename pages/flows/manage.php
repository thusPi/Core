<?php 
	// Determine if the user is creating a new flow 
	// or editing an existing one one
	$action = isset($_GET['id']) ? 'edit' : 'create';

	if($action == 'edit') {
		$flow = @new \thusPi\Flows\Flow($_GET['id']);
		if(is_null($flow->getProperties())) {
			$action = 'create';
		}
	}
		
	// =================================== //
	//                                     //
	//                BLOCKS               //
	//                                     //
	// =================================== //
	
	// List of all available blocks
	$blocks_features = \thusPi\Extensions\list_all_features('flows/blocks');

	// Variable for storing extension translations to speed up the translation process
	$extension_translations = [];

	$blocks = [];
	// Loop the blocks to add save their manifest
	foreach ($blocks_features as $feature) {
		$extension  = new \thusPi\Extensions\Extension($feature['extension_id']);
		$manifest = @json_decode($extension->callFeatureComponent('flows/blocks', $feature['feature']['id'], 'manifest.json'), true);
		$translation_prefix = "features.flows/blocks.{$feature['feature']['id']}.";

		// Continue if the manifest is invalid
		if(!isset($manifest) || empty($manifest) || !is_array($manifest)) {
			continue;
		}

		$manifest = array_replace([
			'title' => null,
			'content' => null,
			'parameters' => []
		], $manifest);

		// Load extension translations if not already loaded
		if(!isset($extension_translations[$feature['extension_id']])) {
			$extension_translations[$feature['extension_id']] = $extension->getTranslations();
		} 

		// Translate block title
		$manifest['title'] = thusPi\Locale\translate($translation_prefix.'title', [], null, $extension_translations[$feature['extension_id']]);
		
		// Translate block content
		$manifest['content'] = thusPi\Locale\translate($translation_prefix.'content', [], null, $extension_translations[$feature['extension_id']]);

		$blocks[$feature['feature']['id']] = $manifest;
	}
	
	// =================================== //
	//                                     //
	//                OPTIONS              //
	//                                     //
	// =================================== //

	$options_features = \thusPi\Extensions\list_all_features('flows/options');

	$options = [];
	foreach ($options_features as $feature) {
		$extension  = new \thusPi\Extensions\Extension($feature['extension_id']);
		$options_tmp = @json_decode($extension->callFeatureComponent('flows/options', $feature['feature']['id'], 'main'), true);

		// Load extension translations if not already loaded
		if(!isset($extension_translations[$feature['extension_id']])) {
			$extension_translations[$feature['extension_id']] = $extension->getTranslations();
		}

		if(!is_array($options_tmp)) {
			continue;
		}

		// Translate the options
		foreach ($options_tmp as &$option_tmp) {
			if(!isset($option_tmp['value'])) {
				continue;
			}

			// Translate the option, using ['value'] as fallback
			$option_tmp['text'] = $option_tmp['text'] ?? $option_tmp['value'];
			$option_tmp['text'] = \thusPi\Locale\translate(
				"features.flows/options.{$feature['feature']['id']}.{$option_tmp['value']}",
				[], 
				$option_tmp['text'], 
				$extension_translations[$feature['extension_id']]
			);
		}
		
		// Continue if the json is invalid
		if(!isset($options_tmp) || empty($options_tmp) || !is_array($options_tmp)) {
			continue;
		}

		$values[$feature['feature']['id']] = $options_tmp;
	}
		
	// =================================== //
	//                                     //
	//              VARIABLES              //
	//                                     //
	// =================================== //

	// List of all available variables
	$features_variables = \thusPi\Extensions\list_all_features('flows/variables');
	
	$variables = [];
	foreach ($features_variables as $feature) {
		$extension  = new \thusPi\Extensions\Extension($feature['extension_id']);
		$manifest = @json_decode($extension->callFeatureComponent('flows/variables', $feature['feature']['id'], 'manifest.json'), true);
		$translation_prefix = "features.flows/variables.{$feature['feature']['id']}.";

		// Translate variable content
		$manifest['content'] = thusPi\Locale\translate($translation_prefix.'content', [], null, $extension_translations[$feature['extension_id']]);

		list($group_id, $variable_id) = explode('/', $feature['feature']['id']);

		$variables[$group_id][$variable_id] = $manifest;
	}
?>
<script>
	thusPiAssign('data.flows.options', <?php echo(json_encode($values)); ?>);
	thusPiAssign('data.flows.blocks', <?php echo(json_encode($blocks)); ?>);
	thusPiAssign('data.flows.variables', <?php echo(json_encode($variables)); ?>);
</script>
<div class="flow-editor-container transition-slide-top flex-row">
	<div class="flow-editor-body col">
		<div class="flow-editor" id="thuspi-flow-editor"></div>
	</div>
	<!-- <div class="flow-editor-toolbox tile col-auto h-100" id="thuspi-flow-editor-toolbox">
		<div class="tile-content flex-column">
			aaa
		</div>
	</div> -->
</div>
<!-- Templates -->
<!-- <div class="template flow-component tile transition-fade-order">
	<h3 class="flow-component-title tile-title"></h3>
	<div class="flow-component-content tile-content"></div>
</div>
<span class="template input flow-component-parameter" contenteditable="true"></span>
<span class="template input flow-component-parameter" data-type="search" contenteditable="true"></span>
<div class="template flow-variable flex-row transition-fade-order" data-flow-variable-type>
	<span class="flow-variable-icon"></span>
	<div class="flow-variable-content"></div>
</div> -->