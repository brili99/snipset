<?php
require_once __DIR__ . '/../controller/User.php';
class AccessUser
{
    public function __construct()
    {
        $actions = [
            'create_user',
            'login',
            'get_user_by_token',
            'delete_user'
        ];
        if (
            isset($_REQUEST['action']) &&
            in_array($_REQUEST['action'], $actions)
        ) {
            // Collect parameters from request
            $params = $_REQUEST;

            $user = new User();

            switch ($_REQUEST['action']) {
                // case 'create_user':
                //     $user->create_user($params['user_id'] ?? null, $params['user_pass'] ?? null, $params['token'] ?? null);
                //     break;
                case 'login':
                    $user->login($params['user_id'] ?? null, $params['user_pass'] ?? null);
                    break;
                case 'get_user_by_token':
                    $user->get_user_by_token($params['token'] ?? null);
                    break;
                // case 'delete_user':
                //     $user->delete_user($params['user_id'] ?? null);
                //     break;
            }
        } else {
            header('Content-Type: application/json');
            http_response_code(404);
            echo json_encode([
                'status' => false,
                'msg' => 'action not found'
            ]);
        }
    }
};

new AccessUser();
