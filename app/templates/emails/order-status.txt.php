<?php
/**
 * Plain Text Email Template: Order Status Notification
 * NNACOSA — E-Commerce
 */

$statusLabels = [
    'pending' => 'Menunggu Pembayaran',
    'processing' => 'Sedang Diproses',
    'shipped' => 'Telah Dikirim',
    'completed' => 'Selesai',
    'cancelled' => 'Dibatalkan'
];

$statusLabel = $statusLabels[strtolower($newStatus)] ?? strtoupper($newStatus);
$orderCode = '#ORD-' . str_pad($order['id'], 5, '0', STR_PAD_LEFT);

$statusMessages = [
    'processing' => 'Pesanan Anda di NNACOSA sedang kami proses.',
    'shipped' => 'Kabar baik! Pesanan Anda telah dikirim.',
    'completed' => 'Pesanan Anda telah selesai dan berhasil diterima.',
    'cancelled' => 'Pesanan Anda telah dibatalkan.',
    'pending' => 'Pesanan Anda telah dibuat dan sedang menunggu konfirmasi pembayaran.'
];

$customMessage = $statusMessages[strtolower($newStatus)] ?? 'Status pesanan Anda telah diperbarui menjadi ' . $statusLabel . '.';
?>
============================================================
<?= strtoupper($brandName); ?> — NOTIFIKASI STATUS PESANAN
============================================================

Halo <?= $recipientName ?: 'Pelanggan'; ?>,

<?= $customMessage; ?>


RINCIAN PESANAN:
------------------------------------------------------------
Nomor Pesanan    : <?= $orderCode; ?>

Tanggal Pesanan  : <?= date('d M Y, H:i', strtotime($order['created_at'])); ?> WIB
Status Terbaru   : <?= $statusLabel; ?> (<?= strtoupper($newStatus); ?>)
<?php if (!empty($oldStatus) && $oldStatus !== $newStatus): ?>
Status Sebelumnya: <?= strtoupper($oldStatus); ?>

<?php endif; ?>
<?php if (!empty($shipment['courier'])): ?>
Kurir            : <?= $shipment['courier']; ?>

<?php endif; ?>
<?php if (!empty($shipment['tracking_number'])): ?>
Nomor Resi       : <?= $shipment['tracking_number']; ?>

<?php endif; ?>

<?php if (!empty($items)): ?>
ITEM PESANAN:
------------------------------------------------------------
<?php foreach ($items as $item): ?>
* <?= $item['name']; ?> (x<?= $item['quantity']; ?>) — Rp <?= number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?>

<?php endforeach; ?>
------------------------------------------------------------
TOTAL PEMBAYARAN : Rp <?= number_format($order['total_amount'], 0, ',', '.'); ?>

<?php endif; ?>

<?php if (!empty($order['shipping_address'])): ?>
ALAMAT PENGIRIMAN:
<?= $order['shipping_address']; ?>


<?php endif; ?>
Anda dapat membuka riwayat pembelian untuk melihat informasi pesanan terbaru:
<?= $orderUrl; ?>


Terima kasih telah berbelanja di <?= $brandName; ?>.

------------------------------------------------------------
Email ini dikirim secara otomatis oleh sistem <?= $brandName; ?>.
(c) <?= date('Y'); ?> <?= $brandName; ?>. Seluruh hak cipta dilindungi.
