<?php 
    namespace thusPi\Users;

    use thusPi\Interfaces\defaultInterface;

    class User extends defaultInterface {
        private $uuid;
        private $user;

        public function __construct($uuid) {
            $db = \thusPi\Database\connect();

            $db->where('uuid', $uuid);
            $user = $db->getOne('users');

            $user['flags'] = @json_decode($user['flags'], true) ?? [];
            $user['settings'] = @json_decode($user['settings'], true) ?? [];

            // // Load default settings from config
            // $default_settings = \thusPi\Config\get(null, 'user/settings');

            // // Apply default settings if a setting is not set
            // foreach ($default_settings as $name => $setting) {
            //     if(isset($user['settings'][$name])) {
            //         continue;
            //     }

            //     // Select default item if it is specified
            //     if(isset($setting['default']) && isset($setting['items'][$setting['default']])) {
            //         $user['settings'][$name] = $setting['default'];
            //         continue;
            //     }

            //     // Select first item if default is not specified
            //     if(isset($setting['default']) && isset($setting['items'][$setting['default']])) {
            //         $user['settings'][$name] = array_key_first($setting['items']);
            //     }
            // }

            $this->uuid = $uuid;
            $this->user = $user;

            return true;
        }

        public function checkFlagItem($flag, $item) {
            $allow = $this->getFlag("{$flag}_allow") ?? [];
            $deny  = $this->getFlag("{$flag}_deny") ?? [];

            if($this->getFlag('is_admin') === true) {
                return true;
            }

            if($deny == '*' || in_array($item, $deny)) {
                return false;
            }

            if($allow == '*' || in_array($item, $allow)) {
                return true;
            }

            return false;
        } 

        public function getFlag($flag) {
            if(!isset($this->user['flags'][$flag])) {
                return null;
            }

            return $this->user['flags'][$flag];
        }

        public function getProperties() {
            $properties = $this->user;
            
            if(isset($properties['password_hash'])) {
                unset($properties['password_hash']);
            }

            return $properties;
        }

        public function getSetting($key) {
            return $this->getSettings()[$key] ?? null;
        }

        public function getSettings() {
            return $this->user['settings'] ?? [];
        }

        public function setSetting($key, $value) {
            $db = \thusPi\Database\connect();

            $new_settings       = $this->user['settings'];
            $new_settings[$key] = $value; 

            $db->where('uuid', $this->uuid);
            if(!$db->update('users', ['settings' => @json_encode($new_settings)])) {
                return false;
            }

            $this->user['settings'] = $new_settings;

            return true;
        }

        public function getProfilePicture() {
            $src = DIR_DATA."/users/pictures/{$this->user['uuid']}.jpg";

            if(!file_exists($src)) {
                return null;
            }

            if(!$binary = file_get_contents($src)) {
                return null;
            }

            return 'data:image/jpeg;base64,'.base64_encode($binary);
        }
    }

    class CurrentUser {
        static public function authorized() {
            return (isset($_SESSION['thusPi_uuid']) && $_SESSION['thusPi_uuid'] != '__GUEST__' && isset($_COOKIE['thusPi_token']));
        }

        static public function getFlag($flag) {
            $user = @new \thusPi\Users\User($_SESSION['thusPi_uuid']);
            return $user->getFlag($flag);
        }

        static public function checkFlagItem($flag, $item) {
            $user = @new \thusPi\Users\User($_SESSION['thusPi_uuid']);
            return $user->checkFlagItem($flag, $item);
        }

        static public function getProperty($property) {
            $user = @new \thusPi\Users\User($_SESSION['thusPi_uuid']);
            return $user->getProperty($property);
        }

        static public function getProperties() {
            $user = @new \thusPi\Users\User($_SESSION['thusPi_uuid']);
            return $user->getProperties();
        }

        static public function getSetting($key) {
            $user = @new \thusPi\Users\User($_SESSION['thusPi_uuid']);
            return $user->getSetting($key);
        }

        static public function getSettings() {
            $user = @new \thusPi\Users\User($_SESSION['thusPi_uuid']);
            return $user->getSettings();
        }

        static public function setSetting($key, $value) {
            $user = @new \thusPi\Users\User($_SESSION['thusPi_uuid']);
            return $user->setSetting($key, $value);
        }

        static public function signOut() {
            return \thusPi\Authorization\delete_token($_COOKIE['thusPi_token'] ?? null);
        }

        static public function getProfilePicture() {
            $user = @new \thusPi\Users\User($_SESSION['thusPi_uuid']);
            return $user->getProfilePicture();
        }
    }

    function get($uuid, $respect_permissions = false) {
		if($respect_permissions && !\thusPi\Users\CurrentUser::checkFlagItem('users', $uuid)) {
            return null;
        }

        $db = \thusPi\Database\connect();

        $db->where('uuid', $uuid);
        $user = $db->getOne('users');

		if(!isset($user)) {
			return null;
		}
        
        $user = array_replace([
            'uuid' => $uuid,
            'name' => '',
            'flags' => [],
            'setttings' => []
        ], $user);

        if(is_string($user['flags'])) {
            $user['flags'] = @json_decode($user['flags'], true);
        }

        if(is_string($user['settings'])) {
            $user['settings'] = @json_decode($user['settings'], true);
        }

        return $user;
    }

    function get_all($respect_permissions = false) {
        $db = \thusPi\Database\connect();

        $users = [];

        $uuids = array_column($db->get('users', null, 'uuid'), 'uuid');

        foreach ($uuids as $uuid) {
            $user = \thusPi\Users\get($uuid);

            if(!is_array($user)) {
                continue;
            } 

            $users[] = $user;
        }

        return $users;
    }
?>