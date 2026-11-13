<?php
class Order extends Model {
    protected $table = 'orders';

    public function createOrder($user_id, $shipping_address, $cartItems, $totalAmount, $phone = null) {
        try {
            $this->db->beginTransaction();

            $this->db->query("INSERT INTO orders (user_id, phone, total_amount, status, shipping_address) VALUES (:user_id, :phone, :total_amount, 'pending', :shipping_address)");
            $this->db->bind('user_id', $user_id);
            $this->db->bind('phone', $phone);
            $this->db->bind('total_amount', $totalAmount);
            $this->db->bind('shipping_address', $shipping_address);
            $this->db->execute();
            
            $order_id = $this->db->lastInsertId();

            if (!empty($phone)) {
                $this->db->query("UPDATE users SET phone = :phone, is_phone_verified = 1 WHERE id = :user_id AND (phone IS NULL OR phone = '')");
                $this->db->bind('phone', $phone);
                $this->db->bind('user_id', $user_id);
                $this->db->execute();
            }

            foreach ($cartItems as $item) {
                $this->db->query("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (:order_id, :product_id, :quantity, :price)");
                $this->db->bind('order_id', $order_id);
                $this->db->bind('product_id', $item['product_id']);
                $this->db->bind('quantity', $item['quantity']);
                $this->db->bind('price', $item['price']);
                $this->db->execute();

                $this->db->query("UPDATE products SET stock = stock - :qty WHERE id = :product_id");
                $this->db->bind('qty', $item['quantity']);
                $this->db->bind('product_id', $item['product_id']);
                $this->db->execute();
            }

            // Get cart_id for this user
            $this->db->query("SELECT id FROM carts WHERE user_id = :user_id");
            $this->db->bind('user_id', $user_id);
            $cart = $this->db->single();
            if ($cart) {
                $this->db->query("DELETE FROM cart_items WHERE cart_id = :cart_id");
                $this->db->bind('cart_id', $cart['id']);
                $this->db->execute();
            }

            $this->db->commit();
            return $order_id;
            
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    public function getOrdersByUser($user_id) {
        $this->db->query("SELECT * FROM orders WHERE user_id = :user_id ORDER BY created_at DESC");
        $this->db->bind('user_id', $user_id);
        return $this->db->resultSet();
    }

    public function getOrderDetails($order_id, $user_id = null) {
        if ($user_id) {
            $this->db->query("SELECT * FROM orders WHERE id = :id AND user_id = :user_id");
            $this->db->bind('id', $order_id);
            $this->db->bind('user_id', $user_id);
            $order = $this->db->single();
            if (!$order) return false;
        } else {
            $this->db->query("SELECT * FROM orders WHERE id = :id");
            $this->db->bind('id', $order_id);
            $order = $this->db->single();
        }

        $this->db->query("SELECT od.*, p.name FROM order_items od JOIN products p ON od.product_id = p.id WHERE od.order_id = :order_id");
        $this->db->bind('order_id', $order_id);
        $items = $this->db->resultSet();

        $this->db->query("SELECT * FROM payments WHERE order_id = :order_id ORDER BY id DESC LIMIT 1");
        $this->db->bind('order_id', $order_id);
        $payment = $this->db->single();

        return ['order' => $order, 'items' => $items, 'payment' => $payment];
    }
}
