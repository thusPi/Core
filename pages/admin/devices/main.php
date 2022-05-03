<div class="btn-column">
	<?php
		$devices = \thusPi\Devices\get_all();

		foreach($devices as $device_id => $device) { ?>
			<a 
				class="btn btn-tertiary tile transition-fade-order"
				data-page-search="<?php echo($device['name']); ?>"
				data-category="<?php echo($device['category']); ?>"
				href="#/admin/devices/manage/?id=<?php echo($device['id']); ?>">
				<?php echo(create_icon($device['icon'], 'xl', ['tile-icon text-category'])); ?>
					<h3 class="tile-title"><?php echo($device['name']); ?></h3>
					<span class="tile-subtitle"><?php echo(\thusPi\Locale\translate("generic.category.{$device['category']}.title")); ?></span>
			</a>
		<?php } ?>
</div>
<div class="btn bg-secondary btn-info btn-floating transition-slide-right" onclick="loadPage('admin', 'devices>edit_device');">
<?php echo(create_icon('far.plus', 'xl')); ?>
</div>