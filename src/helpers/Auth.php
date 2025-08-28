<?php
namespace App\Helpers;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Helpers\Request;
class Auth {
    protected static $secret ='AIzaSyCsh3Z4BE_tGzMHFVZvWwmXTQ0jvuE83cI';
    public static function token($user)
    {
        if (!$user || !isset($user['id'])) {
            return null;
        }
        $payload = [
            'sub' => $user['id'],
            'email' => $user['email'],
            'iat' => time(),
            'exp' => time() + 3600
        ];

        return \Firebase\JWT\JWT::encode($payload, self::$secret, 'HS256');
    }
    public static function userId()
    {
        $authHeader = Request::header('Authorization');
        if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            return null;
        }
        $token = $matches[1];
        try {
            $decoded = JWT::decode($token, new Key(self::$secret, 'HS256'));
            return $decoded->sub ?? null;
        } catch (\Exception $e) {
            return null;
        }
    }
}

