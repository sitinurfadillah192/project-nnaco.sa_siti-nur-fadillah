<div class="container-fluid py-4 px-lg-5">
    <!-- Header Title & Breadcrumb -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="<?= BASEURL; ?>/dashboard" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item">Pengaturan Sistem</li>
                    <li class="breadcrumb-item active" aria-current="page">Autentikasi OTP WhatsApp</li>
                </ol>
            </nav>
            <h2 class="fw-bold mb-0 text-dark">
                <i class="bi bi-shield-lock text-primary me-2"></i>Konfigurasi Autentikasi OTP WhatsApp
            </h2>
            <p class="text-muted small mb-0">Kelola gateway WhatsApp Fonnte, masa berlaku OTP, dan kebijakan batas percobaan verifikasi pelanggan.</p>
        </div>
    </div>

    <!-- Navigation Tabs Pengaturan Sistem -->
    <ul class="nav nav-pills mb-4 bg-white p-2 rounded-4 shadow-sm border" style="max-width: 480px;">
        <li class="nav-item flex-fill text-center">
            <a class="nav-link text-muted rounded-pill fw-semibold py-2" href="<?= BASEURL; ?>/settings/ai">
                <i class="bi bi-cpu me-2"></i>Konfigurasi AI
            </a>
        </li>
        <li class="nav-item flex-fill text-center">
            <a class="nav-link active rounded-pill fw-semibold py-2" href="<?= BASEURL; ?>/settings/otp">
                <i class="bi bi-shield-lock me-2"></i>Autentikasi OTP
            </a>
        </li>
    </ul>

    <!-- Flash Notification -->
    <?php Flasher::flash(); ?>

    <form action="<?= BASEURL; ?>/settings/otp_save" method="POST" id="otpSettingsForm">
        <input type="hidden" name="csrf_token" id="csrf_token" value="<?= $data['csrf_token']; ?>">
        <input type="hidden" name="otp_allowed_channels" value="whatsapp">

        <!-- Inner Tabs: WhatsApp & Kebijakan OTP -->
        <div class="card border-0 shadow-sm rounded-4 bg-white mb-4">
            <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                <ul class="nav nav-tabs card-header-tabs" id="otpTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active fw-semibold text-dark pb-3" id="whatsapp-tab" data-bs-toggle="tab" data-bs-target="#whatsapp" type="button" role="tab">
                            <i class="bi bi-whatsapp text-success me-2"></i>WhatsApp Gateway (Fonnte)
                            <?php if (!empty($data['fonnte_config']['is_enabled'])): ?>
                                <span class="badge bg-success ms-2">Aktif</span>
                            <?php else: ?>
                                <span class="badge bg-light text-muted border ms-2">Nonaktif</span>
                            <?php endif; ?>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-semibold text-dark pb-3" id="policy-tab" data-bs-toggle="tab" data-bs-target="#policy" type="button" role="tab">
                            <i class="bi bi-sliders text-warning me-2"></i>Kebijakan Keamanan OTP
                        </button>
                    </li>
                </ul>
            </div>

            <div class="card-body p-4 p-lg-5">
                <div class="tab-content" id="otpTabsContent">
                    
                    <!-- ========================================================= -->
                    <!-- TAB 1: WHATSAPP FONNTE -->
                    <!-- ========================================================= -->
                    <div class="tab-pane fade show active" id="whatsapp" role="tabpanel">
                        <div class="row g-4">
                            <!-- Toggle Activation -->
                            <div class="col-12">
                                <div class="p-3 bg-light rounded-4 border d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="fw-bold mb-1 text-dark">Status Layanan WhatsApp OTP</h6>
                                        <p class="text-muted small mb-0">Aktifkan pengiriman kode verifikasi 6 digit ke nomor WhatsApp pelanggan melalui gateway resmi Fonnte.</p>
                                    </div>
                                    <div class="form-check form-switch fs-4">
                                        <input class="form-check-input" type="checkbox" name="fonnte_enabled" id="fonnte_enabled" value="1" <?= !empty($data['fonnte_config']['is_enabled']) ? 'checked' : ''; ?>>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-8">
                                <label class="form-label small fw-semibold text-dark">Fonnte API Endpoint <span class="text-danger">*</span></label>
                                <input type="url" class="form-control font-monospace" name="fonnte_api_url" id="fonnte_api_url" value="<?= Security::escape($data['fonnte_config']['settings']['api_url'] ?? 'https://api.fonnte.com/send'); ?>" required>
                                <div class="form-text small">Default endpoint resmi Fonnte: <code>https://api.fonnte.com/send</code>. Terlindungi dari SSRF.</div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label small fw-semibold text-dark">Default Country Code</label>
                                <input type="text" class="form-control" name="fonnte_country_code" id="fonnte_country_code" value="<?= Security::escape($data['fonnte_config']['settings']['country_code'] ?? '62'); ?>">
                            </div>

                            <div class="col-12">
                                <label class="form-label small fw-semibold text-dark">Fonnte API Token Secret</label>
                                <div class="input-group">
                                    <input type="password" class="form-control font-monospace" name="fonnte_token" id="fonnte_token" placeholder="Masukkan token Fonnte baru jika ingin mengganti">
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('fonnte_token')">
                                        <i class="bi bi-eye" id="fonnte_token_icon"></i>
                                    </button>
                                </div>
                                <div class="form-text small">
                                    Terenkripsi AES-256-GCM. Token tersimpan: 
                                    <span class="font-monospace text-dark fw-bold"><?= Security::escape($data['fonnte_config']['masked_credentials'] ?? 'Belum diset'); ?></span>. 
                                    Kosongkan jika tidak ingin mengubah token lama.
                                </div>
                            </div>

                            <!-- Diagnostic Testing WhatsApp Box -->
                            <div class="col-12 mt-4">
                                <div class="card border border-info border-opacity-25 rounded-4 bg-info bg-opacity-10">
                                    <div class="card-body p-4">
                                        <h6 class="fw-bold text-dark mb-1"><i class="bi bi-broadcast me-2 text-info"></i>Pengujian Koneksi & Pengiriman Uji WhatsApp</h6>
                                        <p class="text-muted small mb-3">Uji koneksi ke endpoint Fonnte tanpa mengirim pesan, atau kirim pesan uji coba ke nomor tujuan yang ditentukan.</p>
                                        
                                        <div class="d-flex flex-wrap gap-2 mb-3">
                                            <button type="button" class="btn btn-info text-white rounded-pill px-4" id="btnTestFonnte" onclick="testFonnteConnection()">
                                                <i class="bi bi-lightning-charge me-1"></i> Uji Koneksi Gateway (Cek Device)
                                            </button>
                                        </div>

                                        <div id="fonnteTestFeedback" style="display:none;" class="alert mb-3"></div>

                                        <hr class="opacity-25">

                                        <label class="form-label small fw-semibold text-dark">Kirim Pesan WhatsApp Uji (Explicit Test)</label>
                                        <div class="input-group" style="max-width: 480px;">
                                            <input type="text" class="form-control" id="test_wa_phone" placeholder="Contoh: 08123456789">
                                            <button type="button" class="btn btn-success" id="btnSendTestWa" onclick="sendTestWhatsApp()">
                                                <i class="bi bi-send me-1"></i> Kirim Pesan Uji
                                            </button>
                                        </div>
                                        <div class="form-text small">Pesan hanya akan dikirim jika tombol ini ditekan secara eksplisit.</div>
                                        <div id="fonnteSendFeedback" style="display:none;" class="alert mt-2 mb-0"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ========================================================= -->
                    <!-- TAB 2: OTP POLICIES -->
                    <!-- ========================================================= -->
                    <div class="tab-pane fade" id="policy" role="tabpanel">
                        <div class="row g-4">
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold text-dark">Panjang Digit OTP</label>
                                <select class="form-select" name="otp_length" id="otp_length">
                                    <option value="4" <?= ($data['otp_policies']['otp_length'] ?? 6) == 4 ? 'selected' : ''; ?>>4 Digit</option>
                                    <option value="6" <?= ($data['otp_policies']['otp_length'] ?? 6) == 6 ? 'selected' : ''; ?>>6 Digit (Standar Rekomendasi)</option>
                                    <option value="8" <?= ($data['otp_policies']['otp_length'] ?? 6) == 8 ? 'selected' : ''; ?>>8 Digit</option>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label small fw-semibold text-dark">Masa Berlaku (Menit)</label>
                                <input type="number" class="form-control" name="otp_expiry_minutes" id="otp_expiry_minutes" value="<?= (int)($data['otp_policies']['otp_expiry_minutes'] ?? 5); ?>" min="1" max="60" required>
                                <div class="form-text small">Default: 5 menit.</div>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label small fw-semibold text-dark">Maksimal Percobaan</label>
                                <input type="number" class="form-control" name="otp_max_attempts" id="otp_max_attempts" value="<?= (int)($data['otp_policies']['otp_max_attempts'] ?? 5); ?>" min="1" max="10" required>
                                <div class="form-text small">Batas salah input sebelum kedaluwarsa (Default: 5x).</div>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label small fw-semibold text-dark">Cooldown Kirim Ulang (Detik)</label>
                                <input type="number" class="form-control" name="otp_cooldown_seconds" id="otp_cooldown_seconds" value="<?= (int)($data['otp_policies']['otp_cooldown_seconds'] ?? 60); ?>" min="10" max="300" required>
                                <div class="form-text small">Jeda waktu sebelum minta kode baru (Default: 60 dtk).</div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label small fw-semibold text-dark">Maksimal Request Per Nomor (Per Jam)</label>
                                <input type="number" class="form-control" name="otp_max_requests_phone_hour" id="otp_max_requests_phone_hour" value="<?= (int)($data['otp_policies']['otp_max_requests_phone_hour'] ?? 5); ?>" min="1" max="50" required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label small fw-semibold text-dark">Maksimal Request Per IP (Per Jam)</label>
                                <input type="number" class="form-control" name="otp_max_requests_ip_hour" id="otp_max_requests_ip_hour" value="<?= (int)($data['otp_policies']['otp_max_requests_ip_hour'] ?? 15); ?>" min="5" max="100" required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label small fw-semibold text-dark">Kanal Autentikasi Aktif</label>
                                <input type="text" class="form-control bg-light text-success fw-semibold" value="WhatsApp (Fonnte) Khusus" readonly>
                            </div>

                            <div class="col-md-6">
                                <div class="p-3 bg-light rounded-3 border">
                                    <div class="form-check form-switch mb-1">
                                        <input class="form-check-input" type="checkbox" name="otp_auto_register" id="otp_auto_register" value="1" <?= !empty($data['otp_policies']['otp_auto_register']) ? 'checked' : ''; ?>>
                                        <label class="form-check-label fw-semibold text-dark" for="otp_auto_register">
                                            Registrasi Otomatis Akun Baru
                                        </label>
                                    </div>
                                    <small class="text-muted">Jika nomor WhatsApp belum terdaftar saat verifikasi OTP berhasil, otomatis buat akun baru.</small>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="p-3 bg-light rounded-3 border">
                                    <div class="form-check form-switch mb-1">
                                        <input class="form-check-input" type="checkbox" name="otp_login_required" id="otp_login_required" value="1" <?= !empty($data['otp_policies']['otp_login_required']) ? 'checked' : ''; ?>>
                                        <label class="form-check-label fw-semibold text-dark" for="otp_login_required">
                                            Wajibkan Login dengan OTP WhatsApp
                                        </label>
                                    </div>
                                    <small class="text-danger">
                                        <i class="bi bi-shield-exclamation me-1"></i> Jika diaktifkan, semua pengguna wajib memverifikasi OTP WhatsApp untuk masuk.
                                    </small>
                                </div>
                            </div>

                            <div class="col-12">
                                <label class="form-label small fw-semibold text-dark">Template Pesan WhatsApp</label>
                                <textarea class="form-control" name="otp_wa_template" id="otp_wa_template" rows="3" required><?= Security::escape($data['otp_policies']['otp_wa_template'] ?? ''); ?></textarea>
                                <div class="form-text small">Variabel yang tersedia: <code>{app_name}</code>, <code>{otp}</code>, <code>{expiry}</code>. Format wajib menyertakan <code>{otp}</code>.</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card Footer: Save Button -->
            <div class="card-footer bg-white border-top p-4 d-flex justify-content-between align-items-center">
                <span class="text-muted small">
                    <i class="bi bi-shield-check text-success me-1"></i> Kredensial token WhatsApp terlindungi enkripsi AES-256-GCM.
                </span>
                <button type="submit" class="btn btn-primary rounded-pill px-5 py-2 fw-semibold shadow-sm">
                    <i class="bi bi-check-circle-fill me-2"></i>Simpan Pengaturan OTP WhatsApp
                </button>
            </div>
        </div>
    </form>
