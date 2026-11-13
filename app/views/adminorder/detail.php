<div class="container mt-5 pt-4 min-vh-100">
    <div class="row mb-4 align-items-center">
        <div class="col-md-6">
            <h2 class="fw-bold mb-0" style="color:var(--color-primary);"><i class="bi bi-file-earmark-text me-2"></i>Detail Pesanan</h2>
            <p class="text-muted mt-1">#ORD-<?= str_pad($data['order']['id'], 5, '0', STR_PAD_LEFT); ?></p>
        </div>
        <div class="col-md-6 text-md-end">
            <a href="<?= BASEURL; ?>/adminorder" class="btn btn-outline-dark rounded-pill px-4 shadow-sm">
                <i class="bi bi-arrow-left me-2"></i>Kembali
            </a>
        </div>
    </div>

    <div class="row g-4">
        <!-- Informasi Pelanggan -->
        <div class="col-lg-8">
            <div class="card card-soft border shadow-sm rounded-4 mb-4" style="background:var(--color-surface); border-color:var(--color-border) !important;">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-4" style="color:var(--color-text);">Informasi Pelanggan & Pengiriman</h5>
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Nama Pelanggan</div>
                        <div class="col-sm-8 fw-medium" style="color:var(--color-text);"><?= Security::escape($data['customer']['name']); ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Email</div>
                        <div class="col-sm-8 fw-medium" style="color:var(--color-text);"><?= Security::escape($data['customer']['email']); ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Nomor WhatsApp</div>
                        <div class="col-sm-8 fw-medium">
                            <?php if (!empty($data['customer']['phone'])): ?>
                                <span class="badge border px-3 py-2 rounded-pill" style="background:var(--color-success-soft); color:var(--color-success); border-color:rgba(71,122,91,0.2) !important;">
                                    <i class="bi bi-whatsapp me-1"></i><?= Security::escape($data['customer']['phone']); ?>
                                </span>
                            <?php else: ?>
                                <span class="text-muted fst-italic">Belum tersimpan</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Tanggal Order</div>
                        <div class="col-sm-8 fw-medium text-muted"><?= date('d F Y H:i', strtotime($data['order']['created_at'])); ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Alamat Pengiriman</div>
                        <div class="col-sm-8 fw-medium">
                            <p class="mb-0 border p-3 rounded-3" style="background:var(--color-surface-soft); border-color:var(--color-border) !important; color:var(--color-text);">
                                <?= nl2br(Security::escape($data['order']['shipping_address'])); ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Rincian Produk -->
            <div class="card card-soft border shadow-sm rounded-4" style="background:var(--color-surface); border-color:var(--color-border) !important;">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-4" style="color:var(--color-text);">Rincian Produk</h5>
                    <div class="table-responsive">
                        <table class="table table-borderless align-middle">
                            <thead class="border-bottom" style="border-color:var(--color-border) !important;">
                                <tr class="text-muted small uppercase">
                                    <th>Produk</th>
                                    <th class="text-center">Harga</th>
                                    <th class="text-center">Qty</th>
                                    <th class="text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($data['items'] as $item): ?>
                                <tr>
                                    <td class="fw-medium py-3" style="color:var(--color-text);"><?= Security::escape($item['name']); ?></td>
                                    <td class="text-center py-3 text-muted">Rp <?= number_format($item['price'], 0, ',', '.'); ?></td>
                                    <td class="text-center py-3 fw-medium"><?= $item['quantity']; ?></td>
                                    <td class="text-end fw-bold py-3" style="color:var(--color-primary);">Rp <?= number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot class="border-top" style="border-color:var(--color-border) !important;">
                                <tr>
                                    <td colspan="3" class="text-end fw-bold pt-4 fs-5" style="color:var(--color-text);">TOTAL KESELURUHAN</td>
                                    <td class="text-end fw-bold pt-4 fs-5" style="color:var(--color-primary);">Rp <?= number_format($data['order']['total_amount'], 0, ',', '.'); ?></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Status & Bukti Bayar -->
        <div class="col-lg-4">
            <div class="card card-soft border shadow-sm rounded-4 mb-4" style="background:var(--color-surface); border-color:var(--color-border) !important;">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-4" style="color:var(--color-text);">Status & Pembayaran</h5>
                    
                    <div class="mb-4">
                        <label class="text-muted small mb-2 d-block">Status Pesanan</label>
                        <?php 
                            $st = strtolower($data['order']['status']);
                            $badgeStyle = 'background:var(--color-taupe); color:#fff;';
                            if ($st == 'pending') $badgeStyle = 'background:var(--color-warning-soft); color:var(--color-warning); border:1px solid rgba(166,106,32,0.3);';
                            elseif ($st == 'processing' || $st == 'paid') $badgeStyle = 'background:var(--color-info-soft); color:var(--color-info); border:1px solid rgba(82,109,130,0.3);';
                            elseif ($st == 'shipped') $badgeStyle = 'background:var(--color-primary); color:var(--color-accent);';
                            elseif ($st == 'completed' || $st == 'delivered') $badgeStyle = 'background:var(--color-success-soft); color:var(--color-success); border:1px solid rgba(71,122,91,0.3);';
                            elseif ($st == 'cancelled') $badgeStyle = 'background:var(--color-danger-soft); color:var(--color-danger); border:1px solid rgba(181,71,71,0.3);';
                        ?>
                        <span class="badge rounded-pill px-4 py-2 fs-6 w-100 text-uppercase" style="letter-spacing:0.5px; <?= $badgeStyle; ?>">
                            <?= strtoupper($data['order']['status']); ?>
                        </span>
                    </div>

                    <?php if(in_array($_SESSION['user_role'], ['admin', 'operator'])): ?>
                    <form action="<?= BASEURL; ?>/adminorder/update_status" method="POST" class="border-top pt-4" style="border-color:var(--color-border) !important;">
                        <input type="hidden" name="csrf_token" value="<?= Security::generateCsrfToken(); ?>">
                        <input type="hidden" name="order_id" value="<?= $data['order']['id']; ?>">
                        <input type="hidden" name="redirect_to" value="<?= BASEURL; ?>/adminorder/detail/<?= $data['order']['id']; ?>">
                        <label class="text-muted small mb-2 d-block">Ubah Status</label>
                        <div class="input-group">
                            <select name="status" class="form-select shadow-none" style="border-color:var(--color-border);">
                                <option value="pending" <?= $data['order']['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="processing" <?= $data['order']['status'] == 'processing' ? 'selected' : ''; ?>>Processing</option>
                                <option value="shipped" <?= $data['order']['status'] == 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                <option value="completed" <?= $data['order']['status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                                <option value="cancelled" <?= $data['order']['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                            </select>
                            <button type="submit" class="btn btn-primary px-3">Update</button>
                        </div>
                    </form>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Bukti Transfer Card -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4 text-center">
                    <h5 class="fw-bold text-dark mb-4 text-start">Bukti Pembayaran</h5>
                    
                    <?php if(!empty($data['payment'])): ?>
                        <div class="mb-3">
                            <span class="badge bg-light text-dark border px-3 py-2">
                                <i class="bi bi-wallet2 me-2"></i>Metode: <?= strtoupper($data['payment']['payment_method']); ?>
                            </span>
                        </div>
                        <?php if($data['payment']['payment_method'] === 'Cash'): ?>
                            <div class="py-5 bg-light border rounded-3 text-muted">
                                <i class="bi bi-cash-stack d-block fs-1 mb-2 opacity-75 text-success"></i>
                                <span class="fw-bold">Pembayaran Tunai (Cash)</span><br>
                                <small>Tagih langsung ke pembeli</small>
                            </div>
                        <?php elseif(!empty($data['payment']['payment_proof']) && $data['payment']['payment_proof'] !== 'cash_transaction'): ?>
                            <a href="<?= BASEURL; ?>/uploads/payments/<?= $data['payment']['payment_proof']; ?>" target="_blank" class="d-block mt-3 position-relative rounded-3 overflow-hidden border">
                                <img src="<?= BASEURL; ?>/uploads/payments/<?= $data['payment']['payment_proof']; ?>" alt="Bukti Transfer" class="img-fluid w-100" style="max-height: 250px; object-fit: cover;">
                                <div class="position-absolute bottom-0 w-100 p-2 bg-dark bg-opacity-75 text-white small">
                                    <i class="bi bi-zoom-in me-1"></i> Klik untuk perbesar
                                </div>
                            </a>
                        <?php else: ?>
                            <div class="py-5 bg-light rounded-3 text-muted">
                                <i class="bi bi-image d-block fs-1 mb-2 opacity-25"></i>
                                Tidak ada foto
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="py-5 border border-dashed rounded-3 text-muted">
                            <i class="bi bi-clock-history d-block fs-1 mb-2 opacity-25"></i>
                            Belum ada pembayaran
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Log Notifikasi WhatsApp Pelanggan -->
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h5 class="fw-bold text-dark mb-0">
                            <i class="bi bi-whatsapp me-2 text-success"></i>Notifikasi WhatsApp
                        </h5>
                    </div>

                    <?php if (empty($data['notifications'])): ?>
                        <div class="text-center py-4 text-muted small">
                            <i class="bi bi-chat-left-dots d-block fs-3 mb-2 opacity-50"></i>
                            Belum ada riwayat notifikasi WhatsApp untuk pesanan ini.
                        </div>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($data['notifications'] as $log): ?>
                                <div class="list-group-item px-0 py-3 border-bottom">
                                    <div class="d-flex justify-content-between align-items-start mb-1">
                                        <div>
                                            <?php if (!empty($log['whatsapp_status']) && $log['whatsapp_status'] === 'SENT'): ?>
                                                <span class="badge bg-success rounded-pill px-3 py-1 small">
                                                    <i class="bi bi-whatsapp me-1"></i>WA Terkirim
                                                </span>
                                            <?php elseif (!empty($log['whatsapp_status']) && $log['whatsapp_status'] === 'FAILED'): ?>
                                                <span class="badge bg-danger rounded-pill px-3 py-1 small">
                                                    <i class="bi bi-x-circle me-1"></i>WA Gagal
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary rounded-pill px-3 py-1 small">
                                                    <i class="bi bi-clock me-1"></i>WA Belum Dikirim
                                                </span>
                                            <?php endif; ?>
                                            <small class="text-muted d-block mt-1 fw-bold"><?= strtoupper($log['old_status']); ?> &rarr; <?= strtoupper($log['new_status']); ?></small>
                                        </div>
                                        <small class="text-muted"><?= date('d/m H:i', strtotime($log['created_at'])); ?></small>
                                    </div>
                                    <div class="small text-muted mt-2">
                                        <?php 
                                            $custPhone = !empty($log['whatsapp_phone']) ? $log['whatsapp_phone'] : (!empty($data['order']['phone']) ? $data['order']['phone'] : ($data['customer']['phone'] ?? ''));
                                        ?>
                                        <?php if (!empty($custPhone)): ?>
                                            <span class="text-dark fw-semibold"><i class="bi bi-whatsapp text-success me-1"></i><?= Security::escape($custPhone); ?></span>
                                        <?php else: ?>
                                            <span class="text-muted fst-italic"><i class="bi bi-exclamation-circle text-warning me-1"></i>Nomor WA belum ada</span>
                                        <?php endif; ?>
                                    </div>
                                    <?php if (!empty($log['whatsapp_error']) && $log['whatsapp_status'] === 'FAILED'): ?>
                                        <div class="small text-danger mt-2 bg-danger bg-opacity-10 p-2 rounded-2">
                                            <i class="bi bi-exclamation-triangle me-1"></i><?= Security::escape($log['whatsapp_error']); ?>
                                        </div>
                                    <?php endif; ?>
                                    <div class="mt-3">
                                        <form action="<?= BASEURL; ?>/adminorder/send_whatsapp" method="POST" class="d-inline mb-0">
                                            <input type="hidden" name="csrf_token" value="<?= Security::generateCsrfToken(); ?>">
                                            <input type="hidden" name="notification_id" value="<?= $log['id']; ?>">
                                            <input type="hidden" name="phone" value="<?= Security::escape($custPhone); ?>">
                                            <button type="submit" class="btn btn-success btn-sm rounded-pill py-2 px-3 fw-semibold shadow-sm">
                                                <i class="bi bi-whatsapp me-1"></i>Kirim Notif WA
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>
</div>
