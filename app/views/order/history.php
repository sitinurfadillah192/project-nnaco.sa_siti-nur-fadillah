<div class="container py-4 min-vh-100">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1" style="color:var(--color-text);">Riwayat Pembelian</h2>
            <p class="text-muted small mb-0">Pantau seluruh status pesanan dan detail pengiriman Anda.</p>
        </div>
        <a href="<?= BASEURL; ?>/catalog" class="btn btn-primary rounded-pill px-4 shadow-sm">Belanja Lagi</a>
    </div>

    <div class="card card-soft border shadow-sm" style="background:var(--color-surface); border-color:var(--color-border) !important;">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4 py-3">Order ID</th>
                            <th>Tanggal</th>
                            <th>Total Tagihan</th>
                            <th>Status</th>
                            <th class="pe-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($data['orders'] as $order): ?>
                        <tr>
                            <td class="ps-4 fw-bold" style="color:var(--color-text);">#<?= $order['id']; ?></td>
                            <td class="text-muted"><?= date('d M Y, H:i', strtotime($order['created_at'])); ?></td>
                            <td class="fw-bold fs-6" style="color:var(--color-primary);">Rp <?= number_format($order['total_amount'], 0, ',', '.'); ?></td>
                            <td>
                                <?php 
                                    $st = strtolower($order['status']);
                                    $badgeStyle = 'background:var(--color-taupe); color:#fff;';
                                    if ($st == 'pending') $badgeStyle = 'background:var(--color-warning-soft); color:var(--color-warning); border:1px solid rgba(166,106,32,0.3);';
                                    elseif ($st == 'processing') $badgeStyle = 'background:var(--color-info-soft); color:var(--color-info); border:1px solid rgba(82,109,130,0.3);';
                                    elseif ($st == 'shipped') $badgeStyle = 'background:var(--color-primary); color:var(--color-accent);';
                                    elseif ($st == 'completed' || $st == 'delivered') $badgeStyle = 'background:var(--color-success-soft); color:var(--color-success); border:1px solid rgba(71,122,91,0.3);';
                                    elseif ($st == 'cancelled') $badgeStyle = 'background:var(--color-danger-soft); color:var(--color-danger); border:1px solid rgba(181,71,71,0.3);';
                                ?>
                                <span class="badge px-3 py-2 rounded-pill text-uppercase" style="font-size:11.5px; letter-spacing:0.5px; <?= $badgeStyle; ?>"><?= $order['status']; ?></span>
                            </td>
                            <td class="pe-4 text-center">
                                <a href="<?= BASEURL; ?>/order/detail/<?= $order['id']; ?>" class="btn btn-sm btn-outline-dark rounded-pill px-3">Lihat Detail</a>
                                <?php if($order['status'] == 'pending'): ?>
                                    <a href="<?= BASEURL; ?>/order/payment/<?= $order['id']; ?>" class="btn btn-sm btn-primary rounded-pill px-3 mt-1 mt-md-0 shadow-sm ms-1">Bayar Sekarang</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        
                        <?php if(empty($data['orders'])): ?>
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="bi bi-bag-x fs-1 d-block mb-3 opacity-25"></i>
                                    Belum ada riwayat pembelian. Mulai isi keranjang Anda dengan koleksi perhiasan terbaik!
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
