<?php 
	$html = '';

	// Don't show sidebar if user is not signed in
	if(!\thusPi\Users\CurrentUser::authorized()) {
		return '<nav class="sidenav" style="display: none;"></nav>';
	}
	
	// Load sidenav items
	$items = \thusPi\Config\get(null, 'generic/sidenav');

	/* Generate sidenav */
	foreach ($items as $position => $pages) {
		if($position == 'bottom') {
			$html .= '<div class="mt-auto">';
		}

		foreach ($pages as $name => $page) {
			$manifest_path = DIR_PAGES."/{$page['target']}.json";
			$manifest = @file_get_json($manifest_path) ?? [];

			// Skip if page is not enabled
			if(isset($page['enabled']) && $page['enabled'] !== true) {
				continue;
			}
			
			// Skip page if page requires admin permissions and user is not admin
			if(isset($manifest['admin_only']) && $manifest['admin_only'] == true && \thusPi\Users\CurrentUser::getFlag('is_admin') !== true) {
				continue;
			}

			// Skip page if user doesn't have access to this page
			if(!\thusPi\Users\CurrentUser::checkFlagItem('pages', $name)) {
				continue;
			}
				
			$name_translated = \thusPi\Locale\translate("generic.page.{$name}.title");

			$html .= "
				<li class='sidenav-item'>
					<a class='btn btn-fw btn-lg btn-tertiary sidenav-link flex-row' data-target='{$page['target']}' href='#/{$page['target']}' style='color:{$page['color']};'>
						<span class='sidenav-item-icon' data-icon='{$page['icon']}' data-icon-scale='lg'></span>
						<span class='sidenav-item-name'>{$name_translated}</span>
					</a>
				</li>
			";
		}
	}

	if(isset($pagelist['bottom'])) {
		$html .= '</div>';
	}

	return "
		<nav class='sidenav bg-secondary'>
			<ul class='sidenav-items btn-column'>
				{$html}
			</ul>
		</nav>
	";
?>