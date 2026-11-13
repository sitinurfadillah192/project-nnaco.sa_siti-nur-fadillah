<!-- Hero Full Width -->
<section class="container-fluid px-0 mb-5 position-relative overflow-hidden" style="background: linear-gradient(135deg, var(--color-surface-soft) 0%, var(--color-background) 100%); min-height: 85vh; display: flex; align-items: center; border-bottom: 1px solid var(--color-border);">
    <!-- Abstract shapes & Glass Effects -->
    <div style="position:absolute; top:-10%; right:-5%; width:50vw; height:50vw; background:var(--color-accent); border-radius:50%; filter:blur(140px); opacity:0.25; z-index:0;"></div>
    <div style="position:absolute; bottom:10%; left:-10%; width:40vw; height:40vw; background:var(--color-border); border-radius:50%; filter:blur(120px); opacity:0.3; z-index:0;"></div>
    
    <div class="container position-relative" style="z-index: 1;">
        <div class="row align-items-center g-5">
            <div class="col-lg-6 text-center text-lg-start" data-aos="fade-right" data-aos-duration="1200">
                <div class="d-inline-flex align-items-center rounded-pill px-3 py-2 mb-4 shadow-sm border" style="background:var(--color-surface); border-color:var(--color-border) !important;">
                    <span class="badge rounded-pill me-2 px-2 py-1" style="background:var(--color-accent); color:var(--color-primary); font-weight:600;">New</span>
                    <span class="small fw-medium" style="color:var(--color-text);">Koleksi Musim Semi 2026 telah tiba! ✨</span>
                </div>
                
                <h1 class="display-3 fw-bold mb-4" style="color:var(--color-text); letter-spacing: -1.5px; line-height: 1.15;">
                    Perhiasan <span class="position-relative fst-italic" style="color: var(--color-taupe);">Handmade</span><br>Untuk Momen Terbaikmu.
                </h1>
                
                <p class="lead mb-5" style="color:var(--color-text-secondary); font-weight: 400; max-width: 500px; margin: 0 auto 0 0; font-size:1.1rem;">
                    Ekspresikan aura cantik, anggun, dan feminin melalui koleksi kado dan aksesoris eksklusif yang dibuat dengan sentuhan kemewahan lembut.
                </p>
                
                <div class="d-flex justify-content-center justify-content-lg-start gap-3">
                    <a href="<?= BASEURL; ?>/catalog" class="btn btn-primary px-5 py-3 fs-5 shadow-sm rounded-pill fw-bold hover-lift d-flex align-items-center gap-2">
                        Belanja Sekarang <i class="bi bi-arrow-right"></i>
                    </a>
                </div>

                <!-- Mobile Hero Showcase (< 992px) -->
                <div class="d-lg-none mt-4 pt-2 text-center" data-aos="fade-up">
                    <div class="position-relative d-inline-block">
                        <img src="<?= BASEURL; ?>/img/hero/hero_model.jpg" class="shadow-soft img-fluid" style="max-height: 340px; border-radius: 120px 120px 24px 24px; border: 5px solid var(--color-surface); object-fit: cover;" alt="Model Perhiasan NNACOSA">
                        <div class="position-absolute bottom-0 start-50 translate-middle-x mb-3 badge rounded-pill px-3 py-2 shadow-sm d-flex align-items-center gap-2" style="background:var(--color-surface); color:var(--color-text); border:1px solid var(--color-border); font-size:12px;">
                            <i class="bi bi-gem" style="color:var(--color-primary);"></i> Koleksi Perhiasan Eksklusif
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-6 d-none d-lg-block position-relative" data-aos="fade-left" data-aos-duration="1200">
                <!-- Aesthetic Collage -->
                <div class="position-relative d-flex justify-content-center align-items-center" style="height: 560px;">
                    <!-- Ambient Glow Behind Model -->
                    <div style="position:absolute; width: 320px; height: 380px; background: var(--color-accent); border-radius: 50%; filter: blur(80px); opacity: 0.35; right: 20px; top: 40px; z-index: 1;"></div>

                    <!-- Gambar Kanan Atas / Utama (Model Perhiasan) -->
                    <div class="position-absolute shadow-lg hover-lift overflow-hidden" style="width: 330px; height: 450px; border-radius: 160px 160px 30px 30px; right: 15px; top: 15px; z-index:2; border: 6px solid var(--color-surface); transition: transform 0.5s cubic-bezier(0.165, 0.84, 0.44, 1);">
                        <img src="<?= BASEURL; ?>/img/hero/hero_model.jpg" class="w-100 h-100 object-fit-cover" alt="Koleksi Perhiasan NNACOSA">
                    </div>

                    <!-- Gambar Kiri Bawah (Macro Detail Cincin Permata) -->
                    <div class="position-absolute shadow-lg hover-lift overflow-hidden" style="width: 240px; height: 260px; border-radius: 30px 110px 30px 30px; left: 15px; bottom: 35px; z-index:3; border: 6px solid var(--color-surface); transition: transform 0.5s cubic-bezier(0.165, 0.84, 0.44, 1);">
                        <img src="<?= BASEURL; ?>/img/hero/hero_ring.jpg" class="w-100 h-100 object-fit-cover" alt="Detail Cincin Mewah">
                    </div>
                    
                    <!-- Floating Badge: Artisan Handcrafted -->
                    <div class="position-absolute rounded-4 shadow-soft p-3 d-flex align-items-center gap-3 border" style="background:var(--color-surface); border-color:var(--color-border) !important; top: 110px; left: -25px; z-index:4; backdrop-filter: blur(8px); animation: float 4s ease-in-out infinite;">
                        <div class="rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width:44px; height:44px; background:var(--color-primary); color:var(--color-accent); font-size:18px;">
                            <i class="bi bi-gem"></i>
                        </div>
                        <div>
                            <h6 class="mb-0 fw-bold" style="color:var(--color-text); font-size:14px; letter-spacing:0.3px;">Artisan Handcrafted</h6>
                            <small class="text-muted" style="font-size:12px;">100% Original Jewelry</small>
                        </div>
                    </div>

                    <!-- Floating Badge 2: Rating & Ulasan Pelanggan -->
                    <div class="position-absolute rounded-pill shadow-soft px-3 py-2 d-flex align-items-center gap-2 border" style="background:var(--color-surface); border-color:var(--color-border) !important; bottom: 25px; right: 5px; z-index:4;">
                        <div class="d-flex text-warning small">
                            <i class="bi bi-star-fill"></i>
                        </div>
                        <span class="fw-bold small" style="color:var(--color-text); font-size:13px;">4.9 / 5</span>
                        <span class="text-muted small" style="font-size:11px;">(1.2k+ Ulasan)</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Kategori Populer (Modern Bubbles) -->
