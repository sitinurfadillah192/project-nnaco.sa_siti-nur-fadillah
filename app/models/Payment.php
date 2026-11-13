<?php
class Payment extends Model {
    protected $table = 'payments';

    public function savePayment($order_id, $payment_method, $payment_proof) {
        $this->db->query("INSERT INTO payments (order_id, payment_method, payment_proof, status) VALUES (:order_id, :payment_method, :payment_proof, 'pending')");
        $this->db->bind('order_id', $order_id);
        $this->db->bind('payment_method', $payment_method);
        $this->db->bind('payment_proof', $payment_proof);
        $this->db->execute();

        // Update status order to processing (or paid)
        $this->db->query("UPDATE orders SET status = 'processing' WHERE id = :order_id");
        $this->db->bind('order_id', $order_id);
        $this->db->execute();
        
        return $this->db->rowCount();
    }
}
