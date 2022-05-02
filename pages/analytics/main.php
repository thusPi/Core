<?php 
	$analytics = \thusPi\Analytics\get_all(true);
?>
<div class="btn-list" data-type="single">
	<?php foreach($analytics as $analytic) : ?>
		<?php 
			if(!$device = new \thusPi\Devices\Device($analytic['id'])) {
				continue;
			}
			$properties = $device->getProperties();
			
			$category = new \thusPi\Categories\Category($properties['category']);

			$last_recorded_at_str = \thusPi\Locale\date_format('best,best', $analytic['latest_recording']);
		?>
		<a tabindex="0" href="/#/analytics/graph/?id=<?php echo($analytic['id']); ?>" class="btn btn-tertiary bg-secondary mb-2 transition-fade-order" data-category="<?php echo($properties['category']); ?>" data-page-search="<?php echo($properties['name']); ?>">
			<div class="row">
				<div class="col-auto d-none d-sm-flex pl-0 pr-2">
					<?php echo(create_icon($properties['icon'], 'xl', ['text-category'])); ?>
				</div>
				<div class="col">
					<h3 class="tile-title"><?php echo($properties['name']); ?></h3>
					<span class="tile-subtitle"><?php echo(\thusPi\Locale\translate('analytics.last_recorded_at', [$last_recorded_at_str])); ?></span>
				</div>
			</div>
		</a>
	<?php endforeach; ?>
</div>