</div>

<script>
const CSRF_TOKEN = '<?= $data['csrf_token']; ?>';
const BASE_URL = '<?= BASEURL; ?>';

function togglePasswordVisibility(fieldId) {
    const input = document.getElementById(fieldId);
    const icon = document.getElementById(fieldId + '_icon');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('bi-eye', 'bi-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.replace('bi-eye-slash', 'bi-eye');
    }
}

// Test WhatsApp Fonnte Connection (Device Status)
function testFonnteConnection() {
    const token = document.getElementById('fonnte_token').value.trim();
    const apiUrl = document.getElementById('fonnte_api_url').value.trim();
    const btn = document.getElementById('btnTestFonnte');
    const fb = document.getElementById('fonnteTestFeedback');

    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Menguji Fonnte...';
    fb.style.display = 'block';
    fb.className = 'alert alert-info mb-3';
    fb.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Menghubungi endpoint device Fonnte...';

    const fd = new FormData();
    fd.append('csrf_token', CSRF_TOKEN);
    fd.append('channel', 'whatsapp');
    fd.append('token', token);
    fd.append('api_url', apiUrl);

    fetch(BASE_URL + '/settings/otp_test', {
        method: 'POST',
        body: fd
    })
    .then(r => r.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-lightning-charge me-1"></i> Uji Koneksi Gateway (Cek Device)';
        if (data.success) {
            fb.className = 'alert alert-success mb-3';
            fb.innerHTML = '<i class="bi bi-check-circle-fill me-2"></i>' + data.message + ' (' + data.latency_ms + ' ms)';
        } else {
            fb.className = 'alert alert-danger mb-3';
            fb.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-2"></i>' + data.message;
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-lightning-charge me-1"></i> Uji Koneksi Gateway (Cek Device)';
        fb.className = 'alert alert-danger mb-3';
        fb.innerHTML = '<i class="bi bi-x-circle-fill me-2"></i>Kesalahan AJAX: ' + err.message;
    });
}

