<?php 
	function include_classes($classes) {
		foreach (func_get_args() as $class) {
			$class_path = DIR_CLASSES."/{$class}.php";

			if(!file_exists($class_path)) {
				exit("Failed to load class {$class} ({$class_path}): No such file!");
			}
		
			if(!include_once($class_path)) {
				exit("Failed to load class {$class} ({$class_path})!");
			}
		}
	}

	function include_namespaces($namespaces) {
		foreach (func_get_args() as $namespace) {
			$namespace_path = DIR_NAMESPACES."/{$namespace}.php";

			if(!file_exists($namespace_path)) {
				exit("Failed to load namespace {$namespace} ({$namespace_path}): No such file!");
			}
		
			if(!include_once($namespace_path)) {
				exit("Failed to load namespace {$namespace} ({$namespace_path})!");
			}
		}
	}

	function include_configs() {
		$configs = [];

		$config_paths = glob(DIR_CONFIG.'/*.json');

		foreach ($config_paths as $config_path) {
			$config_id = pathinfo($config_path, PATHINFO_FILENAME);
			$config    = file_get_json($config_path);

			$configs[$config_id] = $config;
		}

		return $configs;
	}

	function unique_id($length = 16) {
		$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
		$str = str_shuffle(str_repeat($chars, $length));
		return substr($str, 0, $length);
	}

	function unique_id_secure($length = 12) {
		return substr(bin2hex(random_bytes($length)), 0, $length);
	}

	function encodeshellargarray(array $arg) {
		return @base64_encode(@json_encode($arg));
	}

	function decodeshellargarray(string $arg) {
		return @json_decode(@base64_decode($arg), true);
	}

	function str_to_bool($var) {
		return filter_var($var, FILTER_VALIDATE_BOOLEAN);
	}

	function bool_to_str($bool) {
		if($bool == true) {
			$str = 'true';
		} else if($bool == false) {
			$str = 'false';
		} else {
			$str = null;
		}

		return $str;
	}

	if(!function_exists('str_starts_with')) {
		function str_starts_with(string $haystack, string $needle) {
     		return substr($haystack, 0, strlen($needle)) === $needle;
		}
	}

	if(!function_exists('str_ends_with')) {
		function str_ends_with(string $haystack, string $needle) {
			$length = strlen($needle);

			if(!$length) {
				return true;
			}

			return substr($haystack, -$length) === $needle;
		}
	}

	if(!function_exists('str_contains')) {
		function str_contains(string $haystack, string $needle) {
			return strpos($haystack, $needle) !== false;
		}
	}

	function clamp($min, $val, $max) {
		return max($min, min($max, $val));
	}

	function get_script_output($path) {
		ob_start();
		if(is_readable($path) && $path) {
			include($path);
		} else {
			return false;
		}

		return ob_get_clean();
	}

	function file_get_json($path) {
		if(!file_exists($path)) {
			return null;
		}
			
		if(($content = @file_get_contents($path)) === false) {
			return null;
		}

		$parsed = @json_decode($content, true);

		return (empty($parsed) ? [] : $parsed);
	}

	function input_iconpicker($icon_list, $class = '') {
		$icons_html = $style = '';
		foreach ($icon_list as $color => $icons) {
			foreach ($icons as $icon) {
				if($icon == '_ROW_') {
					$style = 'style="grid-column-start: 1;"';
					continue;
				}

				if(strpos($icon, '.') !== false){
					$namespace = explode('.', $icon)[1];
					$name = str_replace(['-', '_'], ' ', $namespace);
					$name = str_replace(['outline', 'variant', 'alt'], '', $name);
					$name = ucwords(trim($name));

					$icons_html .= "<div tabindex='0' class='btn btn-md-square btn-primary icon-wrapper' {$style} data-icon='{$icon}' data-tooltip='{$name}' data-tooltip-position='below'>".create_icon($icon, 'md', [], ['color' => $color]).'</div>';
					$style = '';
				}
			}
		}
		return "
			<div class='input' data-type='iconpicker'>
				<div class='inner icons scrollbar-visible btn-list' data-type='single'>
					$icons_html
				</div>
			</div>
		";
	}

	function str_putcsv($input, $delimiter = ',', $enclosure = '"') {
        $fp = fopen('php://memory', 'r+b');
        fputcsv($fp, $input, $delimiter, $enclosure);
        rewind($fp);
        $data = rtrim(stream_get_contents($fp), "\n");
        fclose($fp);
        return $data;
    }

	
	function associative_array_sort(&$arr){
		if(is_array($arr)) {
			if(array_keys($arr) !== range(0, count($arr) - 1)) {
				// If array is associative
				ksort($arr);
			} else{
				asort($arr);
			}
			
			foreach ($arr as &$a){
				if(is_array($a)){
					associative_array_sort($a);
				}
			}
		}
	}

	function parse_crontab($crontab, $time = null) {
		if(!isset($time)) {
			$time = time();
		}

		$time = explode(' ', date('i G j n w', $time));

		$crontab = explode(' ', $crontab);

		foreach ($crontab as $k => &$v) {
			$time[$k] = preg_replace('/^0+(?=\d)/', '', $time[$k]);

			$v = explode(',', $v);

			foreach ($v as &$v1) {
				$v1 = preg_replace(
					array(
						// *
						'/^\*$/',
						// 5
						'/^\d+$/',
						// 5-10
						'/^(\d+)\-(\d+)$/',
						// */5
						'/^\*\/(\d+)$/'
					),
					array(
						'true',
						$time[$k] . '===\0',
						'(\1<=' . $time[$k] . ' and ' . $time[$k] . '<=\2)',
						$time[$k] . '%\1===0'
					),
					$v1
				);
			}
			$v = '(' . implode(' or ', $v) . ')';
		}

		$crontab = implode(' and ', $crontab);

		if($crontab == '()') {
			return false;
		}

		return eval('return ' . $crontab . ';');
	}

	function script_name_to_shell_cmd($scriptname, $args = '') {
		$programs = [
			'py' => 'python3', 
			'sh' => 'bash', 
			'php' => 'php', 
			'exec' => ''
		];

		if(is_array($args)) {
			$args = implode(' ', $args);
		}

		$ext = pathinfo($scriptname, PATHINFO_EXTENSION);
		if(isset($programs[$ext])) {
			if($ext == 'exec') {
				if(file_exists($scriptname)) {
					return escapeshellcmd(file_get_contents($scriptname));
				}
			} else {
				return trim(escapeshellcmd("{$programs[$ext]} {$scriptname} {$args}"));
			}
		}

		return null;
	}

	function get_tool_conf($tool_id = '') {
		global $d;
		if($tools = @json_decode(@file_get_contents("{$d['data']}/memory/tools.json"), true)) {
			if(!isset($tool_id) || empty($tool_id)) {
				return $tools;
			} else if(isset($tools[$tool_id])) {
				return $tools[$tool_id];
			}
		}
		return false;
	}

	function array_select_by_string(string $str = '', array $arr = []) {
		$result = [];
		$str_split = str_split($str);

		foreach ($str_split as $char) {
			if(isset($arr[$char])) {
				if(is_array($arr[$char]) && count($arr[$char]) === 1) {
					$key = array_key_first($arr[$char]);
					$value = $arr[$char][$key];

					$result[$key] = $value;
				}
			}
		}

		return $result;
	}

	function get_device_list() {
		global $d;
		
		if($devices = @json_decode(@file_get_contents("{$d['data']}/memory/devices.json"), true)) {
			return $devices;
		}

		return [];
	}

	function uuid() {
		return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
			mt_rand(0, 0xffff), mt_rand(0, 0xffff),
			mt_rand(0, 0xffff),
			mt_rand(0, 0x0fff) | 0x4000,
			mt_rand(0, 0x3fff) | 0x8000,
			mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
		);
	}

	function execute(string $cmd, &$output, int $timeout = 60, $line_callback = null) {
		$descriptors = array(
			0 => array('pipe', 'r'),  // stdin
			1 => array('pipe', 'w'),  // stdout
			2 => array('pipe', 'w')   // stderr
		);

		// If timeout is passed is more than to zero, terminate the process when 
		// the timeout has ended. Let the process run otherwise.
		$do_terminate = $timeout > 0;

		// Timeout of at least 0.01 seconds
		$timeout = max(0.01, $timeout);

		// Keep process running in background so it doesn't end when
		// the parent script ends
		if(!$do_terminate) {
			$cmd = "nohup $cmd >/dev/null 2>&1";
		}

		// Start process
		$process = proc_open($cmd, $descriptors, $pipes);

		if (!is_resource($process)) {
			throw new \Exception('Could not execute process');
		}

		while($timeout > 0) {
			$start = microtime(true);

			if($output_line = fgets($pipes[1])) {
				if(isset($line_callback) && is_callable($line_callback)) {
					call_user_func($line_callback, $output_line);
				}

				$output .= $output_line;
			}

			$status = proc_get_status($process);

			// Break loop if process ends before timeout
			if(!$status['running']) {
				break;
			}

			$timeout -= (microtime(true) - $start);
		}

		// Terminate process if it was set to do so
		if($do_terminate) {
			proc_terminate($process, 9);

			fclose($pipes[0]);
			fclose($pipes[1]);
			fclose($pipes[2]);

			proc_close($process);
		}

		return true;
	}

	function json_output($errstr = 'An unexpected error occured', $type = 'error', $show_info = true, $errno = 500) {
		$success = false;
		$return = ['file' => 'unknown', 'line' => 'unknown'];
		
		if($show_info && @$caller = array_shift(debug_backtrace())) {
			if(isset($caller['file']) && isset($caller['line'])) {
				$return['file'] = $caller['file'];
				$return['line'] = $caller['line'];
			}
		}

		$type = strtolower($type);

		if($type == 'notice') {
			$type_formatted = 'Notice';
			$success = true;
			$errno = 200;
		} else if($type == 'success') {
			$type_formatted = 'Success';
			$success = true;
			$errno = 200;
		} else {
			$type_formatted = 'Error';
		}

		// if($type == 'success') {
		// 	$errmsg = $errstr;
		// } else if($show_info) {
		// 	$errmsg = "{$errstr} {$type_formatted} in file {$return['file']}:{$return['line']}. Exiting with code {$errno}.";
		// } else {
		// 	$errmsg = "{$type_formatted}: {$errstr}.";
		// }

		$return['success']  = $success;
		$return['code']     = $errno;
		$return['message'] = $errstr;

		if(@$returnstr = json_encode($return, JSON_UNESCAPED_SLASHES)) {
			return $returnstr;
		} else {
			return '{}';
		}
	}

	function ms_sleep($milliseconds = 0) {
		if($milliseconds > 0) {
			$test = $milliseconds / 1000;
			$seconds = floor($test);
			$micro = round(($test - $seconds) * 1000000);
			if($seconds > 0) sleep($seconds);
			if($micro > 0) usleep($micro);
		}
	}

	function rmtree($dir) {
		$files = array_diff(scandir($dir), array('.','..'));
		foreach ($files as $file) {
			if(is_dir("$dir/$file") && !is_link($dir)) {
				rmtree("$dir/$file");
			} else {
				unlink("$dir/$file");
			}
		}
		
		return rmdir($dir);
	}

	function get_user_flag($flag) {
		global $userdata;
		if(isset($userdata['flags'][$flag])) {
			if($userdata['flags'][$flag] == 'true') {
				return true;
			} else if($userdata['flags'][$flag] == 'false') {
				return false;
			} else {
				return $userdata['flags'][$flag];
			}
		} else {
			return false;
		}
	}

	function get_user_info($key, $uid = null) {
		global $userdata;
		global $userdata_all;
		
		if(!isset($uid)) {
			if(isset($userdata[$key])) {
				return $userdata[$key];
			}
		}
		if(isset($userdata_all[$uid][$key])) {
			return $userdata_all[$uid][$key];
		}
		return '';
	}

	function user_flag_options($flag_base) {
		global $docroot, $d;
		if($flag_base == 'pages') {
			$pages = glob("{$docroot}/pages/*/main.php");
			foreach ($pages as $key => $page) {
				$pages[$key] = strtolower(basename(dirname($page)));
			}
			return $pages;
		} else if($flag_base == 'devices') {
			if($devices = @json_decode(file_get_contents("{$d['data']}/memory/devices.json"), true)) {
				return array_keys($devices);
			}
		} else if($flag_base == 'analytics') {
			$analytics = glob("{$d['analytics']}/info/*.json");
			foreach ($analytics as $key => $analytic) {
				$analytics[$key] = strtolower(pathinfo($analytic, PATHINFO_FILENAME));
			}
			return $analytics;
		} else if($flag_base == 'widgets') {
			$widgets = glob("{$d['assets']}/widgets/*/widget.json");
			foreach ($widgets as $key => $widget) {
				$widgets[$key] = strtolower(basename(dirname($widget)));
			}
			return $widgets;
		} else {
			return ['true', 'false'];
		}
	}

	function get_blacklist_whitelist($flag, $child, $userdata_inner) {
		if(isset($userdata_inner['flags'])) {
			$flags = $userdata_inner['flags'];
			if(get_user_flag('is_admin') == 'true') {
				return true;
			} else {
				if(!isset($flags["{$flag}_allow"])) { $flags["{$flag}_allow"] = []; }
				if(!isset($flags["{$flag}_disallow"])) { $flags["{$flag}_disallow"] = []; }

				if(in_array('*', $flags["{$flag}_disallow"])) {
					return false;
				} else if(in_array('*', $flags["{$flag}_allow"])) {
					return true;
				} else if(!in_array($child, $flags["{$flag}_disallow"]) && in_array($child, $flags["{$flag}_allow"])) {
					return true;
				} else {
					return false;
				}
			}
		}
	}

	function curl_wrapper($ch_url) {
		// Init session
		$ch = curl_init($ch_url);

		// Set options
		curl_setopt($ch, CURLOPT_URL, $ch_url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

		// Get data
		$data = curl_exec($ch);

		// Close session
		curl_close($ch);

		return $data;
	}

	function is_color(string $color) {
		if(
			strpos($color, '#') === 0 || 
			strpos($color, 'hsl') === 0 || 
			strpos($color, 'hsv') === 0 || 
			strpos($color, 'rgb') === 0
		) {
			return true;
		}

		return false;
	}

	function color_to_rgb(string $color) {
		$alpha = 255;

		$color = str_replace([' ', '%', '°', '(', ')'], '', strtolower($color));

		if(strpos($color, '#') === 0) {
			// HEX

			// Remove #
			$color = substr($color, 1);

			// Calculate HEX length
			$length = strlen($color);
			
			// HEX to RGB(A)
			switch($length) {
				case 3:
					// f00 to ff0000. Flowing into next case.
					$color_split = str_split($color);
					$color = '';

					foreach ($color_split as $char) {
						$color .= $char.$char;
					}

				case 6:
					list($r, $g, $b) = sscanf($color, "%02x%02x%02x");
					break;

				case 8:
					list($r, $g, $b, $a) = sscanf($color, "%02x%02x%02x%02x");
					break;

				default:
					break;
			}
		} else if(strpos($color, 'hsl') === 0) {
			// HSL

			$color = substr($color, 3);
			list($h, $s, $l) = explode(',', $color);

			$h = clamp(0, floatval($h), 360) / 360;
			$s = clamp(0, floatval($s), 100) / 100;
			$l = clamp(0, floatval($l), 100) / 100;

			$v = ($l <= 0.5) ? ($l * (1.0 + $s)) : ($l + $s - $l * $s);

			if ($v > 0) {
				$m = $l + $l - $v;
				$sv = ($v - $m ) / $v;
				$h *= 6.0;
				$sextant = floor($h);
				$fract = $h - $sextant;
				$vsf = $v * $sv * $fract;
				$mid1 = $m + $vsf;
				$mid2 = $v - $vsf;

				switch ($sextant) {
					case 0:
						$r = $v;
						$g = $mid1;
						$b = $m;
						break;

					case 1:
						$r = $mid2;
						$g = $v;
						$b = $m;
						break;
						
					case 2:
						$r = $m;
						$g = $v;
						$b = $mid1;
						break;
						
					case 3:
						$r = $m;
						$g = $mid2;
						$b = $v;
						break;
						
					case 4:
						$r = $mid1;
						$g = $m;
						$b = $v;
						break;
						
					case 5:
						$r = $v;
						$g = $m;
						$b = $mid2;
						break;
				}

				$r *= 255;
				$g *= 255;
				$b *= 255;
			}
		} else if(strpos($color, 'hsv') === 0) {
			$color = substr($color, 3);
			list($h, $s, $v) = explode(',', $color);

			$h = clamp(0, floatval($h), 360) / 360;
			$s = clamp(0, floatval($s), 100) / 100;
			$v = clamp(0, floatval($v), 100) / 100;

			$h *= 6;

			$i = floor($h);
			$f = $h - $i;

			$m = $v * (1 - $s);
			$n = $v * (1 - $s * $f);
			$k = $v * (1 - $s * (1 - $f));

			switch ($i) {
				case 0:
					$r = $v;
					$g = $k;
					$b = $m;
					break;
				case 1:
					$r = $n;
					$g = $v;
					$b = $m;
					break;
				case 2:
					$r = $m;
					$g = $v;
					$b = $k;
					break;
				case 3:
					$r = $m;
					$g = $n;
					$b = $v;
					break;
				case 4:
					$r = $k;
					$g = $m;
					$b = $v;
					break;
				case 5:
				case 6:
					$r = $v;
					$g = $m;
					$b = $n;
					break;
			}

			$r *= 255;
			$g *= 255;
			$b *= 255;
		} else {
			// Other or invalid format

			return 'rgb(0, 0, 0)';
		}


		if(!isset($r)) { $r = 0; }
		if(!isset($g)) { $g = 0; }
		if(!isset($b)) { $b = 0; }
		if(!isset($a)) { $a = 255; }

		$r = round($r, 0);
		$g = round($g, 0);
		$b = round($b, 0);

		if($a == 255) {
			$color_formatted = "rgb($r, $g, $b)";
		} else {
			$a /= 255;
			$color_formatted = "rgba($r, $g, $b, $a)";
		}

		return $color_formatted;
	}

	function create_icon($icon = null, $scale = null, $classes = null, $styles = null, $element = null) {
		if(!is_array($icon)) {
			return create_icon([
				'icon' => $icon,
				'scale' => $scale,
				'classes' => $classes,
				'styles' => $styles,
				'element' => $element
			]);
		}

		$arguments = $icon;
		$arguments = array_replace([
			'icon'    => 'far.exclamation-circle',
			'scale'   => 'md',
			'element' => 'span',
			'attributes' => [],
			'classes' => [],
			'styles'  => []
		], array_filter($arguments));
		
		$classes_str = $styles_str = $content_str = $attributes_str = '';

		// Check if a library was given
		if(strpos($arguments['icon'], '.') === false) {
			return false;
		}

		// Seperate library and icon name
		$library = strtok($arguments['icon'], '.');
		$name    = strtolower(strtok(' '));

		// Create resources for element, depending on icon library
		switch($library) {
			case 'far':
				array_push($arguments['classes'], 'far', "fa-{$name}");
				break;

			case 'mdi':
				array_push($arguments['classes'], 'mdi', "mdi-{$name}");
				break;

			case 'mi':
				array_push($arguments['classes'], 'material-icons-outlined');
				$content_str = $name;
				break;

			default:
				return false;
		}

		// Set default classes
		array_push($arguments['classes'], "icon-scale-{$arguments['scale']}", 'icon', "icon-library-{$library}");
		
		// Create attributes string
		foreach($arguments['attributes'] as $attribute => $value) {
			$attributes_str .= "{$attribute}=\"{$value}\" ";
		}
		$attributes_str = ' '.trim($attributes_str, ' ');

		// Create class string
		$classes_str = implode(' ', $arguments['classes']);

		// Create style string
		foreach($arguments['styles'] as $property => $value) {
			$styles_str .= "{$property}: {$value}; ";
		}
		$styles_str = trim($styles_str, ' ');

		return "<{$arguments['element']} class=\"{$classes_str}\" style=\"{$styles_str}\"{$attributes_str}>{$content_str}</{$arguments['element']}>";
	}

	function icon_html($icon = 'error_outline', $classes = '', $style = '', $elem = 'span') {
		if(strpos($icon, '.') !== false) {
			if(strlen($classes) > 0) {
				$classes = " {$classes}";
			}

			if(strlen($style) > 0) {
				$style = "style='{$style}'";
			}

			$service = explode('.', $icon)[0];
			$icon = strtolower(explode('.', $icon)[1]);
			if($service == 'mi') { // MATERIAL ICONS
				$icon_html = "<{$elem} class='icon icon-library-mi material-icons-outlined{$classes}' {$style}>{$icon}</{$elem}>";
			} else if($service == 'mdi') { // MATERIAL DESIGN ICONS
				$icon_html = "<{$elem} class='icon icon-library-mdi mdi mdi-{$icon}{$classes}' {$style}></{$elem}>";
			} else if($service == 'far') { // FONT AWESOME REGULAR
				$icon_html = "<{$elem} class='icon icon-library-far far fa-{$icon}{$classes}' {$style}></{$elem}>";
			}
		}
		if(isset($icon_html)) {
			return $icon_html;
		}
		return "<{$elem} class='{$classes}'></{$elem}>";
	}
	
	function is_boolish($var) {
		if(!isset($var)) {
			return null;
		}
		return ($var == 'true' || $var == 'false');
	}

	function string_between($start, $end, $context) {
		if(isset($context) && isset($start) && isset($end)) {
			$context = " {$context}";
			$strpos_start = strpos($context, $start);
			if ($strpos_start == 0) return '';
			$strpos_start += strlen($start);
			$len = strpos($context, $end, $strpos_start) - $strpos_start;
			return substr($context, $strpos_start, $len);
		}
		return false;
	}

	function sanitize_filename($filename, $replace = '-', $extreme = false) {
		if($extreme) {
			return preg_replace('/[^a-zA-Z0-9]+/', $replace, $filename);
		} else {
			return preg_replace('/[^a-zA-Z0-9\_\- ]+/', $replace, $filename);
		}
	}
?>