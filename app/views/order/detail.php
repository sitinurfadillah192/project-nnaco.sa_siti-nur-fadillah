<div class="container py-4 min-vh-100">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h2 class="fw-bold mb-1" style="color:var(--color-text);">Detail Pesanan #<?= $data['order']['id']; ?></h2>
            <p class="text-muted small mb-0">Rincian produk dan status pengiriman perhiasan Anda.</p>
        </div>
        <div>
            <a href="<?= BASEURL; ?>/order/invoice/<?= $data['order']['id']; ?>" target="_blank" class="btn btn-outline-dark rounded-pill me-2 px-3 shadow-sm"><i class="bi bi-printer me-1"></i> Cetak Invoice PDF</a>
            <a href="<?= BASEURL; ?>/order" class="btn btn-primary rounded-pill px-4 shadow-sm">Kembali</a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-8">
            <div class="card card-soft border shadow-sm mb-4" style="background:var(--color-surface); border-color:var(--color-border) !important;">
                <div class="card-header py-3 fw-bold border-bottom" style="background:var(--color-surface-soft); border-color:var(--color-border) !important; color:var(--color-text);">
                    <i class="bi bi-bag-check me-2 text-primary"></i>Daftar Produk
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Nama Produk</th>
                                    <th>Harga</th>
                                    <th class="text-center">Qty</th>
                                    <th class="text-center">Ulasan & Rating</th>
                                    <th class="pe-4 text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($data['items'] as $item): ?>
                                <tr>
                                    <td class="ps-4 py-3 fw-medium" style="max-width:220px; color:var(--color-text);">
                                        <a href="<?= BASEURL; ?>/catalog/detail/<?= $item['product_id']; ?>" class="text-decoration-none text-reset hover-primary d-block text-truncate fw-semibold">
                                            <?= Security::escape($item['name']); ?>
                                        </a>
                                    </td>
                                    <td class="text-muted">Rp <?= number_format($item['price'], 0, ',', '.'); ?></td>
                                    <td class="text-center fw-medium"><?= $item['quantity']; ?></td>
                                    <td class="text-center">
                                        <?php 
                                            $userReview = $data['reviews'][$item['product_id']] ?? null;
                                            $orderStatus = strtolower($data['order']['status']);
                                            $canReview = in_array($orderStatus, ['processing', 'shipped', 'completed', 'delivered']);
                                        ?>
                                        <?php if($userReview): ?>
                                            <div class="d-inline-flex flex-column align-items-center">
                                                <div class="text-warning small mb-1">
                                                    <?php for($s=1; $s<=5; $s++): ?>
                                                        <i class="bi <?= ($s <= (int)$userReview['rating']) ? 'bi-star-fill' : 'bi-star'; ?>"></i>
                                                    <?php endfor; ?>
                                                </div>
                                                <div class="d-flex align-items-center gap-1">
                                                    <span class="badge rounded-pill px-2 py-1" style="background:rgba(71,122,91,0.12); color:var(--color-success); font-size:10px;">
                                                        <i class="bi bi-check-circle-fill me-1"></i>Sudah Diulas
                                                    </span>
                                                    <button type="button" class="btn btn-sm btn-link p-0 text-muted small text-decoration-none" data-bs-toggle="modal" data-bs-target="#reviewModal<?= $item['product_id']; ?>" title="Ubah Ulasan">
                                                        <i class="bi bi-pencil-square"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        <?php elseif($canReview): ?>
                                            <button type="button" class="btn btn-sm btn-primary-soft rounded-pill px-3 shadow-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#reviewModal<?= $item['product_id']; ?>">
                                                <i class="bi bi-star-fill text-warning me-1"></i> Beri Ulasan
                                            </button>
                                        <?php else: ?>
                                            <span class="text-muted small fst-italic">Menunggu pesanan</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="pe-4 text-end fw-bold" style="color:var(--color-primary);">Rp <?= number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card card-soft border shadow-sm mb-4" style="background:var(--color-surface); border-color:var(--color-border) !important;">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3 border-bottom pb-2" style="color:var(--color-text); border-color:var(--color-border) !important;">Informasi Pengiriman</h5>
                    <p class="small text-muted mb-1">Alamat Tujuan:</p>
                    <p class="fw-medium lh-base mb-3" style="color:var(--color-text);"><?= nl2br(Security::escape($data['order']['shipping_address'])); ?></p>
                    
                    <h5 class="fw-bold mt-4 mb-3 border-bottom pb-2" style="color:var(--color-text); border-color:var(--color-border) !important;">Ringkasan Pembayaran</h5>
                    <div class="d-flex justify-content-between mb-2 align-items-center">
                        <span class="text-muted">Status Pesanan:</span>
                        <?php 
                            $st = strtolower($data['order']['status']);
                            $badgeStyle = 'background:var(--color-taupe); color:#fff;';
                            if ($st == 'pending') $badgeStyle = 'background:var(--color-warning-soft); color:var(--color-warning); border:1px solid rgba(166,106,32,0.3);';
                            elseif ($st == 'processing') $badgeStyle = 'background:var(--color-info-soft); color:var(--color-info); border:1px solid rgba(82,109,130,0.3);';
                            elseif ($st == 'shipped') $badgeStyle = 'background:var(--color-primary); color:var(--color-accent);';
                            elseif ($st == 'completed' || $st == 'delivered') $badgeStyle = 'background:var(--color-success-soft); color:var(--color-success); border:1px solid rgba(71,122,91,0.3);';
                            elseif ($st == 'cancelled') $badgeStyle = 'background:var(--color-danger-soft); color:var(--color-danger); border:1px solid rgba(181,71,71,0.3);';
                        ?>
                        <span class="badge rounded-pill text-uppercase px-3 py-1" style="font-size:11.5px; letter-spacing:0.5px; <?= $badgeStyle; ?>"><?= $data['order']['status']; ?></span>
                    </div>
                    <div class="d-flex justify-content-between mt-3 pt-3 border-top" style="border-color:var(--color-border) !important;">
                        <span class="fw-bold fs-5" style="color:var(--color-text);">Total Bayar:</span>
                        <strong class="fs-4" style="color:var(--color-primary);">Rp <?= number_format($data['order']['total_amount'], 0, ',', '.'); ?></strong>
                    </div>

                    <?php if($data['order']['status'] == 'pending'): ?>
                        <a href="<?= BASEURL; ?>/order/payment/<?= $data['order']['id']; ?>" class="btn btn-primary w-100 py-3 fw-bold rounded-pill mt-4 shadow-sm text-uppercase">Konfirmasi Pembayaran</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Review Modals -->
