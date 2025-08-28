<?php

namespace App\Models;

use PDO;

class User
{
    protected $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function create($data)
    {
        $stmt = $this->pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (:name, :email, :password, :role)");
        return $stmt->execute($data);
    }
    public function getUserById($data)
    {
        $stmt = $this->pdo->prepare("SELECT id, name, email, role FROM users WHERE id=:id");
        $stmt->execute($data);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public function updateUser($data)
    {
        $fields = [];
        if (isset($data['name'])) {
            $fields[] = 'name = :name';
        }
        if (isset($data['email'])) {
            $fields[] = 'email = :email';
        }
        if (isset($data['password'])) {
            $fields[] = 'password = :password';
        }
        if (isset($data['role'])) {
            $fields[] = 'role = :role';
        }
        if (empty($fields)) {
            return false;
        }
        $setClause = implode(', ', $fields);
        $stmt = $this->pdo->prepare("UPDATE users SET $setClause WHERE id = :id");
        return $stmt->execute($data);
    }


}
