<?php 
    namespace thusPi\Database;

    function connect() {
        return new \MysqliDb(
            \thusPi\Config\get('hostname', 'system/database'),
            \thusPi\Config\get('username', 'system/database'),
            \thusPi\Config\get('password', 'system/database'),
            \thusPi\Config\get('database', 'system/database')
        );
    }
?>