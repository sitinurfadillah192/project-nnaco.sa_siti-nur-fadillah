<?php
$c = $data['conversation'];
$isRegistered = !empty($c['user_id']) && !empty($c['customer_name']);

// Helper to safely format chat text with XSS prevention
function renderSafeMessageContent($text, $isBot = false) {
    if ($text === null || $text === '') return '<em class="text-muted small">(Pesan kosong)</em>';
    // 1. Mandatory HTML escaping for XSS prevention
    $escaped = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');

    // 2. Format linebreaks
    $formatted = nl2br($escaped);

    // 3. For AI responses, allow safe markdown bold & bullet points
    if ($isBot) {
        $formatted = preg_replace('/\*\*(.*?)\*\*/s', '<strong>$1</strong>', $formatted);
        $formatted = preg_replace('/(?:^|<br\s*\/?>)[*-]\s+/s', '<br>&bull; ', $formatted);
    }

    return $formatted;
}
?>

<div class="container py-4">
    <!-- Top Action & Navigation -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div class="d-flex align-items-center gap-3">
            <a href="<?= BASEURL; ?>/chatbot" class="btn btn-outline-secondary btn-sm rounded-circle d-flex align-items-center justify-content-center shadow-none" style="width:38px; height:38px;" title="Kembali ke Daftar">
                <i class="bi bi-arrow-left fs-5"></i>
            </a>
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1">
                        <li class="breadcrumb-item"><a href="<?= BASEURL; ?>/dashboard" class="text-decoration-none">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="<?= BASEURL; ?>/chatbot" class="text-decoration-none">Riwayat Chatbot</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Detail Percakapan</li>
                    </ol>
                </nav>
                <h3 class="fw-bold mb-0 d-flex align-items-center gap-2">
                    <i class="bi bi-chat-square-text-fill text-primary"></i> Detail Percakapan
                </h3>
            </div>
        </div>

        <div class="d-flex align-items-center gap-2">
            <a href="<?= BASEURL; ?>/chatbot" class="btn btn-light btn-sm rounded-pill px-3">
                <i class="bi bi-list-ul me-1"></i> Kembali ke Daftar
            </a>
            <button onclick="window.print()" class="btn btn-outline-secondary btn-sm rounded-pill px-3 d-none d-md-inline-flex align-items-center">
                <i class="bi bi-printer me-1"></i> Cetak / Ekspor PDF
            </button>
        </div>
    </div>

    <!-- Metadata Header Card -->
    <div class="card card-soft p-4 border-0 shadow-sm mb-4" style="background:var(--surface); border-radius:18px;">
        <div class="row g-3 align-items-center">
            <!-- Customer Avatar & Main Info -->
            <div class="col-12 col-md-5 border-md-end">
                <div class="d-flex align-items-start gap-3">
                    <?php if ($isRegistered): ?>
                        <div class="rounded-circle bg-success-subtle text-success d-flex align-items-center justify-content-center fw-bold fs-4 flex-shrink-0" style="width:54px; height:54px;">
                            <?= strtoupper(substr($c['customer_name'], 0, 1)); ?>
                        </div>
                        <div>
                            <div class="d-flex align-items-center gap-2">
                                <h5 class="fw-bold mb-0"><?= Security::escape($c['customer_name']); ?></h5>
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">
                                    <i class="bi bi-check-circle-fill me-1"></i>Customer Terdaftar
                                </span>
                            </div>
                            <div class="text-muted small mt-1">
                                <i class="bi bi-envelope me-1"></i> <?= Security::escape($c['customer_email'] ?: '-'); ?>
                            </div>
                            <?php if (!empty($c['customer_phone'])): ?>
                                <div class="text-muted small">
                                    <i class="bi bi-whatsapp me-1 text-success"></i> <?= Security::escape($c['customer_phone']); ?>
                                </div>
                            <?php endif; ?>
                            <div class="text-muted small mt-1 font-monospace">
                                ID Customer: #<?= (int)$c['user_id']; ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="rounded-circle bg-secondary-subtle text-secondary d-flex align-items-center justify-content-center fs-4 flex-shrink-0" style="width:54px; height:54px;">
                            <i class="bi bi-incognito"></i>
                        </div>
                        <div>
                            <div class="d-flex align-items-center gap-2">
                                <h5 class="fw-bold mb-0 text-secondary">Pengunjung Anonim</h5>
                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill">
                                    Tamu Belum Login
                                </span>
                            </div>
                            <div class="text-muted small mt-1">
                                <i class="bi bi-hdd-network me-1"></i> IP Address: <?= !empty($c['ip_address']) ? Security::escape($c['ip_address']) : 'Tidak tercatat'; ?>
                            </div>
                            <div class="text-muted small" style="font-size:11px;">
                                Mengakses chatbot tanpa login akun.
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Operational Metadata -->
            <div class="col-12 col-md-7">
                <div class="row g-2">
                    <div class="col-6 col-lg-3">
                        <div class="p-2 rounded bg-light">
                            <div class="text-muted small" style="font-size:11px;">ID Percakapan</div>
                            <div class="fw-bold text-truncate small font-monospace" title="<?= Security::escape($c['conversation_id']); ?>">
                                <?= Security::escape($c['conversation_id']); ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-lg-3">
                        <div class="p-2 rounded bg-light">
                            <div class="text-muted small" style="font-size:11px;">Total Pesan</div>
                            <div class="fw-bold text-truncate small font-monospace">
                                <?= (int)$c['total_messages']; ?> Interaksi
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-lg-3">
                        <div class="p-2 rounded bg-light">
                            <div class="text-muted small" style="font-size:11px;">Waktu Dimulai</div>
                            <div class="fw-bold text-truncate small">
                                <?= date('d M Y, H:i', strtotime($c['started_at'])); ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-lg-3">
                        <div class="p-2 rounded bg-light">
                            <div class="text-muted small" style="font-size:11px;">Pesan Terakhir</div>
                            <div class="fw-bold text-truncate small">
                                <?= date('d M Y, H:i', strtotime($c['last_message_at'])); ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 mt-2">
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 p-2 rounded bg-light">
                            <div class="small text-muted d-flex align-items-center gap-2">
                                <span><i class="bi bi-cpu-fill text-primary me-1"></i> Model AI:</span>
                                <span class="badge bg-white text-dark border font-monospace">
                                    <?= !empty($c['model_id']) ? Security::escape($c['model_id']) : 'Default Model'; ?>
                                </span>
                            </div>
                            <div class="small">
                                Status: 
                                <?php if ($c['status'] === 'completed'): ?>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">
                                        <i class="bi bi-check-circle-fill me-1"></i>Completed
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill">
                                        <i class="bi bi-exclamation-circle-fill me-1"></i><?= Security::escape(ucfirst($c['status'])); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Chat Conversation Stream Card -->
    <div class="card card-soft border-0 shadow-sm overflow-hidden" style="background:var(--surface); border-radius:18px;">
        <!-- Card Header -->
        <div class="card-header bg-light border-0 py-3 px-4 d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-chat-left-dots text-primary"></i>
                <span class="fw-bold small text-uppercase" style="letter-spacing: 0.5px;">Kronologi Dialog Percakapan</span>
            </div>
            <span class="badge bg-white text-muted border font-monospace" style="font-size:11px;">
                Urutan: Terlama &rarr; Terbaru
            </span>
        </div>

        <!-- Chat Stream Body -->
        <div class="card-body p-4 p-md-5" style="background:var(--bg-color); min-height: 400px;">
            <?php if (empty($data['messages'])): ?>
                <div class="text-center py-5">
                    <i class="bi bi-chat-dots text-muted fs-1 mb-2 d-block"></i>
                    <p class="text-muted">Tidak ada pesan yang tersimpan dalam percakapan ini.</p>
                </div>
            <?php else: ?>
                <div class="d-flex flex-column gap-4">
                    <?php 
                    $turnIndex = 1;
                    foreach ($data['messages'] as $msg): 
                    ?>
                        <!-- Conversation Turn Divider -->
                        <div class="text-center my-1 position-relative">
                            <hr class="border-secondary opacity-25 m-0">
                            <span class="position-absolute top-50 start-50 translate-middle badge bg-light text-muted border rounded-pill px-3 py-1 font-monospace" style="font-size:10px;">
                                Interaksi #<?= $turnIndex++; ?> &bull; <?= date('d M Y, H:i:s', strtotime($msg['created_at'])); ?>
                            </span>
                        </div>

                        <!-- 1. USER / CUSTOMER MESSAGE (Right-Aligned) -->
                        <div class="d-flex justify-content-end align-items-start gap-2">
                            <div class="d-flex flex-column align-items-end" style="max-width: 82%;">
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <span class="small fw-semibold text-muted" style="font-size:12px;">
                                        <?= $isRegistered ? Security::escape($c['customer_name']) : 'Pengunjung Anonim'; ?>
                                    </span>
                                    <span class="badge bg-light text-muted border rounded-pill" style="font-size:10px;">
                                        <i class="bi bi-clock me-1"></i><?= date('H:i', strtotime($msg['created_at'])); ?>
                                    </span>
                                </div>
                                <div class="p-3 shadow-sm text-break" 
                                     style="background:var(--color-accent); color:var(--color-primary); border-radius:18px 18px 4px 18px; font-size:14.5px; line-height:1.6; font-weight:500;">
                                    <?= renderSafeMessageContent($msg['message'], false); ?>
                                </div>
                            </div>
                            <div class="rounded-circle d-flex align-items-center justify-content-center shadow-sm flex-shrink-0 mt-3" style="width:36px; height:36px; background:var(--color-primary); color:var(--color-accent);">
                                <i class="bi bi-person-fill fs-6"></i>
                            </div>
                        </div>

                        <!-- 2. BOT / AI ASSISTANT RESPONSE (Left-Aligned) -->
                        <div class="d-flex justify-content-start align-items-start gap-2">
                            <div class="rounded-circle d-flex align-items-center justify-content-center shadow-sm flex-shrink-0 mt-3" style="width:36px; height:36px; background:var(--color-primary); color:var(--color-accent);">
                                <i class="bi bi-robot fs-6"></i>
                            </div>
                            <div class="d-flex flex-column align-items-start" style="max-width: 82%;">
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <span class="small fw-bold" style="font-size:12px; color:var(--color-text);">
                                        NNACO Assistant
                                    </span>
                                    <span class="badge border-0 rounded-pill" style="font-size:10px; background:var(--color-accent-soft); color:var(--color-primary);">
                                        AI Bot
                                    </span>
                                    <span class="badge bg-light text-muted border rounded-pill" style="font-size:10px;">
                                        <i class="bi bi-clock me-1"></i><?= date('H:i', strtotime($msg['created_at'])); ?>
                                    </span>
                                    <?php if ($msg['status'] === 'failed'): ?>
                                        <span class="badge bg-danger-subtle text-danger rounded-pill" style="font-size:10px;">
                                            Failed Turn
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <div class="p-3 shadow-sm text-break" 
                                     style="background:var(--color-surface); color:var(--color-text); border:1px solid var(--color-border); border-radius:18px 18px 18px 4px; font-size:14.5px; line-height:1.6;">
                                    <?= renderSafeMessageContent($msg['response'], true); ?>
                                </div>
                                <?php if (!empty($msg['model_id'])): ?>
                                    <div class="text-muted mt-1 small" style="font-size:10px;">
                                        <i class="bi bi-cpu me-1"></i>Model: <?= Security::escape($msg['model_id']); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Chat Stream Footer Notice -->
        <div class="card-footer bg-light border-0 py-3 px-4 d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div class="small text-muted d-flex align-items-center gap-1">
                <i class="bi bi-shield-lock-fill text-success"></i>
                Data percakapan diarsipkan secara aman untuk keperluan audit dan penjaminan kualitas layanan pelanggan.
            </div>
            <a href="<?= BASEURL; ?>/chatbot" class="btn btn-outline-secondary btn-sm rounded-pill px-4">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Riwayat
            </a>
        </div>
    </div>
</div>
