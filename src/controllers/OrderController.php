<?php

namespace App\Controllers;

use App\Database;
use App\Helpers\Auth;
use App\Helpers\Response;
use App\Models\Order;
use App\Models\Cart;

class OrderController
{
    public static function createOrder()
    {
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
                Response::json(false, 'Sepet bulunamadı', null, [], 404);
            }
            $cartItems = $cartModel->items(['cart_id' => $cartData['id']]);
            if (!$cartItems || count($cartItems) == 0) {
                Response::json(false, 'Sepet boş', null, [], 400);
            }
            $orderModel = new Order($pdo);
            $result = $orderModel->createFromCart([
                'user_id' => $userId,
                'cart' => $cartData,
                'items' => $cartItems
            ]);
            if ($result['success']) {
                Response::json(true, 'Sipariş oluşturuldu', ['order_id' => $result['order_id']], [], 201);
            } else {
                Response::json(false, 'Sipariş oluşturulamadı', null, $result['errors'], $result['code'] ?? 500);
            }
        } catch (\Exception $e) {
            Response::json(false, 'Sunucu hatası', null, [$e->getMessage()], 500);
        }
    }

    public static function getOrders()
    {
        try {
            $db = new \App\Database();
            $pdo = $db->getConnection();
            $userId = Auth::userId();
            if (!$userId) {
                Response::json(false, 'Yetkisiz', null, [], 401);
            }
            $orderModel = new Order($pdo);
            $orderList = $orderModel->findByUserId(['user_id' => $userId]);
            Response::json(true, 'Siparişler getirildi', ['orders' => $orderList]);
        } catch (\Exception $e) {
            Response::json(false, 'Sunucu hatası', null, [$e->getMessage()], 500);
        }
    }

    public static function getOrderDetail($orderId)
    {
        try {
            $db = new \App\Database();
            $pdo = $db->getConnection();
            $userId = Auth::userId();
            if (!$userId) {
                Response::json(false, 'Yetkisiz', null, [], 401);
            }
            $orderModel = new Order($pdo);
            $orderData = $orderModel->findById(['order_id' => $orderId, 'user_id' => $userId]);
            if (!$orderData) {
                Response::json(false, 'Sipariş bulunamadı', null, [], 404);
            }
            $orderItems = $orderModel->items(['order_id' => $orderId]);
            Response::json(true, 'Sipariş detayı', ['order' => $orderData, 'items' => $orderItems]);
        } catch (\Exception $e) {
            Response::json(false, 'Sunucu hatası', null, [$e->getMessage()], 500);
        }
    }
}
