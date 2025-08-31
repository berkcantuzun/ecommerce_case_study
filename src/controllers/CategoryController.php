<?php

namespace App\Controllers;

use App\Database;
use App\Helpers\Auth;
use App\Helpers\Request;
use App\Helpers\Response;
use App\Models\Category;

class CategoryController{
    public static function create(){
        try {
            $db = new \App\Database();
            $pdo = $db->getConnection();
            $userRole = Auth::userRole();
            if ($userRole != 'admin') {

                Response::json(false, 'Yetkisiz', null, [], 401);

            }
            $input = Request::input();
            $rules = [
                'name' => 'required|min:3|max:100|unique:categories,name',
                'description' => 'nullable|max:255'
            ];
            $errors = Request::validate($input, $rules, $pdo);
            if (!empty($errors)) {
                Response::json(false, 'Doğrulama hatası', null, $errors, 422);
            }
            $categoryModel = new Category($pdo);
            $result = $categoryModel->create([
                'name' => $input['name'],
                'description' => $input['description'] ?? null
            ]);
            if (!$result) {
                Response::json(false, 'Kategori kaydedilemedi', null, [], 500);
            }
            Response::json(true, 'Kategori başarıyla kaydedildi', null, [], 201);



        }catch (\Exception $e){
            Response::json(false, 'Sunucu hatası', null, [$e->getMessage()], 500);
        }
    }
}