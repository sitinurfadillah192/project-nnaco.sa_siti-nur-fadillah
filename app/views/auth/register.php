<div class="row justify-content-center py-4">
    <div class="col-md-7 col-lg-5">
        <div class="card card-soft border shadow-sm rounded-4" style="background:var(--color-surface); border-color:var(--color-border) !important;">
            <div class="card-body p-4 p-md-5">
                <div class="text-center mb-4">
                    <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3 shadow-sm" style="width:48px; height:48px; background:var(--color-primary); color:var(--color-accent);">
                        <i class="bi bi-gem fs-5"></i>
                    </div>
                    <h3 class="card-title fw-bold mb-1" style="color:var(--color-text);">Buat Akun Baru</h3>
                    <p class="text-muted small mb-0">Daftar akun baru dan verifikasi nomor WhatsApp Anda</p>
                </div>
                
                <?php if(!empty($data['error'])): ?>
                    <div class="alert alert-danger py-2 rounded-3 small mb-3">
                        <i class="bi bi-exclamation-circle me-1"></i><?= Security::escape($data['error']); ?>
                    </div>
                <?php endif; ?>
                
                <?php if(!empty($data['success'])): ?>
                    <div class="alert alert-success py-2 rounded-3 small mb-3">
                        <i class="bi bi-check-circle me-1"></i><?= Security::escape($data['success']); ?>
                    </div>
                <?php endif; ?>

                <!-- Dynamic Notice Alert for AJAX OTP Send/Verify -->
                <div id="otpAlert" class="alert d-none py-2 rounded-3 small mb-3" role="alert"></div>

                <form action="<?= BASEURL; ?>/auth/register" method="POST" id="formRegister">
                    <input type="hidden" name="csrf_token" value="<?= Security::generateCsrfToken(); ?>">
                    
                    <div class="mb-3">
                        <label for="name" class="form-label fw-semibold small" style="color:var(--color-text);">Nama Lengkap</label>
                        <input type="text" class="form-control rounded-3" id="name" name="name" value="<?= Security::escape($data['old']['name'] ?? ''); ?>" required placeholder="Contoh: Siti Nurhaliza">
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label fw-semibold small" style="color:var(--color-text);">Email address</label>
                        <input type="email" class="form-control rounded-3" id="email" name="email" value="<?= Security::escape($data['old']['email'] ?? ''); ?>" required placeholder="nama@email.com">
                    </div>

                    <div class="mb-3">
                        <label for="phone" class="form-label fw-semibold small" style="color:var(--color-text);">Nomor WhatsApp</label>
                        <div class="input-group">
                            <span class="input-group-text border-end-0" style="background:var(--color-surface-soft); border-color:var(--color-border); color:var(--color-success);">
                                <i class="bi bi-whatsapp"></i>
                            </span>
                            <input type="tel" 
                                   class="form-control border-start-0" 
                                   id="phone" 
                                   name="phone" 
                                   value="<?= Security::escape($data['old']['phone'] ?? ''); ?>" 
                                   required 
                                   placeholder="08123456789 atau 628..." 
                                   inputmode="numeric">
                            <button type="button" 
                                    id="btnSendOtp" 
                                    class="btn fw-semibold px-3" 
                                    style="background:var(--color-accent); color:var(--color-primary); border:1px solid var(--color-border);"
                                    onclick="handleSendOtpRegister()">
                                Kirim OTP
                            </button>
                        </div>
                        <div class="form-text small text-muted">
                            Nomor WhatsApp aktif untuk menerima 6-digit kode verifikasi OTP.
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label for="otp" class="form-label fw-semibold small mb-0" style="color:var(--color-text);">Kode OTP WhatsApp</label>
                            <span id="countdownText" class="small text-muted d-none"></span>
                        </div>
                        <div class="input-group">
                            <span class="input-group-text border-end-0" style="background:var(--color-surface-soft); border-color:var(--color-border); color:var(--color-primary);">
                                <i class="bi bi-shield-check"></i>
                            </span>
                            <input type="text" 
                                   class="form-control border-start-0 fw-bold text-center fs-5" 
                                   id="otp" 
                                   name="otp" 
                                   required 
                                   maxlength="6" 
                                   placeholder="------" 
                                   inputmode="numeric" 
                                   autocomplete="one-time-code"
                                   style="letter-spacing: 4px; background:var(--color-surface); color:var(--color-primary);">
                        </div>
                        <div class="form-text small text-muted" id="otpHelpText">
                            Masukkan 6 digit angka OTP yang dikirimkan ke WhatsApp Anda.
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label fw-semibold small" style="color:var(--color-text);">Password</label>
                        <input type="password" class="form-control rounded-3" id="password" name="password" required minlength="6" placeholder="Minimal 6 karakter">
                    </div>

                    <div class="mb-4">
                        <label for="password_confirm" class="form-label fw-semibold small" style="color:var(--color-text);">Konfirmasi Password</label>
                        <input type="password" class="form-control rounded-3" id="password_confirm" name="password_confirm" required minlength="6" placeholder="Ulangi password">
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100 py-3 fw-bold rounded-pill shadow-sm">
                        Daftar Akun
                    </button>

                    <div class="text-center mt-4">
                        <a href="<?= BASEURL; ?>/auth/login" class="text-decoration-none small text-muted">
                            Sudah punya akun? <strong style="color:var(--color-primary);">Login di sini.</strong>
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
let otpCooldownInterval = null;

