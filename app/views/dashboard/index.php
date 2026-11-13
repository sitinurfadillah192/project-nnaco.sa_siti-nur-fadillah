<div class="card card-soft p-4 p-md-5 mb-4 border shadow-sm text-center rounded-4" style="background:var(--color-surface); border-color:var(--color-border) !important;">
    <div class="container-fluid py-4">
        <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3 shadow-sm" style="width:56px; height:56px; background:var(--color-primary); color:var(--color-accent);">
            <i class="bi bi-speedometer2 fs-4"></i>
        </div>
        <h1 class="display-5 fw-bold mb-2" style="color:var(--color-primary);">Admin & Staff Portal</h1>
        <p class="col-md-8 mx-auto fs-5 text-muted">Selamat datang kembali, <strong style="color:var(--color-text);"><?= Security::escape($_SESSION['user_name']); ?></strong>!</p>
        
        <div class="d-inline-flex align-items-center gap-2 px-3 py-2 rounded-pill mt-2 mb-4 border shadow-sm" style="background:var(--color-surface-soft); border-color:var(--color-border) !important;">
            <span class="small text-muted">Tingkat Akses:</span>
            <span class="badge rounded-pill px-3 py-1 text-uppercase" style="background:var(--color-primary); color:var(--color-accent); font-size:11px; letter-spacing:0.5px;">
                <?= Security::escape($_SESSION['user_role']); ?>
            </span>
        </div>
        
        <div class="mt-4 row justify-content-center g-4">
            
            <?php if(in_array($_SESSION['user_role'], ['admin', 'operator'])): ?>
            <div class="col-md-4 col-lg-3">
                <a href="<?= BASEURL; ?>/product" class="text-decoration-none text-reset">
                    <div class="card card-soft h-100 p-3 border shadow-sm hover-lift" style="background:var(--color-surface); border-color:var(--color-border) !important;">
                        <div class="card-body text-center p-3">
                            <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3 shadow-sm" style="width:48px; height:48px; background:var(--color-accent-soft); color:var(--color-primary);">
                                <i class="bi bi-box-seam fs-4"></i>
                            </div>
                            <h5 class="card-title fw-bold mb-1" style="color:var(--color-text);">Kelola Produk</h5>
                            <p class="card-text small text-muted mb-0">Tambah, edit, hapus katalog & inventori.</p>
                        </div>
                    </div>
                </a>
            </div>
            <?php endif; ?>

            <div class="col-md-4 col-lg-3">
                <a href="<?= BASEURL; ?>/adminorder" class="text-decoration-none text-reset">
                    <div class="card card-soft h-100 p-3 border shadow-sm hover-lift" style="background:var(--color-surface); border-color:var(--color-border) !important;">
                        <div class="card-body text-center p-3">
                            <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3 shadow-sm" style="width:48px; height:48px; background:var(--color-success-soft); color:var(--color-success);">
                                <i class="bi bi-receipt fs-4"></i>
                            </div>
                            <h5 class="card-title fw-bold mb-1" style="color:var(--color-text);">Pesanan Masuk</h5>
                            <p class="card-text small text-muted mb-0">Lihat histori & update status pengiriman.</p>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-md-4 col-lg-3">
                <a href="<?= BASEURL; ?>/review/admin" class="text-decoration-none text-reset">
                    <div class="card card-soft h-100 p-3 border shadow-sm hover-lift" style="background:var(--color-surface); border-color:var(--color-border) !important;">
                        <div class="card-body text-center p-3">
                            <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3 shadow-sm" style="width:48px; height:48px; background:rgba(216,191,163,0.25); color:var(--color-primary);">
                                <i class="bi bi-star-fill fs-4 text-warning"></i>
                            </div>
                            <h5 class="card-title fw-bold mb-1" style="color:var(--color-text);">Ulasan Pelanggan</h5>
                            <p class="card-text small text-muted mb-0">Pantau kepuasan, rating bintang & ulasan.</p>
                        </div>
                    </div>
                </a>
            </div>

            <?php if(in_array($_SESSION['user_role'], ['admin', 'owner'])): ?>
            <div class="col-md-4 col-lg-3">
                <a href="<?= BASEURL; ?>/ai" class="text-decoration-none text-reset">
                    <div class="card card-soft h-100 p-3 border shadow-sm hover-lift" style="background:var(--color-surface); border-color:var(--color-border) !important;">
                        <div class="card-body text-center p-3">
                            <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3 shadow-sm" style="width:48px; height:48px; background:var(--color-accent-soft); color:var(--color-primary);">
                                <i class="bi bi-graph-up-arrow fs-4"></i>
                            </div>
                            <h5 class="card-title fw-bold mb-1" style="color:var(--color-text);">AI Analytics</h5>
                            <p class="card-text small text-muted mb-0">Prediksi penjualan & analitik cerdas.</p>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-md-4 col-lg-3">
                <a href="<?= BASEURL; ?>/chatbot" class="text-decoration-none text-reset">
                    <div class="card card-soft h-100 p-3 border shadow-sm hover-lift" style="background:var(--color-surface); border-color:var(--color-border) !important;">
                        <div class="card-body text-center p-3">
                            <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3 shadow-sm" style="width:48px; height:48px; background:var(--color-info-soft); color:var(--color-info);">
                                <i class="bi bi-chat-dots-fill fs-4"></i>
                            </div>
                            <h5 class="card-title fw-bold mb-1" style="color:var(--color-text);">Riwayat Chatbot</h5>
                            <p class="card-text small text-muted mb-0">Pantau seluruh percakapan pengunjung.</p>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-md-4 col-lg-3">
                <a href="<?= BASEURL; ?>/user" class="text-decoration-none text-reset">
                    <div class="card card-soft h-100 p-3 border shadow-sm hover-lift" style="background:var(--color-surface); border-color:var(--color-border) !important;">
                        <div class="card-body text-center p-3">
                            <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3 shadow-sm" style="width:48px; height:48px; background:var(--color-warning-soft); color:var(--color-warning);">
                                <i class="bi bi-people-fill fs-4"></i>
                            </div>
                            <h5 class="card-title fw-bold mb-1" style="color:var(--color-text);">Manajemen User</h5>
                            <p class="card-text small text-muted mb-0">Kelola akun staf, peran/role, & customer.</p>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-md-4 col-lg-3">
                <a href="<?= BASEURL; ?>/settings/ai" class="text-decoration-none text-reset">
                    <div class="card card-soft h-100 p-3 border shadow-sm hover-lift" style="background:var(--color-surface); border-color:var(--color-border) !important;">
                        <div class="card-body text-center p-3">
                            <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3 shadow-sm" style="width:48px; height:48px; background:var(--color-surface-soft); color:var(--color-primary); border:1px solid var(--color-border);">
                                <i class="bi bi-gear-wide-connected fs-4"></i>
                            </div>
                            <h5 class="card-title fw-bold mb-1" style="color:var(--color-text);">Pengaturan Sistem</h5>
                            <p class="card-text small text-muted mb-0">Konfigurasi AI Provider, OTP, & Gateway.</p>
                        </div>
                    </div>
                </a>
            </div>
            <?php endif; ?>

        </div>
    </div>
</div>
