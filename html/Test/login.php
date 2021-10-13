<?php
#composer
require_once(__DIR__ . '/../../vendor/autoload.php');

#自作ライブラリ
require_once __DIR__ . "/../../lib/UserInfo.php";
require_once __DIR__ . "/../../config/Config.php";
use \Firebase\JWT\JWT;

$userInfo = new UserInfo($loginInfo);

// POST 時
if (strtoupper($_SERVER['REQUEST_METHOD']) == 'POST') {
    $inputString = file_get_contents('php://input'); // JSON 文字列取得
    $input = @json_decode($inputString, true);

    if (is_array($input)) {
        $input = array_merge(array('loginUserId' => '', 'password' => ''), $input);
        $loginUserId = $input['loginUserId'];
        $password = $input['password'];

        $ok = $userInfo->userAuth($loginUserId,$password); // loginUserId = test, password = test で認証 OK とする (仮)
        if ($ok) {
            $payload = array(
                'iss' => JWT_ISSUER,
                'exp' => time() + JWT_EXPIRES,
                'loginUserId' => $loginUserId,
            );
            $jwt = JWT::encode($payload, JWT_KEY, JWT_ALG);

            header('Content-Type: application/json');
            header('Access-Control-Allow-Origin: *'); // CORS
            echo json_encode(array('token' => $jwt)); // token を返却
            return;
        }
    }
    // JSON 取得失敗、認証に失敗した場合は 401
    http_response_code(401);
}