<section class="container mb-5 pb-5">
    <div class="text-center mb-5" data-aos="fade-up">
        <h4 class="fw-bold" style="color:var(--color-text);">Kategori Terfavorit</h4>
    </div>
    <div class="d-flex justify-content-center flex-wrap gap-4 gap-md-5" data-aos="zoom-in" data-aos-delay="100">
        <?php 
        $cats = ['Kalung', 'Cincin', 'Gelang', 'Anting', 'Kado Spesial'];
        $icons = ['bi-gem', 'bi-record-circle', 'bi-watch', 'bi-stars', 'bi-gift-fill'];
        foreach($cats as $i => $c): ?>
            <a href="<?= BASEURL; ?>/catalog?category=<?= $i+1; ?>" class="text-decoration-none text-center hover-lift category-bubble">
                <div class="shadow-sm rounded-circle d-flex align-items-center justify-content-center mb-3 mx-auto" style="width: 90px; height: 90px; transition: 0.3s; color:var(--color-text); font-size:2rem; border: 1px solid var(--color-border); background:var(--color-surface);">
                    <i class="bi <?= $icons[$i]; ?>"></i>
                </div>
                <span class="fw-bold small" style="color:var(--color-text); letter-spacing: 0.5px;"><?= $c; ?></span>
            </a>
        <?php endforeach; ?>
    </div>
