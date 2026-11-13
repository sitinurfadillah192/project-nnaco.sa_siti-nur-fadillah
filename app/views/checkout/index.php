<div class="container py-5 min-vh-100">
    <div class="d-flex justify-content-between align-items-end mb-4" data-aos="fade-down">
        <div>
            <h2 class="fw-bold display-6 mb-1" style="color:var(--color-text);">Checkout</h2>
            <p class="text-muted fs-5 mb-0">Selesaikan pesanan Anda dengan aman.</p>
        </div>
    </div>

    <div class="row g-4">
        <!-- Form Pengiriman -->
        <div class="col-lg-7" data-aos="fade-up" data-aos-delay="100">
            <div class="card card-soft p-4 h-100 border" style="background:var(--color-surface); border-color:var(--color-border) !important;">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-4">
                        <div class="rounded-circle d-flex justify-content-center align-items-center fw-bold me-3 shadow-sm" style="width:36px; height:36px; background:var(--color-primary); color:var(--color-accent);">1</div>
                        <h4 class="fw-bold mb-0" style="color:var(--color-text);">Detail Pengiriman</h4>
                    </div>
                    
                    <form action="<?= BASEURL; ?>/checkout/process" method="POST" id="checkoutForm">
                        <!-- Nomor WhatsApp untuk Notifikasi -->
                        <div class="mb-4 position-relative">
                            <label class="form-label fw-bold d-flex justify-content-between align-items-center" style="color:var(--color-text);">
                                <span><i class="bi bi-whatsapp me-2 fs-5" style="color:var(--color-success);"></i>Nomor WhatsApp (Aktif)</span>
                                <span class="badge border px-2 py-1 rounded-pill small" style="background:var(--color-success-soft); color:var(--color-success); border-color:rgba(71,122,91,0.2) !important;">
                                    <i class="bi bi-bell-fill me-1"></i>Notifikasi Otomatis
                                </span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text rounded-start-4 border-end-0 px-3 fw-bold" style="background:var(--color-surface-soft); border-color:var(--color-border); color:var(--color-taupe);">
                                    <i class="bi bi-telephone"></i>
                                </span>
                                <input type="tel" name="phone" class="form-control rounded-end-4 p-3 shadow-none border-start-0 fs-6" required 
                                       placeholder="Contoh: 081234567890" 
                                       style="background:var(--color-surface-soft); border-color:var(--color-border); color:var(--color-text);"
                                       value="<?= !empty($data['user']['phone']) ? Security::escape($data['user']['phone']) : ''; ?>">
                            </div>
                            <div class="form-text text-muted mt-2">
                                <i class="bi bi-shield-check me-1" style="color:var(--color-success);"></i> Notifikasi status pesanan (sedang diproses, dikirim, no. resi kurir) akan otomatis dikirimkan ke WhatsApp Anda.
                            </div>
                        </div>

                        <!-- Alamat Lengkap -->
                        <div class="mb-4 position-relative">
                            <label class="form-label fw-bold" style="color:var(--color-text);"><i class="bi bi-geo-alt-fill me-2" style="color:var(--color-danger);"></i>Alamat Lengkap</label>
                            <textarea name="shipping_address" class="form-control rounded-4 p-3 shadow-none" rows="4" required placeholder="Masukkan alamat lengkap (Jalan, RT/RW, Desa, Kecamatan, Kota, Provinsi, Kode Pos)" style="background:var(--color-surface-soft); border-color:var(--color-border); color:var(--color-text);"></textarea>
                            <div class="form-text text-muted mt-2"><i class="bi bi-truck me-1" style="color:var(--color-taupe);"></i> Pastikan alamat diisi dengan detail agar kurir mudah menemukan lokasi Anda.</div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Ringkasan Pesanan -->
        <div class="col-lg-5" data-aos="fade-left" data-aos-delay="200">
            <div class="card card-soft p-4 h-100 border" style="background:var(--color-surface); border-color:var(--color-border) !important;">
                <div class="card-body d-flex flex-column">
                    <div class="d-flex align-items-center mb-4 border-bottom pb-3" style="border-color:var(--color-border) !important;">
                        <div class="rounded-circle d-flex justify-content-center align-items-center fw-bold me-3 shadow-sm" style="width:36px; height:36px; background:var(--color-accent); color:var(--color-primary);">2</div>
                        <h4 class="fw-bold mb-0" style="color:var(--color-text);">Ringkasan Pesanan</h4>
                    </div>
                    
                    <div class="overflow-auto mb-4" style="max-height: 250px;">
                        <ul class="list-group list-group-flush">
                            <?php foreach($data['cart'] as $item): ?>
                                <li class="list-group-item bg-transparent px-0 d-flex justify-content-between align-items-center border-bottom" style="border-color:var(--color-border) !important;">
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-3 me-3 d-flex justify-content-center align-items-center border" style="width:50px; height:50px; overflow:hidden; background:var(--color-surface-soft); border-color:var(--color-border) !important;">
                                            <?php $img = !empty($item['image']) ? BASEURL . '/uploads/products/' . Security::escape($item['image']) : 'https://images.unsplash.com/photo-1515562141207-7a88fb7ce338?w=100&q=80'; ?>
                                            <img src="<?= $img; ?>" class="w-100 h-100 object-fit-cover" alt="img">
                                        </div>
                                        <div>
                                            <h6 class="my-0 text-truncate fw-bold" style="max-width:150px; color:var(--color-text);"><?= Security::escape($item['name']); ?></h6>
                                            <small class="text-muted"><?= $item['quantity']; ?> x Rp <?= number_format($item['price'], 0, ',', '.'); ?></small>
                                        </div>
                                    </div>
                                    <span class="fw-bold" style="color:var(--color-text);">Rp <?= number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    
                    <div class="mt-auto pt-3 border-top" style="border-color:var(--color-border) !important;">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Subtotal</span>
                            <span class="fw-bold" style="color:var(--color-text);">Rp <?= number_format($data['total'], 0, ',', '.'); ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted">Biaya Pengiriman</span>
                            <span class="fw-bold" style="color:var(--color-success);">Gratis</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top" style="border-color:var(--color-border) !important;">
                            <span class="fw-bold fs-5" style="color:var(--color-text);">Total Tagihan</span>
                            <strong class="fs-3" style="color:var(--color-primary);">Rp <?= number_format($data['total'], 0, ',', '.'); ?></strong>
                        </div>
                    </div>

                    <button type="button" class="btn btn-primary w-100 py-3 rounded-pill fw-bold fs-5 mt-4 shadow-sm" onclick="document.getElementById('checkoutForm').submit();">Buat Pesanan <i class="bi bi-check2-circle ms-2"></i></button>
                </div>
            </div>
        </div>
    </div>
</div>
