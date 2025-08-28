<?php
namespace App\Helpers;
class Response {
    public static function json($success, $message, $data = null, $errors = [], $httpCode = 200) {
        http_response_code($httpCode);
        echo json_encode([
            'success' => $success,
            'message' => $message,
            'data' => $data,
            'errors' => $errors
        ]);
    }
}
