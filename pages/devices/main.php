<div class="ml-0 categories-list-row transition-slide-top position-relative btn-row mb-2">
	<?php 
		$devices    = \thusPi\Devices\get_all(true);
		$categories = \thusPi\Categories\get_all();

		// Get list of inactive categories for current user
		$inactive_categories = \thusPi\Users\CurrentUser::getSetting('devices_inactive_categories') ?? [];

		// Create bar where user can filter device categories
		foreach ($categories as $namespace => $category) {
	?>
		<div 
			data-category="<?php echo($namespace); ?>"
			class="btn btn-<?php echo($category['color']); ?> bg-tertiary no-hover mx-1 category-button<?php echo(in_array($namespace, $inactive_categories) ? '' : ' active'); ?>" 
			tabindex="0">
			<?php echo(create_icon($category['icon'], 'md', ['icon-inline', 'mr-1'])); ?>
			<?php echo(ucfirst(\thusPi\Locale\translate("generic.category.{$namespace}.title"))); ?>
		</div>
	<?php } ?>
</div>
<div class="devices-outer m-n1">
	<div class="devices row">
		<?php 
			foreach ($devices as $device) :
				if($device['control_support'] == false) {
					$device['control_type'] = 'none';
				}

				switch($device['control_type']) {
					case 'range':
						// Convert valuelist string into array
						if(!isset($device['valuelist']) || !is_string($device['valuelist'])) {
							$control_range_min  = 0;
							$control_range_max  = 100;
							$control_range_step = 1;
						} else {
							list($control_range_min, $control_range_max, $control_range_step) = explode(',', $device['valuelist']);
						}

						$device['valuelist'] = range($control_range_min, $control_range_max, $control_range_step);
						break;
					
					case 'toggle':
						$device['valuelist'] = ['on', 'off'];
						break;

					default:
						$device['valuelist'] = [];
						break;
				}

				// Check if device category is active
				if(in_array($device['category'], $inactive_categories)) {
					continue;
				}

				$visible = true;
				if(isset($device['visible']) && $device['visible'] == false) {
					$visible = false;
				}

				// Set shown value to value if it's not specifically set
				if(!isset($device['shown_value'])) {
					$device['shown_value'] = $device['value'];
				}
		?>
		<div 
			class="device col-12 col-md-6 col-lg-4 col-lg-3 p-1 transition-fade-order tile-wrapper"
			data-category="<?php echo($device['category']); ?>" 
			data-control-support="<?php echo(bool_to_str($device['control_support'])); ?>" 
			data-control-type="<?php echo($device['control_type']); ?>"
			data-family="<?php echo($device['family']); ?>"
			data-force-set="<?php echo((isset($device['force_set']) && $device['force_set'] == true) ? 'true' : 'false'); ?>"
			data-handler="<?php echo($device['handler']); ?>"
			data-id="<?php echo($device['id']); ?>" 
			data-page-search="<?php echo($device['name']); ?>"
			data-search-handler="<?php echo($device['search_handler']); ?>"
			data-value="<?php echo($device['value']); ?>" 
			data-values="<?php echo(implode(',', $device['valuelist'])); ?>" 
			data-visible="<?php echo(bool_to_str($visible)); ?>">
			<div class="bg-secondary device-inner header-bg-category tile">
				<div class="tile-row">
					<h3 class="device-name tile-title text-overflow-ellipsis"><?php echo($device['name']); ?></h3>
					<span class="device-category tile-side-title text-muted"><?php echo(ucfirst(\thusPi\Locale\translate("generic.category.{$device['category']}.title"))); ?></span>
				</div>
				<div class="row mt-1 device-main">
					<div class="col-auto px-0 mr-1">
						<?php echo(create_icon($device['icon'], 'xl', ['device-icon', 'text-category'])); ?>
					</div>
					<div class="col pl-2 pr-0 ml-1">
						<div class="device-control-wrapper">
							<?php if($device['control_type'] == 'range') : ?>
								<div class="input device-control" data-type="range" data-value="<?php echo($device['shown_value']); ?>" data-min="<?php echo($control_range_min); ?>" data-max="<?php echo($control_range_max); ?>" data-step="<?php echo($control_range_step); ?>" tabindex="0">
									<div class="range-thumb bg-category"></div>
									<div class="range-track"></div>
									<div class="range-tooltip"><?php echo($device['value']); ?></div>
								</div>
							<?php elseif($device['control_type'] == 'search') : ?>
								<?php 
									if(!$search_handler = @glob("{$d['assets']}/device_handlers/public/{$device['search_handler']}/search.{py,sh,php,exec}", GLOB_BRACE)[0]) {
										$search_handler = 'none';
									}

									if(isset($device['shown_value'])) {
										$shown_value = $device['shown_value'];
									} else {
										$shown_value = $device['value'];
									}
								?>
								<input class="input device-control" data-type="search" data-value="<?php echo($device['value']); ?>" data-shown-value="<?php echo($device['shown_value']); ?>" onchange="loadDeviceSearchResults($(this).parents('.device'));">
							<?php elseif($device['control_type'] == 'toggle') : ?>
								<div class="input device-control" data-type="toggle" data-value="<?php echo($device['shown_value']); ?>" tabindex="0">
									<div class="toggle-track">
										<div class="toggle-thumb bg-category"></div>
									</div>
								</div>
							<?php endif; ?>
						</div>
					</div>
					<div class="col-auto ml-auto d-flex">
						<div class="device-favorite-icon-wrapper btn btn-sm mt-auto btn-tertiary no-active" onclick="toggleFavorite($(this).parents('.device'));">
							<?php echo(create_icon('mi.star_border', 'sm', ['text-muted favorite-false'])); ?>
							<?php echo(create_icon('mi.star', 'sm', ['text-category favorite-true'])); ?>
						</div>
					</div>
				</div>
			</div>
		</div>
	<?php endforeach; ?>
</div>
<?php if(get_user_flag('is_admin') === true) : ?>
	<div class="btn btn-xl-square bg-secondary btn-info btn-floating btn-floating-bottom btn-floating-right btn-lg-square transition-slide-right" onclick="loadPage('admin', 'devices>edit_device');">
		<?php echo(create_icon('far.plus', 'xl')); ?>
	</div>
<?php endif; ?>