</section>

<!-- Promo Mid Banner -->
<section class="container mb-5 pb-5" data-aos="fade-up">
    <div class="rounded-4 overflow-hidden position-relative shadow-soft hover-lift" style="background: linear-gradient(135deg, #4B3B32 0%, #302722 100%); min-height: 260px; display: flex; align-items: center; border: 1px solid var(--color-border);">
        <!-- Image background for right half on desktop -->
        <div class="position-absolute top-0 end-0 h-100 w-50 d-none d-md-block" style="background-image: url('<?= BASEURL; ?>/img/hero/promo_banner.jpg'); background-size: cover; background-position: center; clip-path: polygon(15% 0, 100% 0, 100% 100%, 0 100%);"></div>
        
        <div class="p-4 p-md-5 position-relative z-1 w-100 w-md-50 ms-md-4 text-center text-md-start">
            <span class="badge rounded-pill px-3 py-2 shadow-sm border mb-3" style="background:var(--color-accent); color:var(--color-primary); border-color:var(--color-accent) !important;"><i class="bi bi-lightning-fill"></i> Flash Sale Weekend</span>
            <h2 class="fw-bold text-white display-5 mb-3" style="letter-spacing: -1px;">Diskon Ekstra 50%</h2>
            <p class="text-white opacity-75 mb-4 fs-5">Untuk koleksi cincin resin dan kalung titanium edisi terbatas minggu ini.</p>
            <a href="<?= BASEURL; ?>/catalog" class="btn btn-primary-soft rounded-pill px-5 py-3 fw-bold shadow-sm hover-lift">Klaim Promo Sekarang <i class="bi bi-arrow-right ms-2"></i></a>
        </div>
    </div>
</section>

