<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Breadcrumbs -->
            <nav aria-label="breadcrumb" class="mb-3">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= BASEURL; ?>/dashboard" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= BASEURL; ?>/user" class="text-decoration-none">Manajemen User</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Edit Pengguna</li>
                </ol>
            </nav>

            <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-2">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-warning bg-opacity-10 p-3 me-3 text-warning fs-4">
                            <i class="bi bi-person-gear"></i>
                        </div>
                        <div>
                            <h4 class="fw-bold mb-0 text-dark">Edit Data Pengguna</h4>
                            <p class="text-muted small mb-0">Perbarui informasi akun, nomor WhatsApp, peran, atau reset kata sandi.</p>
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

                    <?php if($data['user']['id'] == $_SESSION['user_id']): ?>
                        <div class="alert alert-info border-0 rounded-3 shadow-sm mb-4 small">
                            <i class="bi bi-info-circle-fill me-2"></i>Anda sedang mengedit akun login Anda sendiri (<strong><?= Security::escape($data['user']['name']); ?></strong>).
                        </div>
                    <?php endif; ?>

                    <form action="<?= BASEURL; ?>/user/edit/<?= $data['user']['id']; ?>" method="POST">
                        <input type="hidden" name="csrf_token" value="<?= Security::generateCsrfToken(); ?>">

                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label fw-semibold text-dark">Nama Lengkap <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-person text-muted"></i></span>
                                    <input type="text" name="name" class="form-control bg-light border-start-0" value="<?= Security::escape($data['user']['name']); ?>" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark">Alamat Email</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-envelope text-muted"></i></span>
                                    <input type="email" name="email" class="form-control bg-light border-start-0" value="<?= Security::escape($data['user']['email'] ?? ''); ?>">
                                </div>
                                <div class="form-text small text-muted">Alamat email utama pengguna.</div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark">Nomor WhatsApp</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-whatsapp text-success"></i></span>
                                    <input type="text" name="phone" class="form-control bg-light border-start-0" value="<?= Security::escape($data['user']['phone'] ?? ''); ?>">
                                </div>
                                <div class="form-text small text-muted">Nomor WhatsApp aktif (misal: 628123... / 08123...).</div>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-semibold text-dark">Peran / Hak Akses (Role) <span class="text-danger">*</span></label>
                                <select name="role_id" class="form-select bg-light" required>
                                    <?php foreach($data['roles'] as $role): ?>
                                        <option value="<?= $role['id']; ?>" <?= ($data['user']['role_id'] == $role['id']) ? 'selected' : ''; ?>>
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

                            <div class="col-12 mt-4">
                                <div class="p-3 bg-light rounded-3 border">
                                    <h6 class="fw-bold text-dark mb-1"><i class="bi bi-key me-2 text-warning"></i>Ganti / Reset Password (Opsional)</h6>
                                    <p class="text-muted small mb-3">Biarkan kedua bidang di bawah ini kosong jika tidak ingin mengubah kata sandi pengguna.</p>

                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold small text-dark">Password Baru</label>
                                            <input type="password" name="password" class="form-control bg-white" placeholder="Minimal 6 karakter" minlength="6">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold small text-dark">Konfirmasi Password Baru</label>
                                            <input type="password" name="confirm_password" class="form-control bg-white" placeholder="Ulangi password baru" minlength="6">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4 opacity-25">

                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <?php if($data['user']['id'] != $_SESSION['user_id']): ?>
                                    <a href="<?= BASEURL; ?>/user/delete/<?= $data['user']['id']; ?>" 
                                       class="btn btn-outline-danger rounded-pill px-3" 
                                       onclick="return confirm('Apakah Anda yakin ingin menghapus akun <?= addslashes(Security::escape($data['user']['name'])); ?>?');">
                                        <i class="bi bi-trash me-1"></i>Hapus Akun
                                    </a>
                                <?php endif; ?>
                            </div>
                            <div class="d-flex gap-2">
                                <a href="<?= BASEURL; ?>/user" class="btn btn-outline-secondary rounded-pill px-4">
                                    Batal
                                </a>
                                <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold shadow-sm">
                                    <i class="bi bi-check-lg me-1"></i>Simpan Perubahan
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
