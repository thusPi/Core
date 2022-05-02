<?php 
	global $userdata_all;

	$uid = $username = '';
	if(isset($_GET['user_uid'])) { $uid = $_GET['user_uid']; }
	if(isset($userdata_all[$uid]['username'])) { $username = $userdata_all[$uid]['username']; }
	$name = $username;
	if(isset($userdata_all[$uid]['name'])) { $name = $userdata_all[$uid]['name']; }
?>
<?php 
	$userdata_replaced = array_replace_recursive($userdata_all['default'], $userdata_all[$uid]);
	if(isset($userdata_replaced['flags'])) {
		$flags_looped = [];
		foreach ($userdata_replaced['flags'] as $flag => $flag_value) {
			$flag_base = str_replace(['_allow', '_disallow'], '', $flag);
			if(!isset($flags_looped[$flag_base])) { // Don't check both allow and disallow
				$options_stated = [];
				$button_array_type = 'multiple';
				if(is_boolish($flag_value)) {
					$button_array_type = 'single';
				}
				$options_html[$flag_base] = "
					<div class='admin-user-flag tile mb-2 transition-fade-order' data-flag='{$flag_base}'>
						<h3 class='tile-title'>".l("admin.users.edit_flags.flag_{$flag_base}.title")."</h3>
						<div class='text-muted mb-1'>".l("admin.users.edit_flags.flag_{$flag_base}.description", false, [$username, $name])."</div>
						<div class='btn-array bg-primary' data-type='{$button_array_type}'>
				";	

				$flags_looped[$flag_base] = true;
				$value_options = user_flag_options($flag_base);

				if(is_boolish($flag_value)) {
					foreach($value_options as $value_option) {
						if($value_option === $flag_value) {
							$options_stated[$value_option] = 'true';
						} else {
							$options_stated[$value_option] = 'false';
						}
					}
				} else if(is_array($flag_value)) {
					natsort($value_options);
					foreach ($value_options as $value_option) {
						if(array_contains($value_option, $flag_value, false)) {
							$options_stated[$value_option] = 'true';
						} else {
							$options_stated[$value_option] = 'false';
						}
					}
				}
				$options_html[$flag_base] .= options_html($flag_base, $options_stated);
				$options_html[$flag_base] .= "</div></div>";
				echo($options_html[$flag_base]);
			}
		}
	} else {
		exit(json_output('Failed to load flags for user \'default\''));
	}

	function options_html($flag_base, $options_stated) {
		global $d;
		$html = '';
		foreach ($options_stated as $option => $enabled) {
			$translation = $option;
			if($flag_base == 'pages') {
				$translation = \thusPi\Locale\translate("page.{$option}");
			} else if(is_boolish($option)) {
				if($option == 'true') {
					$translation = \thusPi\Locale\translate('state.yes');
				} else {
					$translation = \thusPi\Locale\translate('state.no');
				}
			} else if($flag_base == 'devices') {
				if($devices = @json_decode(file_get_contents("{$d['data']}/memory/devices.json"), true)) {
					if(isset($devices[$option]['name'])) {
						$translation = $devices[$option]['name'];
					}
				}
			} else if($flag_base == 'analytics') {
				if($analytic = @json_decode(file_get_contents("{$d['analytics']}/info/{$option}.json"), true)) {
					if(isset($analytic['title'])) {
						$translation = $analytic['title'];
					}
				}
			} else if($flag_base == 'widgets') {
				$widget_namespace = str_replace('-', '_', $option);
				$translation = \thusPi\Locale\translate("dashboard.widget.{$widget_namespace}.title");
			}

			$addClass = '';
			if($enabled == 'true') {
				$addClass = ' active';
			}

			$html .= "<div title='{$option}' class='btn btn-tertiary bg-primary admin-user-option no-hover{$addClass}' data-setting='{$option}' data-value='{$enabled}'>{$translation}</div>";
		}

		return $html;
	}
?>
<script>var user_flags = <?php echo(json_encode($userdata_replaced['flags'])); ?>;</script>
