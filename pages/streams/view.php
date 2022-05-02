<?php 
	if(!isset($_GET['id'])) {
		return false;
	}
	$stream_id = $_GET['id'];

	$stream      = new \thusPi\Streams\Stream($stream_id);
	$category    = new \thusPi\Categories\Category($stream->getProperty('category'));
	$collections = $stream->getCards();

	$collections = [
		'trigger' => [
			'sub_collections' => ['main'],
			'icon'            => 'far.arrow-down'
		], 
		'condition' => [
			'sub_collections' => ['main', 'and'],  
			'icon'            => 'far.arrow-down'
		],
		'do' => [
			'sub_collections' => ['main', 'and'],  
			'hr'              => true
		],
		'else' => [
			'sub_collections' => ['main', 'and'],  
		],
	];
?>
<?php 
	$html = '';

	foreach ($collections as $collection => $cards) {
		$html .= "
			<div class=\"stream-card-collection mb-2\" data-collection=\"{$collection}\">
				<h3 class=\"mb-1\">".\thusPi\Locale\Translate("streams.action.{$collection}.title")."</h3>
		";
		
		foreach ($cards as $card) {
			$manifest = \thusPi\Streams\get_card($collection, $card['name']);
			$params_str = json_encode($card['parameters']);
			$html .= "
				<div class=\"tile stream-card\" data-namespace=\"{$manifest['namespace']}\">
					<div class=\"stream-card-parameters\">{$params_str}</div>
				</div>
			";
		}

		$html .= '</div>';
	}

	echo($html);
?>
