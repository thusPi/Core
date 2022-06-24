<?php 
    namespace thusPi\Flows;

    use thusPi\Interfaces\defaultInterface;

    class Flow extends defaultInterface {
        public function __construct($id) {
            $this->id = $id;
        }

        public function getProperties() {
			return \thusPi\Flows\get($this->id);
		}

        public function getCards() {
            $properties = $this->getProperties();

            return [
                'trigger'   => $properties['cards']['trigger'] ?? [], 
                'condition' => $properties['cards']['condition'] ?? [], 
                'do'        => $properties['cards']['do'] ?? [], 
                'else'      => $properties['cards']['else'] ?? []
            ];
        }

        public function trigger($name = 'cron', $parameters = []) {
            $properties = $this->getProperties();

            // Return if the trigger does not match with the trigger name
            // specified in the manifest, unless the trigger is cron
            foreach ($properties['trigger'] as $trigger) {
                if($trigger['name'] != $name && $name != 'cron') {
                    return 'name_not_equal';
                }

                // Return if the parameters don't match with 
                // the parameters specified in the manifest
                foreach ($trigger['parameters'] as $parameter_key => $parameter_value) {
                    if(!isset($parameters[$parameter_key]) || $parameters[$parameter_key] != $parameter_value) {
                        return "param_{$parameter_key}_not_equal_to_{$parameter_value}";
                    }
                }
            }

            // Find script that runs flows
            $script_path = DIR_SYSTEM.'/flows/run_flow.php';
            if(!file_exists($script_path)) {
                return 'flow_script_not_found';
            }

            // Create command
            $args = "{$this->id} {$name} ".encodeshellargarray($parameters);
            $cmd  = script_name_to_shell_cmd($script_path, $args);

            // Execute script
            $output = execute($cmd, $output, 2);

            return true;
        }

        public function getCardOutput($group, $namespace, $parameters = [], $async = false) {
            $output = ['success' => false];

            $manifest = \thusPi\Flows\get_card($group, $namespace);

            if(count($parameters) < count($manifest['parameters'])) {
                return 'insufficient_amount_of_parameters';
            }

            $card_runner_path = DIR_SYSTEM."Flows/run_card.php";
            if(!file_exists($card_runner_path)) {
                return 'card_runner_not_found';
            }

            $args = ['group' => $group, 'namespace' => $namespace, 'parameters' => $parameters];
            $cmd = script_name_to_shell_cmd($card_runner_path, encodeshellargarray($args));

            if($async) {
                shell_exec("{$cmd} > /dev/null 2>&1 &");
                $json = '{"success":true}';
            } else {
                execute($cmd, $json, 15);
            }

            if(!($output = @json_decode($json, true))) {
                $output = ['success' => false];
            }

            return @str_to_bool($output['success']);
        }
    }

    function get($id) {
        $db = \thusPi\Database\connect();

        $db->where('id', $id);
        $flow = $db->getOne('flows') ?? [];

        // Rename keys since trigger, condition, do and else are reserved keywords
        $flow = array_replace([
            'flow_trigger'   => '[]',
            'flow_condition' => '[]',
            'flow_do'        => '[]',
            'flow_else'      => '[]'
        ], $flow);
        
        $flow = array_replace([
            'id'        => $id,
            'name'      => '',
            'icon'      => '',
            'category'  => '',
            'cards'     => [
                'trigger'   => $flow['flow_trigger'],
                'condition' => $flow['flow_condition'],
                'do'        => $flow['flow_do'],
                'else'      => $flow['flow_else']
            ]
        ], $flow);

        // Delete old key indexes
        unset($flow['flow_condition'], $flow['flow_do'], $flow['flow_trigger'], $flow['flow_else']);

        // Decode cards json
        foreach ($flow['cards'] as &$cards) {
            $cards = json_decode($cards, true) ?? [];
        }

        return $flow;
    }

    function get_all() {
        $db = \thusPi\Database\Connect();

        $flows = [];

        $ids = array_column($db->get('flows', null, 'id'), 'id');

        foreach ($ids as $id) {
            $flow = \thusPi\Flows\get($id);

            if(!is_array($flow)) {
                continue;
            }

            $flows[] = $flow;
        }

        return $flows;
    }
    
    function trigger_all($name = 'cron', $parameters = []) {
        $output = '';
            
        $flows = \thusPi\Flows\get_all();

        foreach ($flows as $properties) {
            $flow = new \thusPi\Flows\Flow($properties['id']);
            $output .= $flow->trigger($name, $parameters);
        }

        return $output;
    }

    function get_card($group, $namespace) {
        $manifest_fallback = [
            'icon'        => '',
            'group'       => '',
            'predictable' => false,
            'group'       => '',
            'parameters'  => []
        ];

        $manifest_path = DIR_ASSETS."/Flows/cards/{$group}/{$namespace}.json";
        if(!file_exists($manifest_path)) {
            return $manifest_fallback;
        }

        if(!($manifest = @file_get_json($manifest_path))) {
            return $manifest_fallback;
        }

        $manifest = array_replace($manifest_fallback, $manifest);
        $manifest['namespace']  = $namespace;
        $manifest['group']      = basename(dirname($manifest_path));

        return $manifest;
    }
?>