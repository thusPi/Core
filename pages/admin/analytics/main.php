<?php global $c, $user_config, $d, $f, $userdata; ?>
<div class="btn-list" data-type="single">
	<?php
		$logs_dir = "{$d['analytics']}/historical";

		$sensors = [];
		if($devices = get_device_list()) {
			$sensors = array_filter($devices, function($device) {
				return (isset($device['group']) && $device['group'] == 'sensors' && isset($device['log_interval']));
			});
		}

		foreach($sensors as $sensor_key => $sensor) {
			$info_file = "{$d['data']}/recordings/info/{$sensor_key}.json";
			if($info = file_get_json($info_file)) {
				if(isset($sensor['handler']) && $handler_manifest = \thusPi\Devices\get_handler_manifest($sensor['handler'])) {
					
				?>
				<div class="btn btn-tertiary bg-secondary d-block mb-2 transition-fade-order">
					<div class="row">
						<div class="col-auto d-none d-sm-flex pl-0 pr-2">
							<?php echo(icon_html($sensor['icon'], 'icon-scale-lg text-debug')); ?>
						</div>
						<div class="col">
							<h3 class="tile-title">
								<div class="row">
									<div class="col text-overflow-ellipsis text-left">
										<?php echo($info['title']); ?>
									</div>
									<div class="col-auto text-overflow-ellipsis text-right">
										aaa
									</div>
								</div>
							</h3>
							<div class="col-12 px-0">
								<div class="text-muted d-none d-sm-flex" style="padding-bottom: 0.1rem;">
									<span class="w-100">
										<span class="text-muted text-overflow-ellipsis"><?php echo($handler_manifest['name']); ?></span>
										<span class="bullet-dot"></span>
										<span class="text-muted text-overflow-ellipsis"><?php \thusPi\Locale\translate('generic.every_x_minutes', true, [$sensor['log_interval']]); ?></span>
									</span>
								</div>
							</div>
						</div>
					</div>
				</div>
				<?php
				} else {
					echo("<script>sendMessage('Failed to load device handler manifest for sensor {$sensor_key}', true);</script>");
				}
			} else {
				echo("<script>sendMessage('error.recordings.failed_to_load_info_file', ['{$sensor_key}'], true);</script>");
			}
		}
	?>
</div>