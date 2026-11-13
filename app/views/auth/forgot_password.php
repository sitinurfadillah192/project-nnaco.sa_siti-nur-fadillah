<div class="row justify-content-center py-4">
    <div class="col-md-6 col-lg-5">
        <div class="card shadow-soft rounded-4 border-0" style="background:var(--color-surface); border:1px solid var(--color-border) !important;">
            <div class="card-body p-4 p-md-5">
                <div class="text-center mb-4">
                    <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3 shadow-sm" style="width:52px; height:52px; background:var(--color-surface-soft); color:var(--color-primary); border: 1px solid var(--color-border);">
                        <i class="bi bi-key fs-4"></i>
                    </div>
                    <h3 class="fw-bold mb-1 text-dark" style="font-family: 'Cormorant Garamond', Georgia, serif; font-size: 2rem;">Lupa Password</h3>
                    <p class="text-muted small">Masukkan email Anda untuk menerima tautan pemulihan kata sandi.</p>
                </div>
                
                <?php if(!empty($data['error'])): ?>
                    <div class="alert alert-danger py-2 rounded-3 border-0 small mb-3"><?= Security::escape($data['error']); ?></div>
                <?php endif; ?>
                
                <?php if(!empty($data['success'])): ?>
                    <div class="alert alert-success py-2 rounded-3 border-0 small mb-3"><?= Security::escape($data['success']); ?></div>
                    <div class="alert alert-warning py-2 text-break rounded-3 border-0 small mb-3">
                        <strong>Simulasi Link:</strong> <br> Klik link berikut untuk reset: <br>
                        <a href="<?= $data['reset_link']; ?>" class="text-primary"><?= $data['reset_link']; ?></a>
                    </div>
                <?php endif; ?>
                
                <form action="<?= BASEURL; ?>/auth/forgot_password" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= Security::generateCsrfToken(); ?>">
                    
                    <div class="mb-4">
                        <label for="email" class="form-label fw-semibold text-dark">Alamat Email Terdaftar</label>
                        <input type="email" class="form-control" id="email" name="email" required placeholder="nama@email.com" style="border-radius:10px;">
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100 py-3 fw-semibold rounded-pill shadow-sm">Kirim Link Reset</button>
                    <div class="text-center mt-4">
                        <a href="<?= BASEURL; ?>/auth/login" class="text-decoration-none text-muted small"><i class="bi bi-arrow-left me-1"></i>Kembali ke Halaman Masuk</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