<!-- Produk Terbaru -->
<section class="container mb-5 pb-4">
    <div class="d-flex justify-content-between align-items-end mb-4 border-bottom pb-3" style="border-color:var(--color-border) !important;" data-aos="fade-right">
        <div>
            <h3 class="fw-bold mb-1" style="color:var(--color-text);">Koleksi Terbaru</h3>
            <p class="text-muted mb-0">Baru saja selesai dibuat dari studio kami.</p>
        </div>
        <a href="<?= BASEURL; ?>/catalog" class="btn btn-outline-dark rounded-pill px-4 py-2 fw-medium d-none d-sm-inline-block hover-lift">Lihat Semua</a>
    </div>
    
    <div class="row g-4">
        <?php if(!empty($data['latest_products'])): ?>
            <?php foreach($data['latest_products'] as $idx => $prod): ?>
            <div class="col-md-3 col-6" data-aos="fade-up" data-aos-delay="<?= ($idx+1)*100 ?>">
                <div class="card card-soft h-100 position-relative border" style="border-color:var(--color-border) !important; background:var(--color-surface);">
                    <span class="badge position-absolute shadow-sm" style="top:12px; left:12px; z-index:10; border-radius:10px; font-weight:600; background:var(--color-accent-soft); color:var(--color-primary); border:1px solid var(--color-border);"><?= Security::escape($prod['category_name'] ?? 'NEW'); ?></span>
                    <div class="product-img-wrapper" style="height: 240px; background:var(--color-surface-soft);">
                        <?php 
                            $imgSrc = !empty($prod['image']) 
                                ? BASEURL . '/uploads/products/' . Security::escape($prod['image']) 
                                : BASEURL . '/img/hero/hero_ring.jpg';
                        ?>
                        <a href="<?= BASEURL; ?>/catalog/detail/<?= $prod['id']; ?>" class="d-block w-100 h-100">
                            <img src="<?= $imgSrc; ?>" class="w-100 h-100" style="object-fit: cover;" alt="<?= Security::escape($prod['name']); ?>">
                        </a>
                        <div class="quick-action-btns">
                            <a href="<?= BASEURL; ?>/catalog/detail/<?= $prod['id']; ?>" class="btn btn-primary-soft rounded-pill px-4 shadow-sm fw-bold d-flex align-items-center"><i class="bi bi-eye me-1"></i> Detail</a>
                        </div>
                    </div>
                    <div class="card-body text-center p-3 pt-3 pb-4">
                        <h6 class="fw-bold mb-1 text-truncate" style="font-size:15px; color:var(--color-text);">
                            <a href="<?= BASEURL; ?>/catalog/detail/<?= $prod['id']; ?>" class="text-decoration-none text-reset hover-primary">
                                <?= Security::escape($prod['name']); ?>
                            </a>
                        </h6>
                        
                        <?php 
                            $rInfo = $data['ratings'][$prod['id']] ?? null;
                            $hasR = $rInfo && $rInfo['total'] > 0;
                        ?>
                        <div class="small mb-2" style="font-size:12px;">
                            <?php if($hasR): ?>
                                <span class="text-warning fw-semibold"><i class="bi bi-star-fill"></i> <?= number_format($rInfo['avg'], 1); ?></span>
                                <span class="text-muted ms-1">(<?= $rInfo['total']; ?>)</span>
                            <?php else: ?>
                                <span class="text-muted opacity-75"><i class="bi bi-star me-1"></i>Belum ada ulasan</span>
                            <?php endif; ?>
                        </div>

                        <p class="fw-bold mb-0 fs-5" style="color:var(--color-primary);">Rp <?= number_format((float)$prod['price'], 0, ',', '.'); ?></p>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <!-- Fallback Mock -->
            <?php for($i=1; $i<=4; $i++): ?>
            <div class="col-md-3 col-6" data-aos="fade-up" data-aos-delay="<?= $i*100 ?>">
                <div class="card card-soft h-100 position-relative border" style="border-color:var(--color-border) !important; background:var(--color-surface);">
                    <span class="badge position-absolute shadow-sm" style="top:12px; left:12px; z-index:10; border-radius:10px; font-weight:600; background:var(--color-accent-soft); color:var(--color-primary); border:1px solid var(--color-border);">NEW</span>
                    <div class="product-img-wrapper" style="height: 240px; background:var(--color-surface-soft);">
                        <img src="<?= BASEURL; ?>/img/hero/hero_ring.jpg" class="w-100 h-100" style="object-fit: cover;" alt="Produk">
                        <div class="quick-action-btns">
                            <a href="<?= BASEURL; ?>/catalog" class="btn btn-primary-soft rounded-pill px-4 shadow-sm fw-bold d-flex align-items-center"><i class="bi bi-cart-plus me-1"></i> Beli</a>
                        </div>
                    </div>
                    <div class="card-body text-center p-4">
                        <h6 class="fw-bold mb-2 text-truncate" style="font-size:15px; color:var(--color-text);">Perhiasan Eksklusif NNACOSA</h6>
                        <p class="fw-bold mb-0 fs-5" style="color:var(--color-primary);">Rp 250.000</p>
                    </div>
                </div>
            </div>
            <?php endfor; ?>
        <?php endif; ?>
    </div>
</section>

