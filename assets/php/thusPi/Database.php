<?php 
    namespace thusPi\Database;

    function connect() {
        return new \MysqliDb(
            \thusPi\Config\get('hostname', 'database'),
            \thusPi\Config\get('username', 'database'),
            \thusPi\Config\get('password', 'database'),
            \thusPi\Config\get('database', 'database')
        );
    }
?>