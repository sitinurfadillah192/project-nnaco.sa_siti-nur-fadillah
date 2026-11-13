<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Breadcrumbs -->
            <nav aria-label="breadcrumb" class="mb-3">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= BASEURL; ?>/dashboard" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= BASEURL; ?>/user" class="text-decoration-none">Manajemen User</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Tambah Pengguna</li>
                </ol>
            </nav>

            <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-2">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-primary bg-opacity-10 p-3 me-3 text-primary fs-4">
                            <i class="bi bi-person-plus-fill"></i>
                        </div>
                        <div>
                            <h4 class="fw-bold mb-0 text-dark">Tambah Pengguna Baru</h4>
                            <p class="text-muted small mb-0">Lengkapi formulir di bawah ini untuk menambahkan akun staf atau pelanggan baru.</p>
                        </div>
                    </div>
                </div>

                <div class="card-body p-4">
                    <?php if(!empty($data['errors'])): ?>
                        <div class="alert alert-danger border-0 rounded-3 shadow-sm mb-4">
                            <div class="fw-bold mb-1"><i class="bi bi-exclamation-triangle-fill me-2"></i>Perhatian:</div>
                            <ul class="mb-0 ps-3">
                                <?php foreach($data['errors'] as $err): ?>
                                    <li><?= Security::escape($err); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form action="<?= BASEURL; ?>/user/create" method="POST">
                        <input type="hidden" name="csrf_token" value="<?= Security::generateCsrfToken(); ?>">

                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label fw-semibold text-dark">Nama Lengkap <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-person text-muted"></i></span>
                                    <input type="text" name="name" class="form-control bg-light border-start-0" placeholder="Contoh: Rina Melati" value="<?= Security::escape($data['old']['name'] ?? ''); ?>" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark">Alamat Email</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-envelope text-muted"></i></span>
                                    <input type="email" name="email" class="form-control bg-light border-start-0" placeholder="nama@email.com" value="<?= Security::escape($data['old']['email'] ?? ''); ?>">
                                </div>
                                <div class="form-text small text-muted">Digunakan untuk login dan notifikasi pesanan.</div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark">Nomor WhatsApp</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-whatsapp text-success"></i></span>
                                    <input type="text" name="phone" class="form-control bg-light border-start-0" placeholder="08123456789 atau 628..." value="<?= Security::escape($data['old']['phone'] ?? ''); ?>">
                                </div>
                                <div class="form-text small text-muted">Dapat digunakan untuk login OTP & notif status pesanan.</div>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-semibold text-dark">Peran / Hak Akses (Role) <span class="text-danger">*</span></label>
                                <select name="role_id" class="form-select bg-light" required>
                                    <?php foreach($data['roles'] as $role): ?>
                                        <?php 
                                            $selected = (isset($data['old']['role_id']) && $data['old']['role_id'] == $role['id']) ? 'selected' : (($role['id'] == 1 && !isset($data['old']['role_id'])) ? 'selected' : '');
                                        ?>
                                        <option value="<?= $role['id']; ?>" <?= $selected; ?>>
                                            <?= strtoupper(Security::escape($role['name'])); ?> 
                                            <?php 
                                                if($role['name'] === 'admin') echo '— Administrator Sistem Penuh';
                                                elseif($role['name'] === 'owner') echo '— Pemilik Toko (Analitik & Finansial)';
                                                elseif($role['name'] === 'operator') echo '— Tim Operasional Toko (Kelola Produk & Pesanan)';
                                                elseif($role['name'] === 'customer') echo '— Pelanggan Belanja';
                                            ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark">Password <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-lock text-muted"></i></span>
                                    <input type="password" name="password" class="form-control bg-light border-start-0" placeholder="Minimal 6 karakter" required minlength="6">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark">Konfirmasi Password <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-shield-check text-muted"></i></span>
                                    <input type="password" name="confirm_password" class="form-control bg-light border-start-0" placeholder="Ulangi password" required minlength="6">
                                </div>
                            </div>
                        </div>

                        <hr class="my-4 opacity-25">

                        <div class="d-flex justify-content-end gap-2">
                            <a href="<?= BASEURL; ?>/user" class="btn btn-outline-secondary rounded-pill px-4">
                                Batal
                            </a>
                            <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold shadow-sm">
                                <i class="bi bi-check-lg me-1"></i>Simpan Pengguna
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
