<?php

namespace App\Controllers;

use App\Helpers\Auth;
use App\Helpers\Request;
use App\Helpers\Response;

class AuthController
{
    public static function login()
    {
        try {
            $db = new \App\Database();
            $pdo = $db->getConnection();
            $input = Request::input();
            $rules = [
                'email' => 'required|email',
                'password' => 'required|min:8|max:255'
            ];
            $errors = Request::validate($input, $rules);
            if (!empty($errors)) {
                Response::json(false, 'Doğrulama hatası', null, $errors, 422);
            }
            $userModel = new \App\Models\User($pdo);
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email=:email");
            $stmt->execute(['email' => $input['email']]);
            $user = $stmt->fetch(\PDO::FETCH_ASSOC);
            if (!$user || !password_verify($input['password'], $user['password'])) {
                Response::json(false, 'Geçersiz email veya şifre', null, [], 401);
            }

            $token = Auth::token($user);
            Response::json(true, 'Giriş başarılı', ['token' => $token], [], 200);


        } catch (\Exception $e) {
            Response::json(false, 'Sunucu hatası', null, [$e->getMessage()], 500);
        }

    }
}