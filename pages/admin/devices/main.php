<?php global $c, $d, $f, $userdata; ?>
<div class="btn-list" data-type="single">
	<?php
		$Groups = new Groups();
		$groups = $Groups->list();
		
		$Devices = new Devices();
		$devices = $Devices->list();

		foreach($devices as $device_id => $device) { ?>
			<div class="btn btn-tertiary bg-secondary mb-2 transition-fade-order" data-page-search="<?php echo($device['name']); ?>" onclick="loadPage('admin', 'devices>edit_device', {'device_id': '<?php echo($device_id); ?>'});">
				<div class="row">
					<div class="col-auto pr-2 d-none d-sm-flex">
						<?php echo(create_icon($device['icon'], 'xl', [], ['color' => $groups[$device['group']]['color']])); ?>
					</div>
					<div class="col">
						<h3 class="tile-title"><?php echo($device['name']); ?></h3>
						<div class="tile-subtitle"><?php \thusPi\Locale\translate("devices.group.{$device['group']}.title", true); ?></div>
					</div>
				</div>
			</div><?php
		}
	?>
</div>
<div class="btn btn-xl-square bg-secondary btn-info btn-floating btn-floating-bottom btn-floating-right btn-lg-square transition-slide-right" onclick="loadPage('admin', 'devices>edit_device');">
<?php echo(create_icon('far.plus', 'xl')); ?>
</div>