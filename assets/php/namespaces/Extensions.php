<?php 
    namespace thusPi\Extensions;

    use \thusPi\Interfaces\defaultInterface;

    class Extension extends defaultInterface {
        protected $dir;

        public function __construct($id) {
            $this->id  = $id;
            $this->dir = DIR_EXTENSIONS."/{$this->id}";
        }

        public function getProperties() {
			return \thusPi\Extensions\get($this->id) ?? [];
        }

        public function setProperties(array $properties) {
            $current_properties = $this->getProperties();

            $properties = array_replace_recursive($current_properties, $properties);

            return file_put_contents("{$this->dir}/manifest.json", json_encode($properties));
        }

        public function saveData(string $filename, $data) {
            if(!file_exists("{$this->dir}/data")) {
                mkdir("{$this->dir}/data");
            }

            $filename = preg_replace('/[^a-zA-Z0-9_-]+/', '', basename($filename));

            file_put_contents("{$this->dir}/data/{$filename}.json", @json_encode($data));
        }

        public function getData(string $filename) {
            $filename = preg_replace('/[^a-zA-Z0-9_-]+/', '', basename($filename));

            return @json_decode(@file_get_contents("{$this->dir}/data/{$filename}.json", true), true) ?? null;
        }

        public function translate($key, $replacements = null, $fallback = null, $locale = null) {
            // Use user locale if it's supported
            $locale = \thusPi\Users\CurrentUser::getSetting('locale');

            // Use override locale (constant from feature) instead
            // if it has been defined
            if(defined('EXTENSIONS_OVERRIDE_LOCALE')) {
                $locale = \EXTENSIONS_OVERRIDE_LOCALE;
            }

            // Check if requested locale exists, else use en_US instead
            $locale_file = "{$this->dir}/assets/locale/{$locale}.json";
            if(!file_exists($locale_file)) {
                $locale_file = "{$this->dir}/assets/locale/en_US.json";
            }

            // Load translations
            $translations = file_get_json($locale_file) ?? [];

            return \thusPi\Locale\translate($key, $replacements, $fallback, $translations);
        }

        public function uninstall() {      
            // Return if extension is not installed
            if(is_null($this->getProperties())) {
                return false;
            }

            if(!isset($this->dir)) {
                return false;
            }

            // Delete the manifest file first to prevent the extension
            // from being loaded while deleting the remaining files
            @unlink("{$this->dir}/manifest.json");

            // Delete the remaining files
            $res = [];

            // Catch warnings if they occur
            set_error_handler(function($errno, $errstr) use (&$res) {
                // Store the warning
                $res[] = $errstr;
            }, E_WARNING);
            rmtree($this->dir);
            restore_error_handler();

            // Return true if no warnings occured, otherwise return them
            return empty($res) ? true : $res;
        }

        public function callFeatureComponent($type, $name, $component) {
            if($component == 'main') {
                $feature_component_path = glob("{$this->dir}/features/{$type}/{$name}/main.*")[0] ?? null;
            } else {
                $feature_component_path = "{$this->dir}/features/{$type}/{$name}/{$component}";
            }

            // Return if feature component does not exist
            if(!isset($feature_component_path) || !file_exists($feature_component_path)) {
                return null;
            }

            $feature_component_filetype = PATHINFO($feature_component_path, PATHINFO_EXTENSION);

            // Escape the extension id
            $extension_id_escaped = escapeshellarg($this->id);

            // Escape the user's locale
            $user_locale_escaped = escapeshellarg(\thusPi\Users\CurrentUser::getSetting('locale'));

            // Obtain the output
            if($feature_component_filetype == 'php') {
                $cmd = "php -c ".DIR_SYSTEM."/extensions/features/php.ini {$feature_component_path} {$extension_id_escaped} {$type} {$name} {$user_locale_escaped}";
                execute($cmd, $output, 60);
            } else {
                $output = file_get_contents($feature_component_path);
            }

            return $output;
        }

        public function callHookComponent($type, $name, $args = [], $component = 'main') {
            // Hooks need to be a php file
            $hook_component_path = "{$this->dir}/hooks/{$type}/{$name}/{$component}.php";

            // Return if hook component does not exist
            if(!file_exists($hook_component_path)) {
                return null;
            }

            $extension_id = escapeshellarg($this->id);
            $name         = escapeshellarg($name);
            $type         = escapeshellarg($type);
            $args_encoded = encodeshellargarray($args);

            $cmd = "php -c ".DIR_SYSTEM."/extensions/hooks/php.ini {$hook_component_path} {$extension_id} {$type} {$name} {$args_encoded}";
            

            execute($cmd, $output, 0);
        }
    }

    function get_from_server($extension_id) {
        $extensions = \thusPi\Extensions\get_all_from_server();

        foreach ($extensions as $extension) {
            if($extension['id'] == $extension_id) {
                return $extension;
            }
        }

        return null;
    }

    function get_all_from_server($category = 'none', $filter = null) {
        if($category == 'installed') {
            return [];
        }

        $client = new \GuzzleHttp\Client();
        $res = [];

        // Fetch catalogue
        $response = $client->request('GET', \thusPi\Config\get('extensions_catalogue', 'servers'));
        $catalogue = json_decode($response->getBody(), true);

        foreach ($catalogue['items'] as $extension) {
            if(
                isset($filter) &&
                stripos($extension['manifest']['name'], $filter) === false && 
                stripos($extension['manifest']['description'], $filter) === false
            ) {
                continue;
            }

            $extension['repository']['pushed_ago'] = \thusPi\Locale\date_format('best,best', $extension['repository']['pushed_at']);
            $extension_installed = (\thusPi\Extensions\get($extension['id'])['installed'] ?? false) !== false;

            $res[] = [
                'id'              => $extension['id'] ?? null,
                'name'            => $extension['manifest']['name'] ?? null,
                'description'     => $extension['manifest']['description'] ?? null,
                'repository'      => $extension['repository'],
                'installed'       => $extension_installed,
                'enabled'         => \thusPi\Extensions\get($extension['id'])['enabled'] ?? false
            ];
        }

        return $res;
    }

    function list_types_for_family($type, $family) {
        $extensions = \thusPi\Extensions\get_all();

        $res = [];
        foreach ($extensions as $extension_id => $manifest) {
            if(!isset($manifest[$family][$type]) || empty($manifest[$family][$type])) {
                continue;
            }

            $res[$extension_id] = $manifest[$family][$type];
        }

        return $res;
    }
    
    function list_hooks_for_type($type) {
        return \thusPi\Extensions\list_types_for_family($type, 'hooks'); 
    }

    function call_hooks_for_type($type, $args = [], $component = 'main') {
        $all_hooks = \thusPi\Extensions\list_hooks_for_type($type);

        foreach ($all_hooks as $extension_id => $hooks) {
            $extension = new \thusPi\Extensions\Extension($extension_id);

            foreach ($hooks as $hook) {
                $extension->callHookComponent($type, $hook, $args, $component);
            }
        }
    }

    // Returns an array of all features across all installed extensions for a specific type
    function list_all_features(string $type, $include_disabled = false) {
        $manifests = \thusPi\Extensions\get_all();

        $res = [];
        foreach ($manifests as $extension_id => $manifest) {
            if(!isset($manifest['features'][$type])) {
                continue;
            }

            foreach ($manifest['features'][$type] as $feature_id) {
                // Continue if feature id is not a string
                if(!is_string($feature_id)) {
                    continue;
                }

                // Continue if feature folder does not exist
                if(!file_exists(DIR_EXTENSIONS."/{$extension_id}/features/{$type}/{$feature_id}")) {
                    continue;
                }

                $feature['id'] = $feature_id;
                array_push($res, [
                    'extension_id' => $extension_id,
                    'feature' => $feature
                ]);
            }
        }

        return $res;
    }

    // Returns the manifest for an installed extension
    function get($extension_id) {
        $manifest_file = DIR_EXTENSIONS."/{$extension_id}/manifest.json";

        // Read the manifest
        $manifest = file_get_json($manifest_file) ?? [];

        $manifest['enabled']   = (isset($manifest['enabled']) && $manifest['enabled'] === true);
        $manifest['installed'] = file_exists(DIR_EXTENSIONS."/{$extension_id}/");

        return $manifest;
    }

    // Returns an array of the manifests for every installed extension
    function get_all($include_disabled = false) {
        $manifest_files = glob(DIR_EXTENSIONS.'/*/manifest.json');

        $res = [];
        foreach ($manifest_files as $manifest_file) {
            $extension_id = basename(dirname($manifest_file));

            $manifest = \thusPi\Extensions\get($extension_id);
            if(!isset($manifest)) {
                continue;
            }

            // Continue if extension is disabled
            if(isset($manifest['enabled']) && $manifest['enabled'] === false && !$include_disabled) {
                continue;
            }

            $res[$extension_id] = $manifest;
        }

        return $res;
    }

    function install($extension_id) {
        $extension = \thusPi\Extensions\get_from_server($extension_id);

        if(!isset($extension)) {
            return 'The selected extension was not found.';
        }

        if($extension['installed']) {
            return 'The selected extension is already installed.';
        }

        // Create HTTP client
        $client = new \GuzzleHttp\Client();

        // Check if manifest.json file exists in remote repository
        $res = $client->request('GET', "https://raw.githubusercontent.com/{$extension['repository']['full_name']}/{$extension['repository']['default_branch']}/manifest.json", [
            'http_errors'    => false,
            'decode_content' => 'json'
        ]);

        if($res->getStatusCode() != 200) {
            return 'The selected repository does not have a manifest.json file.';
        }

        exec("git --version", $git_version);
        if(strpos($git_version[0] ?? null, 'git version') === false) {
            return 'Git was not found on your machine.';
        }

        $destination  = DIR_EXTENSIONS."/{$extension_id}";
        $command      = "git clone -b {$extension['repository']['default_branch']} https://github.com/{$extension['repository']['full_name']} {$destination}";

        // End script automatically after 25 minutes
        set_time_limit(1500);

        // Clone the selected branch to the local directory, which is
        // not allowed to take more than 20 minutes
        $exit_code = execute($command, $output, 1200);
        
        if($exit_code != 0 && $exit_code != 1) {
            return 'Failed to clone the repository';
        }

        // Throw an error if the repository was not cloned
        if(!file_exists($destination)) {
            return 'The repository could not be cloned.';
        }

        // Throw an error if the manifest file was not cloned
        if(!file_exists($destination.'/manifest.json')) {
            return 'The repository was cloned, however the manifest file could not be found locally.';
        }
        
        // Enable the extension
        $extension = new \thusPi\Extensions\Extension($extension_id);
        $extension->setProperty('enabled', true);

        return true;
    }

    // function get_disabled_features($scopes = []) {
    //     $defined_features = array_merge(get_defined_features(false)['internal'], get_defined_features(false)['user']);

    //     $disabled_features = array_filter($defined_features, function($v) use ($scopes) {
    //         if(
    //             str_starts_with($v, 'str') ||
    //             str_starts_with($v, 'substr') ||
    //             str_starts_with($v, 'array_') ||
    //             str_starts_with($v, 'preg_') ||
    //             str_starts_with($v, 'html') ||

    //             str_contains($v, 'sleep') ||
    //             str_contains($v, '_exists') ||
    //             str_contains($v, 'is_') ||
    //             str_contains($v, 'timezone') ||

    //             str_ends_with($v, 'val') ||
    //             str_ends_with($v, 'sort') ||

    //             $v == 'basename' ||
    //             $v == 'dirname' ||
    //             $v == 'pathinfo' ||
    //             $v == 'implode' || $v == 'join' ||
    //             $v == 'explode' ||
    //             $v == 'trim' || $v == 'ltrim' || $v == 'rtrim' ||
    //             $v == 'similar_text' || $v == 'levenshtein' ||
    //             $v == 'ucfirst' || $v == 'ucwords' || $v == 'lcfirst' ||
    //             $v == 'strtolower' || $v == 'strtoupper' ||
    //             $v == 'chr' || $v == 'ord' ||
    //             $v == 'define'
    //             // $v == 'set_time_limit' ||
    //             // $v == 'session_start' ||
    //             // $v == 'call_user_func'
    //         ) {
    //             return false;
    //         }

    //         return true;
    //     });

    //     return $disabled_features;
    // }

    // function get_disabled_classes($scopes = []) {
    //     return [];
    // }
?>