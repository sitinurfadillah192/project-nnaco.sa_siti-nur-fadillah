<?php
/**
 * HTML Email Template: Order Status Notification
 * NNACOSA — E-Commerce
 *
 * Variables provided:
 * - $recipientName (string)
 * - $recipientEmail (string)
 * - $order (array: id, total_amount, status, created_at, shipping_address)
 * - $items (array of items: name, quantity, price)
 * - $shipment (array|null: courier, tracking_number, status)
 * - $oldStatus (string)
 * - $newStatus (string)
 * - $orderUrl (string)
 * - $brandName (string)
 */

$statusLabels = [
    'pending' => 'Menunggu Pembayaran',
    'processing' => 'Sedang Diproses',
    'shipped' => 'Telah Dikirim',
    'completed' => 'Selesai',
    'cancelled' => 'Dibatalkan'
];

$statusColors = [
    'pending' => '#f59e0b',
    'processing' => '#3b82f6',
    'shipped' => '#6366f1',
    'completed' => '#10b981',
    'cancelled' => '#ef4444'
];

$statusColor = $statusColors[strtolower($newStatus)] ?? '#4f46e5';
$statusLabel = $statusLabels[strtolower($newStatus)] ?? strtoupper($newStatus);
$orderCode = '#ORD-' . str_pad($order['id'], 5, '0', STR_PAD_LEFT);

// Status specific message headers
$statusMessages = [
    'processing' => 'Kabar baik! Pesanan Anda saat ini sedang kami siapkan dan kemas dengan teliti oleh tim kami.',
    'shipped' => 'Kabar gembira! Pesanan Anda telah dikirim dan sedang dalam perjalanan menuju alamat tujuan Anda.',
    'completed' => 'Pesanan Anda telah selesai dan berhasil diterima. Terima kasih telah mempercayai kami!',
    'cancelled' => 'Pesanan Anda telah dibatalkan. Jika Anda memiliki pertanyaan atau kendala, hubungi tim dukungan kami.',
    'pending' => 'Pesanan Anda telah dibuat dan sedang menunggu konfirmasi pembayaran.'
];

