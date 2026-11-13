<div class="container py-5 text-center">
    <div class="card shadow-sm border-0 mx-auto" style="max-width: 600px;">
        <div class="card-body p-5">
            <div class="display-1 text-success mb-4 fw-bold">
                ✓
            </div>
            <h2 class="fw-bold mb-3">Pesanan Berhasil Dibuat!</h2>
            <p class="text-muted mb-4">Terima kasih atas pesanan Anda. Order ID Anda adalah: <strong>#<?= $data['order_id']; ?></strong></p>
            
            <div class="alert alert-warning mb-4 shadow-sm border-0 text-start">
                <i class="bi bi-info-circle me-2"></i> Silakan lakukan konfirmasi pembayaran cash agar pesanan Anda dapat segera kami proses.
            </div>
            
            <div class="d-grid gap-2 col-md-8 mx-auto">
                <a href="<?= BASEURL; ?>/order/payment/<?= $data['order_id']; ?>" class="btn btn-primary py-2 fw-bold rounded-pill shadow-sm"><i class="bi bi-cash-coin me-2"></i>Konfirmasi Pembayaran Cash</a>
                <a href="<?= BASEURL; ?>/order" class="btn btn-outline-secondary py-2 rounded-pill mt-2">Lihat Riwayat Pesanan</a>
            </div>
        </div>
    </div>
</div>
