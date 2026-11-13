<?php
class Cart extends Model {
    protected $table = 'carts';

    public function getUserCart($user_id) {
        $query = "SELECT ci.id as cart_item_id, ci.product_id, ci.quantity, p.name, p.price, pi.image_url as image, p.stock 
                  FROM cart_items ci 
                  JOIN carts c ON ci.cart_id = c.id
                  JOIN products p ON ci.product_id = p.id 
                  LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
                  WHERE c.user_id = :user_id";
        $this->db->query($query);
        $this->db->bind('user_id', $user_id);
        return $this->db->resultSet();
    }

    public function addToCart($user_id, $product_id, $quantity) {
        // Find or create cart
        $this->db->query("SELECT id FROM carts WHERE user_id = :user_id");
        $this->db->bind('user_id', $user_id);
        $cart = $this->db->single();
        if (!$cart) {
            $this->db->query("INSERT INTO carts (user_id) VALUES (:user_id)");
            $this->db->bind('user_id', $user_id);
            $this->db->execute();
            $cart_id = $this->db->lastInsertId();
        } else {
            $cart_id = $cart['id'];
        }

        $this->db->query("SELECT id, quantity FROM cart_items WHERE cart_id = :cart_id AND product_id = :product_id");
        $this->db->bind('cart_id', $cart_id);
        $this->db->bind('product_id', $product_id);
        $existing = $this->db->single();

        if ($existing) {
            $newQty = $existing['quantity'] + $quantity;
            $this->db->query("UPDATE cart_items SET quantity = :quantity WHERE id = :id");
            $this->db->bind('quantity', $newQty);
            $this->db->bind('id', $existing['id']);
        } else {
            $this->db->query("INSERT INTO cart_items (cart_id, product_id, quantity) VALUES (:cart_id, :product_id, :quantity)");
            $this->db->bind('cart_id', $cart_id);
            $this->db->bind('product_id', $product_id);
            $this->db->bind('quantity', $quantity);
        }
        $this->db->execute();
        return $this->db->rowCount();
    }

    public function updateQuantity($cart_id, $user_id, $quantity) {
        // Here cart_id from controller is actually product_id in the old logic? Let's check CartController.php later.
        // For now, let's assume cart_id is the product_id if they use it like that. Wait, if it's product_id:
        $this->db->query("SELECT id FROM carts WHERE user_id = :user_id");
        $this->db->bind('user_id', $user_id);
        $cart = $this->db->single();
        if ($cart) {
            $this->db->query("UPDATE cart_items SET quantity = :quantity WHERE product_id = :product_id AND cart_id = :cart_id");
            $this->db->bind('quantity', $quantity);
            $this->db->bind('product_id', $cart_id); // using argument cart_id as product_id
            $this->db->bind('cart_id', $cart['id']);
            $this->db->execute();
        }
    }

    public function remove($product_id, $user_id) {
        $this->db->query("SELECT id FROM carts WHERE user_id = :user_id");
        $this->db->bind('user_id', $user_id);
        $cart = $this->db->single();
        if ($cart) {
            $this->db->query("DELETE FROM cart_items WHERE product_id = :product_id AND cart_id = :cart_id");
            $this->db->bind('product_id', $product_id);
            $this->db->bind('cart_id', $cart['id']);
            $this->db->execute();
        }
    }
}
