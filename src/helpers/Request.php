<?php

namespace App\Helpers;
class Request
{
    public static function header($key)
    {
        $headers = getallheaders();
        return $headers[$key] ?? null;
    }

    public static function validate($input, $rules, $pdo = null)
    {
        $errors = [];
        foreach ($rules as $field => $rule) {
            $value = $input[$field] ?? null;
            $ruleParts = explode('|', $rule);
            $isNullable = in_array('nullable', $ruleParts);

            if ($isNullable && (is_null($value) || $value === '')) {
                continue;
            }

            foreach ($ruleParts as $r) {
                if ($r === 'required' && (is_null($value) || $value === '')) {
                    $errors[$field][] = 'Alan zorunlu';
                }
                if (strpos($r, 'min:') === 0) {
                    $min = (int)substr($r, 4);
                    if (is_null($value) || !is_string($value) || strlen($value) < $min) {
                        $errors[$field][] = "Minimum $min karakter olmalı";
                    }
                }
                if (strpos($r, 'max:') === 0) {
                    $max = (int)substr($r, 4);
                    if (!is_null($value) && is_string($value) && strlen($value) > $max) {
                        $errors[$field][] = "Maksimum $max karakter olmalı";
                    }
                }
                if ($r === 'email' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $errors[$field][] = 'Geçerli email olmalı';
                }
                if ($r === 'numeric' && !is_numeric($value)) {
                    $errors[$field][] = 'Sayı olmalı';
                }
                if ($r === 'positive' && isset($value) && $value <= 0) {
                    $errors[$field][] = 'Pozitif olmalı';
                }
                if (strpos($r, 'unique:') === 0 && $pdo) {

                    $parts = explode(':', $r);
                    if (count($parts) == 2) {
                        list($table, $col) = explode(',', $parts[1]);
                        $stmt = $pdo->prepare("SELECT COUNT(*) FROM $table WHERE $col = :value");
                        $stmt->execute(['value' => $value]);
                        if ($stmt->fetchColumn() > 0) {
                            $errors[$field][] = 'Bu veri zaten kayıtlı';
                        }
                    }
                }
            }
        }
        return $errors;
    }

    public static function input()
    {

        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        if (stripos($contentType, 'application/json') !== false) {
            $data = json_decode(file_get_contents('php://input'), true);
            return is_array($data) ? $data : [];
        }

        if (in_array($method, ['PUT', 'PATCH'])) {
            parse_str(file_get_contents('php://input'), $data);
            return is_array($data) ? $data : [];
        }
        return array_merge($_GET, $_POST);
    }
}