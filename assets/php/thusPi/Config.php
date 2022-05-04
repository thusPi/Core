<?php 
    namespace thusPi\Config;

    function get($key, $config = 'main') {
        if(!isset(CONFIG[$config][$key])) {
            return null;
        }

        return CONFIG[$config][$key];
    }
?>