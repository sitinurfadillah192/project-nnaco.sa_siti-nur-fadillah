<?php
class Product extends Model {
    protected $table = 'products';

    public function getProductsWithCategory($limit = 10, $offset = 0, $search = '', $category_id = null) {
        $query = "SELECT p.*, c.name as category_name, pi.image_url as image 
                  FROM " . $this->table . " p 
                  LEFT JOIN categories c ON p.category_id = c.id 
                  LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1 
                  WHERE 1=1 ";
        
        if (!empty($search)) {
            $query .= "AND (p.name LIKE :search OR c.name LIKE :search) ";
        }
        if (!empty($category_id)) {
            $query .= "AND p.category_id = :category_id ";
        }
        
        $query .= "ORDER BY p.id DESC LIMIT :limit OFFSET :offset";
        
        $this->db->query($query);
        
        if (!empty($search)) {
            $this->db->bind('search', '%' . $search . '%');
        }
        if (!empty($category_id)) {
            $this->db->bind('category_id', $category_id);
        }
        
        $this->db->bind('limit', $limit, PDO::PARAM_INT);
        $this->db->bind('offset', $offset, PDO::PARAM_INT);
        
        return $this->db->resultSet();
    }

    public function countProducts($search = '', $category_id = null) {
        $query = "SELECT COUNT(*) as total FROM " . $this->table . " p ";
        $query .= "LEFT JOIN categories c ON p.category_id = c.id WHERE 1=1 ";
        if (!empty($search)) {
            $query .= "AND (p.name LIKE :search OR c.name LIKE :search) ";
        }
        if (!empty($category_id)) {
            $query .= "AND p.category_id = :category_id ";
        }
        
        $this->db->query($query);
        if (!empty($search)) {
            $this->db->bind('search', '%' . $search . '%');
        }
        if (!empty($category_id)) {
            $this->db->bind('category_id', $category_id);
        }
        
        return $this->db->single()['total'];
    }

    public function create($data) {
        $query = "INSERT INTO " . $this->table . " (category_id, name, description, price, stock) VALUES (:category_id, :name, :description, :price, :stock)";
        $this->db->query($query);
        $this->db->bind('category_id', $data['category_id']);
        $this->db->bind('name', $data['name']);
        $this->db->bind('description', $data['description']);
        $this->db->bind('price', $data['price']);
        $this->db->bind('stock', $data['stock']);
        $this->db->execute();
        
        $product_id = $this->db->lastInsertId();
        
        if (isset($data['image_url']) && !empty($data['image_url'])) {
            $this->db->query("INSERT INTO product_images (product_id, image_url, is_primary) VALUES (:product_id, :image_url, 1)");
            $this->db->bind('product_id', $product_id);
            $this->db->bind('image_url', $data['image_url']);
            $this->db->execute();
        }
        
        return $product_id;
    }

    public function updateProduct($id, $data) {
        $query = "UPDATE " . $this->table . " SET category_id = :category_id, name = :name, description = :description, price = :price, stock = :stock WHERE id = :id";
        
        $this->db->query($query);
        $this->db->bind('id', $id);
        $this->db->bind('category_id', $data['category_id']);
        $this->db->bind('name', $data['name']);
        $this->db->bind('description', $data['description']);
        $this->db->bind('price', $data['price']);
        $this->db->bind('stock', $data['stock']);
        $this->db->execute();
        
        if (isset($data['image_url']) && !empty($data['image_url'])) {
            // Check if image exists
            $this->db->query("SELECT id FROM product_images WHERE product_id = :product_id AND is_primary = 1");
            $this->db->bind('product_id', $id);
            if ($this->db->single()) {
                $this->db->query("UPDATE product_images SET image_url = :image_url WHERE product_id = :product_id AND is_primary = 1");
            } else {
                $this->db->query("INSERT INTO product_images (product_id, image_url, is_primary) VALUES (:product_id, :image_url, 1)");
            }
            $this->db->bind('product_id', $id);
            $this->db->bind('image_url', $data['image_url']);
            $this->db->execute();
        }
        return 1;
    }

    public function getProductDetail($id) {
        $this->db->query("
            SELECT p.*, c.name as category_name, pi.image_url as image 
            FROM " . $this->table . " p 
            LEFT JOIN categories c ON p.category_id = c.id 
            LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1 
            WHERE p.id = :id
            LIMIT 1
        ");
        $this->db->bind(':id', $id);
        return $this->db->single();
    }
}
