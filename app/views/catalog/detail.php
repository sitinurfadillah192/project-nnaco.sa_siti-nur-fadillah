<div class="container py-4 min-vh-100">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb mb-0 small">
            <li class="breadcrumb-item"><a href="<?= BASEURL; ?>/" class="text-decoration-none">Beranda</a></li>
            <li class="breadcrumb-item"><a href="<?= BASEURL; ?>/catalog" class="text-decoration-none">Katalog</a></li>
            <li class="breadcrumb-item"><a href="<?= BASEURL; ?>/catalog?category=<?= $data['product']['category_id']; ?>" class="text-decoration-none"><?= Security::escape($data['product']['category_name'] ?? 'Kategori'); ?></a></li>
            <li class="breadcrumb-item active text-truncate" aria-current="page" style="max-width:250px;"><?= Security::escape($data['product']['name']); ?></li>
        </ol>
    </nav>

    <!-- Product Showcase (2 Columns) -->
    <div class="row g-4 g-lg-5 mb-5 pb-3">
        <!-- Image Column -->
        <div class="col-lg-6">
            <div class="position-sticky" style="top: 100px;">
                <div class="rounded-4 overflow-hidden shadow-soft border position-relative" style="background:var(--color-surface); border-color:var(--color-border) !important; aspect-ratio: 1/1;">
                    <?php 
                        $img = !empty($data['product']['image']) 
                            ? BASEURL . '/uploads/products/' . Security::escape($data['product']['image']) 
                            : BASEURL . '/img/hero/hero_ring.jpg'; 
                    ?>
                    <img src="<?= $img; ?>" class="w-100 h-100 object-fit-cover" alt="<?= Security::escape($data['product']['name']); ?>" id="mainProductImage">
                    
                    <?php if($data['product']['stock'] < 5 && $data['product']['stock'] > 0): ?>
                        <span class="badge position-absolute shadow-sm" style="top: 20px; left: 20px; border-radius:10px; background:var(--color-warning); color:#fff; font-size:12px;">
                            <i class="bi bi-fire me-1"></i>Sisa <?= $data['product']['stock']; ?> Unit
                        </span>
                    <?php elseif($data['product']['stock'] == 0): ?>
                        <span class="badge position-absolute shadow-sm" style="top: 20px; left: 20px; border-radius:10px; background:var(--color-taupe); color:#fff; font-size:12px;">
                            Stok Habis
                        </span>
                    <?php endif; ?>
                </div>

                <!-- Trust Highlights -->
                <div class="row g-2 mt-3 text-center">
                    <div class="col-4">
                        <div class="p-2 rounded-3 border small" style="background:var(--color-surface); border-color:var(--color-border) !important;">
                            <i class="bi bi-gem text-primary d-block mb-1 fs-5"></i>
                            <span class="text-muted" style="font-size:11px;">100% Asli Handmade</span>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-2 rounded-3 border small" style="background:var(--color-surface); border-color:var(--color-border) !important;">
                            <i class="bi bi-shield-check text-success d-block mb-1 fs-5"></i>
                            <span class="text-muted" style="font-size:11px;">Garansi Kualitas</span>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-2 rounded-3 border small" style="background:var(--color-surface); border-color:var(--color-border) !important;">
                            <i class="bi bi-box-seam text-warning d-block mb-1 fs-5"></i>
                            <span class="text-muted" style="font-size:11px;">Kotak Kado Eksklusif</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Details Column -->
        <div class="col-lg-6">
            <span class="text-uppercase fw-bold small" style="color:var(--color-taupe); letter-spacing: 1.5px; font-size:12px;">
                <?= Security::escape($data['product']['category_name'] ?? 'Perhiasan Eksklusif'); ?>
            </span>
            
            <h1 class="fw-bold mb-2 text-dark mt-1" style="font-family: 'Cormorant Garamond', Georgia, serif; font-size: 2.6rem; line-height: 1.15;">
                <?= Security::escape($data['product']['name']); ?>
            </h1>

            <!-- Ratings & Quick Review Anchor -->
            <?php 
                $summary = $data['rating_summary'];
                $hasReviews = $summary['total_reviews'] > 0;
            ?>
            <div class="d-flex align-items-center gap-2 mb-3">
                <div class="text-warning small d-flex gap-1">
                    <?php 
                        $fullStars = floor($summary['avg_rating']);
                        for($s=1; $s<=5; $s++):
                    ?>
                        <i class="bi <?= ($s <= $fullStars) ? 'bi-star-fill' : 'bi-star'; ?>"></i>
                    <?php endfor; ?>
                </div>
                <span class="fw-bold text-dark small"><?= number_format($summary['avg_rating'], 1); ?></span>
                <span class="text-muted small">•</span>
                <a href="#reviewsSection" class="text-decoration-none small" style="color:var(--color-primary); font-weight:500;">
                    <?= $summary['total_reviews']; ?> Ulasan Pelanggan
                </a>
            </div>

            <!-- Price -->
            <div class="mb-4">
                <span class="display-6 fw-bold" style="color:var(--color-primary);">
                    Rp <?= number_format($data['product']['price'], 0, ',', '.'); ?>
                </span>
            </div>

            <!-- Description -->
            <div class="p-4 rounded-4 mb-4 border" style="background:var(--color-surface-soft); border-color:var(--color-border) !important;">
                <h6 class="fw-bold text-dark mb-2">Deskripsi Produk</h6>
                <p class="text-muted mb-0 lh-lg" style="font-size:14.5px;">
                    <?= nl2br(Security::escape($data['product']['description'] ?? 'Perhiasan eksklusif dengan keanggunan abadi, dirancang secara teliti menggunakan material berkualitas tinggi untuk menyempurnakan penampilan Anda di setiap momen istimewa.')); ?>
                </p>
            </div>

            <!-- Purchase Form -->
            <?php if($data['product']['stock'] > 0): ?>
            <form action="<?= BASEURL; ?>/cart/add" method="POST" class="mb-4">
                <input type="hidden" name="product_id" value="<?= $data['product']['id']; ?>">
                
                <div class="row g-3 align-items-center mb-4">
                    <div class="col-sm-4">
                        <label class="form-label fw-bold text-dark small mb-1">Jumlah</label>
                        <div class="input-group border rounded-pill overflow-hidden" style="border-color:var(--color-border) !important; background:var(--color-surface);">
                            <button type="button" class="btn btn-link text-decoration-none text-dark shadow-none px-3" onclick="let q=document.getElementById('buyQty'); if(parseInt(q.value)>1) q.value=parseInt(q.value)-1;">-</button>
                            <input type="number" name="quantity" id="buyQty" class="form-control text-center border-0 bg-transparent shadow-none fw-bold" value="1" min="1" max="<?= $data['product']['stock']; ?>">
                            <button type="button" class="btn btn-link text-decoration-none text-dark shadow-none px-3" onclick="let q=document.getElementById('buyQty'); if(parseInt(q.value)<<?= $data['product']['stock']; ?>) q.value=parseInt(q.value)+1;">+</button>
                        </div>
                    </div>
                    <div class="col-sm-8">
                        <label class="form-label text-muted small mb-1 d-block">Ketersediaan</label>
                        <span class="badge rounded-pill px-3 py-2 fw-semibold" style="background:rgba(71, 122, 91, 0.12); color:var(--color-success); border:1px solid rgba(71, 122, 91, 0.2);">
                            <i class="bi bi-check-circle-fill me-1"></i>Stok Tersedia (<?= $data['product']['stock']; ?> unit)
                        </span>
                    </div>
                </div>

                <div class="d-flex gap-3">
                    <button type="submit" class="btn btn-primary rounded-pill px-5 py-3 fw-bold shadow-sm flex-grow-1 hover-lift d-flex align-items-center justify-content-center gap-2">
                        <i class="bi bi-bag-plus fs-5"></i> Tambah ke Keranjang
                    </button>
                    
                    <button type="button" class="btn btn-outline-secondary rounded-circle d-flex align-items-center justify-content-center shadow-sm btn-wishlist <?= $data['in_wishlist'] ? 'active' : ''; ?>" data-id="<?= $data['product']['id']; ?>" style="width:52px; height:52px;" title="Wishlist">
                        <i class="bi <?= $data['in_wishlist'] ? 'bi-heart-fill' : 'bi-heart'; ?> text-danger fs-5"></i>
                    </button>
                </div>
            </form>
            <?php else: ?>
            <div class="alert alert-warning rounded-4 border-0 p-3 mb-4">
                <i class="bi bi-exclamation-circle-fill me-2"></i>Produk ini sedang habis. Silakan periksa kembali beberapa hari ke depan.
            </div>
            <?php endif; ?>

            <!-- Share & Guarantee -->
            <div class="d-flex align-items-center justify-content-between pt-3 border-top" style="border-color:var(--color-border) !important;">
                <span class="text-muted small">Kategori: <a href="<?= BASEURL; ?>/catalog?category=<?= $data['product']['category_id']; ?>" class="fw-semibold text-dark text-decoration-none"><?= Security::escape($data['product']['category_name'] ?? 'Umum'); ?></a></span>
                <span class="text-muted small"><i class="bi bi-lock-fill text-success me-1"></i>Transaksi Aman & Terenkripsi</span>
            </div>
        </div>
    </div>

    <!-- REVIEWS & RATINGS SECTION -->
    <section id="reviewsSection" class="pt-5 mt-4 border-top" style="border-color:var(--color-border) !important;">
        <div class="d-flex justify-content-between align-items-end mb-4 flex-wrap gap-3">
            <div>
                <span class="badge badge-soft mb-1 px-3 py-1">Customer Reviews</span>
                <h3 class="fw-bold mb-0 text-dark" style="font-family: 'Cormorant Garamond', Georgia, serif; font-size: 2.2rem;">
                    Penilaian & Ulasan Pembeli
                </h3>
            </div>
            
            <?php if($data['can_review']): ?>
                <button type="button" class="btn btn-primary rounded-pill px-4 py-2 shadow-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#productReviewModal">
                    <i class="bi bi-pencil-square me-1"></i> <?= $data['my_review'] ? 'Ubah Ulasan Saya' : 'Tulis Ulasan Produk' ?>
                </button>
            <?php endif; ?>
        </div>

        <!-- Rating Scorecard Breakdown -->
        <div class="card card-soft border-0 shadow-soft rounded-4 mb-4" style="background:var(--color-surface); border:1px solid var(--color-border) !important;">
            <div class="card-body p-4 p-md-5">
                <div class="row g-4 align-items-center">
                    <!-- Overall Score -->
                    <div class="col-md-4 text-center border-md-end" style="border-color:var(--color-border) !important;">
                        <h1 class="display-3 fw-bold mb-1" style="color:var(--color-primary); font-family: 'Cormorant Garamond', Georgia, serif;">
                            <?= number_format($summary['avg_rating'], 1); ?>
                        </h1>
                        <div class="text-warning fs-5 mb-2">
                            <?php for($s=1; $s<=5; $s++): ?>
                                <i class="bi <?= ($s <= round($summary['avg_rating'])) ? 'bi-star-fill' : 'bi-star'; ?>"></i>
                            <?php endfor; ?>
                        </div>
                        <p class="text-muted small mb-0">Berdasarkan <strong><?= $summary['total_reviews']; ?></strong> ulasan terverifikasi</p>
                    </div>

                    <!-- Star Distribution Bars -->
                    <div class="col-md-5">
                        <div class="d-flex flex-column gap-2">
                            <?php for($star=5; $star>=1; $star--): ?>
                                <?php 
                                    $cnt = $summary['counts'][$star] ?? 0;
                                    $pct = $summary['total_reviews'] > 0 ? round(($cnt / $summary['total_reviews']) * 100) : 0;
                                ?>
                                <div class="d-flex align-items-center gap-2 small">
                                    <span class="fw-semibold text-muted" style="width:30px;"><?= $star; ?> <i class="bi bi-star-fill text-warning"></i></span>
                                    <div class="progress flex-grow-1" style="height: 8px; background:var(--color-surface-soft); border-radius:10px;">
                                        <div class="progress-bar" role="progressbar" style="width: <?= $pct; ?>%; background:var(--color-primary);" aria-valuenow="<?= $pct; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                    <span class="text-muted text-end" style="width:35px; font-size:11px;"><?= $cnt; ?></span>
                                </div>
                            <?php endfor; ?>
                        </div>
                    </div>

                    <!-- User Action Box -->
                    <div class="col-md-3 text-center text-md-start ps-md-4">
                        <?php if($data['can_review']): ?>
                            <h6 class="fw-bold mb-1 text-dark">Anda Membeli Produk Ini</h6>
                            <p class="text-muted small mb-3">Bagikan kepuasan dan kualitas produk ini untuk calon pembeli lainnya.</p>
                            <button type="button" class="btn btn-outline-primary rounded-pill w-100 py-2 small fw-semibold" data-bs-toggle="modal" data-bs-target="#productReviewModal">
                                <?= $data['my_review'] ? 'Edit Ulasan Anda' : 'Beri Ulasan Sekarang' ?>
                            </button>
                        <?php elseif(isset($_SESSION['user_id'])): ?>
                            <h6 class="fw-bold mb-1 text-dark">Ingin Mengulas?</h6>
                            <p class="text-muted small mb-0">Ulasan hanya dapat diberikan setelah Anda memesan produk ini.</p>
                        <?php else: ?>
                            <h6 class="fw-bold mb-1 text-dark">Ulasan Terverifikasi</h6>
                            <p class="text-muted small mb-3">Masuk ke akun Anda untuk melihat status pesanan dan memberi ulasan.</p>
                            <a href="<?= BASEURL; ?>/auth/login" class="btn btn-outline-dark rounded-pill w-100 py-2 small fw-semibold">Masuk Akun</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Verified Reviews List -->
        <div class="row g-3">
            <?php if(!empty($data['reviews'])): ?>
                <?php foreach($data['reviews'] as $rev): ?>
                <div class="col-12">
                    <div class="card card-soft border-0 shadow-sm rounded-4 p-4" style="background:var(--color-surface); border:1px solid var(--color-border) !important;">
                        <div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
                            <div class="d-flex align-items-center gap-3">
                                <img src="https://ui-avatars.com/api/?name=<?= urlencode($rev['user_name']); ?>&background=D8BFA3&color=4B3B32" class="rounded-circle shadow-sm" width="46" height="46" alt="<?= Security::escape($rev['user_name']); ?>">
                                <div>
                                    <div class="d-flex align-items-center gap-2">
                                        <h6 class="fw-bold mb-0 text-dark"><?= Security::escape($rev['user_name']); ?></h6>
                                        <span class="badge rounded-pill px-2 py-1" style="background:rgba(71,122,91,0.12); color:var(--color-success); font-size:10px;">
                                            <i class="bi bi-patch-check-fill me-1"></i>Verified Buyer
                                        </span>
                                    </div>
                                    <small class="text-muted"><?= date('d M Y, H:i', strtotime($rev['created_at'])); ?></small>
                                </div>
                            </div>
                            
                            <div class="text-warning">
                                <?php for($s=1; $s<=5; $s++): ?>
                                    <i class="bi <?= ($s <= (int)$rev['rating']) ? 'bi-star-fill' : 'bi-star text-muted opacity-25'; ?>"></i>
                                <?php endfor; ?>
                                <span class="fw-bold text-dark small ms-1"><?= $rev['rating']; ?>/5</span>
                            </div>
                        </div>

                        <?php if(!empty($rev['comment'])): ?>
                            <p class="text-muted mb-0 lh-lg" style="font-size:14.5px;">
                                "<?= nl2br(Security::escape($rev['comment'])); ?>"
                            </p>
                        <?php else: ?>
                            <p class="text-muted fst-italic small mb-0">Pembeli memberikan rating bintang <?= $rev['rating']; ?> tanpa komentar tertulis.</p>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center py-5">
                    <div class="display-3 text-muted opacity-25 mb-3"><i class="bi bi-chat-heart"></i></div>
                    <h5 class="fw-bold text-dark">Belum Ada Ulasan</h5>
                    <p class="text-muted small">Jadilah yang pertama memiliki dan memberikan ulasan berharga untuk perhiasan ini!</p>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- RELATED PRODUCTS -->
    <?php if(!empty($data['related_products'])): ?>
    <section class="my-5 pt-4">
        <div class="d-flex justify-content-between align-items-end mb-4 border-bottom pb-3" style="border-color:var(--color-border) !important;">
            <div>
                <span class="badge badge-soft mb-1">Koleksi Terkait</span>
                <h4 class="fw-bold mb-0 text-dark" style="font-family: 'Cormorant Garamond', Georgia, serif; font-size: 2rem;">Perhiasan Serupa yang Mungkin Anda Sukai</h4>
            </div>
            <a href="<?= BASEURL; ?>/catalog?category=<?= $data['product']['category_id']; ?>" class="btn btn-outline-dark rounded-pill px-4 py-2 small fw-semibold">Lihat Semua</a>
        </div>
        
        <div class="row g-4">
            <?php foreach($data['related_products'] as $rel): ?>
                <?php if($rel['id'] == $data['product']['id']) continue; ?>
                <div class="col-md-4 col-sm-6">
                    <div class="card card-soft h-100 border rounded-4 overflow-hidden" style="background:var(--color-surface); border-color:var(--color-border) !important;">
                        <div class="product-img-wrapper" style="height: 220px; background:var(--color-surface-soft);">
                            <?php 
                                $rimg = !empty($rel['image']) ? BASEURL . '/uploads/products/' . Security::escape($rel['image']) : BASEURL . '/img/hero/hero_ring.jpg'; 
                            ?>
                            <a href="<?= BASEURL; ?>/catalog/detail/<?= $rel['id']; ?>" class="d-block w-100 h-100">
                                <img src="<?= $rimg; ?>" class="w-100 h-100 object-fit-cover" alt="<?= Security::escape($rel['name']); ?>">
                            </a>
                        </div>
                        <div class="card-body text-center p-3">
                            <h6 class="fw-bold mb-1 text-truncate">
                                <a href="<?= BASEURL; ?>/catalog/detail/<?= $rel['id']; ?>" class="text-decoration-none text-reset hover-primary">
                                    <?= Security::escape($rel['name']); ?>
                                </a>
                            </h6>
                            <p class="fw-bold mb-0" style="color:var(--color-primary);">Rp <?= number_format($rel['price'], 0, ',', '.'); ?></p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>
