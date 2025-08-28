<?php
namespace App;



use App\Controllers\UserController;
use App\Helpers\Response;


class Router
{
    public function handleRequest()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = $_SERVER['REQUEST_URI'];
        header('Content-Type: application/json');
        if ($uri === '/api/register' && $method === 'POST') {

            UserController::register();
        } else {
            Response::json(false, 'Endpoint bulunamadı', null, [], 404);
        }
    }
}
