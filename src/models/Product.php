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
    public function getAll($filters, $limit, $offset)
    {
        $sql = "SELECT id, name, description, price, stock_quantity, category_id, created_at, updated_at FROM products WHERE 1=1";
        $params = [];

        if (isset($filters['name'])) {
            $sql .= " AND name LIKE :name";
            $params['name'] = '%' . $filters['name'] . '%';
        }
        if (isset($filters['category_id'])) {
            $sql .= " AND category_id = :category_id";
            $params['category_id'] = $filters['category_id'];
        }
        if (isset($filters['min_price'])) {
            $sql .= " AND price >= :min_price";
            $params['min_price'] = $filters['min_price'];
        }
        if (isset($filters['max_price'])) {
            $sql .= " AND price <= :max_price";
            $params['max_price'] = $filters['max_price'];
        }

        $sql .= " ORDER BY id DESC LIMIT :limit OFFSET :offset";
        $params['limit'] = (int)$limit;
        $params['offset'] = (int)$offset;

        $stmt = $this->pdo->prepare($sql);

        foreach ($params as $key => $value) {
            if ($key === 'limit' || $key === 'offset') {
                $stmt->bindValue(':' . $key, $value, \PDO::PARAM_INT);
            } else {
                $stmt->bindValue(':' . $key, $value);
            }
        }

        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}