<?php

namespace App\Helpers;
class Request
{

    public static function validate($input, $rules, $pdo = null)
    {
        $errors = [];
        foreach ($rules as $field => $rule) {
            $value = $input[$field] ?? null;
            foreach (explode('|', $rule) as $r) {
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
                    // örnek: unique:users,email
                    $parts = explode(':', $r);
                    if (count($parts) == 2) {
                        list($table, $col) = explode(',', $parts[1]);
                        $stmt = $pdo->prepare("SELECT COUNT(*) FROM $table WHERE $col = :value");
                        $stmt->execute(['value' => $value]);
                        if ($stmt->fetchColumn() > 0) {
                            $errors[$field][] = 'Bu email zaten kayıtlı';
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
        if (stripos($contentType, 'application/json') !== false) {
            $data = json_decode(file_get_contents('php://input'), true);
            return is_array($data) ? $data : [];
        }
        // Form-data veya query string
        return array_merge($_GET, $_POST);
    }
}