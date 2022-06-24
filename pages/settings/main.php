<div class="flex-column">
<?php 
	$settings = \ThusPi\Config\get(null, 'user/settings');

	foreach($settings as $name => $setting) { ?>
		<?php 
			$current_value = \thusPi\Users\CurrentUser::getSetting($name);
			$current_translation = isset($setting['items'][$current_value]) 
				? \thusPi\Locale\translate($setting['items'][$current_value]) 
				: (
					isset($setting['items'][$setting['default']])
						? thusPi\Locale\translate($setting['items'][$setting['default']])
						: thusPi\Locale\translate('generic.state.invalid')
				);
		?>
		<div class="setting tile transition-fade-order" data-setting="<?php echo($name); ?>" data-needs-reload="<?php echo(bool_to_str($setting['options']['needs_reload'] ?? false)); ?>">
			<h3 class="tile-title">
				<?php echo(\thusPi\Locale\translate("settings.setting.{$name}.title")); ?>
			</h3>
			<div class="tile-content">
				<input type="text" data-type="search" name="<?php echo($name); ?>" value="<?php echo($current_translation); ?>" data-value="<?php echo($current_value); ?>">
				<ul class="input-search-results" for="<?php echo($name); ?>">
					<?php foreach($setting['items'] ?? [] as $value => $translate_key) { ?>
						<li class="input-search-result" value="<?php echo($value); ?>"><?php echo(\thusPi\Locale\translate($translate_key)); ?></li>
					<?php } ?>
				</ul>
			</div>
		</div>
	<?php  } ?>
</div>