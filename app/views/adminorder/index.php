<div class="container mt-5 pt-4 min-vh-100">
    <div class="row align-items-center mb-4">
        <div class="col-md-6">
            <h2 class="fw-bold mb-0" style="color:var(--color-primary);"><i class="bi bi-receipt me-2"></i>Manajemen Pesanan</h2>
            <p class="text-muted mt-1">Kelola seluruh order pelanggan toko Anda secara rapi dan efisien.</p>
        </div>
        <div class="col-md-6 text-md-end">
            <div class="d-inline-block fw-bold px-4 py-2 rounded-pill shadow-sm mb-3 mb-md-0 me-2 border" style="background:var(--color-success-soft); color:var(--color-success); border-color:rgba(71,122,91,0.2) !important;">
                Total Pemasukan: Rp <?= number_format($data['total_income'] ?? 0, 0, ',', '.'); ?>
            </div>
            <a href="<?= BASEURL; ?>/dashboard" class="btn btn-outline-dark rounded-pill px-4 shadow-sm">
                <i class="bi bi-arrow-left me-2"></i>Kembali
            </a>
        </div>
    </div>

    <div class="card card-soft border shadow-sm rounded-4 overflow-hidden mb-5" style="background:var(--color-surface); border-color:var(--color-border) !important;">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="py-3 px-4">Order ID</th>
                            <th class="py-3">Tanggal</th>
                            <th class="py-3">Pelanggan</th>
                            <th class="py-3">Total</th>
                            <th class="py-3">Status</th>
                            <th class="py-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($data['orders'])): ?>
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-3 opacity-25"></i>
                                Belum ada pesanan yang masuk.
                            </td>
                        </tr>
                        <?php else: ?>
                            <?php foreach($data['orders'] as $order): ?>
                            <tr>
                                <td class="px-4 fw-bold" style="color:var(--color-text);">#ORD-<?= str_pad($order['id'], 5, '0', STR_PAD_LEFT); ?></td>
                                <td class="text-muted"><?= date('d M Y H:i', strtotime($order['created_at'])); ?></td>
                                <td class="fw-medium" style="color:var(--color-text);"><?= Security::escape($order['customer_name']); ?></td>
                                <td class="fw-bold fs-6" style="color:var(--color-primary);">Rp <?= number_format($order['total_amount'], 0, ',', '.'); ?></td>
                                <td>
                                    <?php 
                                        $st = strtolower($order['status']);
                                        $badgeStyle = 'background:var(--color-taupe); color:#fff;';
                                        if ($st == 'pending') $badgeStyle = 'background:var(--color-warning-soft); color:var(--color-warning); border:1px solid rgba(166,106,32,0.3);';
                                        elseif ($st == 'processing' || $st == 'paid') $badgeStyle = 'background:var(--color-info-soft); color:var(--color-info); border:1px solid rgba(82,109,130,0.3);';
                                        elseif ($st == 'shipped') $badgeStyle = 'background:var(--color-primary); color:var(--color-accent);';
                                        elseif ($st == 'completed' || $st == 'delivered') $badgeStyle = 'background:var(--color-success-soft); color:var(--color-success); border:1px solid rgba(71,122,91,0.3);';
                                        elseif ($st == 'cancelled') $badgeStyle = 'background:var(--color-danger-soft); color:var(--color-danger); border:1px solid rgba(181,71,71,0.3);';
                                    ?>
                                    <span class="badge rounded-pill px-3 py-2 text-uppercase" style="font-size:11.5px; letter-spacing:0.5px; <?= $badgeStyle; ?>">
                                        <?= strtoupper($order['status']); ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <?php if(in_array($_SESSION['user_role'], ['admin', 'operator'])): ?>
                                    <form action="<?= BASEURL; ?>/adminorder/update_status" method="POST" class="d-inline-flex gap-2 align-items-center">
                                        <input type="hidden" name="csrf_token" value="<?= Security::generateCsrfToken(); ?>">
                                        <input type="hidden" name="order_id" value="<?= $order['id']; ?>">
                                        <select name="status" class="form-select form-select-sm shadow-none rounded-3" style="width: 130px; border-color:var(--color-border);">
                                            <option value="pending" <?= $order['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                            <option value="processing" <?= $order['status'] == 'processing' ? 'selected' : ''; ?>>Processing</option>
                                            <option value="shipped" <?= $order['status'] == 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                            <option value="completed" <?= $order['status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                                            <option value="cancelled" <?= $order['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                        </select>
                                        <button type="submit" class="btn btn-primary btn-sm rounded-3 px-3 shadow-none" title="Simpan Perubahan"><i class="bi bi-check-lg"></i></button>
                                    </form>
                                    <?php else: ?>
                                    <span class="text-muted fst-italic small">Read Only</span>
                                    <?php endif; ?>
                                    <a href="<?= BASEURL; ?>/adminorder/detail/<?= $order['id']; ?>" class="btn btn-outline-dark btn-sm rounded-3 ms-1" title="Lihat Detail Pesanan"><i class="bi bi-eye"></i></a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
