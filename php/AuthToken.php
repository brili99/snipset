<?php
// Simple AuthToken middleware for token validation
class AuthToken
{
    public static function check($token)
    {
        if (empty($token)) return false;
        require_once __DIR__ . '/Database.php';
        $db = new Database();
        try {
            $user = $db->select('users', ['user_id'], 'WHERE token=? AND ip_address=?', [$token, $_SERVER['REMOTE_ADDR']]);
            return count($user) > 0;
        } catch (\Throwable $th) {
            return false;
        }
    }

    public static function short_cek()
    {
        // Check authentication
        if (!isset($_REQUEST['token']) || !AuthSession::check($_REQUEST['token'])) {
            header('Content-Type: application/json');
            http_response_code(401);
            echo json_encode([
                'status' => false,
                'msg' => 'Unauthorized'
            ]);
            exit;
        }
    }
}
