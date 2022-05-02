<?php 
	if(isset($user_config['theme'])) {
		$theme_stylesheet = "{$d['assets']}/css/themes/{$user_config['theme']}.css";
		if(file_exists($theme_stylesheet)) {
			$css = file_get_contents($theme_stylesheet);
			$html = "<style id='style-theme'>$css</style>";

			return $html;
		}
	}

	return false;
?>