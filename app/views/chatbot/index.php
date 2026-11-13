<div class="container py-4">
    <!-- Breadcrumb & Header -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="<?= BASEURL; ?>/dashboard" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Riwayat Chatbot</li>
                </ol>
            </nav>
            <h2 class="fw-bold mb-0 d-flex align-items-center gap-2">
                <i class="bi bi-chat-dots-fill text-primary"></i> Riwayat Percakapan Chatbot
            </h2>
            <p class="text-muted small mb-0">Baca dan pantau seluruh dialog antara customer/pengunjung dengan AI Assistant.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= BASEURL; ?>/settings/ai" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                <i class="bi bi-gear-fill me-1"></i> Pengaturan AI
            </a>
            <a href="<?= BASEURL; ?>/chatbot" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                <i class="bi bi-arrow-clockwise me-1"></i> Refresh
            </a>
        </div>
    </div>

    <!-- Summary KPI Cards -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card card-soft p-3 h-100 border-0 shadow-sm" style="background:var(--surface);">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-medium">Total Sesi Chat</div>
                        <h3 class="fw-bold mb-0 mt-1"><?= number_format($data['stats']['total_conversations']); ?></h3>
                    </div>
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:48px; height:48px; background:var(--color-accent-soft);">
                        <i class="bi bi-chat-text-fill fs-4" style="color:var(--color-primary);"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card card-soft p-3 h-100 border-0 shadow-sm" style="background:var(--surface);">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-medium">Customer Terdaftar</div>
                        <h3 class="fw-bold mb-0 mt-1 text-success"><?= number_format($data['stats']['registered_conversations']); ?></h3>
                    </div>
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:48px; height:48px; background:rgba(40, 167, 69, 0.15);">
                        <i class="bi bi-person-check-fill text-success fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card card-soft p-3 h-100 border-0 shadow-sm" style="background:var(--surface);">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-medium">Pengunjung Anonim</div>
                        <h3 class="fw-bold mb-0 mt-1 text-secondary"><?= number_format($data['stats']['anonymous_conversations']); ?></h3>
                    </div>
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:48px; height:48px; background:rgba(108, 117, 125, 0.15);">
                        <i class="bi bi-person-x-fill text-secondary fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card card-soft p-3 h-100 border-0 shadow-sm" style="background:var(--surface);">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-medium">Total Pesan Diproses</div>
                        <h3 class="fw-bold mb-0 mt-1 text-info"><?= number_format($data['stats']['total_messages']); ?></h3>
                    </div>
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:48px; height:48px; background:rgba(13, 202, 240, 0.15);">
                        <i class="bi bi-chat-quote-fill text-info fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Search & Filter Card -->
    <div class="card card-soft p-4 border-0 shadow-sm mb-4" style="background:var(--surface); border-radius:16px;">
        <form method="GET" action="<?= BASEURL; ?>/chatbot" class="row g-3 align-items-end">
            <!-- Keyword Search -->
            <div class="col-12 col-md-4">
                <label class="form-label small fw-semibold text-muted mb-1">
                    <i class="bi bi-search me-1"></i> Pencarian
                </label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="keyword" class="form-control border-0 bg-light shadow-none" 
                           placeholder="Nama, email, ID percakapan, isi chat..." 
                           value="<?= Security::escape($data['filters']['keyword']); ?>">
                </div>
            </div>

            <!-- User Type Filter -->
            <div class="col-6 col-md-2">
                <label class="form-label small fw-semibold text-muted mb-1">Tipe Pengguna</label>
                <select name="user_type" class="form-select border-0 bg-light shadow-none">
                    <option value="all" <?= $data['filters']['user_type'] === 'all' ? 'selected' : ''; ?>>Semua Tipe</option>
                    <option value="registered" <?= $data['filters']['user_type'] === 'registered' ? 'selected' : ''; ?>>Customer Terdaftar</option>
                    <option value="anonymous" <?= $data['filters']['user_type'] === 'anonymous' ? 'selected' : ''; ?>>Pengunjung Anonim</option>
                </select>
            </div>

            <!-- Status Filter -->
            <div class="col-6 col-md-2">
                <label class="form-label small fw-semibold text-muted mb-1">Status</label>
                <select name="status" class="form-select border-0 bg-light shadow-none">
                    <option value="all" <?= $data['filters']['status'] === 'all' ? 'selected' : ''; ?>>Semua Status</option>
                    <option value="completed" <?= $data['filters']['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                    <option value="failed" <?= $data['filters']['status'] === 'failed' ? 'selected' : ''; ?>>Failed</option>
                </select>
            </div>

            <!-- Date Range: Start -->
            <div class="col-6 col-md-2">
                <label class="form-label small fw-semibold text-muted mb-1">Dari Tanggal</label>
                <input type="date" name="start_date" class="form-control border-0 bg-light shadow-none" 
                       value="<?= Security::escape($data['filters']['start_date']); ?>">
            </div>

            <!-- Date Range: End -->
            <div class="col-6 col-md-2">
                <label class="form-label small fw-semibold text-muted mb-1">Sampai Tanggal</label>
                <input type="date" name="end_date" class="form-control border-0 bg-light shadow-none" 
                       value="<?= Security::escape($data['filters']['end_date']); ?>">
            </div>

            <!-- Limit & Buttons -->
            <div class="col-12 d-flex flex-wrap justify-content-between align-items-center gap-2 pt-2 border-top">
                <div class="d-flex align-items-center gap-2">
                    <span class="small text-muted">Tampilkan:</span>
                    <select name="limit" class="form-select form-select-sm border-0 bg-light shadow-none w-auto" onchange="this.form.submit()">
                        <option value="10" <?= $data['limit'] === 10 ? 'selected' : ''; ?>>10 / hal</option>
                        <option value="25" <?= $data['limit'] === 25 ? 'selected' : ''; ?>>25 / hal</option>
                        <option value="50" <?= $data['limit'] === 50 ? 'selected' : ''; ?>>50 / hal</option>
                        <option value="100" <?= $data['limit'] === 100 ? 'selected' : ''; ?>>100 / hal</option>
                    </select>
                </div>
                <div class="d-flex gap-2">
                    <a href="<?= BASEURL; ?>/chatbot" class="btn btn-light btn-sm rounded-pill px-3">
                        <i class="bi bi-x-circle me-1"></i> Reset Filter
                    </a>
                    <button type="submit" class="btn btn-primary-soft btn-sm rounded-pill px-4 shadow-sm">
                        <i class="bi bi-funnel-fill me-1"></i> Terapkan Filter
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Conversation Table Card -->
    <div class="card card-soft border-0 shadow-sm overflow-hidden" style="background:var(--surface); border-radius:16px;">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light border-0">
                    <tr class="text-muted small text-uppercase" style="letter-spacing: 0.5px;">
                        <th class="ps-4 py-3" style="min-width: 170px;">Customer / Pengguna</th>
                        <th style="min-width: 180px;">ID Percakapan</th>
                        <th style="min-width: 250px;">Cuplikan Pesan Terakhir</th>
                        <th style="min-width: 100px;" class="text-center">Jml Pesan</th>
                        <th style="min-width: 150px;">Waktu Terakhir</th>
                        <th style="min-width: 100px;" class="text-center">Status</th>
                        <th class="pe-4 py-3 text-end" style="min-width: 140px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data['conversations'])): ?>
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <div class="py-4">
                                    <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width:70px; height:70px; background:rgba(248, 200, 220, 0.3);">
                                        <i class="bi bi-chat-square-dots text-primary fs-2"></i>
                                    </div>
                                    <h5 class="fw-bold text-muted">Belum Ada Percakapan Ditemukan</h5>
                                    <p class="text-muted small mb-3">Tidak ada riwayat chat yang sesuai dengan kriteria pencarian atau filter yang dipilih.</p>
                                    <a href="<?= BASEURL; ?>/chatbot" class="btn btn-sm btn-outline-secondary rounded-pill px-4">
                                        Hapus Filter
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($data['conversations'] as $row): ?>
                            <tr>
                                <!-- Customer Identity -->
                                <td class="ps-4 py-3">
                                    <?php if (!empty($row['user_id']) && !empty($row['customer_name'])): ?>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="rounded-circle bg-success-subtle text-success d-flex align-items-center justify-content-center fw-bold" style="width:36px; height:36px; font-size:13px;">
                                                <?= strtoupper(substr($row['customer_name'], 0, 1)); ?>
                                            </div>
                                            <div>
                                                <div class="fw-bold text-truncate" style="max-width: 160px;">
                                                    <?= Security::escape($row['customer_name']); ?>
                                                </div>
                                                <div class="small text-muted text-truncate" style="max-width: 160px;">
                                                    <?= Security::escape($row['customer_email'] ?: '-'); ?>
                                                </div>
                                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill" style="font-size:10px;">
                                                    <i class="bi bi-person-fill"></i> ID #<?= (int)$row['user_id']; ?>
                                                </span>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="rounded-circle bg-secondary-subtle text-secondary d-flex align-items-center justify-content-center fw-bold" style="width:36px; height:36px; font-size:13px;">
                                                <i class="bi bi-incognito"></i>
                                            </div>
                                            <div>
                                                <div class="fw-bold text-secondary">Pengunjung Anonim</div>
                                                <div class="small text-muted" style="font-size:11px;">
                                                    <?= !empty($row['ip_address']) ? Security::escape($row['ip_address']) : 'Tamu Website'; ?>
                                                </div>
                                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill" style="font-size:10px;">
                                                    Belum Login
                                                </span>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </td>

                                <!-- Conversation ID -->
                                <td>
                                    <span class="font-monospace small bg-light px-2 py-1 rounded text-truncate d-inline-block border" style="max-width: 170px;" title="<?= Security::escape($row['conversation_id']); ?>">
                                        <?= Security::escape($row['conversation_id']); ?>
                                    </span>
                                    <?php if (!empty($row['model_id'])): ?>
                                        <div class="text-muted" style="font-size:10px;" title="<?= Security::escape($row['model_id']); ?>">
                                            <i class="bi bi-cpu me-1"></i><?= Security::escape(substr($row['model_id'], 0, 22)) . (strlen($row['model_id']) > 22 ? '...' : ''); ?>
                                        </div>
                                    <?php endif; ?>
                                </td>

                                <!-- Last Message Snippet -->
                                <td>
                                    <?php if (!empty($row['last_user_message'])): ?>
                                        <div class="small text-truncate mb-1" style="max-width: 250px;">
                                            <span class="badge bg-light text-dark me-1 border">User</span>
                                            <?= Security::escape($row['last_user_message']); ?>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (!empty($row['last_bot_response'])): ?>
                                        <div class="small text-muted text-truncate" style="max-width: 250px;">
                                            <span class="badge bg-primary-subtle text-dark me-1">AI</span>
                                            <?= Security::escape($row['last_bot_response']); ?>
                                        </div>
                                    <?php endif; ?>
                                </td>

                                <!-- Message Count -->
                                <td class="text-center">
                                    <span class="badge bg-light text-dark border px-3 py-2 rounded-pill font-monospace">
                                        <?= (int)$row['total_messages']; ?>
                                    </span>
                                </td>

                                <!-- Last Message Time -->
                                <td>
                                    <div class="small fw-medium">
                                        <?= date('d M Y, H:i', strtotime($row['last_message_at'])); ?>
                                    </div>
                                    <div class="text-muted" style="font-size:11px;">
                                        Mulai: <?= date('d M, H:i', strtotime($row['started_at'])); ?>
                                    </div>
                                </td>

                                <!-- Status Badge -->
                                <td class="text-center">
                                    <?php if ($row['status'] === 'completed'): ?>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1">
                                            <i class="bi bi-check-circle-fill me-1"></i>Completed
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2 py-1">
                                            <i class="bi bi-exclamation-circle-fill me-1"></i><?= Security::escape(ucfirst($row['status'])); ?>
                                        </span>
                                    <?php endif; ?>
                                </td>

                                <!-- Action Button -->
                                <td class="pe-4 py-3 text-end">
                                    <a href="<?= BASEURL; ?>/chatbot/detail/<?= urlencode($row['conversation_id']); ?>" 
                                       class="btn btn-sm btn-outline-primary rounded-pill px-3 shadow-none">
                                        <i class="bi bi-chat-left-text me-1"></i> Lihat Percakapan
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination Footer -->
        <?php if ($data['totalRecords'] > 0): ?>
            <div class="card-footer bg-transparent border-0 px-4 py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div class="small text-muted">
                    Menampilkan <strong><?= min($data['totalRecords'], ($data['currentPage'] - 1) * $data['limit'] + 1); ?></strong> 
                    sampai <strong><?= min($data['totalRecords'], $data['currentPage'] * $data['limit']); ?></strong> 
                    dari <strong><?= number_format($data['totalRecords']); ?></strong> percakapan
                </div>

                <?php if ($data['totalPages'] > 1): ?>
                    <?php
                        $queryParams = $_GET;
                        $makeUrl = function($pageNum) use ($queryParams) {
                            $queryParams['page'] = $pageNum;
                            return BASEURL . '/chatbot?' . http_build_query($queryParams);
                        };
                    ?>
                    <nav aria-label="Navigasi halaman">
                        <ul class="pagination pagination-sm mb-0">
                            <!-- Prev Button -->
                            <li class="page-item <?= $data['currentPage'] <= 1 ? 'disabled' : ''; ?>">
                                <a class="page-link shadow-none" href="<?= $makeUrl($data['currentPage'] - 1); ?>">
                                    <i class="bi bi-chevron-left"></i>
                                </a>
                            </li>

                            <?php
                                $startP = max(1, $data['currentPage'] - 2);
                                $endP = min($data['totalPages'], $data['currentPage'] + 2);
                                for ($p = $startP; $p <= $endP; $p++):
                            ?>
                                <li class="page-item <?= $p === $data['currentPage'] ? 'active' : ''; ?>">
                                    <a class="page-link shadow-none" href="<?= $makeUrl($p); ?>">
                                        <?= $p; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>

                            <!-- Next Button -->
                            <li class="page-item <?= $data['currentPage'] >= $data['totalPages'] ? 'disabled' : ''; ?>">
                                <a class="page-link shadow-none" href="<?= $makeUrl($data['currentPage'] + 1); ?>">
                                    <i class="bi bi-chevron-right"></i>
                                </a>
                            </li>
                        </ul>
                    </nav>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
