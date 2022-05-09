<?php 
	// Determine if the user is creating a new flow 
	// or editing one
	$action = isset($_GET['id']) ? 'edit' : 'create';

	if($action == 'edit') {
		$flow = new \thusPi\Streams\Stream($_GET['id']);
		if(!isset($stream)) {
			$action = 'create';
		}
	}
		
	// =================================== //
	//                                     //
	//              COMPONENTS             //
	//                                     //
	// =================================== //
	
	// List of all available components
	$features_components = \thusPi\Extensions\list_all_features('streams/components');

	// Variable for storing extension translations to speed up the translation process
	$extension_translations = [];

	$components = [];
	// Loop the components to add save their manifest
	foreach ($features_components as $feature) {
		$extension  = new \thusPi\Extensions\Extension($feature['extension_id']);
		$manifest = @json_decode($extension->callFeatureComponent('streams/components', $feature['feature']['id'], 'manifest.json'), true);
		$translation_prefix = "features.streams/components.{$feature['feature']['id']}.";

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

		// Translate component title
		$manifest['title'] = thusPi\Locale\translate($translation_prefix.'title', [], null, $extension_translations[$feature['extension_id']]);
		
		// Translate component content
		$manifest['content'] = thusPi\Locale\translate($translation_prefix.'content', [], null, $extension_translations[$feature['extension_id']]);

		$components[$feature['feature']['id']] = $manifest;
	}
	
	// =================================== //
	//                                     //
	//                VALUES               //
	//                                     //
	// =================================== //

	$features_values = \thusPi\Extensions\list_all_features('streams/values');

	$values = [];
	foreach ($features_values as $feature) {
		$extension  = new \thusPi\Extensions\Extension($feature['extension_id']);
		$values_tmp = @json_decode($extension->callFeatureComponent('streams/values', $feature['feature']['id'], 'main'), true);

		// Load extension translations if not already loaded
		if(!isset($extension_translations[$feature['extension_id']])) {
			$extension_translations[$feature['extension_id']] = $extension->getTranslations();
		} 

		// Translate the values
		foreach ($values_tmp as &$value_tmp) {
			if(!isset($value_tmp['value'])) {
				continue;
			}

			// Translate the value, using [text] as fallback
			$value_tmp['text'] = $value_tmp['text'] ?? $value_tmp['value'];
			$value_tmp['text'] = \thusPi\Locale\translate("features.streams/values.{$feature['feature']['id']}.{$value_tmp['value']}", [], $value_tmp['text'], $extension_translations[$feature['extension_id']]);
		}
		
		// Continue if the json is invalid
		if(!isset($values_tmp) || empty($values_tmp) || !is_array($values_tmp)) {
			continue;
		}

		$values[$feature['feature']['id']] = $values_tmp;
	}
		
	// =================================== //
	//                                     //
	//              VARIABLES              //
	//                                     //
	// =================================== //

	// List of all available variables
	$features_variables = \thusPi\Extensions\list_all_features('streams/variables');

	$variables = [];
	foreach ($features_variables as $feature) {
		$extension  = new \thusPi\Extensions\Extension($feature['extension_id']);
		$manifest = @json_decode($extension->callFeatureComponent('streams/variables', $feature['feature']['id'], 'manifest.json'), true);
		$translation_prefix = "features.streams/variables.{$feature['feature']['id']}.";

		// Translate variable content
		$manifest['content'] = thusPi\Locale\translate($translation_prefix.'content', [], null, $extension_translations[$feature['extension_id']]);

		list($group_id, $variable_id) = explode('/', $feature['feature']['id']);

		$variables[$group_id][$variable_id] = $manifest;
	}
?>
<script>
	thusPiAssign('data.streams.values', <?php echo(json_encode($values)); ?>);
	thusPiAssign('data.streams.components', <?php echo(json_encode($components)); ?>);
	thusPiAssign('data.streams.variables', <?php echo(json_encode($variables)); ?>);
</script>
<div class="stream-editor flex-column">
	<span data-icon="far.car"></span>
	<div class="flex-row">
		<div class="flex-column stream-variables-lol">
			<div class="stream-variable flex-row" data-stream-variable-type="string">
				<div class="stream-variable-icon" data-icon="far.text" data-icon-scale="xs"></div>
				<div class="stream-variable-name">String</div>
			</div>
			<div class="stream-variable flex-row" data-stream-variable-type="numeric">
				<div class="stream-variable-icon" data-icon="far.sun" data-icon-scale="xs"></div>
				<div class="stream-variable-name">Position of the sun</div>
			</div>
			<div class="stream-variable flex-row" data-stream-variable-type="date">
				<div class="stream-variable-icon" data-icon="far.sunrise" data-icon-scale="xs"></div>
				<div class="stream-variable-name">Sunrise</div>
			</div>
			<div class="stream-variable flex-row" data-stream-variable-type="date">
				<div class="stream-variable-icon" data-icon="far.sunset" data-icon-scale="xs"></div>
				<div class="stream-variable-name">Sunset</div>
			</div>
			<div class="stream-variable flex-row stream-user-variable" data-stream-variable-type="numeric">
				<div class="stream-variable-icon" data-icon="far.hashtag" data-icon-scale="xs"></div>
				<div class="stream-variable-name">Numeric</div>
			</div>
		</div>
		<div class="flex-column stream-components-lol">
			<div class="stream-component tile">
				<h3 class="stream-component-title tile-title">Apparaat wordt ingesteld</h3>
				<div class="stream-component-content tile-content">
					<span class="stream-component-text">Als apparaat</span>
					<input class="stream-component-parameter" type="text" data-type="search" data-stream-input-values-index="devices/ids_changeable">
				</div>
			</div>
		</div>
	</div>
	<div class="tile transition-fade-order">
		<div class="tile-icon text-yellow" data-icon="far.bolt" data-icon-scale="xl"></div>
		<h3 class="tile-title">Event</h3>
	</div>
	<div class="stream-components flex-column" data-family="event">
		<div class="tile">
			<div class="tile-content">
				<span class="input stream-component-parameter" data-stream-input-accept-type="string">Only string</span>
				<span class="input stream-component-parameter" data-stream-input-accept-type="numeric">Only numeric</span>
				<span class="input stream-component-parameter" data-stream-input-accept-type="date">Only date</span>
				<span class="input stream-component-parameter" data-stream-input-accept-type="string,numeric,date">All</span>
			</div>
		</div>
	</div>
	<div class="tile transition-fade-order">
		<div class="tile-icon text-blue" data-icon="far.question-circle" data-icon-scale="xl"></div>
		<h3 class="tile-title">Condition</h3>
	</div>
	<div class="tile transition-fade-order">
		<div class="tile-icon text-green" data-icon="far.check-circle" data-icon-scale="xl"></div>
		<h3 class="tile-title">Do</h3>
		<span class="tile-subtitle">If...</span>
	</div>
	<div class="tile transition-fade-order">
		<div class="tile-icon text-red" data-icon="far.times-circle" data-icon-scale="xl"></div>
		<h3 class="tile-title">Else</h3>
		<span class="tile-subtitle">If not...</span>
	</div>
</div>
<!-- Templates -->
<div class="template stream-component tile transition-fade-order">
	<h3 class="stream-component-title tile-title"></h3>
	<div class="stream-component-content tile-content"></div>
</div>
<span class="template stream-component-text"></span>
<span class="template stream-component-parameter"></span>
<input class="template stream-component-parameter" data-type="search" data-stream-input-values-index type="text">
<div class="template stream-variable flex-row" data-stream-variable-group>
	<span class="stream-variable-icon"></span>
	<div class="stream-variable-content"></div>
</div>