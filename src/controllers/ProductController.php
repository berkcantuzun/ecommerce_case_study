<?php

namespace App\Controllers;

use App\Database;
use App\Helpers\Auth;
use App\Helpers\Request;
use App\Helpers\Response;
use App\Models\Category;
use App\Models\Product;

class ProductController
{


    public static function create()
    {
        try {
            $db = new \App\Database();
            $pdo = $db->getConnection();
            $userRole = Auth::userRole();
            if ($userRole != 'admin') {

                Response::json(false, 'Yetkisiz', null, [], 401);

            }
            $input = Request::input();
            $rules = [
                'name' => 'required|min:3|max:100|unique:products,name',
                'description' => 'nullable|max:255',
                'price' => 'required|numeric|min:1',
                'stock_quantity' => 'required|numeric|min:0',
                'category_id' => 'required|numeric'
            ];
            $errors = Request::validate($input, $rules, $pdo);
            if (!empty($errors)) {
                Response::json(false, 'Doğrulama hatası', null, $errors, 422);
            }
            $categoryModel = new Category($pdo);
            $category = $categoryModel->getById(['id' => $input['category_id']]);
            if (!$category) {
                Response::json(false, 'Geçersiz kategori ID', null, [], 422);
            }
            $productModel = new Product($pdo);
            $result = $productModel->create([
                'name' => $input['name'],
                'description' => $input['description'] ?? null,
                'price' => $input['price'],
                'stock_quantity' => $input['stock_quantity'],
                'category_id' => $input['category_id']
            ]);
            if (!$result) {
                Response::json(false, 'Ürün kaydedilemedi', null, [], 500);
            }
            Response::json(true, 'Ürün başarıyla kaydedildi', null, [], 201);
        } catch (\Exception $e) {
            Response::json(false, 'Sunucu hatası', null, [$e->getMessage()], 500);
        }
    }
}