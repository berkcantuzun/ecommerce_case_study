<?php

namespace App\Models;

use PDO;

class Product
{
    protected $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function create($data)
    {

        $stmt = $this->pdo->prepare("INSERT INTO products (name, description, price, stock_quantity, category_id) VALUES (:name, :description, :price, :stock_quantity, :category_id)");
        return $stmt->execute($data);
    }
    public function getById($data)
    {
        $stmt = $this->pdo->prepare("SELECT id, name, description, price, stock_quantity, category_id, created_at, updated_at FROM products WHERE id=:id");
        $stmt->execute($data);
        return $stmt->fetch(PDO::FETCH_ASSOC);

    }
}