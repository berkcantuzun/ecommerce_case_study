<?php
namespace App\Helpers;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Auth {
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
        $secret = 'AIzaSyCsh3Z4BE_tGzMHFVZvWwmXTQ0jvuE83cI';
        return \Firebase\JWT\JWT::encode($payload, $secret, 'HS256');
    }
}
