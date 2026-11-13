<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #<?= $data['order']['id']; ?> - NNACO SA</title>
    <!-- Use standard Bootstrap via CDN for reliable print layout -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background: #eee; }
        .invoice-box { max-width: 800px; margin: auto; padding: 40px; border: 1px solid #eee; box-shadow: 0 0 15px rgba(0, 0, 0, .05); font-size: 15px; line-height: 24px; color: #555; background: #fff; border-radius: 8px;}
        .invoice-box table { width: 100%; line-height: inherit; text-align: left; }
        .invoice-box table td { padding: 8px; vertical-align: top; }
        .invoice-box table tr.top table td { padding-bottom: 20px; }
        .invoice-box table tr.top table td.title { font-size: 36px; line-height: 42px; color: #4B3B32; font-weight: 800; letter-spacing: 1.5px; font-family: 'Outfit', sans-serif; }
        .invoice-box table tr.heading td { background: #F8F2EB; border-bottom: 2px solid #EDE1D5; font-weight: bold; color:#4B3B32; }
        .invoice-box table tr.item td{ border-bottom: 1px solid #EDE1D5; }
        .invoice-box table tr.total td:nth-child(2) { border-top: 2px solid #EDE1D5; font-weight: bold; }
        @media print {
            body { background: #fff; -webkit-print-color-adjust: exact; print-color-adjust: exact;}
            .invoice-box { box-shadow: none; border: none; margin: 0; padding: 0; max-width: 100%;}
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
    <div class="text-center my-4 no-print d-flex justify-content-center gap-2">
        <button onclick="window.print()" class="btn btn-dark btn-lg shadow-sm px-5 fw-bold rounded-pill" style="background:#4B3B32; border-color:#4B3B32;">Cetak / Simpan PDF</button>
        <a href="<?= BASEURL; ?>/order/detail/<?= $data['order']['id']; ?>" class="btn btn-outline-dark btn-lg rounded-pill px-4">Tutup</a>
    </div>

    <div class="invoice-box mt-4 mb-5 border" style="border-color:#EDE1D5 !important;">
        <table cellpadding="0" cellspacing="0">
            <tr class="top">
                <td colspan="2">
                    <table>
                        <tr>
                            <td class="title">NNACO SA</td>
                            <td class="text-end">
                                <h3 class="mb-0 fw-bold" style="color:#4B3B32;">INVOICE</h3>
                                Order #: <strong><?= $data['order']['id']; ?></strong><br>
                                Tanggal: <?= date('d M Y, H:i', strtotime($data['order']['created_at'])); ?><br>
                                Status: <span class="text-uppercase fw-bold" style="color:#4B3B32;"><?= $data['order']['status']; ?></span>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            
            <tr class="information">
                <td colspan="2">
                    <table>
                        <tr>
                            <td>
                                <strong>Ditagihkan ke:</strong><br>
                                Sistem Toko Aksesoris<br>
                                Customer ID: #<?= $data['order']['user_id']; ?>
                            </td>
                            <td class="text-end">
                                <strong>Dikirim ke Alamat:</strong><br>
                                <?= nl2br(Security::escape($data['order']['shipping_address'])); ?>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
        
        <table class="table mt-4 table-borderless">
            <tr class="heading">
                <td>Deskripsi Produk</td>
                <td class="text-center">Harga Satuan</td>
                <td class="text-center">Qty</td>
                <td class="text-end pe-3">Subtotal</td>
            </tr>
            
            <?php foreach($data['items'] as $item): ?>
            <tr class="item">
                <td class="fw-bold text-dark"><?= Security::escape($item['name']); ?></td>
                <td class="text-center">Rp <?= number_format($item['price'], 0, ',', '.'); ?></td>
                <td class="text-center"><?= $item['quantity']; ?></td>
                <td class="text-end pe-3">Rp <?= number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?></td>
            </tr>
            <?php endforeach; ?>
            
            <tr class="total">
                <td colspan="3" class="text-end pe-4 border-0 text-muted">Total Keseluruhan:</td>
                <td class="text-end fs-4 border-top pe-3" style="border-color:#4B3B32 !important;">
                   <strong style="color:#4B3B32;">Rp <?= number_format($data['order']['total_amount'], 0, ',', '.'); ?></strong>
                </td>
            </tr>
        </table>
        
        <div class="text-center mt-5 pt-5 text-muted small border-top">
            <strong>Terima kasih telah berbelanja di NNACO SA!</strong><br>
            Invoice ini sah dan dicetak secara otomatis oleh sistem komputer. <br>
            Harap simpan invoice ini sebagai bukti pembelian yang sah.
        </div>
    </div>
</body>
</html>