<?php foreach($data['items'] as $item): ?>
    <?php 
        $userReview = $data['reviews'][$item['product_id']] ?? null;
        $currentRating = $userReview ? (int)$userReview['rating'] : 5;
        $currentComment = $userReview ? $userReview['comment'] : '';
    ?>
    <div class="modal fade" id="reviewModal<?= $item['product_id']; ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden" style="background:var(--color-surface); border:1px solid var(--color-border) !important;">
                <div class="modal-header border-0 py-3 px-4" style="background:var(--color-surface-soft); border-bottom:1px solid var(--color-border) !important;">
                    <div class="d-flex align-items-center gap-2">
                        <div class="rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width:38px; height:38px; background:var(--color-primary); color:var(--color-accent);">
                            <i class="bi bi-star-fill"></i>
                        </div>
                        <div>
                            <h6 class="modal-title fw-bold mb-0 text-dark">Beri Nilai & Ulasan</h6>
                            <small class="text-muted d-block text-truncate" style="max-width:280px;"><?= Security::escape($item['name']); ?></small>
                        </div>
                    </div>
                    <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <form action="<?= BASEURL; ?>/review/submit" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= Security::generateCsrfToken(); ?>">
                    <input type="hidden" name="order_id" value="<?= $data['order']['id']; ?>">
                    <input type="hidden" name="product_id" value="<?= $item['product_id']; ?>">
                    
                    <div class="modal-body p-4">
                        <div class="text-center mb-4">
                            <label class="form-label fw-bold text-dark d-block mb-2">Berapa bintang untuk produk ini?</label>
                            
                            <div class="d-inline-flex gap-2 p-2 px-3 rounded-pill border star-rating-container" style="background:var(--color-surface-soft); border-color:var(--color-border) !important;">
                                <?php for($star=1; $star<=5; $star++): ?>
                                    <label class="mb-0" style="cursor:pointer;" title="<?= $star ?> Bintang">
                                        <input type="radio" name="rating" value="<?= $star ?>" class="d-none star-radio" data-prod="<?= $item['product_id']; ?>" <?= ($currentRating === $star) ? 'checked' : ''; ?> required>
                                        <i class="bi <?= ($star <= $currentRating) ? 'bi-star-fill text-warning' : 'bi-star text-muted opacity-50'; ?> fs-3 star-star-<?= $item['product_id']; ?>" data-star="<?= $star ?>"></i>
                                    </label>
                                <?php endfor; ?>
                            </div>
                            <div class="small fw-semibold mt-2 text-muted" id="ratingDesc<?= $item['product_id']; ?>">
                                <?= $currentRating ?> dari 5 Bintang
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark">Ceritakan Pengalaman Anda</label>
                            <textarea name="comment" class="form-control" rows="4" placeholder="Bagikan bagaimana kualitas bahan, keindahan kilau perhiasan, dan kemasan paket yang Anda terima..." style="border-radius:12px;"><?= Security::escape($currentComment); ?></textarea>
                            <div class="form-text small text-muted">Ulasan asli Anda sangat berarti untuk membantu butik NNACOSA dan pelanggan lainnya.</div>
                        </div>
                    </div>
                    
                    <div class="modal-footer border-0 p-4 pt-0 d-flex justify-content-between">
                        <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4 py-2 shadow-sm fw-semibold">
                            <i class="bi bi-check-lg me-1"></i> <?= $userReview ? 'Perbarui Ulasan' : 'Kirim Ulasan' ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endforeach; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const starRadios = document.querySelectorAll('.star-radio');
    starRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            const prodId = this.getAttribute('data-prod');
            const selectedVal = parseInt(this.value);
            const stars = document.querySelectorAll('.star-star-' + prodId);
            const desc = document.getElementById('ratingDesc' + prodId);
            
            stars.forEach(s => {
                const sVal = parseInt(s.getAttribute('data-star'));
                if (sVal <= selectedVal) {
                    s.className = 'bi bi-star-fill text-warning fs-3 star-star-' + prodId;
                } else {
                    s.className = 'bi bi-star text-muted opacity-50 fs-3 star-star-' + prodId;
                }
            });
            if (desc) {
                const labels = ['', 'Sangat Mengecewakan (1/5)', 'Kurang Memuaskan (2/5)', 'Cukup Bagus (3/5)', 'Sangat Bagus (4/5)', 'Luar Biasa Memuaskan! (5/5)'];
                desc.textContent = labels[selectedVal] || (selectedVal + ' dari 5 Bintang');
            }
        });
    });
});
</script>