$customMessage = $statusMessages[strtolower($newStatus)] ?? 'Status pesanan Anda telah diperbarui menjadi ' . htmlspecialchars($statusLabel) . '.';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Status Pesanan <?= htmlspecialchars($orderCode); ?> - <?= htmlspecialchars($brandName); ?></title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #f8fafc;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            color: #1e293b;
            -webkit-font-smoothing: antialiased;
        }
        table {
            border-collapse: collapse;
        }
        .wrapper {
            width: 100%;
            background-color: #f8fafc;
            padding: 40px 16px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
            border: 1px solid #e2e8f0;
        }
        .header {
            background: linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%);
            padding: 32px 28px;
            text-align: center;
            color: #ffffff;
        }
        .header h1 {
            margin: 0;
            font-size: 26px;
            font-weight: 800;
            letter-spacing: 1px;
        }
        .header p {
            margin: 6px 0 0 0;
            font-size: 14px;
            opacity: 0.9;
        }
        .content {
            padding: 32px 28px;
        }
        .greeting {
            font-size: 17px;
            font-weight: 600;
            color: #0f172a;
            margin-bottom: 12px;
        }
        .message-box {
            background-color: #f1f5f9;
            border-left: 4px solid <?= $statusColor; ?>;
            padding: 16px;
            border-radius: 8px;
            font-size: 15px;
            line-height: 1.6;
            color: #334155;
            margin-bottom: 24px;
        }
        .badge {
            display: inline-block;
            background-color: <?= $statusColor; ?>;
            color: #ffffff;
            font-weight: 700;
            font-size: 13px;
            padding: 6px 14px;
            border-radius: 20px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .info-grid {
            width: 100%;
            margin-bottom: 24px;
            border-top: 1px solid #e2e8f0;
            border-bottom: 1px solid #e2e8f0;
            padding: 16px 0;
        }
        .info-grid td {
            padding: 6px 0;
            font-size: 14px;
        }
        .info-label {
            color: #64748b;
            width: 40%;
        }
        .info-value {
            font-weight: 600;
            color: #0f172a;
            text-align: right;
        }
        .items-table {
            width: 100%;
            margin-bottom: 24px;
        }
        .items-table th {
            text-align: left;
            font-size: 12px;
            text-transform: uppercase;
            color: #64748b;
            padding-bottom: 10px;
            border-bottom: 2px solid #e2e8f0;
        }
        .items-table td {
            padding: 12px 0;
            font-size: 14px;
            border-bottom: 1px solid #f1f5f9;
        }
        .items-total {
            border-top: 2px solid #e2e8f0;
            font-weight: 700;
            font-size: 16px;
            color: #4f46e5;
        }
        .btn-container {
            text-align: center;
            margin: 32px 0 16px 0;
        }
        .btn {
            display: inline-block;
            background: linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%);
            color: #ffffff !important;
            text-decoration: none;
            font-weight: 700;
            font-size: 15px;
            padding: 14px 32px;
            border-radius: 30px;
            box-shadow: 0 4px 14px rgba(79, 70, 229, 0.35);
        }
        .footer {
            background-color: #f8fafc;
            border-top: 1px solid #e2e8f0;
            padding: 24px 28px;
            text-align: center;
            font-size: 12px;
            color: #94a3b8;
            line-height: 1.5;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container">
            <div class="header">
                <h1><?= htmlspecialchars($brandName); ?></h1>
                <p>Notifikasi Resmi Status Pesanan</p>
            </div>
            <div class="content">
                <div class="greeting">Halo <?= htmlspecialchars($recipientName ?: 'Pelanggan'); ?>,</div>
                <div class="message-box">
                    <?= htmlspecialchars($customMessage); ?>
                </div>

                <div style="margin-bottom: 20px; text-align: center;">
                    <span class="badge"><?= htmlspecialchars($statusLabel); ?></span>
                </div>

                <table class="info-grid">
                    <tr>
                        <td class="info-label">Nomor Pesanan</td>
                        <td class="info-value"><?= htmlspecialchars($orderCode); ?></td>
                    </tr>
                    <tr>
                        <td class="info-label">Tanggal Pesanan</td>
                        <td class="info-value"><?= date('d M Y, H:i', strtotime($order['created_at'])); ?> WIB</td>
                    </tr>
                    <?php if (!empty($oldStatus) && $oldStatus !== $newStatus): ?>
                    <tr>
                        <td class="info-label">Status Sebelumnya</td>
                        <td class="info-value"><?= htmlspecialchars(strtoupper($oldStatus)); ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if (!empty($shipment['courier'])): ?>
                    <tr>
                        <td class="info-label">Kurir Ekspedisi</td>
                        <td class="info-value"><?= htmlspecialchars($shipment['courier']); ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if (!empty($shipment['tracking_number'])): ?>
                    <tr>
                        <td class="info-label">Nomor Resi</td>
                        <td class="info-value" style="font-family: monospace; letter-spacing: 1px; color: #4f46e5;">
                            <?= htmlspecialchars($shipment['tracking_number']); ?>
                        </td>
                    </tr>
                    <?php endif; ?>
                </table>

                <?php if (!empty($items)): ?>
                <h3 style="font-size: 15px; font-weight: 700; margin-bottom: 12px; color: #0f172a;">Ringkasan Produk</h3>
                <table class="items-table">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th style="text-align: center;">Qty</th>
                            <th style="text-align: right;">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                        <tr>
                            <td><?= htmlspecialchars($item['name']); ?></td>
                            <td style="text-align: center;"><?= (int)$item['quantity']; ?></td>
                            <td style="text-align: right;">Rp <?= number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <tr>
                            <td colspan="2" class="items-total" style="padding-top: 14px;">Total Tagihan</td>
                            <td class="items-total" style="text-align: right; padding-top: 14px;">
                                Rp <?= number_format($order['total_amount'], 0, ',', '.'); ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <?php endif; ?>

                <?php if (!empty($order['shipping_address'])): ?>
                <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px; margin-bottom: 24px;">
                    <div style="font-size: 12px; text-transform: uppercase; font-weight: 700; color: #64748b; margin-bottom: 4px;">Alamat Pengiriman:</div>
                    <div style="font-size: 13px; line-height: 1.5; color: #334155;">
                        <?= nl2br(htmlspecialchars($order['shipping_address'])); ?>
                    </div>
                </div>
                <?php endif; ?>

                <div class="btn-container">
                    <a href="<?= htmlspecialchars($orderUrl); ?>" class="btn" target="_blank">Lihat Detail Pesanan</a>
                </div>
            </div>

            <div class="footer">
                <p>Email ini dikirim secara otomatis oleh sistem <strong><?= htmlspecialchars($brandName); ?></strong>.<br>
                Jika Anda memiliki pertanyaan, silakan hubungi tim layanan pelanggan kami.</p>
                <p>&copy; <?= date('Y'); ?> <?= htmlspecialchars($brandName); ?>. Seluruh hak cipta dilindungi.</p>
            </div>
        </div>
    </div>
</body>
</html>