<!-- AI Recommendation Section (Exclusive Feature) -->
<section class="container mb-5 pb-5">
    <div class="rounded-5 p-4 p-md-5 position-relative shadow-soft overflow-hidden border" style="background-color: var(--color-surface); border-color: var(--color-border) !important;" data-aos="zoom-in">
        <!-- AI Glowing Orbs -->
        <div style="position:absolute; top:-50px; right:-50px; width:250px; height:250px; background:var(--color-accent); border-radius:50%; filter:blur(70px); z-index:0; opacity:0.35;"></div>
        <div style="position:absolute; bottom:-50px; left:-50px; width:200px; height:200px; background:var(--color-border); border-radius:50%; filter:blur(60px); z-index:0; opacity:0.35;"></div>
        
        <div class="row align-items-center position-relative z-1 g-4">
            <div class="col-lg-5 text-center text-lg-start mb-4 mb-lg-0 pe-lg-4">
                <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-4 shadow-sm" style="width:70px; height:70px; font-size:28px; background:var(--color-primary); color:var(--color-accent);">
                    <i class="bi bi-magic"></i>
                </div>
                <h2 class="fw-bold mb-3" style="color:var(--color-text);">Rekomendasi Pintar AI</h2>
                <p class="text-muted fs-5 lh-base mb-4">Tidak yakin aksesoris mana yang cocok? Teknologi AI kami dapat menganalisa preferensi gaya Anda dan memberikan saran perhiasan yang paling memancarkan auramu.</p>
                <a href="<?= BASEURL; ?>/catalog" class="btn btn-primary rounded-pill px-5 py-3 fw-bold shadow-sm hover-lift"><i class="bi bi-robot me-2"></i> Tanya AI Sekarang</a>
            </div>
            <div class="col-lg-7">
                <div class="row g-3">
                    <?php 
                        $aiSamples = [
                            ['img' => BASEURL . '/img/hero/hero_ring.jpg', 'title' => 'Cincin Royal Gemstone', 'match' => '98% Cocok'],
                            ['img' => BASEURL . '/img/hero/hero_model.jpg', 'title' => 'Kalung Mutiara Aurelia', 'match' => '96% Cocok'],
                            ['img' => BASEURL . '/img/hero/promo_banner.jpg', 'title' => 'Set Gelang Gold Artisan', 'match' => '94% Cocok']
                        ];
                        foreach($aiSamples as $idx => $sample): 
                    ?>
                    <div class="col-4">
                        <div class="card border-0 bg-transparent text-center hover-lift cursor-pointer">
                            <div class="rounded-4 shadow-sm overflow-hidden mb-3 border" style="aspect-ratio:1/1; border-color:var(--color-border) !important;">
                                <img src="<?= $sample['img']; ?>" class="w-100 h-100 object-fit-cover" alt="<?= $sample['title']; ?>">
                            </div>
                            <h6 class="fw-bold mb-1 text-truncate" style="font-size:13px; color:var(--color-text);"><?= $sample['title']; ?></h6>
                            <small class="fw-bold" style="color:var(--color-taupe);"><?= $sample['match']; ?></small>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Testimoni Customer (Adelina Soft Luxury Cards) -->
