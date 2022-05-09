<?php 
    namespace thusPi\Streams;

    use thusPi\Interfaces\defaultInterface;

    class Stream extends defaultInterface {
        public function __construct($id) {
            $this->id = $id;
        }

        public function getProperties() {
			return \thusPi\Streams\get($this->id);
		}

        public function listCards() {
            $properties = $this->getProperties();

            $cards = [
                'trigger'   => $properties['trigger'], 
                'condition' => $properties['condition'], 
                'do'        => $properties['do'], 
                'else'      => $properties['else']
            ];

            return $cards;
        }

        public function trigger($name = 'cron', $parameters = []) {
            $properties = $this->getProperties();

            foreach ($properties['trigger'] as $trigger) {
                if($trigger['name'] != $name && $name != 'cron') {
                    return 'name_not_equal';
                }

                foreach ($trigger['parameters'] as $parameter_key => $parameter_value) {
                    if(!isset($parameters[$parameter_key]) || $parameters[$parameter_key] != $parameter_value) {
                        return "param_{$parameter_key}_not_equal_to_{$parameter_value}";
                    }
                }
            }

            $stream_runner_path = DIR_SYSTEM.'streams/run_stream.php';
            if(!file_exists($stream_runner_path)) {
                return 'stream_runner_not_found';
            }

            $args = "{$this->id} {$name} ".encodeshellargarray($parameters);
            $cmd  = script_name_to_shell_cmd($stream_runner_path, $args);

            $output = execute($cmd, $output, 15);

            return true;
        }

        public function getCardOutput($collection, $namespace, $parameters = [], $async = false) {
            $output = ['success' => false];

            $manifest = \thusPi\Streams\get_card($collection, $namespace);

            if(count($parameters) < count($manifest['parameters'])) {
                return 'insufficient_amount_of_parameters';
            }

            $card_runner_path = DIR_SYSTEM."streams/run_card.php";
            if(!file_exists($card_runner_path)) {
                return 'card_runner_not_found';
            }

            $args = ['collection' => $collection, 'namespace' => $namespace, 'parameters' => $parameters];
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
        $stream = $db->getOne('streams');

        // Rename keys since trigger, condition, do and else are reserved keywords
        $stream = array_replace([
            'stream_trigger'   => '[]',
            'stream_condition' => '[]',
            'stream_do'        => '[]',
            'stream_else'      => '[]'
        ], $stream);
        
        $stream = array_replace([
            'id'        => $id,
            'name'      => '',
            'icon'      => '',
            'category'  => '',
            'trigger'   => $stream['stream_trigger'],
            'condition' => $stream['stream_condition'],
            'do'        => $stream['stream_do'],
            'else'      => $stream['stream_else']
        ], $stream);

        // Delete old key indexes
        unset($stream['stream_condition'], $stream['stream_do'], $stream['stream_trigger'], $stream['stream_else']);

        if(!is_array($stream['trigger'])) {
            $stream['trigger'] = @json_decode($stream['trigger'], true) ?? [];
        }

        if(!is_array($stream['condition'])) {
            $stream['condition'] = @json_decode($stream['condition'], true) ?? [];
        }

        if(!is_array($stream['do'])) {
            $stream['do'] = @json_decode($stream['do'], true) ?? [];
        }

        if(!is_array($stream['else'])) {
            $stream['else'] = @json_decode($stream['else'], true) ?? [];
        }

        return $stream;
    }

    function get_all() {
        $db = \thusPi\Database\Connect();

        $streams = [];

        $ids = array_column($db->get('streams', null, 'id'), 'id');

        foreach ($ids as $id) {
            $stream = \thusPi\Streams\get($id);

            if(!is_array($stream)) {
                continue;
            }

            $streams[] = $stream;
        }

        return $streams;
    }
    
    function trigger_all($name = 'cron', $parameters = []) {
        $output = '';
            
        $streams = \thusPi\Streams\get_all();

        foreach ($streams as $properties) {
            $stream = new Stream($properties['id']);
            $output .= $stream->trigger($name, $parameters);
        }

        return $output;
    }

    function get_card($collection, $namespace) {
        $manifest_fallback = [
            'icon'        => '',
            'collection'  => '',
            'predictable' => false,
            'group'       => '',
            'parameters'  => []
        ];

        $manifest_path = DIR_ASSETS."/streams/cards/{$collection}/{$namespace}.json";
        if(!file_exists($manifest_path)) {
            return $manifest_fallback;
        }

        if(!($manifest = @file_get_json($manifest_path))) {
            return $manifest_fallback;
        }

        $manifest = array_replace($manifest_fallback, $manifest);
        $manifest['namespace']  = $namespace;
        $manifest['collection'] = basename(dirname($manifest_path));

        return $manifest;
    }
?>