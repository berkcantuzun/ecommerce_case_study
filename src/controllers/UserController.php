<?php

namespace App\Controllers;

use App\Database;
use App\Helpers\Auth;
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
            $errors = Request::validate($input, $rules, $pdo);
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

    public static function profile()
    {
        try {
            $db = new \App\Database();
            $pdo = $db->getConnection();
            $userId = Auth::userId();
            if (!$userId) {
                Response::json(false, 'Yetkisiz', null, [], 401);
                return;
            }
            $userModel = new \App\Models\User($pdo);
            $user = $userModel->getUserById(['id' => $userId]);
            if (!$user) {
                Response::json(false, 'Kullanıcı bulunamadı', null, [], 404);
                return;
            }
            Response::json(true, 'Profil bilgisi', $user, [], 200);
        } catch (\Exception $e) {
            Response::json(false, 'Sunucu hatası', null, [$e->getMessage()], 500);
        }
    }

    public static function profileUpdate()
    {
        try {
            $db = new \App\Database();
            $pdo = $db->getConnection();
            $userId = Auth::userId();
            if (!$userId) {
                Response::json(false, 'Yetkisiz', null, [], 401);
            }
            $input = Request::input();
            $rules = [
                'name' => 'nullable|min:2|max:100',
                'email' => 'nullable|email|unique:users,email|max:150',
                'password' => 'nullable|min:8|max:255',
                'role' => 'in:user,admin'
            ];
            $errors = Request::validate($input, $rules, $pdo);
            if (!empty($errors)) {
                Response::json(false, 'Doğrulama hatası', null, $errors, 422);
            }
            $userModel = new \App\Models\User($pdo);
            $data = ['id' => $userId];
            if (isset($input['name'])) {
                $data['name'] = $input['name'];
            }
            if (isset($input['email'])) {
                $data['email'] = $input['email'];
            }
            if (!empty($input['password'])) {
                $data['password'] = password_hash($input['password'], PASSWORD_BCRYPT);
            }
            if (isset($input['role'])) {
                $data['role'] = $input['role'];
            }

            $result = $userModel->updateUser($data);
            if (!$result) {
                Response::json(false, 'Kullanıcı güncellenemedi', $input, [], 500);
            }
            Response::json(true, 'Kullanıcı başarıyla güncellendi', null, [], 200);

        }catch (\Exception $e) {
            Response::json(false, 'Sunucu hatası', null, [$e->getMessage()], 500);
        }

    }
}