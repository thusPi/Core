<?php 
	$device_id = $_GET['id'] ?? null;

	if(isset($device_id)) {
		$device = new \thusPi\Devices\Device($device_id);
		$properties = $device->getProperties();
	}
	
	$action = isset($device_id) ? 'manage' : 'create';
?>
<form class="form transition-slide-top" method="POST">
	<!-- Name -->
	<div class="form-group col-12 col-md-6">
		<h3 class="form-group-title"><?php echo(\thusPi\Locale\translate('admin.devices.manage_device.name.title')); ?></h3>
		<span class="form-group-description"><?php echo(\thusPi\Locale\translate('admin.devices.manage_device.name.description')); ?></span>
		<input name="name" class="form-group-input" type="text" placeholder="<?php echo(\thusPi\Locale\translate('admin.devices.manage_device.name.title')); ?>" value="<?php echo($properties['name'] ?? ''); ?>" />
	</div>
	<!-- Handler -->
	<div class="form-group col-12 col-md-6">
		<h3 class="form-group-title" data-tooltip="bbbb"><?php echo(\thusPi\Locale\translate('admin.devices.manage_device.handler.title')); ?></h3>
		<span class="form-group-description"><?php echo(\thusPi\Locale\translate('admin.devices.manage_device.handler.description')); ?></span>
		<input name="handler" class="form-group-input" type="text" data-type="search" placeholder="<?php echo(\thusPi\Locale\translate('admin.devices.manage_device.handler.title')); ?>">
		<ul class="input-search-results" for="handler">
			<?php 
				// Print list of all installed device handlers
				$handlers = \thusPi\Extensions\list_all_features('devices/handlers');
				foreach ($handlers as $handler) {
					$extension = new \thusPi\Extensions\Extension($handler['extension_id']);
					$manifest  = @json_decode($extension->callFeatureComponent('devices/handlers', $handler['feature']['id'], 'manifest.json'), true) ?? [];

					$handler_selected = isset($device) && $handler['feature']['id'] === $device->getProperty('handler');

					if(isset($manifest['name']) && isset($manifest['description'])) {
						echo("<li ".($handler_selected ? 'selected ' : '')."value=\"{$handler['feature']['id']}\" data-description=\"{$manifest['description']}\">{$manifest['name']}</li>");
					} else {
						echo("<li ".($handler_selected ? 'selected ' : '')."value=\"{$handler['feature']['id']}\">{$handler['feature']['id']}</li>");
					}				
				}
			?>
		</ul>
	</div>
	<!-- Icon -->
	<div class="form-group col-12 col-md-6">
		<h3 class="form-group-title" data-tooltip="bbbb"><?php echo(\thusPi\Locale\translate('admin.devices.manage_device.icon.title')); ?></h3>
		<span class="form-group-description"><?php echo(\thusPi\Locale\translate('admin.devices.manage_device.icon.description')); ?></span>
		<input type="text" data-type="search" name="icon">
		<ul class="input-search-results" for="icon">
			<?php 
				$categories = \thusPi\Categories\get_all();

				// Generate a list of all available icons
				foreach ($categories as $category_id => $category) {
					if(!isset($category['icons'])) {
						continue;
					}

					$category_name = \thusPi\Locale\translate("generic.category.{$category_id}.title");
					
					// Print icon
					foreach ($category['icons'] as $icon_tags => $icon) {
						$result_icon_html = htmlspecialchars(create_icon([
							'icon'       => $icon, 
							'classes'    => ['text-category'],
							'scale'      => 'lg',
							'attributes' => ['data-category' => $category_id]
						]));

						$result_match       = $icon_tags;
						$result_title       = ucfirst(strtok($icon_tags, ','));
						$result_selected    = isset($device) && $icon === $device->getProperty('icon');

						echo("<li ".($result_selected ? 'selected ' : '')."value=\"{$icon}\" data-icon-html=\"{$result_icon_html}\" data-description=\"{$result_match}\" data-match=\"{$result_match}\">{$result_title}</li>");
					}
				}
			?>
		</ul>
	</div>
	<!-- Properties -->
	<div class="form-group col-12 col-md-6">
		<h3 class="form-group-title" data-tooltip="bbbb"><?php echo(\thusPi\Locale\translate('admin.devices.manage_device.properties.title')); ?></h3>
		<span class="form-group-description"><?php echo(\thusPi\Locale\translate('admin.devices.manage_device.properties.description')); ?></span>
	</div>
		<!-- <div class="device-setting col-12 col-md-6 mb-2 hidden" data-setting="device_options">
			<script>loadHandlerOptions('<?php echo($properties['handler']); ?>');</script>
			<h3 class="tile-title"><?php echo(\thusPi\Locale\translate('admin.devices.manage_device.options.title')); ?></h3>
			<div class="device-options-inner">
				<div class="row rounded overflow-hidden device-option-template mb-1 bg-tertiary">
					<div class="col-4 px-0 border-right border-secondary">
						<div class="option-key text-muted input text-overflow-ellipsis pr-1 d-table-cell">
							<span></span>
							<div class="tooltip"></div>
						</div>
					</div>
					<div class="col-8 px-0">
						<input class="option-value" type="text">
					</div>
				</div>
			</div>
		</div>
		<div class="device-setting col-12 col-md-6 mb-2" data-setting="category">
			<h3 class="tile-title"><?php echo(\thusPi\Locale\translate('admin.devices.manage_device.devices.grouptitle')); ?></h3>
			<div class="dropdown-search-wrapper">
				<div class="bg-tertiary dropdown-search-results" data-type="single" forinput="device_group">
					<?php 
						if($device_groups = @file_get_json("{$d['config']}/device_groups.json")) {
							foreach ($device_groups as $namespace => $info) { ?>
								<span class="btn btn-secondary dropdown-search-result" val="<?php echo($namespace); ?>" data-match="<?php echo(\thusPi\Locale\translate("devices.devices.group{$namespace}.title")); ?>"><?php echo(@icon_html($info['icon'], 'dropdown-search-result-icon-end', "color: {$info['color']};")); ?><?php echo(\thusPi\Locale\translate("devices.devices.group{$namespace}.title")); ?></span> 	
							<?php }
						} 
					?>
					<span class="no-results dropdown-search-result"><?php echo(\thusPi\Locale\translate('generic.error.search_no_results')); ?></span>
				</div>
				<input class="dropdown-search" data-name="device_group" type="text" value="<?php echo(\thusPi\Locale\translate("devices.devices.group{$properties['group']}.title")); ?>"/>
				<input data-setting="category" class="dropdown-search-hidden" type="hidden" name="device_group" value="<?php echo($properties['group']); ?>" />
			</div>
		</div>
		<div class="device-setting col-12 col-md-6 mb-2" data-setting="control_type">
			<h3 class="tile-title"><?php echo(\thusPi\Locale\translate('admin.devices.manage_device.input_type.title')); ?></h3>
			<div class="dropdown-search-wrapper">
				<div class="bg-tertiary dropdown-search-results" data-type="single" data-user-editable="false" forinput="device_control_type">
					<?php 
						$device_control_types = ['buttons', 'none', 'search', 'range', 'toggle'];

						foreach ($device_control_types as $control_type) { ?>
							<span class="btn btn-secondary dropdown-search-result" val="<?php echo($control_type); ?>"><?php echo(\thusPi\Locale\translate("admin.devices.manage_device.input_type.option.{$control_type}.title", true, [], ucfirst($control_type))); ?></span> 	
						<?php }
					?>
					<span class="no-results dropdown-search-result"><?php echo(\thusPi\Locale\translate('generic.error.search_no_results')); ?></span>
				</div>
				<input class="dropdown-search" data-name="device_control_type" type="text" value="<?php echo(\thusPi\Locale\translate("admin.devices.manage_device.input_type.option.{$properties['control_type']}.title", true, [], ucfirst($properties['control_type']))); ?>"/>
				<input data-setting="control_type" class="dropdown-search-hidden" type="hidden" name="device_control_type" value="<?php echo($properties['control_type']); ?>" />
			</div>
		</div> -->
		<!-- <div class="device-setting col-12 col-md-6 mb-2 hidden" data-setting="draw_graphs">
			<h3 class="tile-title"><?php echo(\thusPi\Locale\translate('admin.devices.manage_device.draw_graphs.title')); ?>
				<span class="tooltip" data-tooltip="<?php echo(\thusPi\Locale\translate('admin.devices.manage_device.draw_graphs.tooltip')); ?>"></span>
			</h3>
			<div class="dropdown-search-wrapper">
				<div class="bg-tertiary dropdown-search-results" data-type="single" forinput="device_control_type">
					<?php 
						$recordings_intervals = [
							0     =>\thusPi\Locale\translate('state.never'),
							1     =>\thusPi\Locale\translate('generic.interval.minute'),
							2     =>\thusPi\Locale\translate('generic.interval.minutes', false, [2]),
							5     =>\thusPi\Locale\translate('generic.interval.minutes', false, [5]),
							10    =>\thusPi\Locale\translate('generic.interval.minutes', false, [10]),
							30    =>\thusPi\Locale\translate('generic.interval.minutes', false, [30]),
							60    =>\thusPi\Locale\translate('generic.interval.hour'),
							120   =>\thusPi\Locale\translate('generic.interval.hours', false, [2]),
							180   =>\thusPi\Locale\translate('generic.interval.hours', false, [3]),
							360   =>\thusPi\Locale\translate('generic.interval.hours', false, [6]),
							720   =>\thusPi\Locale\translate('generic.interval.hours', false, [12]),
							1440  =>\thusPi\Locale\translate('generic.interval.day'),
							2880  =>\thusPi\Locale\translate('generic.interval.days', false, [2]),
							7200  =>\thusPi\Locale\translate('generic.interval.days', false, [5]),
							10080 =>\thusPi\Locale\translate('generic.interval.week'),
							20160 =>\thusPi\Locale\translate('generic.interval.weeks', false, [2]),
							30240 =>\thusPi\Locale\translate('generic.interval.weeks', false, [3]),
							40320 =>\thusPi\Locale\translate('generic.interval.weeks', false, [4]),
						];

						foreach ($recordings_intervals as $seconds => $translation) { ?>
							<span class="btn btn-secondary dropdown-search-result" val="<?php echo($seconds); ?>"><?php echo($translation); ?></span> 	
						<?php }
					?>
					<span class="no-results dropdown-search-result"><?php echo(\thusPi\Locale\translate('generic.error.search_no_results')); ?></span>
				</div>
				<input class="dropdown-search" data-name="device_control_type" type="text" value="<?php echo($recordings_intervals[0]); ?>"/>
				<input data-setting="control_type" class="dropdown-search-hidden" type="hidden" name="device_control_type" value="0" />
			</div>
		</div> -->
</form>
<button class="btn bg-secondary btn-green btn-floating transition-slide-right">
	<?php echo(create_icon('far.check', 'xl')); ?>
</button>