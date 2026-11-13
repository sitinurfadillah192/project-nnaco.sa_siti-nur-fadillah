<div class="container py-5 min-vh-100">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card card-soft border shadow-sm" style="background:var(--color-surface); border-color:var(--color-border) !important;">
                <div class="card-body p-4 p-md-5 text-center">
                    <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3 shadow-sm" style="width:60px; height:60px; background:var(--color-accent); color:var(--color-primary); font-size:24px;">
                        <i class="bi bi-cash-coin"></i>
                    </div>
                    <h3 class="fw-bold mb-3" style="color:var(--color-text);">Konfirmasi Pembayaran</h3>
                    
                    <div class="alert border shadow-sm text-start mb-4 rounded-4" style="background:var(--color-surface-soft); border-color:var(--color-border) !important; color:var(--color-text);">
                        <strong>Order #<?= $data['order']['id']; ?></strong><br>
                        Total Tagihan: <span class="fw-bold fs-5" style="color:var(--color-primary);">Rp <?= number_format($data['order']['total_amount'], 0, ',', '.'); ?></span>
                        <hr style="border-color:var(--color-border);">
                        <p class="mb-0 small text-muted">Sistem Pembayaran:<br>
                        <strong style="color:var(--color-text);">Cash (Tunai / Bayar di Tempat)</strong></p>
                    </div>

                    <form action="<?= BASEURL; ?>/order/payment/<?= $data['order']['id']; ?>" method="POST" class="text-start">
                        <div class="mb-4">
                            <p class="text-muted text-center small lh-base">Saat ini kami hanya melayani pembayaran secara tunai (Cash). Dengan menekan tombol di bawah, Anda setuju untuk menyiapkan uang tunai senilai total tagihan saat mengambil/menerima pesanan.</p>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 py-3 fw-bold rounded-pill shadow-sm"><i class="bi bi-cash-coin me-2"></i>Konfirmasi Pembayaran Tunai</button>
                        <a href="<?= BASEURL; ?>/order" class="btn btn-link text-decoration-none w-100 mt-3 text-muted text-center d-block small">Kembali ke riwayat</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
