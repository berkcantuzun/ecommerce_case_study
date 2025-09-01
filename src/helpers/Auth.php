<?php
namespace App\Helpers;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Helpers\Request;
class Auth {
    protected static $secret;
    public static function getSecret() {
        if (!self::$secret) {
            self::$secret = $_ENV['JWT_SECRET'];
        }
        return self::$secret;
    }
    public static function token($user)
    {
        if (!$user || !isset($user['id'])) {
            return null;
        }
        $payload = [
            'sub' => $user['id'],
            'email' => $user['email'],
            'role' => $user['role'],
            'iat' => time(),
            'exp' => time() + 3600
        ];

        return \Firebase\JWT\JWT::encode($payload, self::getSecret(), 'HS256');
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
    public static function userRole(){
        $authHeader = Request::header('Authorization');
        if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {

            return null;
        }
        $token = $matches[1];
        try {
            $decoded = JWT::decode($token, new Key(self::$secret, 'HS256'));
            return $decoded->role ?? null;
        } catch (\Exception $e) {

            return null;
        }
    }
}

