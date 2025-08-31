<?php

namespace App\Models;

use PDO;

class Category
{
    protected $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function create($data)
    {
        $stmt = $this->pdo->prepare("INSERT INTO categories (name, description) VALUES (:name, :description)");
        return $stmt->execute($data);
    }
    public function getAll()
    {
        $stmt = $this->pdo->query("SELECT id, name, description,created_at,updated_at FROM categories");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}