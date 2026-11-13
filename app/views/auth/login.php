<div class="row justify-content-center py-4">
    <div class="col-md-6 col-lg-4">
        <div class="card card-soft border shadow-sm rounded-4" style="background:var(--color-surface); border-color:var(--color-border) !important;">
            <div class="card-body p-4 p-md-5">
                <div class="text-center mb-4">
                    <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3 shadow-sm" style="width:48px; height:48px; background:var(--color-primary); color:var(--color-accent);">
                        <i class="bi bi-gem fs-5"></i>
                    </div>
                    <h3 class="card-title fw-bold mb-1" style="color:var(--color-text);">Masuk ke Akun</h3>
                    <p class="text-muted small mb-0">Nikmati pengalaman belanja perhiasan eksklusif</p>
                </div>
                
                <?php if(!empty($data['error'])): ?>
                    <div class="alert alert-danger py-2 rounded-3 small mb-3">
                        <i class="bi bi-exclamation-circle me-1"></i><?= Security::escape($data['error']); ?>
                    </div>
                <?php endif; ?>
                
                <form action="<?= BASEURL; ?>/auth/login" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= Security::generateCsrfToken(); ?>">
                    
                    <div class="mb-3">
                        <label for="email" class="form-label fw-semibold small" style="color:var(--color-text);">Alamat Email</label>
                        <input type="email" class="form-control rounded-3" id="email" name="email" required placeholder="nama@email.com">
                    </div>
                    
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label for="password" class="form-label fw-semibold small mb-0" style="color:var(--color-text);">Password</label>
                            <a href="<?= BASEURL; ?>/auth/forgot_password" class="text-decoration-none small" style="color:var(--color-taupe);">Lupa Password?</a>
                        </div>
                        <input type="password" class="form-control rounded-3" id="password" name="password" required placeholder="Masukkan password">
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100 py-3 fw-bold rounded-pill shadow-sm">Masuk</button>
                    
                    <div class="text-center mt-4">
                        <span class="text-muted small">Belum punya akun?</span>
                        <a href="<?= BASEURL; ?>/auth/register" class="text-decoration-none small fw-bold ms-1" style="color:var(--color-primary);">Daftar di sini</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
