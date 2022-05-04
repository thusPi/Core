<?php 
	$tiles = [
		'devices' => ['far.computer-speaker', 'var(--orange)', 'devices/main'],
		'recordings' => ['far.chart-bar', 'var(--red)', 'recordings/main'],
		'streams' => ['far.stream', 'var(--green)', 'streams/main'],
		'rooms' => ['far.door-closed', 'var(--purple)', 'rooms/main'],
		'users' => ['far.users', 'var(--yellow)', 'users/main'],
		'sysconf' => ['far.sliders-h', 'var(--blue)', 'config/main'],
		'logfiles' => ['far.list', 'var(--orange)', 'log/main'],
		'extensions' => ['far.puzzle-piece', 'var(--green)', 'extensions/main']
	];
?>
<div class="row m-n1" data-type="single">
	<?php foreach($tiles as $lang_key => $tile) : ?>
		<div class="col-12 col-sm-6 col-lg-3 p-1 transition-fade-order" data-page-search="<?php \thusPi\Locale\translate("admin.page.{$lang_key}.title", [], $lang_key); ?>">
			<a class="btn btn-tertiary tile h-100 w-100" href="/#/admin/<?php echo($tile[2]); ?>/">
				<?php echo(create_icon($tile[0], 'xl', ['tile-icon'], ['color' => $tile[1]])); ?>
				<h3 class="tile-title"><?php echo(\thusPi\Locale\translate("admin.page.{$lang_key}.title")); ?></h3>
			</a>
		</div>
	<?php endforeach; ?>
</div>