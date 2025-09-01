<?php

namespace App;


use App\Controllers\AuthController;
use App\Controllers\CartController;
use App\Controllers\CategoryController;
use App\Controllers\OrderController;
use App\Controllers\ProductController;
use App\Controllers\UserController;
use App\Helpers\Response;


class Router
{
    public function handleRequest()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = $_SERVER['REQUEST_URI'];
        header('Content-Type: application/json');

        /* User Management */
        if ($uri === '/api/register' && $method === 'POST') {
            UserController::register();
        } else if ($uri === '/api/login' && $method === 'POST') {
            AuthController::login();
        } else if ($uri === '/api/profile' && $method === 'GET') {
            UserController::profile();
        } else if ($uri === '/api/profile' && $method === 'PUT') {
            UserController::profileUpdate();
        }
        /* End User Management */

        /* Category Management */
        else if ($uri === '/api/categories' && $method === 'GET') {
            CategoryController::list();
        } else if ($uri === '/api/categories' && $method === 'POST') {
            CategoryController::create();
        } else if (preg_match('#^/api/categories/(\d+)$#', $uri, $matches) && $method === 'PUT') {
            CategoryController::categoryUpdate($matches[1]);
        } else if (preg_match('#^/api/categories/(\d+)$#', $uri, $matches) && $method === 'DELETE') {
            CategoryController::categoryDelete($matches[1]);
        }
        /* End Category Management */

        /*  Product Management */
        else if ($uri === '/api/products' && $method === 'GET') {
            ProductController::list();
        } else if (preg_match('#^/api/products/(\d+)$#', $uri, $matches) && $method === 'GET') {
            ProductController::detail($matches[1]);
        } else if ($uri === '/api/products' && $method === 'POST') {
            ProductController::create();
        } else if (preg_match('#^/api/products/(\d+)$#', $uri, $matches) && $method === 'PUT') {
            ProductController::productUpdate($matches[1]);
        } else if (preg_match('#^/api/products/(\d+)$#', $uri, $matches) && $method === 'DELETE') {
            ProductController::productDelete($matches[1]);
        }
        /* End Product Management */

        /* Cart Management */
        elseif ($uri === '/api/cart' && $method === 'GET') {
            CartController::getCart();
        } else if ($uri === '/api/cart/add' && $method === 'POST') {
            CartController::addToCart();
        } else if ($uri === '/api/cart/update' && $method === 'PUT') {
            CartController::updateCartItem();
        } else if (preg_match('#^/api/cart/remove/(\d+)$#', $uri, $matches) && $method === 'DELETE') {
            CartController::removeCartItem($matches[1]);
        } else if ($uri === '/api/cart/clear' && $method === 'DELETE') {
            CartController::clearCart();
        }
        /* End Cart Management */

        /* Order Management */

        else if ($uri === '/api/orders' && $method === 'POST') {
            OrderController::createOrder();
        } else if ($uri === '/api/orders' && $method === 'GET') {
            OrderController::getOrders();
        } else if (preg_match('#^/api/orders/(\d+)$#', $uri, $matches) && $method === 'GET') {
            OrderController::getOrderDetail($matches[1]);
        }
        /* End Order Management */


        else {
            Response::json(false, 'Endpoint bulunamadı', null, [], 404);
        }
    }
}
