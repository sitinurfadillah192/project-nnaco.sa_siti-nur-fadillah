<div class="row justify-content-center py-4">
    <div class="col-md-6 col-lg-5">
        <div class="card shadow-soft border-0 rounded-4" style="background:var(--color-surface); border:1px solid var(--color-border) !important;">
            <div class="card-body p-4 p-md-5">
                <div class="text-center mb-4">
                    <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3 shadow-sm" style="width:52px; height:52px; background:var(--color-surface-soft); color:var(--color-primary); border: 1px solid var(--color-border);">
                        <i class="bi bi-shield-lock fs-4"></i>
                    </div>
                    <h3 class="fw-bold mb-1 text-dark" style="font-family: 'Cormorant Garamond', Georgia, serif; font-size: 2rem;">Buat Password Baru</h3>
                    <p class="text-muted small">Tentukan password baru yang kuat untuk keamanan akun Anda.</p>
                </div>
                
                <?php if(!empty($data['error'])): ?>
                    <div class="alert alert-danger py-2 rounded-3 border-0 small mb-3"><?= Security::escape($data['error']); ?></div>
                <?php endif; ?>
                
                <?php if(!empty($data['success'])): ?>
                    <div class="alert alert-success py-2 rounded-3 border-0 small mb-3"><?= Security::escape($data['success']); ?></div>
                    <div class="text-center mt-3">
                        <a href="<?= BASEURL; ?>/auth/login" class="btn btn-primary w-100 rounded-pill py-2">Menuju Halaman Masuk</a>
                    </div>
                <?php else: ?>
                    <form action="<?= BASEURL; ?>/auth/reset_password/<?= Security::escape($data['token']); ?>" method="POST">
                        <input type="hidden" name="csrf_token" value="<?= Security::generateCsrfToken(); ?>">
                        
                        <div class="mb-3">
                            <label for="password" class="form-label fw-semibold text-dark">Password Baru</label>
                            <input type="password" class="form-control" id="password" name="password" required style="border-radius:10px;">
                        </div>

                        <div class="mb-4">
                            <label for="password_confirm" class="form-label fw-semibold text-dark">Konfirmasi Password Baru</label>
                            <input type="password" class="form-control" id="password_confirm" name="password_confirm" required style="border-radius:10px;">
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100 py-3 fw-semibold rounded-pill shadow-sm">Simpan Password Baru</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
