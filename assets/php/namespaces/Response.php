<?php 
    namespace thusPi\Response;
    
    function success($info = 'success', $data = []) {
        http_response_code(200);
        if(!headers_sent()) {
            header('Content-Type: application/json');
        }
        exit(json_encode(['success' => true, 'data' => $data, 'info' => $info])."\n");

        return true;
    }

    function error($info = 'error_unknown', $data = []) {
        http_response_code(400);
        if(!headers_sent()) {
            header('Content-Type: application/json');
        }
        exit(json_encode(['success' => false, 'data' => $data, 'info' => $info])."\n");

        return true;
    }
?>