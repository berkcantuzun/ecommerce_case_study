<?php
namespace App\Models;
use App\Database;

class Cart {
    protected $pdo;
    public function __construct($pdo = null) {
        $this->pdo = $pdo ?: (new Database())->getConnection();
    }
    public function findByUserId($params) {
        $stmt = $this->pdo->prepare('SELECT * FROM carts WHERE user_id = :user_id');
        $stmt->execute(['user_id' => $params['user_id']]);
        return $stmt->fetch(
            \PDO::FETCH_ASSOC
        );
    }
    public function items($params) {
        $stmt = $this->pdo->prepare('SELECT ci.*, p.name, p.price FROM cart_items ci JOIN products p ON ci.product_id = p.id WHERE ci.cart_id = :cart_id');
        $stmt->execute(['cart_id' => $params['cart_id']]);
        return $stmt->fetchAll(
            \PDO::FETCH_ASSOC
        );
    }
    public function addItem($params) {
        $cartData = $this->findByUserId(['user_id' => $params['user_id']]);
        if (!$cartData) {
            $this->pdo->prepare('INSERT INTO carts (user_id, created_at, updated_at) VALUES (:user_id, NOW(), NOW())')->execute(['user_id' => $params['user_id']]);
            $cartId = $this->pdo->lastInsertId('carts_id_seq');
        } else {
            $cartId = $cartData['id'];
        }
        $product = $this->pdo->prepare('SELECT stock_quantity FROM products WHERE id = :id');
        $product->execute(['id' => $params['product_id']]);
        $prod = $product->fetch(
            \PDO::FETCH_ASSOC
        );
        if (!$prod || $prod['stock_quantity'] < $params['quantity']) {
            return ['success' => false, 'error' => 'Yetersiz stok'];
        }
        $item = $this->pdo->prepare('SELECT * FROM cart_items WHERE cart_id = :cart_id AND product_id = :product_id');
        $item->execute(['cart_id' => $cartId, 'product_id' => $params['product_id']]);
        $existing = $item->fetch(
            \PDO::FETCH_ASSOC
        );
        if ($existing) {
            $this->pdo->prepare('UPDATE cart_items SET quantity = quantity + :quantity, updated_at = NOW() WHERE id = :id')
                ->execute(['quantity' => $params['quantity'], 'id' => $existing['id']]);
        } else {
            $this->pdo->prepare('INSERT INTO cart_items (cart_id, product_id, quantity, created_at, updated_at) VALUES (:cart_id, :product_id, :quantity, NOW(), NOW())')
                ->execute(['cart_id' => $cartId, 'product_id' => $params['product_id'], 'quantity' => $params['quantity']]);
        }
        return ['success' => true];
    }
    public function updateItem($params) {
        $cartData = $this->findByUserId(['user_id' => $params['user_id']]);
        if (!$cartData) {
            return ['success' => false, 'error' => 'Sepet bulunamadı'];
        }
        $item = $this->pdo->prepare('SELECT * FROM cart_items WHERE cart_id = :cart_id AND product_id = :product_id');
        $item->execute(['cart_id' => $cartData['id'], 'product_id' => $params['product_id']]);
        $existing = $item->fetch(
            \PDO::FETCH_ASSOC
        );
        if (!$existing) {
            return ['success' => false, 'error' => 'Ürün sepette yok'];
        }
        $product = $this->pdo->prepare('SELECT stock_quantity FROM products WHERE id = :id');
        $product->execute(['id' => $params['product_id']]);
        $prod = $product->fetch(
            \PDO::FETCH_ASSOC
        );
        if (!$prod || $prod['stock_quantity'] < $params['quantity']) {
            return ['success' => false, 'error' => 'Yetersiz stok'];
        }
        $this->pdo->prepare('UPDATE cart_items SET quantity = :quantity, updated_at = NOW() WHERE id = :id')
            ->execute(['quantity' => $params['quantity'], 'id' => $existing['id']]);
        return ['success' => true];
    }
    public function removeItem($params) {
        $cartData = $this->findByUserId(['user_id' => $params['user_id']]);
        if (!$cartData) {
            return ['success' => false, 'error' => 'Sepet bulunamadı'];
        }
        $this->pdo->prepare('DELETE FROM cart_items WHERE cart_id = :cart_id AND product_id = :product_id')
            ->execute(['cart_id' => $cartData['id'], 'product_id' => $params['product_id']]);
        return ['success' => true];
    }
    public function clear($params) {
        $cartData = $this->findByUserId(['user_id' => $params['user_id']]);
        if (!$cartData) {
            return ['success' => false, 'error' => 'Sepet bulunamadı'];
        }
        $this->pdo->prepare('DELETE FROM cart_items WHERE cart_id = :cart_id')->execute(['cart_id' => $cartData['id']]);
        return ['success' => true];
    }
}
