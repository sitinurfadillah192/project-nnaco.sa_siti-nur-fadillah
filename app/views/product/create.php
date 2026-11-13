<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <nav aria-label="breadcrumb" class="mb-3">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="<?= BASEURL; ?>/dashboard" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= BASEURL; ?>/product" class="text-decoration-none">Kelola Produk</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Tambah Produk</li>
                </ol>
            </nav>
            <div class="d-flex align-items-center mb-4">
                <div class="rounded-circle d-flex align-items-center justify-content-center me-3 shadow-sm" style="width:44px; height:44px; background:var(--color-primary); color:var(--color-accent);">
                    <i class="bi bi-plus-lg fs-5"></i>
                </div>
                <div>
                    <h2 class="fw-bold mb-0 text-dark" style="font-family: 'Cormorant Garamond', Georgia, serif; font-size: 2rem;">Tambah Produk Baru</h2>
                    <p class="text-muted small mb-0">Lengkapi data produk perhiasan eksklusif untuk dipublikasikan ke katalog.</p>
                </div>
            </div>

            <div class="card shadow-soft rounded-4 border-0" style="background:var(--color-surface); border:1px solid var(--color-border) !important;">
                <div class="card-body p-4 p-md-5">
                    <!-- enctype for file upload -->
                    <form action="<?= BASEURL; ?>/product/create" method="POST" enctype="multipart/form-data">
                        <div class="mb-4">
                            <label class="form-label fw-bold text-dark">Nama Produk <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" placeholder="Contoh: Kalung Emas Aurelia Pearl" required style="border-radius:10px;">
                        </div>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark">Kategori <span class="text-danger">*</span></label>
                                <select class="form-select" name="category_id" required style="border-radius:10px;">
                                    <option value="">Pilih Kategori</option>
                                    <?php foreach($data['categories'] as $cat): ?>
                                        <option value="<?= $cat['id']; ?>"><?= Security::escape($cat['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark">Stok <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" name="stock" placeholder="Jumlah unit" required style="border-radius:10px;">
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-bold text-dark">Harga (Rp) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0 fw-semibold" style="border-radius: 10px 0 0 10px;">Rp</span>
                                <input type="number" step="1" class="form-control border-start-0" name="price" placeholder="Contoh: 350000" required style="border-radius: 0 10px 10px 0;">
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-bold text-dark">Deskripsi Produk</label>
                            <textarea class="form-control" name="description" rows="4" placeholder="Ceritakan keanggunan, material, dan spesifikasi perhiasan ini..." style="border-radius:10px;"></textarea>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-bold text-dark">Foto Produk</label>
                            <input type="file" class="form-control" name="image" accept="image/*" style="border-radius:10px;">
                            <div class="form-text small text-muted">Format file: JPG, PNG, atau WEBP. Disarankan resolusi tinggi dengan latar bersih.</div>
                        </div>
                        
                        <div class="d-flex gap-2 pt-3 border-top" style="border-color:var(--color-border) !important;">
                            <button type="submit" class="btn btn-primary rounded-pill px-4 py-2 fw-semibold shadow-sm">
                                <i class="bi bi-check-lg me-1"></i>Simpan Produk
                            </button>
                            <a href="<?= BASEURL; ?>/product" class="btn btn-outline-secondary rounded-pill px-4 py-2">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