// Send Test WhatsApp Message
function sendTestWhatsApp() {
    const phone = document.getElementById('test_wa_phone').value.trim();
    const btn = document.getElementById('btnSendTestWa');
    const fb = document.getElementById('fonnteSendFeedback');

    if (!phone) {
        alert('Mohon masukkan nomor WhatsApp tujuan.');
        return;
    }

    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Mengirim...';
    fb.style.display = 'block';
    fb.className = 'alert alert-info mt-2 mb-0';
    fb.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Mengirim pesan WhatsApp uji coba...';

    const fd = new FormData();
    fd.append('csrf_token', CSRF_TOKEN);
    fd.append('channel', 'whatsapp');
    fd.append('target', phone);

    fetch(BASE_URL + '/settings/otp_send_test', {
        method: 'POST',
        body: fd
    })
    .then(r => r.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-send me-1"></i> Kirim Pesan Uji';
        if (data.success) {
            fb.className = 'alert alert-success mt-2 mb-0';
            fb.innerHTML = '<i class="bi bi-check-circle-fill me-2"></i>' + data.message;
        } else {
            fb.className = 'alert alert-danger mt-2 mb-0';
            fb.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-2"></i>' + data.message;
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-send me-1"></i> Kirim Pesan Uji';
        fb.className = 'alert alert-danger mt-2 mb-0';
        fb.innerHTML = '<i class="bi bi-x-circle-fill me-2"></i>Kesalahan pengiriman: ' + err.message;
    });
}
</script>
