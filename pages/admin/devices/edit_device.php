<?php 
	if($devices = get_device_list()) {
		while (!isset($device_id) || isset($devices[$device_id])) {
			$device_id = bin2hex(random_bytes(2));
		}
	}
	$new_device = true;

	if(isset($_GET['device_id'])) {
		$device_id = $_GET['device_id']; 
		$new_device = false; 
	}

	$device = get_device_info($device_id);
?>
<form method="POST" action="../_handlers/edit_device.php" id="form-edit-device" data-device-key="<?php echo($_GET['device_id']); ?>">
	<div class="tile transition-slide-top">
		<div class="row">
			<div class="device-setting col-12 col-md-6 mb-2" data-setting="name">
				<h3 class="tile-title"><?php \thusPi\Locale\translate('admin.devices.edit_device.name.title', true); ?></h3>
				<input data-setting="name" name="device_name" placeholder="<?php \thusPi\Locale\translate('admin.devices.edit_device.name.title', true); ?>" value="<?php echo($device['name']); ?>" type="text" />
			</div>
			<div class="device-setting col-12 col-md-6 mb-2" data-setting="handler">
				<h3 class="tile-title"><?php \thusPi\Locale\translate('admin.devices.edit_device.handler.title', true); ?>
					<span class="tooltip" data-tooltip="<?php \thusPi\Locale\translate('admin.devices.edit_device.handler.tooltip', true); ?>"></span>
				</h3>
				<div class="dropdown-search-wrapper">
					<div class="bg-tertiary dropdown-search-results" data-type="single" forinput="device_handler">
						<?php 
							if($new_device) {
								$device_manifest = ['name' => '', 'namespace' => 'none'];
							} else {
								$device_manifest = ['name' =>\thusPi\Locale\translate('state.unknown'), 'namespace' => 'none'];
							}
							if($manifest_files = glob("{$d['assets']}/device_handlers/private/*/_manifest.json")) {
								foreach ($manifest_files as $manifest_file) {
									if($manifest = \thusPi\Devices\get_handler_manifest(basename(dirname($manifest_file)))) {
										$manifest['icon'] = '';
										$manifest['namespace'] = basename(dirname($manifest_file));

										if($manifest['analytics_support'] == true) {
											$manifest['icon'] .= icon_html('far.chart-area', 'icon-inline dropdown-search-result-icon-end text-debug');
										}

										if($manifest['control_support'] == true) {
											$manifest['icon'] .= icon_html('mdi.devices', 'icon-inline dropdown-search-result-icon-end text-success');
										}

										?>
										<span class="btn btn-secondary dropdown-search-result" val="<?php echo($manifest['namespace']); ?>"><?php echo($manifest['name']); ?> <?php echo($manifest['icon']); ?></span> 
										<?php

										if(isset($device['handler'])) {
											if($manifest['namespace'] == $device['handler']) {
												$device_manifest = $manifest;
											}
										} else {
											$device['handler'] = '';
											$device_manifest['name'] = \thusPi\Locale\translate('state.unset');
										}
									}
								}
							}
						?>
						<span class="no-results dropdown-search-result"><?php \thusPi\Locale\translate('generic.error.search_no_results', true); ?></span>
					</div>
					<input class="dropdown-search" data-name="device_handler" type="text" value="<?php echo($device_manifest['name']); ?>"/>
					<input data-setting="handler" onchange="loadHandlerOptions($(this).val(), true);" class="dropdown-search-hidden" type="hidden" name="device_handler" value="<?php echo($device_manifest['namespace']); ?>" />
				</div>
			</div>
			<div class="device-setting col-12 col-md-6 mb-2" data-setting="icon">
				<h3 class="tile-title"><?php \thusPi\Locale\translate('admin.devices.edit_device.icon.title', true); ?></h3>
				<?php echo(input_iconpicker([
					'var(--debug)' => [ // Music
						'mdi.amplifier',
						'mdi.boombox',
						'mdi.disc-player',
						'mdi.dolby',
						'mdi.soundbar',
						'mi.speaker',
						'mi.pause',
						'mi.play_arrow',
						'far.radio-alt',
						'mdi.record-player',
						'mi.tune',
						'far.turntable',
						'mi.volume_up',
						'_ROW_'
					],
					'var(--success)' => [ // Media
						'mdi.audio-video',
						'far.popcorn',
						'far.presentation',
						'far.projector',
						'mdi.projector-screen-variant-outline',
						'mdi.remote',
						'mdi.soundbar',
						'mdi.theater',
						'mi.TV',
						'_ROW_'
					],
					'var(--warning)' => [ // Lighting
						'mdi.alarm-light-outline',
						'mdi.ceiling-fan-light',
						'mdi.ceiling-light-outline',
						'mdi.chandelier',
						'mdi.coach-lamp',
						'mdi.desk-lamp',
						'mdi.floor-lamp-outline',
						'mdi.floor-lamp-dual-outline',
						'mdi.floor-lamp-torchiere-variant-outline',
						'mdi.globe-light',
						'mdi.lamp-outline',
						'mdi.lamps-outline',
						'mdi.lava-lamp',
						'mdi.led-outline',
						'mdi.led-strip',
						'mi.light',
						'mi.lightbulb',
						'mdi.lightbulb-fluorescent-tube-outline',
						'mdi.light-switch',
						'mdi.outdoor-lamp',
						'mdi.string-lights',
						'mdi.spotlight',
						'mdi.television-ambient-light',
						'mdi.track-light',
						'mdi.vanity-light',
						'mdi.wall-sconce-outline',
						'mdi.wall-sconce-round-outline',
						'mdi.wall-sconce-round-variant-outline',
						'mdi.wall-sconce-flat-outline',
						'mdi.wall-sconce-flat-variant-outline',
						'_ROW_',
					],
					'var(--danger)' => [ // Climate
						'mdi.air-conditioner',
						'mdi.air-filter',
						'mdi.air-humidifier',
						'mdi.air-purifier',
						'far.clouds',
						'far.humidity',
						'mdi.radiator',
						'far.raindrops',
						'far.snowflake',
						'mdi.sprinkler',
						'mdi.sprinkler-variant',
						'far.sort-circle',
						'far.thermometer-empty',
						'far.thermometer-quarter',
						'far.thermometer-half',
						'far.thermometer-three-quarters',
						'far.thermometer-full',
						'mdi.thermostat',
						'mdi.thermostat-box',
						'mdi.water',
						'mdi.waves-arrow-up',
						'far.windsock',
						'_ROW_'
					],
					'var(--info)' => [ // Security
						'mdi.alarm-light-outline',
						'mdi.alarm-panel-outline',
						'far.CCTV',
						'mi.door_front',
						'mi.door_back',
						'mi.door_sliding',
						'mdi.fence',
						'far.fire',	
						'mdi.gate',
						'mi.lock',
						'mi.lock_open',
						'mdi.lock-smart',
						'mdi.motion-sensor',
						'mdi.smoke-detector-outline',
						'_ROW_'
					],
					'var(--caution)' => [ // Appliances
						'mi.balcony',
						'mi.bathtub',
						'mdi.battery-50',
						'mdi.battery-charging-wireless-50',
						'mdi.bed-outline',
						'far.bicycle',
						'mi.blender',
						'mdi.blinds',
						'far.blinds',
						'mdi.bunk-bed-outline',
						'far.bus',
						'far.car',
						'mdi.ceiling-fan',
						'mi.coffee_maker',
						'mi.countertops',
						'mdi.curtains',
						'mdi.devices',
						'mdi.dishwasher',
						'far.fan',
						'far.film',
						'mdi.fireplace',
						'mdi.fridge-outline',
						'mdi.greenhouse',
						'mi.laptop',
						'far.location-arrow',
						'mdi.microwave',
						'mdi.molecule-CO',
						'mdi.molecule-CO2',
						'mdi.mower',
						'mi.NFC',
						'mi.king_bed',
						'mdi.pH',
						'mdi.pipe',
						'mdi.pipe-valve',
						'mdi.pool',
						'mi.power_plug',
						'mi.printer',
						'mdi.printer-3D',
						'mdi.robot-mower-outline',
						'mdi.robot-vacuum-variant',
						'mdi.shower',
						'mi.shower',
						'far.solar-panel',
						'mdi.stove',
						'far.tachometer-alt',
						'mdi.toilet',
						'far.toilet',
						'far.train',
						'mdi.water-boiler',
						'far.washer',
						'mdi.window-open-variant',
						'mdi.window-shutter',
					]
				])); ?>
				<script>$('.device-setting[data-setting="icon"] .input .icon-wrapper[data-icon="<?php echo($device['icon']); ?>"]').addClass('active');</script>
			</div>
			<div class="device-setting col-12 col-md-6 mb-2 hidden" data-setting="device_options">
				<script>loadHandlerOptions('<?php echo($device['handler']); ?>');</script>
				<h3 class="tile-title"><?php \thusPi\Locale\translate('admin.devices.edit_device.options.title', true); ?></h3>
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
				<h3 class="tile-title"><?php \thusPi\Locale\translate('admin.devices.edit_device.devices.grouptitle', true); ?></h3>
				<div class="dropdown-search-wrapper">
					<div class="bg-tertiary dropdown-search-results" data-type="single" forinput="device_group">
						<?php 
							if($device_groups = @file_get_json("{$d['config']}/device_groups.json")) {
								foreach ($device_groups as $namespace => $info) { ?>
									<span class="btn btn-secondary dropdown-search-result" val="<?php echo($namespace); ?>" data-match="<?php \thusPi\Locale\translate("devices.devices.group{$namespace}.title", true); ?>"><?php echo(@icon_html($info['icon'], 'dropdown-search-result-icon-end', "color: {$info['color']};")); ?><?php \thusPi\Locale\translate("devices.devices.group{$namespace}.title", true); ?></span> 	
								<?php }
							} 
						?>
						<span class="no-results dropdown-search-result"><?php \thusPi\Locale\translate('generic.error.search_no_results', true); ?></span>
					</div>
					<input class="dropdown-search" data-name="device_group" type="text" value="<?php \thusPi\Locale\translate("devices.devices.group{$device['group']}.title", true, [], ''); ?>"/>
					<input data-setting="category" class="dropdown-search-hidden" type="hidden" name="device_group" value="<?php echo($device['group']); ?>" />
				</div>
			</div>
			<!-- <div class="device-setting col-12 col-md-6 mb-2" data-setting="control_type">
				<h3 class="tile-title"><?php \thusPi\Locale\translate('admin.devices.edit_device.input_type.title', true); ?></h3>
				<div class="dropdown-search-wrapper">
					<div class="bg-tertiary dropdown-search-results" data-type="single" data-user-editable="false" forinput="device_control_type">
						<?php 
							$device_control_types = ['buttons', 'none', 'search', 'range', 'toggle'];

							foreach ($device_control_types as $control_type) { ?>
								<span class="btn btn-secondary dropdown-search-result" val="<?php echo($control_type); ?>"><?php \thusPi\Locale\translate("admin.devices.edit_device.input_type.option.{$control_type}.title", true, [], ucfirst($control_type)); ?></span> 	
							<?php }
						?>
						<span class="no-results dropdown-search-result"><?php \thusPi\Locale\translate('generic.error.search_no_results', true); ?></span>
					</div>
					<input class="dropdown-search" data-name="device_control_type" type="text" value="<?php \thusPi\Locale\translate("admin.devices.edit_device.input_type.option.{$device['control_type']}.title", true, [], ucfirst($device['control_type'])); ?>"/>
					<input data-setting="control_type" class="dropdown-search-hidden" type="hidden" name="device_control_type" value="<?php echo($device['control_type']); ?>" />
				</div>
			</div> -->
			<div class="device-setting col-12 col-md-6 mb-2 hidden" data-setting="draw_graphs">
				<h3 class="tile-title"><?php \thusPi\Locale\translate('admin.devices.edit_device.draw_graphs.title', true); ?>
					<span class="tooltip" data-tooltip="<?php \thusPi\Locale\translate('admin.devices.edit_device.draw_graphs.tooltip', true); ?>"></span>
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
						<span class="no-results dropdown-search-result"><?php \thusPi\Locale\translate('generic.error.search_no_results', true); ?></span>
					</div>
					<input class="dropdown-search" data-name="device_control_type" type="text" value="<?php echo($recordings_intervals[0]); ?>"/>
					<input data-setting="control_type" class="dropdown-search-hidden" type="hidden" name="device_control_type" value="0" />
				</div>
			</div>
		</div>
	</div>
	<button role="submit" class="btn btn-xl-square bg-secondary btn-success btn-floating btn-floating-bottom btn-floating-right btn-lg-square transition-slide-right no-active">
		<?php echo(create_icon('far.plus', 'xl')); ?>
	</button>
</form>