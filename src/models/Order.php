<?php
namespace App\Models;
use App\Database;

class Order {
    protected $pdo;
    public function __construct($pdo = null) {
        if ($pdo) {
            $this->pdo = $pdo;
        } else {
            $this->pdo = (new Database())->getConnection();
        }
    }
    public function findByUserId($params) {
        $stmt = $this->pdo->prepare('SELECT * FROM orders WHERE user_id = :user_id ORDER BY created_at DESC');
        $stmt->execute(['user_id' => $params['user_id']]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    public function findById($params) {
        $stmt = $this->pdo->prepare('SELECT * FROM orders WHERE id = :order_id AND user_id = :user_id');
        $stmt->execute(['order_id' => $params['order_id'], 'user_id' => $params['user_id']]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
    public function items($params) {
        $stmt = $this->pdo->prepare('SELECT oi.*, p.name FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = :order_id');
        $stmt->execute(['order_id' => $params['order_id']]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    public function createFromCart($params) {
        $userId = $params['user_id'];
        $cartData = $params['cart'];
        $cartItems = $params['items'];
        if (!$cartItems || count($cartItems) == 0) {
            return [
                'success' => false,
                'errors' => ['Sepet boş'],
                'code' => 400
            ];
        }
        $total = 0;
        foreach ($cartItems as $item) {
            $product = $this->pdo->prepare('SELECT stock_quantity FROM products WHERE id = :id');
            $product->execute(['id' => $item['product_id']]);
            $prod = $product->fetch(\PDO::FETCH_ASSOC);
            if (!$prod || $prod['stock_quantity'] < $item['quantity']) {
                return [
                    'success' => false,
                    'errors' => ['Stok yetersiz: ' . $item['product_id']],
                    'code' => 400
                ];
            }
            $total += $item['price'] * $item['quantity'];
        }
        $this->pdo->beginTransaction();
        try {
            $this->pdo->prepare('INSERT INTO orders (user_id, total_amount, status, created_at, updated_at) VALUES (:user_id, :total, :status, NOW(), NOW())')
                ->execute(['user_id' => $userId, 'total' => $total, 'status' => 'pending']);
            $orderId = $this->pdo->lastInsertId('orders_id_seq');
            foreach ($cartItems as $item) {
                $this->pdo->prepare('INSERT INTO order_items (order_id, product_id, quantity, price, created_at, updated_at) VALUES (:order_id, :product_id, :quantity, :price, NOW(), NOW())')
                    ->execute([
                        'order_id' => $orderId,
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'price' => $item['price']
                    ]);
                $this->pdo->prepare('UPDATE products SET stock_quantity = stock_quantity - :qty WHERE id = :id')
                    ->execute(['qty' => $item['quantity'], 'id' => $item['product_id']]);
            }
            $this->pdo->prepare('DELETE FROM cart_items WHERE cart_id = :cart_id')->execute(['cart_id' => $cartData['id']]);
            $this->pdo->commit();
            return [
                'success' => true,
                'order_id' => $orderId
            ];
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            return [
                'success' => false,
                'errors' => [$e->getMessage()],
                'code' => 500
            ];
        }
    }
}
