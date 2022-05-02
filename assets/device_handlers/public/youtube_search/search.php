<?php 
	chdir(__DIR__);
	include_once("../../../../autoload.php");
?>
<?php
	$search_results = [];

	if(!isset($_GET['id'])) {
		\thusPi\Response\error('request_field_missing', 'Field id is missing.');
	}

	if(!isset($_GET['value'])) {
		\thusPi\Response\error('request_field_missing', 'Field value is missing.');
	}

	$device     = new \thusPi\Devices\Device($_GET['id']);
	$properties = $device->getProperties();

	if(!isset($properties['options']['api_key'])) {
		\thusPi\Response\error('request_field_missing', 'Option api_key missing.');
	}
			
	$search_query = strtolower($_GET['value']);

	// Load cached results
	$cache_file = './cache.json';
	if(($cache = @file_get_json($cache_file)) === false) {
		$cache = [];
	}

	$unix          = time();
	$last_used_max = 345600; // Remove cached results that were not used in the last 4 days
	$created_max   = 604800; // Remove cached results that are over 1 week old

	// Check if search query can be found in cache
	foreach ($cache as $query => $results) {
		if(strlen($search_query) > 64) {
			continue;
		}
			
		if(queries_close_match($search_query, $query)) { // If cached query is similar to new query
			if(isset($results) && !empty($results)) {
				$cached_items = $results;
				$cache[$query]['last_used'] = $unix;
			}
		}

		if(abs($unix - $cache[$query]['last_used']) > $last_used_max || abs($unix - $cache[$query]['created']) > $created_max) {
			unset($cache[$query]);
		}
	}

	if(isset($cached_items)) {
		// Load search results from cache
		foreach ($cached_items['items'] as $item) {
			array_push($search_results, $item);
		}
	} else {
		// Make request to youtube api v3
		$cache[$search_query]['items'] = [];

		$api_query = http_build_query([
			'q'          => $search_query,
			'key'        => $properties['options']['api_key'],
			'part'       => 'snippet',
			'maxResults' => 25
		]);

		$api = "https://www.googleapis.com/youtube/v3/search?{$api_query}";

		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $api);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

		$response = curl_exec($ch);
		curl_close($ch);

		if($items = @json_decode($response, true)['items']) {
			foreach ($items as $key => $item) {
				if(isset($item['id']['videoId']) && $item['id']['kind'] == 'youtube#video') {
					$search_result = itemToSearchResult($item);
					array_push($search_results, $search_result);
					array_push($cache[$search_query]['items'], $search_result);
				}
			}
		}
		
		if(empty($cache[$search_query]['items'])) {
			// Remove empty cached results
			unset($cache[$search_query]);
		} else {
			// Update created and last used time
			$cache[$search_query]['created'] = time();
			$cache[$search_query]['last_used'] = time();
		}
	}

	file_put_contents($cache_file, json_encode($cache, JSON_UNESCAPED_SLASHES));
	$response = [
		'success' => true,
		'manifest' => \thusPi\Devices\get_handler_manifest('youtube_search'),
		'results' => $search_results
	];
	exit(json_encode($response, JSON_UNESCAPED_SLASHES));

	function queries_close_match($search_query, $query) {	
		$lev = levenshtein($search_query, $query);
		if($lev <= 5 && ($lev*1.5) <= strlen($search_query)) {
			return true;
		} else {
			return false;
		}
	}

	function itemToSearchResult($item) {
		try {
			return [
				'value'       => $item['id']['videoId'],
				'title'       => $item['snippet']['title'],
				'description' => $item['snippet']['channelTitle'],
				'thumbnail'   => $item['snippet']['thumbnails']['default']['url']
			];
		} catch(Exception $e) {
			return false;
		}
	}
?>