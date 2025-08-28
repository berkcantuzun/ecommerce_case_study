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


}
