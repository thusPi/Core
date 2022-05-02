<?php global $c, $user_config, $d, $f, $userdata; ?>
<?php 
	$settings = file_get_json(DIR_CONFIG.'/user_main.json');

	foreach($settings as $setting_namespace => $setting) : 
?>
	<div class="tile mb-2 setting transition-fade-order" data-setting="<?php echo($setting_namespace); ?>" data-needs-reload="<?php echo(bool_to_str($setting['info']['needs_reload'] ?? false)); ?>">
		<h3 class="tile-title"><?php echo(\thusPi\Locale\Translate("settings.setting.{$setting_namespace}.title")); ?></h3>
		<?php 
			$input = new \thusPi\Frontend\inputSearch();
			$results = [];

			$current_value = \thusPi\Users\CurrentUser::getSetting($setting_namespace) ?? reset($setting['options']);
			$current_shown_value;

			foreach ($setting['options'] as $translation_key => $value) {
				$shown_value = \thusPi\Locale\Translate($translation_key);
				$results[] = [
					(is_bool($value) ? bool_to_str($value) : $value), 
					(is_bool($shown_value) ? bool_to_str($shown_value) : $shown_value)
				];

				if($value == $current_value) {
					$current_shown_value = $shown_value;
				}
			}

			if(!isset($current_shown_value)) {
				$current_shown_value = \thusPi\Locale\translate('generic.state.invalid');
			}

			$input->addResults($results)->setValue($current_value, $current_shown_value)->print();
		?>
	</div>
<?php endforeach; ?>