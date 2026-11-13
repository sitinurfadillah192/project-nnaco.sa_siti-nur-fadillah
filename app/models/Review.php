<?php

class Review extends Model {
    protected $table = 'reviews';

    /**
     * Simpan atau perbarui ulasan pelanggan untuk suatu produk dalam pesanan tertentu
     */
    public function saveReview($userId, $productId, $orderId, $rating, $comment = '') {
        $rating = (int)$rating;
        if ($rating < 1) $rating = 1;
        if ($rating > 5) $rating = 5;
        $comment = trim((string)$comment);

        // Verifikasi kepemilikan pesanan dan apakah produk benar-benar ada di pesanan tersebut
        $this->db->query("
            SELECT o.id, o.status, o.user_id 
            FROM orders o
            JOIN order_items oi ON o.id = oi.order_id
            WHERE o.id = :order_id AND o.user_id = :user_id AND oi.product_id = :product_id
            LIMIT 1
        ");
        $this->db->bind(':order_id', $orderId);
        $this->db->bind(':user_id', $userId);
        $this->db->bind(':product_id', $productId);
        $order = $this->db->single();

        if (!$order) {
            return [
                'success' => false, 
                'message' => 'Anda hanya dapat mengulas produk dari pesanan yang pernah Anda lakukan.'
            ];
        }

        // Cek apakah ulasan sudah pernah dibuat sebelumnya
        $existing = $this->getReviewByOrderProduct($userId, $productId, $orderId);

        if ($existing) {
            // Update ulasan yang sudah ada
            $this->db->query("
                UPDATE reviews 
                SET rating = :rating, comment = :comment, created_at = NOW() 
                WHERE id = :id AND user_id = :user_id
            ");
            $this->db->bind(':rating', $rating);
            $this->db->bind(':comment', $comment);
            $this->db->bind(':id', $existing['id']);
            $this->db->bind(':user_id', $userId);
            $this->db->execute();

            return [
                'success' => true,
                'message' => 'Ulasan Anda berhasil diperbarui!',
                'review_id' => $existing['id'],
                'action' => 'updated'
            ];
        } else {
            // Tambah ulasan baru
            $this->db->query("
                INSERT INTO reviews (user_id, product_id, order_id, rating, comment, created_at)
                VALUES (:user_id, :product_id, :order_id, :rating, :comment, NOW())
            ");
            $this->db->bind(':user_id', $userId);
            $this->db->bind(':product_id', $productId);
            $this->db->bind(':order_id', $orderId);
            $this->db->bind(':rating', $rating);
            $this->db->bind(':comment', $comment);
            $this->db->execute();

            $insertId = $this->db->lastInsertId();

            return [
                'success' => true,
                'message' => 'Terima kasih! Ulasan dan rating Anda berhasil disimpan.',
                'review_id' => $insertId,
                'action' => 'created'
            ];
        }
    }

    /**
     * Ambil ulasan spesifik berdasarkan user, produk, dan pesanan
     */
    public function getReviewByOrderProduct($userId, $productId, $orderId) {
        $this->db->query("
            SELECT * FROM reviews 
            WHERE user_id = :user_id AND product_id = :product_id AND order_id = :order_id
            LIMIT 1
        ");
        $this->db->bind(':user_id', $userId);
        $this->db->bind(':product_id', $productId);
        $this->db->bind(':order_id', $orderId);
        return $this->db->single();
    }

    /**
     * Ambil seluruh ulasan untuk satu pesanan, diindeks dengan product_id
     */
    public function getReviewsForOrder($orderId, $userId) {
        $this->db->query("
            SELECT * FROM reviews 
            WHERE order_id = :order_id AND user_id = :user_id
        ");
        $this->db->bind(':order_id', $orderId);
        $this->db->bind(':user_id', $userId);
        $rows = $this->db->resultSet();

        $indexed = [];
        if ($rows) {
            foreach ($rows as $r) {
                $indexed[$r['product_id']] = $r;
            }
        }
        return $indexed;
    }

    /**
     * Ambil daftar ulasan untuk satu produk (beserta info user)
     */
    public function getReviewsByProduct($productId, $limit = 20, $offset = 0) {
        $this->db->query("
            SELECT r.*, u.name as user_name, u.email as user_email
            FROM reviews r
            JOIN users u ON r.user_id = u.id
            WHERE r.product_id = :product_id
            ORDER BY r.created_at DESC
            LIMIT :limit OFFSET :offset
        ");
        $this->db->bind(':product_id', $productId);
        $this->db->bind(':limit', (int)$limit, PDO::PARAM_INT);
        $this->db->bind(':offset', (int)$offset, PDO::PARAM_INT);
        return $this->db->resultSet();
    }

    /**
     * Hitung ringkasan rating suatu produk (rata-rata bintang, total ulasan, dan sebaran bintang 1-5)
     */
    public function getProductRatingSummary($productId) {
        $this->db->query("
            SELECT 
                COUNT(*) as total_reviews,
                AVG(rating) as avg_rating,
                SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as count_5,
                SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as count_4,
                SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as count_3,
                SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) as count_2,
                SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as count_1
            FROM reviews
            WHERE product_id = :product_id
        ");
        $this->db->bind(':product_id', $productId);
        $row = $this->db->single();

        $total = (int)($row['total_reviews'] ?? 0);
        $avg = $total > 0 ? round((float)$row['avg_rating'], 1) : 0.0;

        return [
            'total_reviews' => $total,
            'avg_rating' => $avg,
            'counts' => [
                5 => (int)($row['count_5'] ?? 0),
                4 => (int)($row['count_4'] ?? 0),
                3 => (int)($row['count_3'] ?? 0),
                2 => (int)($row['count_2'] ?? 0),
                1 => (int)($row['count_1'] ?? 0)
            ]
        ];
    }

    /**
     * Ambil rata-rata rating untuk banyak produk sekaligus dalam satu query (untuk grid katalog)
     */
    public function getAverageRatingsForProducts(array $productIds) {
        if (empty($productIds)) return [];

        $placeholders = implode(',', array_fill(0, count($productIds), '?'));
        $this->db->query("
            SELECT product_id, AVG(rating) as avg_rating, COUNT(*) as total_reviews
            FROM reviews
            WHERE product_id IN ($placeholders)
            GROUP BY product_id
        ");
        foreach ($productIds as $idx => $pid) {
            $this->db->bind($idx + 1, (int)$pid);
        }
        $rows = $this->db->resultSet();

        $result = [];
        if ($rows) {
            foreach ($rows as $r) {
                $result[$r['product_id']] = [
                    'avg' => round((float)$r['avg_rating'], 1),
                    'total' => (int)$r['total_reviews']
                ];
            }
        }
        return $result;
    }

    /**
     * Ambil ulasan unggulan untuk ditampilkan di Beranda
     */
    public function getFeaturedReviews($limit = 6) {
        $this->db->query("
            SELECT r.*, u.name as user_name, p.name as product_name, pi.image_url as product_image
            FROM reviews r
            JOIN users u ON r.user_id = u.id
            JOIN products p ON r.product_id = p.id
            LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
            WHERE r.rating >= 4
            ORDER BY r.created_at DESC
            LIMIT :limit
        ");
        $this->db->bind(':limit', (int)$limit, PDO::PARAM_INT);
        return $this->db->resultSet();
    }

    /**
     * Ambil seluruh ulasan untuk panel Admin dengan filter
     */
    public function getAllReviewsAdmin($limit = 20, $offset = 0, $search = '', $rating = null) {
        $sql = "
            SELECT r.*, u.name as user_name, u.email as user_email, p.name as product_name, pi.image_url as product_image
            FROM reviews r
            JOIN users u ON r.user_id = u.id
            JOIN products p ON r.product_id = p.id
            LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
            WHERE 1=1
        ";
        if (!empty($search)) {
            $sql .= " AND (u.name LIKE :search OR p.name LIKE :search OR r.comment LIKE :search)";
        }
        if ($rating !== null && $rating >= 1 && $rating <= 5) {
            $sql .= " AND r.rating = :rating";
        }
        $sql .= " ORDER BY r.created_at DESC LIMIT :limit OFFSET :offset";

        $this->db->query($sql);
        if (!empty($search)) {
            $this->db->bind(':search', "%$search%");
        }
        if ($rating !== null && $rating >= 1 && $rating <= 5) {
            $this->db->bind(':rating', (int)$rating);
        }
        $this->db->bind(':limit', (int)$limit, PDO::PARAM_INT);
        $this->db->bind(':offset', (int)$offset, PDO::PARAM_INT);
        return $this->db->resultSet();
    }

    /**
     * Hitung total ulasan untuk admin pagination
     */
    public function countReviewsAdmin($search = '', $rating = null) {
        $sql = "
            SELECT COUNT(*) as total
            FROM reviews r
            JOIN users u ON r.user_id = u.id
            JOIN products p ON r.product_id = p.id
            WHERE 1=1
        ";
        if (!empty($search)) {
            $sql .= " AND (u.name LIKE :search OR p.name LIKE :search OR r.comment LIKE :search)";
        }
        if ($rating !== null && $rating >= 1 && $rating <= 5) {
            $sql .= " AND r.rating = :rating";
        }

        $this->db->query($sql);
        if (!empty($search)) {
            $this->db->bind(':search', "%$search%");
        }
        if ($rating !== null && $rating >= 1 && $rating <= 5) {
            $this->db->bind(':rating', (int)$rating);
        }
        $row = $this->db->single();
        return (int)($row['total'] ?? 0);
    }

    /**
     * Hapus ulasan
     */
    public function deleteReview($id, $userId = null, $isAdmin = false) {
        if ($isAdmin) {
            $this->db->query("DELETE FROM reviews WHERE id = :id");
            $this->db->bind(':id', $id);
        } else {
            $this->db->query("DELETE FROM reviews WHERE id = :id AND user_id = :user_id");
            $this->db->bind(':id', $id);
            $this->db->bind(':user_id', $userId);
        }
        return $this->db->execute();
    }
}