<section class="container mb-5 pb-5">
    <div class="text-center mb-5" data-aos="fade-up">
        <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill mb-3 border shadow-sm" style="background:var(--color-surface); border-color:var(--color-border) !important;">
            <i class="bi bi-heart-fill" style="color:var(--color-primary); font-size:12px;"></i>
            <span class="small fw-semibold" style="color:var(--color-text);">Kisah Pelanggan</span>
        </div>
        <h2 class="fw-bold mb-2" style="font-family: 'Cormorant Garamond', Georgia, serif; font-size: 2.5rem; color:var(--color-text);">Disukai & Dipercaya Pelanggan</h2>
        <p class="text-muted small mx-auto" style="max-width: 500px;">Cerita manis dan pengalaman berbelanja aksesoris eksklusif dari wanita anggun di seluruh Indonesia.</p>
    </div>
    
    <div class="row g-4">
        <?php 
        $displayReviews = [];
        if (!empty($data['featured_reviews'])) {
            foreach ($data['featured_reviews'] as $fr) {
                $displayReviews[] = [
                    'nama' => $fr['user_name'],
                    'kota' => 'Pelanggan Terverifikasi',
                    'teks' => !empty($fr['comment']) ? $fr['comment'] : 'Produk perhiasan sangat berkualitas dan pengiriman sangat aman serta cepat.',
                    'rating' => (int)$fr['rating'],
                    'item' => $fr['product_name'] ?? 'Perhiasan NNACOSA'
                ];
            }
        }
        // Fallback default jika ulasan database masih sedikit (< 3)
        $defaultReviews = [
            [
                'nama' => 'Sarah Anggita', 
                'kota' => 'Jakarta Selatan',
                'teks' => 'Gelangnya super cantik dan detail ukirannya sangat halus! Packagingnya rapi seperti kado mewah dengan pita satin. Sangat direkomendasikan untuk kado sahabat tersayang.',
                'rating' => 5,
                'item' => 'Gelang Mutiara Soft'
            ],
            [
                'nama' => 'Nadia Maulida', 
                'kota' => 'Surabaya',
                'teks' => 'Beli cincin resin disini gak pernah nyesel. Warnanya lembut, kilaunya tahan lama, dan sangat nyaman dipakai seharian untuk outfit kasual maupun formal. The best boutique!',
                'rating' => 5,
                'item' => 'Cincin Resin Aesthetic'
            ],
            [
                'nama' => 'Bella Pratiwi', 
                'kota' => 'Bandung',
                'teks' => 'Pengiriman super cepat dan admin chatbotnya sangat membantu dalam memberikan rekomendasi ukuran cincin yang pas di jari. Pasti akan pesan lagi untuk koleksi lainnya!',
                'rating' => 5,
                'item' => 'Kalung Titanium Rose Gold'
            ]
        ];
        
        $finalReviews = !empty($displayReviews) ? array_slice(array_merge($displayReviews, $defaultReviews), 0, 3) : $defaultReviews;
        
        foreach($finalReviews as $i => $rev): ?>
        <div class="col-md-4" data-aos="fade-up" data-aos-delay="<?= ($i+1)*100 ?>">
            <div class="card card-soft h-100 p-4 border position-relative rounded-4" style="background:var(--color-surface); border-color:var(--color-border) !important;">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="d-flex text-warning small gap-1">
                        <?php for($s=0; $s<$rev['rating']; $s++): ?>
                            <i class="bi bi-star-fill"></i>
                        <?php endfor; ?>
                    </div>
                    <span class="badge rounded-pill px-2 py-1" style="background:rgba(71, 122, 91, 0.12); color:var(--color-success); font-size:11px; font-weight:600;">
                        <i class="bi bi-patch-check-fill me-1"></i>Verified Buyer
                    </span>
                </div>
                
                <p class="mb-4 lh-lg" style="font-size:14.5px; color:var(--color-text); font-style:italic;">
                    "<?= Security::escape($rev['teks']); ?>"
                </p>
                
                <div class="mt-auto d-flex align-items-center gap-3 border-top pt-3" style="border-color:var(--color-border) !important;">
                    <img src="https://ui-avatars.com/api/?name=<?= urlencode($rev['nama']) ?>&background=D8BFA3&color=4B3B32" class="rounded-circle shadow-sm" width="44" height="44" alt="<?= Security::escape($rev['nama']) ?>">
                    <div>
                        <h6 class="fw-bold mb-0" style="color:var(--color-text); font-size:14px;"><?= Security::escape($rev['nama']); ?></h6>
                        <small class="text-muted" style="font-size:12px;"><?= Security::escape($rev['kota']); ?> • Membeli <span class="fw-semibold text-dark"><?= Security::escape($rev['item']); ?></span></small>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- Instagram Aesthetic Curated Showcase -->
