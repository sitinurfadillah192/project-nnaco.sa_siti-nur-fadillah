<div class="container-fluid py-4 px-lg-5 min-vh-100">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h2 class="fw-bold mb-1" style="color:var(--color-primary);"><i class="bi bi-box-seam me-2"></i>Manajemen Produk</h2>
            <p class="text-muted small mb-0">Kelola inventori, harga, dan ketersediaan koleksi perhiasan.</p>
        </div>
        <a href="<?= BASEURL; ?>/product/create" class="btn btn-primary rounded-pill px-4 shadow-sm"><i class="bi bi-plus-lg me-1"></i> Tambah Produk</a>
    </div>

    <!-- Search & Filter Form -->
    <div class="card card-soft border shadow-sm rounded-4 p-3 mb-4" style="background:var(--color-surface); border-color:var(--color-border) !important;">
        <form action="<?= BASEURL; ?>/product/index" method="GET" class="row g-3 align-items-center">
            <div class="col-md-5">
                <input type="text" class="form-control rounded-3" name="search" placeholder="Cari perhiasan..." value="<?= isset($_GET['search']) ? Security::escape($_GET['search']) : ''; ?>">
            </div>
            <div class="col-md-4">
                <select name="category_id" class="form-select rounded-3">
                    <option value="">-- Semua Kategori --</option>
                    <?php foreach($data['categories'] as $cat): ?>
                        <option value="<?= $cat['id']; ?>" <?= (isset($_GET['category_id']) && $_GET['category_id'] == $cat['id']) ? 'selected' : ''; ?>><?= Security::escape($cat['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary-soft w-100 rounded-3 fw-bold">Filter Katalog</button>
            </div>
        </form>
    </div>

    <div class="card card-soft border shadow-sm rounded-4 overflow-hidden" style="background:var(--color-surface); border-color:var(--color-border) !important;">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Gambar</th>
                            <th>Nama Produk</th>
                            <th>Kategori</th>
                            <th>Harga</th>
                            <th>Stok</th>
                            <th class="pe-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($data['products'] as $product): ?>
                        <tr>
                            <td class="ps-4 py-3">
                                <?php if(!empty($product['image_url'])): ?>
                                    <img src="<?= BASEURL; ?>/uploads/products/<?= Security::escape($product['image_url']); ?>" width="52" height="52" style="object-fit:cover;" class="rounded-3 border">
                                <?php else: ?>
                                    <div class="rounded-3 d-flex align-items-center justify-content-center text-muted border" style="width:52px; height:52px; background:var(--color-surface-soft);"><i class="bi bi-image"></i></div>
                                <?php endif; ?>
                            </td>
                            <td class="fw-medium" style="color:var(--color-text);"><?= Security::escape($product['name']); ?></td>
                            <td><span class="badge badge-soft"><?= Security::escape($product['category_name']); ?></span></td>
                            <td class="fw-bold" style="color:var(--color-primary);">Rp <?= number_format($product['price'], 0, ',', '.'); ?></td>
                            <td>
                                <?php if($product['stock'] < 5): ?>
                                    <span class="badge bg-warning"><?= Security::escape($product['stock']); ?></span>
                                <?php else: ?>
                                    <span class="badge bg-success"><?= Security::escape($product['stock']); ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="pe-4 text-center">
                                <a href="<?= BASEURL; ?>/product/edit/<?= $product['id']; ?>" class="btn btn-sm btn-outline-dark rounded-pill px-3 me-1">Edit</a>
                                <a href="<?= BASEURL; ?>/product/delete/<?= $product['id']; ?>" class="btn btn-sm btn-outline-danger rounded-pill px-3" onclick="return confirm('Hapus produk ini?');">Hapus</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(empty($data['products'])): ?>
                        <tr><td colspan="6" class="text-center py-5 text-muted">Tidak ada produk ditemukan.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
