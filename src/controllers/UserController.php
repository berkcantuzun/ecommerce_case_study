<?php

namespace App\Controllers;

use App\Database;
use App\Helpers\Request;
use App\Helpers\Response;

class UserController
{
    public static function register()
    {
        try {
            $db = new Database();
            $pdo = $db->getConnection();
            $input = Request::input();
            $rules = [
                'name' => 'required|min:2|max:100',
                'email' => 'required|email|unique:users,email|max:150',
                'password' => 'required|min:8|max:255',
                'role' => 'in:user,admin'
            ];
            $errors = Request::validate($input, $rules,$pdo);
            if (!empty($errors)) {
                Response::json(false, 'Doğrulama hatası', null, $errors, 422);
            }
            $hashedPassword = password_hash($input['password'], PASSWORD_BCRYPT);

            $userModel = new \App\Models\User($pdo);
            $result = $userModel->create([
                'name' => $input['name'],
                'email' => $input['email'],
                'password' => $hashedPassword,
                'role' => $input['role'] ?? 'user'
            ]);
            if (!$result) {
                Response::json(false, 'Kullanıcı kaydedilemedi', null, [], 500);
            }
            Response::json(true, 'Kullanıcı başarıyla kaydedildi', null, [], 201);


        } catch (\Exception $e) {
            Response::json(false, 'Sunucu hatası', null, [$e->getMessage()], 500);
        }

    }
}