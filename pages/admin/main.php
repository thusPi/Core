<?php 
	$pages = \thusPi\Config\get(null, 'generic/admin_pages');
?>
<div class="row m-n1" data-type="single">
	<?php foreach($pages as $translate_key => $page) : ?>
		<div class="col-12 col-sm-6 col-lg-3 p-1 transition-fade-order" data-page-search="<?php \thusPi\Locale\translate("admin.page.{$translate_key}.title", [], $translate_key); ?>">
			<a class="btn btn-tertiary tile h-100 w-100" href="#/admin/<?php echo($page['target'] ?? null); ?>/">
				<?php echo(create_icon($page['icon'] ?? null, 'xl', ['tile-icon'], ['color' => $page['color'] ?? null])); ?>
				<h3 class="tile-title"><?php echo(\thusPi\Locale\translate("generic.page.admin.{$translate_key}.title")); ?></h3>
			</a>
		</div>
	<?php endforeach; ?>
</div>