<section class="container mb-5 pb-5">
    <div class="text-center mb-5" data-aos="fade-up">
        <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill mb-3 border shadow-sm" style="background:var(--color-surface); border-color:var(--color-border) !important;">
            <i class="bi bi-instagram" style="color:var(--color-primary);"></i>
            <span class="small fw-semibold" style="color:var(--color-text);">@nnaco_sa</span>
        </div>
        <h2 class="fw-bold mb-2" style="font-family: 'Cormorant Garamond', Georgia, serif; font-size: 2.5rem; color:var(--color-text);">Inspirasi Kilau Setiap Hari</h2>
        <p class="text-muted small mx-auto" style="max-width: 520px;">Temukan inspirasi padu padan harian dan momen berharga bersama NNACOSA. Bagikan gayamu dengan tag <span class="fw-semibold text-dark">#NnacoJewelry</span>.</p>
    </div>

    <!-- 6 Unique Curated Lifestyle Cards -->
    <div class="row g-3 g-md-4">
        <?php 
        $instaFeeds = [
            ['img' => BASEURL . '/img/instagram/feed_1.jpg', 'title' => 'Stacked Rings', 'likes' => '342'],
            ['img' => BASEURL . '/img/instagram/feed_2.jpg', 'title' => 'Aura Gift Box', 'likes' => '518'],
            ['img' => BASEURL . '/img/instagram/feed_3.jpg', 'title' => 'Dainty Huggies', 'likes' => '295'],
            ['img' => BASEURL . '/img/instagram/feed_4.jpg', 'title' => 'Pearl Bracelet', 'likes' => '420'],
            ['img' => BASEURL . '/img/instagram/feed_5.jpg', 'title' => 'Royal Gemstone', 'likes' => '684'],
            ['img' => BASEURL . '/img/instagram/feed_6.jpg', 'title' => 'Aurelia Pearl', 'likes' => '892'],
        ];
        foreach($instaFeeds as $idx => $feed): ?>
        <div class="col-6 col-md-4 col-lg-2" data-aos="zoom-in" data-aos-delay="<?= ($idx+1)*60 ?>">
            <div class="card border-0 rounded-4 shadow-soft overflow-hidden position-relative hover-lift" style="aspect-ratio: 1/1; border: 1px solid var(--color-border) !important; background:var(--color-surface);">
                <img src="<?= $feed['img']; ?>" class="w-100 h-100 object-fit-cover" alt="<?= $feed['title']; ?>" style="transition: transform 0.6s cubic-bezier(0.165, 0.84, 0.44, 1);">
                <div class="position-absolute w-100 h-100 top-0 start-0 d-flex flex-column align-items-center justify-content-center text-white p-2" 
                     style="background: linear-gradient(to top, rgba(75, 59, 50, 0.8) 0%, rgba(75, 59, 50, 0.2) 100%); opacity:0; transition: opacity 0.3s ease; cursor:pointer;" 
                     onmouseover="this.style.opacity='1'; this.previousElementSibling.style.transform='scale(1.08)';" 
                     onmouseout="this.style.opacity='0'; this.previousElementSibling.style.transform='scale(1)';">
                    <i class="bi bi-instagram fs-4 mb-1 text-white"></i>
                    <span class="fw-semibold small text-center" style="font-size:12px;"><?= $feed['title']; ?></span>
                    <small class="opacity-75" style="font-size:11px;"><i class="bi bi-heart-fill text-danger me-1"></i><?= $feed['likes']; ?></small>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Follow Instagram CTA -->
    <div class="text-center mt-4 pt-3" data-aos="fade-up">
        <a href="https://instagram.com" target="_blank" class="btn btn-outline-primary rounded-pill px-4 py-2 fw-semibold shadow-sm d-inline-flex align-items-center gap-2">
            <i class="bi bi-instagram"></i> Ikuti @nnaco_sa di Instagram
        </a>
    </div>
</section>

<!-- Scoped CSS Utilities -->
<style>
    @keyframes float {
        0% { transform: translateY(0px); }
        50% { transform: translateY(-15px); }
        100% { transform: translateY(0px); }
    }
</style>
