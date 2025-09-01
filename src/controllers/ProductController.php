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

    public static function list(){
        try {
            $db = new Database();
            $pdo = $db->getConnection();
            $userId = Auth::userId();
            if (!$userId) {
                Response::json(false, 'Yetkisiz', null, [], 401);
                return;
            }
            $input = Request::input();
            $page = isset($input['page']) && is_numeric($input['page']) && $input['page'] > 0 ? (int)$input['page'] : 1;
            $limit = isset($input['limit']) && is_numeric($input['limit']) && $input['limit'] > 0 ? (int)$input['limit'] : 10;
            $offset = ($page - 1) * $limit;

            $filters = [];
            if (isset($input['name']) && is_string($input['name'])) {
                $filters['name'] = trim($input['name']);
            }
            if (isset($input['category_id']) && is_numeric($input['category_id'])) {
                $filters['category_id'] = (int)$input['category_id'];
            }
            if (isset($input['min_price']) && is_numeric($input['min_price'])) {
                $filters['min_price'] = (float)$input['min_price'];
            }
            if (isset($input['max_price']) && is_numeric($input['max_price'])) {
                $filters['max_price'] = (float)$input['max_price'];
            }

            $productModel = new Product($pdo);
            $products = $productModel->getAll($filters, $limit, $offset);
            if (!$products) {
                Response::json(false, 'Ürünler bulunamadı', null, [], 404);
                return;
            }
            Response::json(true, 'Ürünler bulundu', [
                'page' => $page,
                'limit' => $limit,
                'products' => $products
            ], [], 200);

        }catch (\Exception $e){
            Response::json(false, 'Sunucu hatası', null, [$e->getMessage()], 500);
        }
    }
    public static function detail($id)
    {
        try {
            $db = new Database();
            $pdo = $db->getConnection();
            $userId = Auth::userId();
            if (!$userId) {
                Response::json(false, 'Yetkisiz', null, [], 401);
                return;
            }
            $productModel = new Product($pdo);
            $product = $productModel->getById(['id' => $id]);
            if (!$product) {
                Response::json(false, 'Ürün bulunamadı', null, [], 404);
                return;
            }
            Response::json(true, 'Ürün Bulundu', $product, [], 200);
        } catch (\Exception $e) {
            Response::json(false, 'Sunucu hatası', null, [$e->getMessage()], 500);
        }
    }

    public static function productUpdate($id)
    {
        try {
            $db = new Database();
            $pdo = $db->getConnection();
            $userRole = Auth::userRole();
            if ($userRole != 'admin') {
                Response::json(false, 'Yetkisiz', null, [], 401);
                return;
            }
            $input = Request::input();
            $rules = [
                'name' => 'nullable|min:3|max:100|unique:products,name',
                'description' => 'nullable|max:255',
                'price' => 'nullable|numeric|min:1',
                'stock_quantity' => 'nullable|numeric|min:0',
                'category_id' => 'nullable|numeric'
            ];
            $errors = Request::validate($input, $rules, $pdo);
            if (!empty($errors)) {
                Response::json(false, 'Doğrulama hatası', null, $errors, 422);
                return;
            }
            if (isset($input['category_id'])) {
                $categoryModel = new Category($pdo);
                $category = $categoryModel->getById(['id' => $input['category_id']]);
                if (!$category) {
                    Response::json(false, 'Geçersiz kategori ID', null, [], 422);
                    return;
                }
            }
            $productModel = new Product($pdo);
            $existingProduct = $productModel->getById(['id' => $id]);
            if (!$existingProduct) {
                Response::json(false, 'Ürün bulunamadı', null, [], 404);
                return;
            }
            $updateData = [
                'id' => $id,
                'name' => $input['name'] ?? $existingProduct['name'],
                'description' => array_key_exists('description', $input) ? $input['description'] : $existingProduct['description'],
                'price' => $input['price'] ?? $existingProduct['price'],
                'stock_quantity' => $input['stock_quantity'] ?? $existingProduct['stock_quantity'],
                'category_id' => $input['category_id'] ?? $existingProduct['category_id']
            ];
            $result = $productModel->update($updateData);
            if (!$result) {
                Response::json(false, 'Ürün güncellenemedi', null, [], 500);
                return;
            }
            Response::json(true, 'Ürün başarıyla güncellendi', null, [], 200);
        } catch (\Exception $e) {
            Response::json(false, 'Sunucu hatası', null, [$e->getMessage()], 500);
        }
    }

    public static function productDelete($id)
    {
        try {
            $db = new Database();
            $pdo = $db->getConnection();
            $userRole = Auth::userRole();
            if ($userRole != 'admin') {
                Response::json(false, 'Yetkisiz', null, [], 401);
                return;
            }
            $productModel = new Product($pdo);
            $existingProduct = $productModel->getById(['id' => $id]);
            if (!$existingProduct) {
                Response::json(false, 'Ürün bulunamadı', null, [], 404);
                return;
            }
            $result = $productModel->delete(['id' => $id]);
            if (!$result) {
                Response::json(false, 'Ürün silinemedi', null, [], 500);
                return;
            }
            Response::json(true, 'Ürün başarıyla silindi', null, [], 200);
        } catch (\Exception $e) {
            Response::json(false, 'Sunucu hatası', null, [$e->getMessage()], 500);
        }
    }
}