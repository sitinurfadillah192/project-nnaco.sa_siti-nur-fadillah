<div class="container-fluid py-4 px-lg-5">
    <!-- Header Title & Action Button -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="<?= BASEURL; ?>/dashboard" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Manajemen User</li>
                </ol>
            </nav>
            <h2 class="fw-bold mb-0 text-dark"><i class="bi bi-people-fill text-primary me-2"></i>Manajemen User</h2>
            <p class="text-muted small mb-0">Kelola akun staf, administrator, dan pelanggan toko NNACOSA.</p>
        </div>
        <div>
            <a href="<?= BASEURL; ?>/user/create" class="btn btn-primary rounded-pill px-4 py-2 fw-semibold shadow-sm">
                <i class="bi bi-person-plus-fill me-2"></i>Tambah Pengguna
            </a>
        </div>
    </div>

    <!-- Stat Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-soft rounded-4 h-100" style="background:var(--color-surface); border:1px solid var(--color-border) !important;">
                <div class="card-body p-4 d-flex align-items-center">
                    <div class="rounded-circle p-3 me-3 fs-3" style="background:var(--color-surface-soft); color:var(--color-primary);">
                        <i class="bi bi-people"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-medium">Total Pengguna</div>
                        <h3 class="fw-bold mb-0 text-dark"><?= number_format($data['stats']['total_users'] ?? 0); ?></h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-soft rounded-4 h-100" style="background:var(--color-surface); border:1px solid var(--color-border) !important;">
                <div class="card-body p-4 d-flex align-items-center">
                    <div class="rounded-circle p-3 me-3 fs-3" style="background:var(--color-accent-soft); color:var(--color-taupe);">
                        <i class="bi bi-person-check"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-medium">Total Pelanggan</div>
                        <h3 class="fw-bold mb-0 text-dark"><?= number_format($data['stats']['total_customers'] ?? 0); ?></h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-soft rounded-4 h-100" style="background:var(--color-surface); border:1px solid var(--color-border) !important;">
                <div class="card-body p-4 d-flex align-items-center">
                    <div class="rounded-circle p-3 me-3 fs-3" style="background:var(--color-surface-soft); color:var(--color-accent);">
                        <i class="bi bi-shield-lock"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-medium">Admin & Tim Staf</div>
                        <h3 class="fw-bold mb-0 text-dark"><?= number_format($data['stats']['total_staff'] ?? 0); ?></h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-soft rounded-4 h-100" style="background:var(--color-surface); border:1px solid var(--color-border) !important;">
                <div class="card-body p-4 d-flex align-items-center">
                    <div class="rounded-circle p-3 me-3 fs-3" style="background:rgba(71, 122, 91, 0.12); color:var(--color-success);">
                        <i class="bi bi-whatsapp"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-medium">WhatsApp Terhubung</div>
                        <h3 class="fw-bold mb-0 text-dark"><?= number_format($data['stats']['total_with_phone'] ?? 0); ?></h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter & Search Card -->
    <div class="card border-0 shadow-soft rounded-4 mb-4" style="background:var(--color-surface); border:1px solid var(--color-border) !important;">
        <div class="card-body p-3 p-md-4">
            <form action="<?= BASEURL; ?>/user" method="GET" class="row g-3 align-items-center">
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 rounded-start-pill ps-3" style="border-color:var(--color-border) !important;">
                            <i class="bi bi-search text-muted"></i>
                        </span>
                        <input type="text" name="search" class="form-control bg-light border-start-0 rounded-end-pill" placeholder="Cari nama, email, atau nomor WhatsApp..." value="<?= Security::escape($data['search']); ?>" style="border-color:var(--color-border) !important;">
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="role_id" class="form-select bg-light rounded-pill" style="border-color:var(--color-border) !important;">
                        <option value="">-- Semua Peran (Role) --</option>
                        <?php foreach($data['roles'] as $role): ?>
                            <option value="<?= $role['id']; ?>" <?= ($data['role_id'] === (int)$role['id']) ? 'selected' : ''; ?>>
                                Role: <?= strtoupper(Security::escape($role['name'])); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary rounded-pill px-4 flex-grow-1 shadow-sm">
                        <i class="bi bi-funnel me-1"></i>Filter
                    </button>
                    <?php if(!empty($data['search']) || $data['role_id'] !== null): ?>
                        <a href="<?= BASEURL; ?>/user" class="btn btn-outline-secondary rounded-pill px-3" title="Reset Filter">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- Users Table Card -->
    <div class="card border-0 shadow-soft rounded-4 overflow-hidden" style="background:var(--color-surface); border:1px solid var(--color-border) !important;">
        <div class="card-header border-0 py-3 px-4 d-flex justify-content-between align-items-center" style="background:var(--color-surface);">
            <h5 class="fw-bold mb-0 text-dark">Daftar Pengguna</h5>
            <span class="badge bg-light text-muted border px-3 py-2 rounded-pill small" style="border-color:var(--color-border) !important;">
                Total: <?= number_format($data['total_users']); ?> pengguna
            </span>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light border-top">
                    <tr>
                        <th class="ps-4 py-3" style="min-width: 200px;">Pengguna</th>
                        <th class="py-3" style="min-width: 180px;">Email</th>
                        <th class="py-3" style="min-width: 160px;">WhatsApp / No HP</th>
                        <th class="py-3 text-center" style="min-width: 130px;">Role / Hak Akses</th>
                        <th class="py-3 text-center" style="min-width: 130px;">Terdaftar</th>
                        <th class="pe-4 py-3 text-end" style="min-width: 150px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($data['users'])): ?>
                        <?php foreach($data['users'] as $u): ?>
                            <tr>
                                <td class="ps-4 py-3">
                                    <div class="d-flex align-items-center">
                                        <img src="https://ui-avatars.com/api/?name=<?= urlencode($u['name']); ?>&background=D8BFA3&color=4B3B32&size=80" 
                                             alt="<?= Security::escape($u['name']); ?>" 
                                             class="rounded-circle me-3 shadow-sm" width="42" height="42">
                                        <div>
                                            <div class="fw-bold text-dark">
                                                <?= Security::escape($u['name']); ?>
                                                <?php if($u['id'] == $_SESSION['user_id']): ?>
                                                    <span class="badge bg-primary-soft text-primary border rounded-pill ms-1" style="font-size: 11px;">Akun Anda</span>
                                                <?php endif; ?>
                                            </div>
                                            <small class="text-muted">ID: #<?= $u['id']; ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3">
                                    <?php if(!empty($u['email'])): ?>
                                        <span class="text-dark"><i class="bi bi-envelope text-muted me-1"></i><?= Security::escape($u['email']); ?></span>
                                    <?php else: ?>
                                        <span class="text-muted fst-italic"><small>Belum ada email</small></span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3">
                                    <?php if(!empty($u['phone'])): ?>
                                        <div class="d-flex align-items-center">
                                            <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $u['phone']); ?>" target="_blank" class="text-decoration-none text-success fw-semibold">
                                                <i class="bi bi-whatsapp me-1"></i><?= Security::escape($u['phone']); ?>
                                            </a>
                                            <?php if(!empty($u['is_phone_verified'])): ?>
                                                <i class="bi bi-check-circle-fill text-primary ms-1" title="Terverifikasi via WhatsApp OTP" style="font-size: 13px;"></i>
                                            <?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted fst-italic"><small>Belum terhubung WA</small></span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3 text-center">
                                    <?php
                                        $roleBadge = 'bg-secondary';
                                        $roleIcon = 'bi-person';
                                        $r = strtolower($u['role_name'] ?? '');
                                        if ($r === 'admin') {
                                            $roleBadge = 'bg-danger';
                                            $roleIcon = 'bi-shield-lock-fill';
                                        } elseif ($r === 'owner') {
                                            $roleBadge = 'bg-dark';
                                            $roleIcon = 'bi-crown';
                                        } elseif ($r === 'operator') {
                                            $roleBadge = 'bg-info text-dark';
                                            $roleIcon = 'bi-headset';
                                        }
                                    ?>
                                    <span class="badge <?= $roleBadge; ?> rounded-pill px-3 py-2 fw-semibold">
                                        <i class="bi <?= $roleIcon; ?> me-1"></i><?= strtoupper(Security::escape($u['role_name'] ?? 'customer')); ?>
                                    </span>
                                </td>
                                <td class="py-3 text-center text-muted small">
                                    <?= !empty($u['created_at']) ? date('d M Y', strtotime($u['created_at'])) : '-'; ?>
                                </td>
                                <td class="pe-4 py-3 text-end">
                                    <div class="btn-group">
                                        <a href="<?= BASEURL; ?>/user/edit/<?= $u['id']; ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3 me-1 shadow-sm" title="Edit Pengguna">
                                            <i class="bi bi-pencil-square me-1"></i>Edit
                                        </a>
                                        <?php if($u['id'] != $_SESSION['user_id']): ?>
                                            <a href="<?= BASEURL; ?>/user/delete/<?= $u['id']; ?>" 
                                               class="btn btn-sm btn-outline-danger rounded-pill px-3 shadow-sm" 
                                               onclick="return confirm('Apakah Anda yakin ingin menghapus pengguna <?= addslashes(Security::escape($u['name'])); ?>?');" 
                                               title="Hapus Pengguna">
                                                <i class="bi bi-trash me-1"></i>Hapus
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="py-4">
                                    <i class="bi bi-person-x fs-1 text-muted opacity-50 d-block mb-3"></i>
                                    <h6 class="text-muted fw-bold">Tidak ada pengguna ditemukan</h6>
                                    <p class="text-muted small mb-0">Coba ubah kata kunci pencarian atau reset filter role.</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if($data['total_pages'] > 1): ?>
            <div class="card-footer bg-white border-0 py-3 px-4">
                <nav aria-label="Page navigation">
                    <ul class="pagination justify-content-center mb-0">
                        <li class="page-item <?= ($data['current_page'] <= 1) ? 'disabled' : ''; ?>">
                            <a class="page-link rounded-pill me-2 px-3" href="<?= BASEURL; ?>/user?page=<?= $data['current_page'] - 1; ?><?= !empty($data['search']) ? '&search=' . urlencode($data['search']) : ''; ?><?= ($data['role_id'] !== null) ? '&role_id=' . $data['role_id'] : ''; ?>">
                                &laquo; Sebelumnya
                            </a>
                        </li>
                        <?php for($i = 1; $i <= $data['total_pages']; $i++): ?>
                            <li class="page-item <?= ($i == $data['current_page']) ? 'active' : ''; ?>">
                                <a class="page-link rounded-circle mx-1" style="width: 38px; height: 38px; display: inline-flex; align-items: center; justify-content: center;" href="<?= BASEURL; ?>/user?page=<?= $i; ?><?= !empty($data['search']) ? '&search=' . urlencode($data['search']) : ''; ?><?= ($data['role_id'] !== null) ? '&role_id=' . $data['role_id'] : ''; ?>">
                                    <?= $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?= ($data['current_page'] >= $data['total_pages']) ? 'disabled' : ''; ?>">
                            <a class="page-link rounded-pill ms-2 px-3" href="<?= BASEURL; ?>/user?page=<?= $data['current_page'] + 1; ?><?= !empty($data['search']) ? '&search=' . urlencode($data['search']) : ''; ?><?= ($data['role_id'] !== null) ? '&role_id=' . $data['role_id'] : ''; ?>">
                                Selanjutnya &raquo;
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        <?php endif; ?>
    </div>
</div>
