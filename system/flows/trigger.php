<?php 
    chdir(__DIR__);
    include_once("../../autoload.php");
?>
<?php 
    if(count($argv) <= 2) {
        \thusPi\Response\error('Insufficient amount of arguments given.');
    }

    $trigger    = $argv[1];
    $parameters = @shell_arg_decode($argv[2]);

    if(!isset($trigger) || !isset($parameters)) {
        return false;
    }

    $Streams    = new Streams;
    $stream_ids = $Streams->list_ids();

    foreach ($stream_ids as $stream_id) {
        echo("Checking stream {$stream_id} for trigger...\n");


        $stream = new Stream($stream_id);

        $cards = [
            'trigger'   => $stream->getProperty('trigger'),
            'condition' => $stream->getProperty('condition')
        ];

        $condition_met[$stream_id] = true;

        foreach ($cards['trigger'] as $parameters) {
            $card = $parameters['name'];
            unset($parameters['name']);
            
            foreach ($parameters as $param_key => $param_value) {
                if(!isset($parameters[$param_key]) || trim($parameters[$param_key]) != trim($param_value)) {
                    echo("    Parameters don't match".PHP_EOL.PHP_EOL);

                    // Skip to next stream
                    continue 3;
                }
            }

            $output = $stream->get_card_output($card, $parameters, 'trigger');

            if($output === true || $output == 'true' || $output === '1') {
                echo("    Stream triggered!\n");
                echo("    Running cards in {$stream_id}/condition\n");
            } else {
                echo("    Not triggered\n\n");

                // Skip to next stream
                continue 2;
            }
        }

        foreach ($cards['condition'] as $parameters) {
            $card = $parameters['name'];
            unset($parameters['name']);

            $card_start_ = microtime(true);
            $output = $stream->get_card_output($card, $parameters, 'condition');
            $card_end_ = microtime(true);

            $result = bool_to_str(str_to_bool($output));

            echo("        Running card {$stream_id}/condition/{$card}:\n");
            echo("            Parameters: ".json_encode($parameters).PHP_EOL);
            echo("            Output:     ".$output.PHP_EOL);
            echo("            Result:     ".$result.PHP_EOL);
            echo("            Took:       ".round(($card_end_-$card_start_)*1000).'ms'.PHP_EOL);

            switch ($result) {
                case 'true':
                    break;
                
                case 'false':
                    $condition_met[$stream_id] = false;

                    // Skip to else category
                    continue 2;
                    break;

                case 'fail':

                    // Skip to next stream
                    continue 3;
                    break;
            }
        }
        
        if($condition_met[$stream_id]) {
            echo("    Running cards in {$stream_id}/do".PHP_EOL);

            // Execute Do block
            $output = $stream->run_cards('do');
    
            // foreach ($stream_list_item['do'] as $card => $parameters) {
            //     echo("\nAttempting to run do card {$card}!");
    
    
            //     if($output === true || $output == 'true' || $output === '1') {
            //         echo("\nSUCCESFULLY RAN STREAM {$stream_id}!");
            //     } else {
            //         echo("\nSOMETHING WENT WRONG WHILE RUNNING STREAM {$stream_id}!");
            //         var_dump($output);
    
            //         $condition_met = false;
            //     }
            // }
        } else {
            echo("    Running cards in {$stream_id}/else".PHP_EOL);
            // Execute Else block
            $output = $stream->run_cards('else');
        }
    }
?>