function showOtpAlert(message, type = 'danger') {
    const alertBox = document.getElementById('otpAlert');
    if (!alertBox) return;
    alertBox.className = `alert alert-${type} py-2 rounded-3 small mb-3`;
    alertBox.innerHTML = (type === 'success' ? '<i class="bi bi-check-circle-fill me-2"></i>' : '<i class="bi bi-exclamation-triangle-fill me-2"></i>') + message;
    alertBox.classList.remove('d-none');
}

function hideOtpAlert() {
    const alertBox = document.getElementById('otpAlert');
    if (alertBox) alertBox.classList.add('d-none');
}

function handleSendOtpRegister() {
    hideOtpAlert();
    const phoneInput = document.getElementById('phone');
    const rawPhone = phoneInput.value.trim();

    if (!rawPhone) {
        showOtpAlert('Silakan masukkan nomor WhatsApp Anda terlebih dahulu.');
        phoneInput.focus();
        return;
    }

    const btn = document.getElementById('btnSendOtp');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = `<span class="spinner-border spinner-border-sm me-1" role="status"></span> Mengirim...`;

    fetch('<?= BASEURL; ?>/auth/send_otp', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            phone: rawPhone,
            check_registered: true
        })
    })
    .then(async (res) => {
        const data = await res.json().catch(() => ({}));
        if (!res.ok) {
            throw new Error(data.message || 'Gagal mengirim kode OTP WhatsApp.');
        }
        return data;
    })
    .then((data) => {
        if (data.success) {
            showOtpAlert(data.message || 'Kode OTP berhasil dikirim ke WhatsApp Anda!', 'success');
            
            // Focus OTP field
            const otpInput = document.getElementById('otp');
            if (otpInput) {
                otpInput.focus();
            }

            // Start Cooldown timer
            startOtpCooldown(data.cooldown || 60);
        } else {
            showOtpAlert(data.message || 'Gagal mengirim kode OTP.');
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    })
    .catch((err) => {
        showOtpAlert(err.message || 'Terjadi kesalahan saat menghubungi server.');
        btn.disabled = false;
        btn.innerHTML = originalText;
    });
}

function startOtpCooldown(seconds) {
    const btn = document.getElementById('btnSendOtp');
    const countdownText = document.getElementById('countdownText');
    let remaining = seconds;

    btn.disabled = true;
    if (countdownText) {
        countdownText.classList.remove('d-none');
    }

    if (otpCooldownInterval) clearInterval(otpCooldownInterval);

    btn.innerHTML = `Kirim Ulang (${remaining}s)`;
    if (countdownText) countdownText.innerText = `Kirim ulang dalam ${remaining}s`;

    otpCooldownInterval = setInterval(() => {
        remaining--;
        if (remaining <= 0) {
            clearInterval(otpCooldownInterval);
            btn.disabled = false;
            btn.innerHTML = `<i class="bi bi-arrow-repeat me-1"></i>Kirim Ulang`;
            if (countdownText) countdownText.classList.add('d-none');
        } else {
            btn.innerHTML = `Kirim Ulang (${remaining}s)`;
            if (countdownText) countdownText.innerText = `Kirim ulang dalam ${remaining}s`;
        }
    }, 1000);
}
</script>
