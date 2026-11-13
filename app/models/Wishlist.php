<?php
class Wishlist extends Model {

    public function getUserWishlist($user_id) {
        $this->db->query("SELECT w.id as wishlist_id, p.*, pi.image_url as image 
                          FROM wishlist w 
                          JOIN products p ON w.product_id = p.id 
                          LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
                          WHERE w.user_id = :user_id 
                          ORDER BY w.created_at DESC");
        $this->db->bind('user_id', $user_id);
        return $this->db->resultSet();
    }

    public function getWishlistProductIds($user_id) {
        $this->db->query("SELECT product_id FROM wishlist WHERE user_id = :user_id");
        $this->db->bind('user_id', $user_id);
        $result = $this->db->resultSet();
        $ids = [];
        foreach($result as $r) {
            $ids[] = $r['product_id'];
        }
        return $ids;
    }

    public function check($user_id, $product_id) {
        $this->db->query("SELECT id FROM wishlist WHERE user_id = :user_id AND product_id = :product_id");
        $this->db->bind('user_id', $user_id);
        $this->db->bind('product_id', $product_id);
        return $this->db->single() ? true : false;
    }

    public function toggle($user_id, $product_id) {
        if ($this->check($user_id, $product_id)) {
            $this->db->query("DELETE FROM wishlist WHERE user_id = :user_id AND product_id = :product_id");
            $this->db->bind('user_id', $user_id);
            $this->db->bind('product_id', $product_id);
            $this->db->execute();
            return 'removed';
        } else {
            $this->db->query("INSERT INTO wishlist (user_id, product_id) VALUES (:user_id, :product_id)");
            $this->db->bind('user_id', $user_id);
            $this->db->bind('product_id', $product_id);
            $this->db->execute();
            return 'added';
        }
    }
}
