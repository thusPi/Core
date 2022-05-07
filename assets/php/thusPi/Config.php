<?php 
    namespace thusPi\Config;

    function get($key = null, $config = 'main') {
        if(!isset($key)) {
            return CONFIG[$config] ?? null;
        }

        return CONFIG[$config][$key] ?? null;
    }
?>