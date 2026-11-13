<div class="container-fluid py-4 px-lg-5">
    <!-- Header Title & Breadcrumb -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="<?= BASEURL; ?>/dashboard" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Ulasan & Rating Pelanggan</li>
                </ol>
            </nav>
            <h2 class="fw-bold mb-0 text-dark" style="font-family: 'Cormorant Garamond', Georgia, serif; font-size: 2.2rem;">
                <i class="bi bi-star-fill text-warning me-2"></i>Ulasan & Penilaian Pelanggan
            </h2>
            <p class="text-muted small mb-0">Kelola ulasan, testimoni pembeli, dan reputasi produk perhiasan NNACOSA.</p>
        </div>
        <div>
            <a href="<?= BASEURL; ?>/catalog" target="_blank" class="btn btn-outline-primary rounded-pill px-4 py-2 small fw-semibold shadow-sm">
                <i class="bi bi-eye me-1"></i>Lihat Katalog Toko
            </a>
        </div>
    </div>

    <!-- Filter & Search Card -->
    <div class="card border-0 shadow-soft rounded-4 mb-4" style="background:var(--color-surface); border:1px solid var(--color-border) !important;">
        <div class="card-body p-3 p-md-4">
            <form action="<?= BASEURL; ?>/review/admin" method="GET" class="row g-3 align-items-center">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 rounded-start-pill ps-3" style="border-color:var(--color-border) !important;">
                            <i class="bi bi-search text-muted"></i>
                        </span>
                        <input type="text" name="search" class="form-control bg-light border-start-0 rounded-end-pill" placeholder="Cari nama pembeli, perhiasan, atau isi ulasan..." value="<?= Security::escape($data['search'] ?? ''); ?>" style="border-color:var(--color-border) !important;">
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="rating" class="form-select bg-light rounded-pill" style="border-color:var(--color-border) !important;">
                        <option value="">-- Semua Bintang --</option>
                        <option value="5" <?= ($data['rating_filter'] === 5) ? 'selected' : ''; ?>>⭐⭐⭐⭐⭐ (5 Bintang)</option>
                        <option value="4" <?= ($data['rating_filter'] === 4) ? 'selected' : ''; ?>>⭐⭐⭐⭐ (4 Bintang)</option>
                        <option value="3" <?= ($data['rating_filter'] === 3) ? 'selected' : ''; ?>>⭐⭐⭐ (3 Bintang)</option>
                        <option value="2" <?= ($data['rating_filter'] === 2) ? 'selected' : ''; ?>>⭐⭐ (2 Bintang)</option>
                        <option value="1" <?= ($data['rating_filter'] === 1) ? 'selected' : ''; ?>>⭐ (1 Bintang)</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary rounded-pill px-4 flex-grow-1 shadow-sm">
                        <i class="bi bi-funnel me-1"></i>Filter
                    </button>
                    <?php if(!empty($data['search']) || $data['rating_filter'] !== null): ?>
                        <a href="<?= BASEURL; ?>/review/admin" class="btn btn-outline-secondary rounded-pill px-3" title="Reset Filter">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- Reviews Table Card -->
    <div class="card border-0 shadow-soft rounded-4 overflow-hidden" style="background:var(--color-surface); border:1px solid var(--color-border) !important;">
        <div class="card-header border-0 py-3 px-4 d-flex justify-content-between align-items-center" style="background:var(--color-surface);">
            <h5 class="fw-bold mb-0 text-dark">Daftar Ulasan Masuk</h5>
            <span class="badge bg-light text-muted border px-3 py-2 rounded-pill small" style="border-color:var(--color-border) !important;">
                Total: <?= number_format($data['total_reviews']); ?> ulasan
            </span>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light border-top">
                    <tr>
                        <th class="ps-4 py-3" style="min-width: 180px;">Pelanggan</th>
                        <th class="py-3" style="min-width: 200px;">Produk Perhiasan</th>
                        <th class="py-3 text-center" style="min-width: 130px;">Rating</th>
                        <th class="py-3" style="min-width: 260px;">Isi Ulasan & Komentar</th>
                        <th class="py-3 text-center" style="min-width: 140px;">Waktu</th>
                        <th class="pe-4 py-3 text-end" style="min-width: 100px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($data['reviews'])): ?>
                        <?php foreach($data['reviews'] as $rev): ?>
                            <tr>
                                <td class="ps-4 py-3">
                                    <div class="d-flex align-items-center">
                                        <img src="https://ui-avatars.com/api/?name=<?= urlencode($rev['user_name']); ?>&background=D8BFA3&color=4B3B32&size=80" 
                                             alt="<?= Security::escape($rev['user_name']); ?>" 
                                             class="rounded-circle me-3 shadow-sm" width="40" height="40">
                                        <div>
                                            <div class="fw-bold text-dark"><?= Security::escape($rev['user_name']); ?></div>
                                            <small class="text-muted"><?= Security::escape($rev['user_email']); ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3">
                                    <div class="d-flex align-items-center">
                                        <?php 
                                            $thumb = !empty($rev['product_image']) ? BASEURL . '/uploads/products/' . Security::escape($rev['product_image']) : BASEURL . '/img/hero/hero_ring.jpg';
                                        ?>
                                        <img src="<?= $thumb; ?>" class="rounded-3 me-2 border" width="38" height="38" style="object-fit:cover; border-color:var(--color-border) !important;">
                                        <div class="text-truncate" style="max-width:180px;">
                                            <a href="<?= BASEURL; ?>/catalog/detail/<?= $rev['product_id']; ?>" target="_blank" class="text-decoration-none text-dark fw-semibold small hover-primary">
                                                <?= Security::escape($rev['product_name']); ?>
                                            </a>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3 text-center">
                                    <div class="text-warning small mb-1">
                                        <?php for($s=1; $s<=5; $s++): ?>
                                            <i class="bi <?= ($s <= (int)$rev['rating']) ? 'bi-star-fill' : 'bi-star'; ?>"></i>
                                        <?php endfor; ?>
                                    </div>
                                    <span class="badge rounded-pill px-2 py-1 small fw-bold" style="background:var(--color-surface-soft); color:var(--color-primary); border:1px solid var(--color-border);">
                                        <?= $rev['rating']; ?> / 5
                                    </span>
                                </td>
                                <td class="py-3">
                                    <?php if(!empty($rev['comment'])): ?>
                                        <p class="mb-0 small text-dark lh-base" style="max-width: 380px;">
                                            "<?= Security::escape($rev['comment']); ?>"
                                        </p>
                                    <?php else: ?>
                                        <span class="text-muted small fst-italic">Tanpa komentar tertulis</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3 text-center text-muted small">
                                    <?= date('d M Y, H:i', strtotime($rev['created_at'])); ?>
                                </td>
                                <td class="pe-4 py-3 text-end">
                                    <form action="<?= BASEURL; ?>/review/delete/<?= $rev['id']; ?>" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus ulasan ini?');" class="d-inline">
                                        <input type="hidden" name="csrf_token" value="<?= Security::generateCsrfToken(); ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3 shadow-sm" title="Hapus Ulasan">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <div class="display-6 opacity-25 mb-2"><i class="bi bi-star"></i></div>
                                Belum ada ulasan yang sesuai dengan pencarian.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if($data['total_pages'] > 1): ?>
        <div class="card-footer bg-white border-0 py-3 px-4 d-flex justify-content-between align-items-center">
            <span class="small text-muted">Halaman <?= $data['current_page']; ?> dari <?= $data['total_pages']; ?></span>
            <ul class="pagination pagination-sm mb-0">
                <?php for($p=1; $p<=$data['total_pages']; $p++): ?>
                    <li class="page-item <?= ($p == $data['current_page']) ? 'active' : ''; ?>">
                        <a class="page-link rounded-pill mx-1" href="<?= BASEURL; ?>/review/admin?page=<?= $p; ?>&search=<?= urlencode($data['search']); ?>&rating=<?= $data['rating_filter']; ?>"><?= $p; ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </div>
        <?php endif; ?>
    </div>
</div>
