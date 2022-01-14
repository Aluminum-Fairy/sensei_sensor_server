<?php

#DB Connection
require_once __DIR__ . "/../config/SQL_Login.php";
#composer
require_once __DIR__ . '/../vendor/autoload.php';
#自作ライブラリ
require_once __DIR__ . "/../config/Config.php";
require_once __DIR__ . "/../lib/UserInfo.php";

use  Firebase\JWT\JWT;

class JwtAuth
{
    protected $dbh;
    protected $UserInfo;


    public function __construct($loginInfo)
    //初期化時にデータベースへの接続
    {
        try {
            $this->dbh = new PDO($loginInfo[0], $loginInfo[1], $loginInfo[2], array(PDO::ATTR_PERSISTENT => true));
        } catch (PDOException $e) {
            http_response_code(500);
            header("Error:" . $e);
            exit();
        }
        $this->UserInfo = new UserInfo($loginInfo);
    }

    public function auth()
    {
        // GET 時
        if (strtoupper($_SERVER['REQUEST_METHOD']) == 'GET') {
            $auth = isset($_SERVER['HTTP_AUTHORIZATION']) ? $_SERVER['HTTP_AUTHORIZATION'] : '';
            if (preg_match('#\ABearer\s+(.+)\z#', $auth, $m)) { // Bearer xxxx...
                $jwt = $m[1];
                try {
                    $payload = $this->JWT::decode($jwt, JWT_KEY, array(JWT_ALG)); // JWT デコード (失敗時は例外)
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

    public function login()
    {
        // POST 時
        if (strtoupper($_SERVER['REQUEST_METHOD']) == 'POST') {
            $inputString = file_get_contents('php://input'); // JSON 文字列取得
            $input = @json_decode($inputString, true);
            if (is_array($input)) {
                $input = array_merge(array('userName' => '', 'password' => ''), $input);
                $loginUserId = $input['userName'];
                $password = $input['password'];
                $ok = $this->UserInfo->userAuth($loginUserId, $password); // loginUserId = test, password = test で認証 OK とする (仮)
                if ($ok) {
                    $payload = array(
                        'iss' => JWT_ISSUER,
                        'exp' => time() + JWT_EXPIRES,
                        'loginUserId' => $loginUserId,
                    );
                    $jwt = JWT::encode($payload, JWT_KEY, JWT_ALG);
                    header('Content-Type: application/json');
                    header('Access-Control-Allow-Origin: *'); // CORS
                    setcookie(
                        "token",
                        $jwt,
                        [
                        'expires' => time() + 3600,
                        'path' => '/',
                        'secure' => false,
                        'httponly' => true,
                        ]
                    ); // token をCookieにセット
                    return;
                }
            }
            // JSON 取得失敗、認証に失敗した場合は 401
            http_response_code(401);
        }
    }

    public function logout()
    {
    }
}
