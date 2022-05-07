<?php 
	$html = '';

	// Don't show sidebar if user is not signed in
	if(!\thusPi\Users\CurrentUser::authorized()) {
		return '<nav class="sidenav" style="display: none;"></nav>';
	}
	
	// Load sidenav items
	$items = \thusPi\Config\get('items', 'sidenav');

	/* Generate sidenav */
	foreach ($items as $position => $pages) {
		if($position == 'bottom') {
			$html .= '<div class="mt-auto">';
		}

		foreach ($pages as $name => $info) {
			// Skip page if user doesn't have access to this page
			if(!\thusPi\Users\CurrentUser::checkFlagItem('pages', $name)) {
				continue;
			}
				
			$name_translated = \thusPi\Locale\translate("generic.page.{$name}.title");
			$icon            = create_icon($info['icon'], 'lg', ['sidenav-icon']);

			$html .= "
				<li class='sidenav-item'>
					<a class='btn btn-fw btn-lg btn-tertiary sidenav-link flex-row' data-target='{$info['target']}' href='#/{$info['target']}' style='color:{$info['color']};'>
						{$icon}
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