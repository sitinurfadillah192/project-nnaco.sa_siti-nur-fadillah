<div class="container py-5 min-vh-100">
    <div class="row mb-5 align-items-center" data-aos="fade-down">
        <div class="col-md-6">
            <h2 class="fw-bold display-6 mb-1" style="color:var(--color-text);"><i class="bi bi-heart-fill me-2" style="color:var(--color-danger);"></i>Wishlist Saya</h2>
            <p class="text-muted fs-5 mb-0">Simpan sekarang, miliki nanti.</p>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0">
            <a href="<?= BASEURL; ?>/catalog" class="btn btn-outline-dark rounded-pill px-4">Lanjut Belanja</a>
        </div>
    </div>

    <div class="row g-4">
        <?php if(empty($data['wishlist'])): ?>
            <div class="col-12 text-center py-5">
                <i class="bi bi-heart text-muted d-block opacity-25 mb-4" style="font-size: 80px;"></i>
                <h4 class="fw-bold" style="color:var(--color-text);">Wishlist Anda Masih Kosong</h4>
                <p class="text-muted mb-4">Temukan perhiasan impian Anda dan simpan di sini.</p>
                <a href="<?= BASEURL; ?>/catalog" class="btn btn-primary rounded-pill px-5 py-3 fw-bold shadow-sm">Mulai Eksplorasi <i class="bi bi-arrow-right ms-2"></i></a>
            </div>
        <?php else: ?>
            <?php foreach($data['wishlist'] as $product): ?>
                <div class="col-lg-3 col-md-4 col-sm-6" data-aos="fade-up">
                    <div class="card card-soft h-100 position-relative border" style="background:var(--color-surface); border-color:var(--color-border) !important;">
                        <button class="btn btn-light rounded-circle shadow-sm position-absolute btn-wishlist active" data-id="<?= $product['id']; ?>" style="top: 15px; right: 15px; z-index: 10; width:40px; height:40px;" title="Hapus dari Wishlist">
                            <i class="bi bi-heart-fill text-danger"></i>
                        </button>
                        
                        <div class="product-img-wrapper" style="height: 260px; background:var(--color-surface-soft);">
                            <?php 
                                $img = !empty($product['image']) ? BASEURL . '/uploads/products/' . Security::escape($product['image']) : 'https://images.unsplash.com/photo-1515562141207-7a88fb7ce338?w=400&q=80'; 
                            ?>
                            <img src="<?= $img; ?>" class="w-100 h-100" style="object-fit: cover;" alt="<?= Security::escape($product['name']); ?>">
                            
                            <div class="quick-action-btns">
                                <?php if($product['stock'] > 0): ?>
                                <form action="<?= BASEURL; ?>/cart/add" method="POST" class="d-inline">
                                    <input type="hidden" name="product_id" value="<?= $product['id']; ?>">
                                    <input type="hidden" name="quantity" value="1">
                                    <button type="submit" class="btn btn-primary-soft rounded-pill px-4 shadow-sm fw-bold d-flex align-items-center" style="height:45px;"><i class="bi bi-cart-plus me-2"></i> Beli</button>
                                </form>
                                <?php else: ?>
                                    <button class="btn btn-secondary rounded-pill px-4 shadow-sm fw-bold disabled" style="height:45px; background:var(--color-taupe);">Habis</button>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="card-body text-center pt-4 pb-4">
                            <h6 class="fw-bold mb-2 fs-5 text-truncate px-2" style="color:var(--color-text);"><?= Security::escape($product['name']); ?></h6>
                            <p class="fw-bold fs-5 mb-0" style="color:var(--color-primary);">Rp <?= number_format($product['price'], 0, ',', '.'); ?></p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
