<?php 
    chdir(__DIR__);
    include_once("../../autoload.php");
?>
<?php 
    $waypoints = new \thusPi\Debug\WaypointList('shell');

    if(count($argv) <= 3) {
        \thusPi\Response\error('Insufficient amount of arguments given.');
    }

    $id                  = $argv[1];
    $trigger             = $argv[2];
    $parameters          = @shell_arg_decode($argv[3]);
    $stream              = new \thusPi\Streams\Stream($id);
    $collections         = $stream->getCards();
    $condition_succeeded = true;

    $collections['trigger'] = $collections['trigger'] ?? [];
    $collections['condition'] = $collections['condition'] ?? [];
    $collections['do'] = $collections['do'] ?? [];
    $collections['else'] = $collections['else'] ?? [];

    // Run trigger
    foreach ($collections['trigger'] as $card) {
        $manifest = \thusPi\Streams\get_card('trigger', $card['name']);

        $card_output = $stream->getCardOutput('trigger', $card['name'], $card['parameters']);

        $waypoints->printWaypoint("Card in collection trigger ({$card['name']}) ".($card_output === true ? 'SUCCEEDED' : 'DID NOT SUCCEED. Ending...'));

        if($card_output !== true) {
            \thusPi\Response\error('trigger_not_matched');
        }
    }

    $waypoints->printWaypoint('');
    $waypoints->printWaypoint("-----------------------------------------");
    $waypoints->printWaypoint('Category: CONDITION');

    // Run condition
    foreach ($collections['condition'] as $card) {
        $waypoints->printWaypoint(json_encode($card));
        $card_output = $stream->getCardOutput('condition', $card['name'], $card['parameters']);
        
        $waypoints->printWaypoint("Card in collection condition ({$card['name']}) ".($card_output === true ? 'SUCCEEDED' : 'DID NOT SUCCEED. Running ELSE...'));
    
        if($card_output !== true) {
            $condition_succeeded = false;
            break;
        }
    }

    if($condition_succeeded === true) {
        $waypoints->printWaypoint('All cards in collection condition SUCCEEDED. Running DO...');
    }

    // Run do / else
    $run_collection = ($condition_succeeded === true ? 'do' : 'else');

    foreach ($collections[$run_collection] as $card) {
        $waypoints->printWaypoint(json_encode($card));

        // Else cards are located in do
        $card_output = $stream->getCardOutput('do', $card['name'], $card['parameters']);
        
        $waypoints->printWaypoint("Card in collection condition ({$card['name']}) ".($card_output === true ? 'SUCCEEDED' : 'DID NOT SUCCEED.'));
    
        // if($card_output !== true) {
        //     $run_collection = 'else';
        //     break;
        // }
    }

    exit();
?>