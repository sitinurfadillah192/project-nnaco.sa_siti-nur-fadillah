<div class="container py-5 min-vh-100">
    <div class="d-flex justify-content-between align-items-end mb-4" data-aos="fade-down">
        <div>
            <h2 class="fw-bold display-6 mb-1" style="color:var(--color-text);">Keranjang Anda</h2>
            <p class="text-muted fs-5 mb-0">Pastikan pesanan Anda sudah benar sebelum membayar.</p>
        </div>
        <a href="<?= BASEURL; ?>/catalog" class="btn btn-outline-dark rounded-pill px-4 d-none d-md-block">Lanjut Belanja</a>
    </div>

    <div class="row g-4">
        <div class="col-lg-8" data-aos="fade-up" data-aos-delay="100">
            <div class="card card-soft p-2 p-md-4 border" style="background:var(--color-surface); border-color:var(--color-border) !important;">
                <div class="card-body">
                    <?php if(!empty($data['cart'])): ?>
                        <?php foreach($data['cart'] as $item): ?>
                        <div class="row align-items-center mb-4 pb-4 border-bottom" style="border-color: var(--color-border) !important;">
                            <div class="col-3 col-md-2">
                                <?php $img = !empty($item['image']) ? BASEURL . '/uploads/products/' . Security::escape($item['image']) : 'https://images.unsplash.com/photo-1515562141207-7a88fb7ce338?w=100&q=80'; ?>
                                <img src="<?= $img; ?>" class="img-fluid rounded-3 shadow-sm border" style="width:100%; aspect-ratio:1/1; object-fit:cover; border-color:var(--color-border) !important;" alt="Produk">
                            </div>
                            <div class="col-9 col-md-4 mb-3 mb-md-0">
                                <h5 class="fw-bold mb-1 text-truncate" style="color:var(--color-text);"><?= Security::escape($item['name']); ?></h5>
                                <p class="fw-bold mb-0" style="color:var(--color-primary);">Rp <?= number_format($item['price'], 0, ',', '.'); ?></p>
                            </div>
                            <div class="col-6 col-md-4 d-flex align-items-center">
                                <form action="<?= BASEURL; ?>/cart/update" method="POST" class="d-flex align-items-center rounded-pill p-1 shadow-sm w-100" style="max-width:150px; background:var(--color-surface-soft); border:1px solid var(--color-border);">
                                    <input type="hidden" name="product_id" value="<?= $item['product_id']; ?>">
                                    <button type="button" class="btn btn-link text-dark text-decoration-none px-3" onclick="this.parentNode.querySelector('input[type=number]').stepDown(); this.parentNode.submit();">-</button>
                                    <input type="number" name="quantity" class="form-control text-center border-0 bg-transparent p-0 shadow-none fw-bold" value="<?= $item['quantity']; ?>" min="1" max="<?= $item['stock']; ?>" readonly style="color:var(--color-text);">
                                    <button type="button" class="btn btn-link text-dark text-decoration-none px-3" onclick="this.parentNode.querySelector('input[type=number]').stepUp(); this.parentNode.submit();">+</button>
                                </form>
                            </div>
                            <div class="col-6 col-md-2 text-end">
                                <a href="<?= BASEURL; ?>/cart/remove/<?= $item['product_id']; ?>" class="btn btn-light rounded-circle shadow-sm border text-danger" style="border-color:var(--color-border);" title="Hapus"><i class="bi bi-trash"></i></a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <div class="display-1 text-muted opacity-25 mb-4"><i class="bi bi-cart-x"></i></div>
                            <h4 class="fw-bold" style="color:var(--color-text);">Keranjang Kosong</h4>
                            <p class="text-muted mb-4">Anda belum menambahkan aksesoris apapun.</p>
                            <a href="<?= BASEURL; ?>/catalog" class="btn btn-primary rounded-pill px-5 py-2">Mulai Belanja</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <?php if(!empty($data['cart'])): ?>
        <div class="col-lg-4" data-aos="fade-left" data-aos-delay="200">
            <div class="card card-soft p-4 sticky-lg-top border" style="top:120px; background:var(--color-surface); border-color:var(--color-border) !important;">
                <h5 class="fw-bold mb-4" style="color:var(--color-text);">Ringkasan Belanja</h5>
                
                <div class="d-flex justify-content-between mb-3 text-muted">
                    <span>Total Harga (<?= count($data['cart']); ?> Barang)</span>
                    <span class="fw-medium" style="color:var(--color-text);">Rp <?= number_format($data['total'], 0, ',', '.'); ?></span>
                </div>
                <div class="d-flex justify-content-between mb-3 text-muted">
                    <span>Estimasi Ongkir</span>
                    <span class="fw-bold" style="color:var(--color-success);">Gratis</span>
                </div>
                
                <hr class="my-4" style="border-color:var(--color-border);">
                
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <span class="fw-bold fs-5" style="color:var(--color-text);">Total Bayar</span>
                    <strong class="fs-3" style="color:var(--color-primary);">Rp <?= number_format($data['total'], 0, ',', '.'); ?></strong>
                </div>
                
                <div class="alert border rounded-4" style="background-color: var(--color-accent-soft); color:var(--color-primary); border-color:var(--color-border) !important; font-size:13px;">
                    <i class="bi bi-info-circle me-1"></i> Promo Gratis Ongkir otomatis diaplikasikan pada pesanan Anda.
                </div>

                <a href="<?= BASEURL; ?>/checkout" class="btn btn-primary w-100 py-3 rounded-pill fw-bold fs-5 mt-2 shadow-sm">Beli Sekarang <i class="bi bi-arrow-right ms-2"></i></a>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
