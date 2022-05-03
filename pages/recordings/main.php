<?php 
	$recordings = \thusPi\Recordings\get_all(true);
?>
<div class="btn-column">
	<?php foreach($recordings as $analytic) : ?>
		<?php 
			if(!$device = new \thusPi\Devices\Device($analytic['id'])) {
				continue;
			}
			$properties = $device->getProperties();
			
			$category = new \thusPi\Categories\Category($properties['category']);

			$last_recorded_at_str = \thusPi\Locale\date_format('best,best', $analytic['latest_recording']);
		?>
		<a class="btn btn-tertiary tile transition-fade-order" href="/#/recordings/graph/?id=<?php echo($analytic['id']); ?>" data-category="<?php echo($properties['category']); ?>" data-page-search="<?php echo($properties['name']); ?>">
			<div class="tile-icon">
				<?php echo(create_icon($properties['icon'], 'xl', ['text-category'])); ?>
			</div>
			<h3 class="tile-title"><?php echo($properties['name']); ?></h3>
			<span class="tile-subtitle"><?php echo(\thusPi\Locale\translate('recordings.last_recorded_at', [$last_recorded_at_str])); ?></span>
		</a>
	<?php endforeach; ?>
</div>