<?php

namespace App;


use App\Controllers\AuthController;
use App\Controllers\CategoryController;
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
            // ProductController::list();
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


        else {
            Response::json(false, 'Endpoint bulunamadı', null, [], 404);
        }
    }
}
