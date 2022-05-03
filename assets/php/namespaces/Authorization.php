<?php 
    namespace thusPi\Authorization;

    function authorize() {
        $db = \thusPi\Database\connect();

        // Return if a token was not supplied
        if(!isset($_COOKIE['thusPi_token'])) {
            session_write_close();          
            return false;
        }

        // Find token
        $db->where('id', $_COOKIE['thusPi_token']);
        $row = $db->getOne('tokens');

        // Throw an error and delete token if one can not be found
        if($row === null) {
            \thusPi\Authorization\delete_token($_COOKIE['thusPi_token']);
            
            session_write_close();
            return false;
        }

        // Create new token when expired
        if($row['expires'] < time()) {
            // Delete old token from database
            $db->where('id', $_COOKIE['thusPi_token']);
            $db->delete('tokens');

            \thusPi\Authorization\create_token($row['uuid']);
        }

        $_SESSION['thusPi_uuid'] = $row['uuid'];

        session_write_close();
        return true;
    }

    function login_verify($username, $password) {
        $db = \thusPi\Database\connect();

        $users = $db->get('users');

        // Loop users to check if username and password match
        foreach ($users as $user) {
            if(trim($username) != trim($user['username'])) {
                continue;
            }

            if($user['uuid'] == 'default') {
                continue;
            }

            if(!password_verify(trim($password), $user['password_hash'])) {
                continue;
            }

            // Create token if username and password entered are correct
            \thusPi\Authorization\create_token($user['uuid']);

            return true;
        }

        return false;
    }

    function create_token($uuid) {
        $db = \thusPi\Database\connect();

        $token   = unique_id_secure(128);
        $expires    = time() + 900; // Token expires after 15 minutes

        if(!$db->insert('tokens', [
            'id'         => $token,
            'uuid'       => $uuid,
            'expires'    => $expires
        ])) {
            \thusPi\Response\error('error_token_creation_failed');
        }

        setcookie('thusPi_token', $token, time() + 365 * 24* 60 * 60, '/');

        return true;
    }

    function delete_token($token) {
        $db = \thusPi\Database\connect();

        $db->where('id', $token);
        $db->delete('tokens');

        setcookie('thusPi_token', null, -1, '/');
        unset($_SESSION['thusPi_uuid']);

        return true;
    }
?>