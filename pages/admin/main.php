<?php 
	$tiles = [
		'devices' => ['mdi.devices', 'var(--caution)', 'devices/main'],
		'analytics' => ['far.chart-area', 'var(--danger)', 'analytics/main'],
		'streams' => ['far.stream', 'var(--success)', 'streams/main'],
		'rooms' => ['mi.door_front', 'var(--debug)', 'rooms/main'],
		'users' => ['far.users', 'var(--warning)', 'users/main'],
		'sysconf' => ['mi.tune', 'var(--info)', 'config/main'],
		'logfiles' => ['far.list', 'var(--caution)', 'log/main'],
		'extensions' => ['mdi.puzzle-outline', 'var(--success)', 'extensions/main']
	];
?>
<div class="row m-n1 btn-list" data-type="single">
	<?php foreach($tiles as $lang_key => $tile) : ?>
		<div class="col-6 col-lg-3 p-1 transition-fade-order" data-page-search="<?php \thusPi\Locale\translate("admin.page.{$lang_key}.title", [], $lang_key); ?>">
			<a class="btn btn-tertiary tile h-100 unstyled" href="/#/admin/<?php echo($tile[2]); ?>/">
				<h3 class="tile-title"><?php echo(\thusPi\Locale\translate("admin.page.{$lang_key}.title", [], ucfirst($lang_key))); ?></h3>
				<div class="w-100 text-center pb-1">
					<?php echo(create_icon($tile[0], 'xl', ['icon-inline'], ['color' => $tile[1]])); ?>
				</div>
			</a>
		</div>
	<?php endforeach; ?>
</div>