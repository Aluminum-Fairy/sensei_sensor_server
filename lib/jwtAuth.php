<?php
#composer
require_once(__DIR__ . '/../vendor/autoload.php');
#自作ライブラリ
require_once __DIR__ . "/../config/Config.php";

use \Firebase\JWT\JWT;

function apiAuth(){
    // GET 時
    if (strtoupper($_SERVER['REQUEST_METHOD']) == 'GET') {
        $auth = isset($_SERVER['HTTP_AUTHORIZATION']) ? $_SERVER['HTTP_AUTHORIZATION'] : '';
        if (preg_match('#\ABearer\s+(.+)\z#', $auth, $m)) { // Bearer xxxx...
            $jwt = $m[1];
            try {
                $payload = JWT::decode($jwt, JWT_KEY, array(JWT_ALG)); // JWT デコード (失敗時は例外)
                $loginUserId = $payload->loginUserId; // エンコード時のデータ取得(loginUserId)

                header('Content-Type: application/json');
                header('Access-Control-Allow-Origin: *'); // CORS
                return $loginUserId;
            } catch (Exception $e) {
            }
        }
    }
    http_response_code(401);
    return false;
}
