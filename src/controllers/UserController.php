<?php

namespace App\Controllers;

use App\Helpers\Request;
use App\Helpers\Response;

class UserController
{
    public static function register()
    {
        try {
            $input = Request::input();
            $rules = [
                'name' => 'required|min:2|max:100',
                'email' => 'required|email|unique:users,email|max:150',
                'password' => 'required|min:8|max:255',
            ];
            $errors = Request::validate($input, $rules);
            if (!empty($errors)) {
                Response::json(false, 'Doğrulama hatası', null, $errors, 422);
            }
            // Kayıt işlemi




        } catch (\Exception $e) {
            Response::json(false, 'Sunucu hatası', null, [$e->getMessage()], 500);
        }

    }
}