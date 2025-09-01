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
    public function update($data)
    {
        $fields = [];
        if (isset($data['name'])) {
            $fields[] = 'name = :name';
        }
        if (isset($data['description'])) {
            $fields[] = 'description = :description';
        }
        if (isset($data['price'])) {
            $fields[] = 'price = :price';
        }
        if (isset($data['stock_quantity'])) {
            $fields[] = 'stock_quantity = :stock_quantity';
        }
        if (isset($data['category_id'])) {
            $fields[] = 'category_id = :category_id';
        }
        if (empty($fields)) {
            return false;
        }
        $setClause = implode(', ', $fields);
        $stmt = $this->pdo->prepare("UPDATE products SET $setClause WHERE id = :id");
        return $stmt->execute($data);

    }
    public function delete($data){
        $stmt = $this->pdo->prepare("DELETE FROM products WHERE id = :id");
        return $stmt->execute($data);
    }
}