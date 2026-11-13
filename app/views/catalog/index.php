<div class="container py-5">
    <div class="row align-items-end mb-5" data-aos="fade-down">
        <div class="col-md-6 mb-4 mb-md-0">
            <span class="badge badge-soft mb-2"><i class="bi bi-stars text-warning me-1"></i> Koleksi Lengkap</span>
            <h2 class="fw-bold display-5 mb-1" style="color:var(--color-text);">Katalog Eksklusif</h2>
            <p class="text-muted fs-5 mb-0">Temukan aksesoris yang melengkapi pesonamu.</p>
        </div>
        <div class="col-md-6 text-md-end">
            <!-- Filter Dropdown & Search Bar -->
            <form action="<?= BASEURL; ?>/catalog" method="GET" class="d-inline-block w-100" style="max-width: 450px;">
                <div class="input-group rounded-pill shadow-sm p-1 border" style="background:var(--color-surface); border-color:var(--color-border) !important;">
                    <select name="category" class="form-select border-0 bg-transparent shadow-none" style="max-width: 140px; color:var(--color-text);">
                        <option value="">Semua</option>
                        <?php foreach($data['categories'] as $cat): ?>
                            <option value="<?= $cat['id']; ?>" <?= (isset($_GET['category']) && $_GET['category'] == $cat['id']) ? 'selected' : ''; ?>>
                                <?= Security::escape($cat['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div style="width: 1px; background:var(--color-border); margin: 8px 0;"></div>
                    <input type="text" name="keyword" class="form-control border-0 shadow-none bg-transparent" style="color:var(--color-text);" placeholder="Cari perhiasan..." value="<?= isset($_GET['keyword']) ? Security::escape($_GET['keyword']) : ''; ?>">
                    <button type="submit" class="btn btn-primary-soft rounded-pill px-4 shadow-sm"><i class="bi bi-search"></i></button>
                </div>
            </form>
        </div>
    </div>

    <!-- Product Grid -->
    <div class="row g-4 mb-5">
        <?php foreach($data['products'] as $index => $product): ?>
        <div class="col-lg-3 col-md-4 col-sm-6" data-aos="fade-up" data-aos-delay="<?= ($index % 4) * 100 ?>">
            <div class="card card-soft h-100 position-relative border" style="background:var(--color-surface); border-color:var(--color-border) !important;">
                <?php if($product['stock'] < 5 && $product['stock'] > 0): ?>
                    <span class="badge position-absolute shadow-sm" style="top: 15px; left: 15px; z-index: 10; border-radius:10px; background:var(--color-warning); color:#fff;"><i class="bi bi-fire"></i> Sisa <?= $product['stock']; ?></span>
                <?php elseif($product['stock'] == 0): ?>
                    <span class="badge position-absolute shadow-sm" style="top: 15px; left: 15px; z-index: 10; border-radius:10px; background:var(--color-taupe); color:#fff;">Habis</span>
                <?php endif; ?>

                <div class="product-img-wrapper" style="height: 260px; background:var(--color-surface-soft);">
                    <?php 
                        $img = !empty($product['image']) ? BASEURL . '/uploads/products/' . Security::escape($product['image']) : BASEURL . '/img/hero/hero_ring.jpg'; 
                    ?>
                    <a href="<?= BASEURL; ?>/catalog/detail/<?= $product['id']; ?>" class="d-block w-100 h-100">
                        <img src="<?= $img; ?>" class="w-100 h-100" style="object-fit: cover;" alt="<?= Security::escape($product['name']); ?>">
                    </a>
                    
                    <div class="quick-action-btns">
                        <?php 
                            $inWishlist = in_array($product['id'], $data['wishlist'] ?? []); 
                        ?>
                        <button class="btn btn-light rounded-circle shadow-sm btn-wishlist <?= $inWishlist ? 'active' : ''; ?>" data-id="<?= $product['id']; ?>" style="width:45px; height:45px;" title="Wishlist">
                            <i class="bi <?= $inWishlist ? 'bi-heart-fill' : 'bi-heart'; ?> text-danger" style="transition: transform 0.2s;"></i>
                        </button>
                        
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

                <div class="card-body text-center pt-3 pb-4">
                    <p class="small mb-1 text-uppercase fw-bold" style="color:var(--color-taupe); letter-spacing:1px; font-size:11px;"><?= Security::escape($product['category_name'] ?? 'Aksesoris'); ?></p>
                    <h6 class="fw-bold mb-1 fs-5 text-truncate px-2" style="color:var(--color-text);">
                        <a href="<?= BASEURL; ?>/catalog/detail/<?= $product['id']; ?>" class="text-decoration-none text-reset hover-primary">
                            <?= Security::escape($product['name']); ?>
                        </a>
                    </h6>
                    
                    <!-- Customer Rating Snippet -->
                    <?php 
                        $prodRating = $data['ratings'][$product['id']] ?? null;
                        $hasRating = $prodRating && $prodRating['total'] > 0;
                    ?>
                    <div class="mb-2" style="font-size:12.5px;">
                        <?php if($hasRating): ?>
                            <span class="text-warning fw-semibold"><i class="bi bi-star-fill"></i> <?= number_format($prodRating['avg'], 1); ?></span>
                            <span class="text-muted small ms-1">(<?= $prodRating['total']; ?> ulasan)</span>
                        <?php else: ?>
                            <span class="text-muted opacity-75 small"><i class="bi bi-star me-1"></i>Belum ada ulasan</span>
                        <?php endif; ?>
                    </div>

                    <p class="fw-bold fs-5 mb-0" style="color:var(--color-primary);">Rp <?= number_format($product['price'], 0, ',', '.'); ?></p>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        
        <?php if(empty($data['products'])): ?>
            <div class="col-12 text-center py-5 my-5" data-aos="fade-in">
                <div class="display-1 text-muted mb-4 opacity-50"><i class="bi bi-search"></i></div>
                <h3 class="fw-bold" style="color:var(--color-text);">Oops! Aksesoris Tidak Ditemukan</h3>
                <p class="text-muted fs-5">Coba ubah kata kunci atau hapus filter kategori Anda.</p>
                <a href="<?= BASEURL; ?>/catalog" class="btn btn-outline-dark rounded-pill px-5 py-2 mt-3 fw-bold">Reset Pencarian</a>
            </div>
        <?php endif; ?>
    </div>
</div>