</div>

<!-- Modal Tulis / Edit Ulasan dari Halaman Detail Produk -->
<?php if($data['can_review']): ?>
<div class="modal fade" id="productReviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden" style="background:var(--color-surface); border:1px solid var(--color-border) !important;">
            <div class="modal-header border-0 py-3 px-4" style="background:var(--color-surface-soft); border-bottom:1px solid var(--color-border) !important;">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width:38px; height:38px; background:var(--color-primary); color:var(--color-accent);">
                        <i class="bi bi-star-fill"></i>
                    </div>
                    <div>
                        <h6 class="modal-title fw-bold mb-0 text-dark">Beri Nilai & Ulasan</h6>
                        <small class="text-muted d-block text-truncate" style="max-width:280px;"><?= Security::escape($data['product']['name']); ?></small>
                    </div>
                </div>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form action="<?= BASEURL; ?>/review/submit" method="POST">
                <input type="hidden" name="csrf_token" value="<?= Security::generateCsrfToken(); ?>">
                <input type="hidden" name="order_id" value="<?= $data['user_order_id']; ?>">
                <input type="hidden" name="product_id" value="<?= $data['product']['id']; ?>">
                <input type="hidden" name="redirect_to" value="catalog/detail/<?= $data['product']['id']; ?>">
                
                <div class="modal-body p-4">
                    <?php 
                        $curRating = $data['my_review'] ? (int)$data['my_review']['rating'] : 5;
                        $curComment = $data['my_review'] ? $data['my_review']['comment'] : '';
                    ?>
                    <div class="text-center mb-4">
                        <label class="form-label fw-bold text-dark d-block mb-2">Berapa bintang untuk perhiasan ini?</label>
                        
                        <div class="d-inline-flex gap-2 p-2 px-3 rounded-pill border" style="background:var(--color-surface-soft); border-color:var(--color-border) !important;">
                            <?php for($star=1; $star<=5; $star++): ?>
                                <label class="mb-0" style="cursor:pointer;" title="<?= $star ?> Bintang">
                                    <input type="radio" name="rating" value="<?= $star ?>" class="d-none detail-star-radio" <?= ($curRating === $star) ? 'checked' : ''; ?> required>
                                    <i class="bi <?= ($star <= $curRating) ? 'bi-star-fill text-warning' : 'bi-star text-muted opacity-50'; ?> fs-3 detail-star-icon" data-star="<?= $star ?>"></i>
                                </label>
                            <?php endfor; ?>
                        </div>
                        <div class="small fw-semibold mt-2 text-muted" id="detailRatingDesc">
                            <?= $curRating ?> dari 5 Bintang
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark">Ceritakan Pengalaman Anda</label>
                        <textarea name="comment" class="form-control" rows="4" placeholder="Bagikan bagaimana kualitas bahan, kilau perhiasan, dan kemasan paket yang Anda terima..." style="border-radius:12px;"><?= Security::escape($curComment); ?></textarea>
                        <div class="form-text small text-muted">Ulasan Anda akan langsung tampil di halaman produk ini.</div>
                    </div>
                </div>
                
                <div class="modal-footer border-0 p-4 pt-0 d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 py-2 shadow-sm fw-semibold">
                        <i class="bi bi-check-lg me-1"></i> <?= $data['my_review'] ? 'Perbarui Ulasan' : 'Kirim Ulasan' ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const detailRadios = document.querySelectorAll('.detail-star-radio');
    detailRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            const selectedVal = parseInt(this.value);
            const stars = document.querySelectorAll('.detail-star-icon');
            const desc = document.getElementById('detailRatingDesc');
            
            stars.forEach(s => {
                const sVal = parseInt(s.getAttribute('data-star'));
                if (sVal <= selectedVal) {
                    s.className = 'bi bi-star-fill text-warning fs-3 detail-star-icon';
                } else {
                    s.className = 'bi bi-star text-muted opacity-50 fs-3 detail-star-icon';
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
<?php endif; ?>
