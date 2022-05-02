<?php 
	$html = '';

	// Don't show sidebar if user is not signed in
	if(!\thusPi\Users\CurrentUser::authorized()) {
		return '<nav class="sidenav" style="display: none;"></nav><style>body{grid-template-columns:0px auto;}</style>';
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
					<a class='btn btn-lg btn-tertiary sidenav-link text-{$info['color']}' data-target='{$info['target']}' href='#/{$info['target']}'>
						{$icon}
						<div class='sidenav-name'>{$name_translated}</div>
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
			<ul class='sidenav-items btn-list' data-type='single'>
				{$html}
			</ul>
		</nav>
	";
?>