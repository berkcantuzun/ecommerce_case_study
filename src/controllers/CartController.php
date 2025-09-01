<?php
namespace App\Controllers;

use App\Database;
use App\Helpers\Auth;
use App\Helpers\Request;
use App\Helpers\Response;
use App\Models\Cart;

class CartController {
    public static function getCart() {
        try {
            $db = new \App\Database();
            $pdo = $db->getConnection();
            $userId = Auth::userId();
            if (!$userId) {
                Response::json(false, 'Yetkisiz', null, [], 401);
            }
            $cartModel = new Cart($pdo);
            $cartData = $cartModel->findByUserId(['user_id' => $userId]);
            if (!$cartData) {
                Response::json(true, 'Sepet boş', ['items' => []]);
            }
            $cartItems = $cartModel->items(['cart_id' => $cartData['id']]);
            Response::json(true, 'Sepet getirildi', ['items' => $cartItems]);
        } catch (\Exception $e) {
            Response::json(false, 'Sunucu hatası', null, [$e->getMessage()], 500);
        }
    }

    public static function addToCart() {
        try {
            $db = new \App\Database();
            $pdo = $db->getConnection();
            $userId = Auth::userId();
            if (!$userId) {
                Response::json(false, 'Yetkisiz', null, [], 401);
            }
            $input = Request::input();
            $rules = [
                'product_id' => 'required|numeric',
                'quantity' => 'required|numeric|min:1'
            ];
            $errors = Request::validate($input, $rules, $pdo);
            if (!empty($errors)) {
                Response::json(false, 'Doğrulama hatası', null, $errors, 422);
            }
            $cartModel = new Cart($pdo);
            $result = $cartModel->addItem([
                'user_id' => $userId,
                'product_id' => $input['product_id'],
                'quantity' => $input['quantity']
            ]);
            if (!$result['success']) {
                Response::json(false, $result['error'] ?? 'Hata', null, [], 400);
            }
            Response::json(true, 'Ürün sepete eklendi', null);
        } catch (\Exception $e) {
            Response::json(false, 'Sunucu hatası', null, [$e->getMessage()], 500);
        }
    }

    public static function updateCartItem() {
        try {
            $db = new \App\Database();
            $pdo = $db->getConnection();
            $userId = Auth::userId();
            if (!$userId) {
                Response::json(false, 'Yetkisiz', null, [], 401);
            }
            $input = Request::input();
            $rules = [
                'product_id' => 'required|numeric',
                'quantity' => 'required|numeric|min:1'
            ];
            $errors = Request::validate($input, $rules, $pdo);
            if (!empty($errors)) {
                Response::json(false, 'Doğrulama hatası', null, $errors, 422);
            }
            $cartModel = new Cart($pdo);
            $result = $cartModel->updateItem([
                'user_id' => $userId,
                'product_id' => $input['product_id'],
                'quantity' => $input['quantity']
            ]);
            if (!$result['success']) {
                Response::json(false, $result['error'] ?? 'Hata', null, [], 400);
            }
            Response::json(true, 'Sepet güncellendi', null);
        } catch (\Exception $e) {
            Response::json(false, 'Sunucu hatası', null, [$e->getMessage()], 500);
        }
    }

    public static function removeCartItem($productId) {
        try {
            $db = new \App\Database();
            $pdo = $db->getConnection();
            $userId = Auth::userId();
            if (!$userId) {
                Response::json(false, 'Yetkisiz', null, [], 401);
            }
            $cartModel = new Cart($pdo);
            $result = $cartModel->removeItem([
                'user_id' => $userId,
                'product_id' => $productId
            ]);
            if (!$result['success']) {
                Response::json(false, $result['error'] ?? 'Hata', null, [], 400);
            }
            Response::json(true, 'Ürün sepetten çıkarıldı', null);
        } catch (\Exception $e) {
            Response::json(false, 'Sunucu hatası', null, [$e->getMessage()], 500);
        }
    }

    public static function clearCart() {
        try {
            $db = new \App\Database();
            $pdo = $db->getConnection();
            $userId = Auth::userId();
            if (!$userId) {
                Response::json(false, 'Yetkisiz', null, [], 401);
            }
            $cartModel = new Cart($pdo);
            $result = $cartModel->clear(['user_id' => $userId]);
            if (!$result['success']) {
                Response::json(false, $result['error'] ?? 'Hata', null, [], 400);
            }
            Response::json(true, 'Sepet temizlendi', null);
        } catch (\Exception $e) {
            Response::json(false, 'Sunucu hatası', null, [$e->getMessage()], 500);
        }
    }
}
