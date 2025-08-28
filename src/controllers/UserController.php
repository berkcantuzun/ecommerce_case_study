<?php
namespace App\Controllers;

use App\Helpers\Response;

class UserController
{
    public static function register()
    {
        try {
            Response::json('true', 'Kayıt başarılı', null, [], 201);
        } catch (\Exception $e) {
            Response::json(false, 'Sunucu hatası', null, [$e->getMessage()], 500);
        }

    }
}