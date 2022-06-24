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
    $parameters          = decodeshellargarray($argv[3]);
    $flow                = new \thusPi\Flows\Flow($id);
    $cards                = $flow->getCards();
    $condition_succeeded = true;

    var_dump($cards);

    // Run trigger
    foreach ($cards['trigger'] as $card) {
        $manifest = \thusPi\Flows\get_card('trigger', $card['name']);

        $card_output = $flow->getCardOutput('trigger', $card['name'], $card['parameters']);

        $waypoints->printWaypoint("Card in group trigger ({$card['name']}) ".($card_output === true ? 'SUCCEEDED' : 'DID NOT SUCCEED. Ending...'));

        if($card_output !== true) {
            \thusPi\Response\error('trigger_not_matched');
        }
    }

    $waypoints->printWaypoint('');
    $waypoints->printWaypoint("-----------------------------------------");
    $waypoints->printWaypoint('Category: CONDITION');

    // Run condition
    foreach ($cards['condition'] as $card) {
        $waypoints->printWaypoint(json_encode($card));
        $card_output = $stream->getCardOutput('condition', $card['name'], $card['parameters']);
        
        $waypoints->printWaypoint("Card in group condition ({$card['name']}) ".($card_output === true ? 'SUCCEEDED' : 'DID NOT SUCCEED. Running ELSE...'));
    
        if($card_output !== true) {
            $condition_succeeded = false;
            break;
        }
    }

    if($condition_succeeded === true) {
        $waypoints->printWaypoint('All cards in group condition SUCCEEDED. Running DO...');
    }

    // Run do / else
    $run_group = ($condition_succeeded === true ? 'do' : 'else');

    foreach ($cards[$run_group] as $card) {
        $waypoints->printWaypoint(json_encode($card));

        // Else cards are located in do
        $card_output = $stream->getCardOutput('do', $card['name'], $card['parameters']);
        
        $waypoints->printWaypoint("Card in group condition ({$card['name']}) ".($card_output === true ? 'SUCCEEDED' : 'DID NOT SUCCEED.'));
    
        // if($card_output !== true) {
        //     $run_group = 'else';
        //     break;
        // }
    }

    exit();
?>