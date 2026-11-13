<div class="row justify-content-center py-4">
    <div class="col-md-6 col-lg-5">
        <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
            <!-- Header Card with WhatsApp Accent -->
            <div class="p-4 text-center text-white" style="background: linear-gradient(135deg, #25D366 0%, #128C7E 100%);">
                <div class="d-inline-flex align-items-center justify-content-center bg-white text-success rounded-circle shadow-sm mb-3" style="width: 64px; height: 64px;">
                    <i class="bi bi-whatsapp fs-1" style="color: #25D366;"></i>
                </div>
                <h3 class="fw-bold mb-1">Masuk dengan WhatsApp</h3>
                <p class="mb-0 opacity-75 small">Autentikasi cepat, aman, dan tanpa password via OTP Fonnte</p>
            </div>

            <div class="card-body p-4 p-md-5">
                <!-- Alert Box Container -->
                <div id="authAlert" class="alert d-none shadow-sm border-0 py-2 px-3 mb-4 rounded-3" role="alert"></div>

                <!-- ========================================== -->
                <!-- STEP 1: INPUT NOMOR WHATSAPP               -->
                <!-- ========================================== -->
                <div id="stepPhone">
                    <p class="text-muted small mb-3">
                        Masukkan nomor WhatsApp Anda yang aktif. Kami akan mengirimkan 6 digit kode OTP verifikasi.
                    </p>

                    <form id="formSendOtp" onsubmit="handleSendOtp(event)">
                        <div class="mb-3">
                            <label for="phoneInput" class="form-label fw-semibold text-dark">Nomor WhatsApp</label>
                            <div class="input-group input-group-lg shadow-sm rounded-3 overflow-hidden">
                                <span class="input-group-text bg-light border-0 fw-bold text-muted px-3">
                                    <span class="me-2">🇮🇩</span> +62
                                </span>
                                <input type="tel" 
                                       class="form-control border-0 bg-light" 
                                       id="phoneInput" 
                                       placeholder="81234567890" 
                                       required 
                                       autocomplete="tel"
                                       maxlength="16"
                                       inputmode="numeric">
                            </div>
                            <div class="form-text text-muted small mt-1">
                                <i class="bi bi-info-circle me-1"></i>Format contoh: <code>081234567890</code> atau <code>6281234567890</code>
                            </div>
                        </div>

                        <button type="submit" id="btnSendOtp" class="btn btn-success w-100 py-3 rounded-pill fw-bold shadow-sm d-flex align-items-center justify-content-center gap-2" style="background-color: #25D366; border-color: #25D366;">
                            <span>Kirim Kode OTP</span>
                            <i class="bi bi-arrow-right"></i>
                        </button>
                    </form>

                    <div class="text-center mt-4 pt-2 border-top">
                        <p class="small text-muted mb-2">Punya akun dengan email dan password?</p>
                        <a href="<?= BASEURL; ?>/auth/login" class="text-decoration-none fw-semibold">
                            <i class="bi bi-envelope me-1"></i> Masuk dengan Email & Password
                        </a>
                    </div>
                </div>

                <!-- ========================================== -->
                <!-- STEP 2: VERIFIKASI KODE OTP                -->
                <!-- ========================================== -->
                <div id="stepVerify" class="d-none">
                    <div class="text-center mb-4">
                        <div class="d-inline-block px-3 py-1 rounded-pill bg-light border text-muted small mb-2">
                            Kode dikirim ke: <strong id="displayMaskedPhone" class="text-dark"></strong>
                            <button type="button" class="btn btn-link btn-sm p-0 ms-2 text-decoration-none" onclick="handleChangePhone()">
                                <i class="bi bi-pencil-square"></i> Ganti
                            </button>
                        </div>
                        <p class="text-muted small">
                            Masukkan 6 digit kode verifikasi yang kami kirimkan ke nomor WhatsApp Anda.
                        </p>
                    </div>

                    <form id="formVerifyOtp" onsubmit="handleVerifyOtp(event)">
                        <!-- OTP Inputs 6 Boxes -->
                        <div class="d-flex justify-content-center gap-2 mb-3">
                            <input type="text" maxlength="1" class="otp-box form-control form-control-lg text-center fw-bold fs-3 rounded-3" inputmode="numeric" autocomplete="one-time-code">
                            <input type="text" maxlength="1" class="otp-box form-control form-control-lg text-center fw-bold fs-3 rounded-3" inputmode="numeric">
                            <input type="text" maxlength="1" class="otp-box form-control form-control-lg text-center fw-bold fs-3 rounded-3" inputmode="numeric">
                            <input type="text" maxlength="1" class="otp-box form-control form-control-lg text-center fw-bold fs-3 rounded-3" inputmode="numeric">
                            <input type="text" maxlength="1" class="otp-box form-control form-control-lg text-center fw-bold fs-3 rounded-3" inputmode="numeric">
                            <input type="text" maxlength="1" class="otp-box form-control form-control-lg text-center fw-bold fs-3 rounded-3" inputmode="numeric">
                        </div>

                        <!-- Timer Expiry -->
                        <div class="d-flex justify-content-between align-items-center mb-4 small text-muted">
                            <div>
                                <i class="bi bi-clock me-1"></i> Masa berlaku: <strong id="expiryTimer" class="text-danger">05:00</strong>
                            </div>
                            <div>
                                <button type="button" id="btnResendOtp" class="btn btn-link p-0 text-decoration-none small text-muted" disabled onclick="handleResendOtp()">
                                    Kirim ulang kode <span id="cooldownCounter">(60s)</span>
                                </button>
                            </div>
                        </div>

                        <button type="submit" id="btnVerifyOtp" class="btn btn-success w-100 py-3 rounded-pill fw-bold shadow-sm d-flex align-items-center justify-content-center gap-2" style="background-color: #128C7E; border-color: #128C7E;">
                            <i class="bi bi-shield-check"></i>
                            <span>Verifikasi dan Masuk</span>
                        </button>
                    </form>

                    <div class="text-center mt-4 pt-2 border-top">
                        <button type="button" class="btn btn-link text-decoration-none small text-muted" onclick="handleChangePhone()">
                            <i class="bi bi-arrow-left me-1"></i> Gunakan nomor WhatsApp lain
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<style>
.otp-box {
    width: 48px;
    height: 56px;
    padding: 0;
    border: 2px solid #e2e8f0;
    transition: all 0.2s ease;
}
.otp-box:focus {
    border-color: #25D366;
    box-shadow: 0 0 0 0.25rem rgba(37, 211, 102, 0.25);
    outline: none;
}
</style>

<script>
let currentPhone = '';
let currentMaskedPhone = '';
let expirySeconds = 300;
let cooldownSeconds = 60;
let expiryInterval = null;
let cooldownInterval = null;

// Helpers: UI Alerts
function showAlert(message, type = 'danger') {
    const alertBox = document.getElementById('authAlert');
    alertBox.className = `alert alert-${type} shadow-sm border-0 py-2 px-3 mb-4 rounded-3 d-flex align-items-center`;
    
    let icon = 'bi-exclamation-triangle-fill';
    if (type === 'success') icon = 'bi-check-circle-fill';
    if (type === 'info') icon = 'bi-info-circle-fill';

    alertBox.innerHTML = `<i class="bi ${icon} me-2 fs-5"></i><div>${message}</div>`;
    alertBox.classList.remove('d-none');
}

function hideAlert() {
    const alertBox = document.getElementById('authAlert');
    alertBox.classList.add('d-none');
}

// ==========================================
// STEP 1: SEND OTP
// ==========================================
function handleSendOtp(event) {
    event.preventDefault();
    hideAlert();

    const phoneInput = document.getElementById('phoneInput');
    const btnSendOtp = document.getElementById('btnSendOtp');
    const phone = phoneInput.value.trim();

    if (!phone) {
        showAlert('Silakan masukkan nomor WhatsApp Anda.');
        return;
    }

    // Set button loading state
    btnSendOtp.disabled = true;
    const originalBtnHtml = btnSendOtp.innerHTML;
    btnSendOtp.innerHTML = `<span class="spinner-border spinner-border-sm me-2" role="status"></span> Mengirim OTP...`;

    fetch('<?= BASEURL; ?>/auth/send_otp', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ phone: phone })
    })
    .then(async (res) => {
        const data = await res.json().catch(() => ({}));
        if (!res.ok) {
            throw new Error(data.message || `Terjadi kesalahan (HTTP ${res.status})`);
        }
        return data;
    })
    .then((data) => {
        if (data.success) {
            currentPhone = data.phone || phone;
            currentMaskedPhone = data.masked_phone || currentPhone;

            document.getElementById('displayMaskedPhone').innerText = currentMaskedPhone;
            showAlert(data.message, 'success');

            // Switch to Step 2
            document.getElementById('stepPhone').classList.add('d-none');
            document.getElementById('stepVerify').classList.remove('d-none');

            // Start countdowns
            startExpiryTimer(data.expires_in || 300);
            startCooldownTimer(data.cooldown || 60);

            // Focus on first OTP input
            setTimeout(() => {
                const firstOtp = document.querySelectorAll('.otp-box')[0];
                if (firstOtp) firstOtp.focus();
            }, 100);
        } else {
            showAlert(data.message || 'Gagal mengirim kode OTP.');
        }
    })
    .catch((err) => {
        showAlert(err.message || 'Koneksi ke server gagal. Pastikan koneksi internet Anda aktif.');
    })
    .finally(() => {
        btnSendOtp.disabled = false;
        btnSendOtp.innerHTML = originalBtnHtml;
    });
}

// ==========================================
// STEP 2: VERIFY OTP
// ==========================================
function handleVerifyOtp(event) {
    event.preventDefault();
    hideAlert();

    const otpBoxes = document.querySelectorAll('.otp-box');
    let otpCode = '';
    otpBoxes.forEach(box => otpCode += box.value.trim());

    if (otpCode.length !== 6 || !/^\d{6}$/.test(otpCode)) {
        showAlert('Silakan masukkan 6 digit angka kode OTP yang lengkap.');
        return;
    }

    const btnVerify = document.getElementById('btnVerifyOtp');
    btnVerify.disabled = true;
    const originalBtnHtml = btnVerify.innerHTML;
    btnVerify.innerHTML = `<span class="spinner-border spinner-border-sm me-2" role="status"></span> Memverifikasi...`;

    fetch('<?= BASEURL; ?>/auth/verify_otp', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            phone: currentPhone,
            otp: otpCode
        })
    })
    .then(async (res) => {
        const data = await res.json().catch(() => ({}));
        if (!res.ok) {
            throw new Error(data.message || `Verifikasi gagal (HTTP ${res.status})`);
        }
        return data;
    })
    .then((data) => {
        if (data.success) {
            showAlert(data.message || 'Verifikasi berhasil!', 'success');
            clearInterval(expiryInterval);
            clearInterval(cooldownInterval);

            // Redirect user to destination
            setTimeout(() => {
                window.location.href = data.redirect || '<?= BASEURL; ?>/home';
            }, 800);
        } else {
            showAlert(data.message || 'Kode OTP tidak valid.');
            // Clear inputs on wrong OTP
            otpBoxes.forEach(box => box.value = '');
            otpBoxes[0].focus();
        }
    })
    .catch((err) => {
        showAlert(err.message || 'Terjadi kesalahan saat memverifikasi kode.');
        otpBoxes.forEach(box => box.value = '');
        otpBoxes[0].focus();
    })
    .finally(() => {
        btnVerify.disabled = false;
        btnVerify.innerHTML = originalBtnHtml;
    });
}

// ==========================================
// RESEND OTP
// ==========================================
function handleResendOtp() {
    hideAlert();
    const btnResend = document.getElementById('btnResendOtp');
    btnResend.disabled = true;
    btnResend.innerText = 'Mengirim ulang...';

    fetch('<?= BASEURL; ?>/auth/send_otp', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ phone: currentPhone })
    })
    .then(async (res) => {
        const data = await res.json().catch(() => ({}));
        if (!res.ok) {
            throw new Error(data.message || 'Gagal mengirim ulang OTP');
        }
        return data;
    })
    .then((data) => {
        if (data.success) {
            showAlert('Kode OTP baru telah dikirimkan ke WhatsApp Anda.', 'info');
            startExpiryTimer(data.expires_in || 300);
            startCooldownTimer(data.cooldown || 60);

            // Clear inputs
            const otpBoxes = document.querySelectorAll('.otp-box');
            otpBoxes.forEach(box => box.value = '');
            otpBoxes[0].focus();
        } else {
            showAlert(data.message || 'Gagal mengirim ulang OTP.');
            btnResend.disabled = false;
            btnResend.innerText = 'Kirim Ulang Kode';
        }
    })
    .catch((err) => {
        showAlert(err.message || 'Koneksi gagal saat meminta kode baru.');
        btnResend.disabled = false;
        btnResend.innerText = 'Kirim Ulang Kode';
    });
}

// ==========================================
// CHANGE PHONE
// ==========================================
function handleChangePhone() {
    hideAlert();
    clearInterval(expiryInterval);
    clearInterval(cooldownInterval);

    document.getElementById('stepVerify').classList.add('d-none');
    document.getElementById('stepPhone').classList.remove('d-none');

    // Clear otp boxes
    document.querySelectorAll('.otp-box').forEach(box => box.value = '');
    document.getElementById('phoneInput').focus();
}

// ==========================================
// TIMERS (Expiry & Cooldown)
// ==========================================
function startExpiryTimer(seconds) {
    clearInterval(expiryInterval);
    expirySeconds = seconds;
    const timerDisplay = document.getElementById('expiryTimer');

    function updateDisplay() {
        const m = Math.floor(expirySeconds / 60);
        const s = expirySeconds % 60;
        timerDisplay.innerText = `${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
        
        if (expirySeconds <= 0) {
            clearInterval(expiryInterval);
            timerDisplay.innerText = 'Kedaluwarsa';
            showAlert('Masa berlaku kode OTP telah habis. Silakan klik "Kirim ulang kode" untuk mendapatkan kode baru.');
        }
        expirySeconds--;
    }

    updateDisplay();
    expiryInterval = setInterval(updateDisplay, 1000);
}

function startCooldownTimer(seconds) {
    clearInterval(cooldownInterval);
    cooldownSeconds = seconds;
    const btnResend = document.getElementById('btnResendOtp');
    const counterDisplay = document.getElementById('cooldownCounter');

    btnResend.disabled = true;

    function updateCooldown() {
        if (cooldownSeconds <= 0) {
            clearInterval(cooldownInterval);
            counterDisplay.innerText = '';
            btnResend.disabled = false;
            btnResend.className = 'btn btn-link p-0 text-decoration-none small text-success fw-bold';
            btnResend.innerHTML = '<i class="bi bi-arrow-clockwise me-1"></i>Kirim ulang kode';
        } else {
            counterDisplay.innerText = `(${cooldownSeconds}s)`;
            btnResend.className = 'btn btn-link p-0 text-decoration-none small text-muted';
            btnResend.innerText = 'Kirim ulang kode ';
            btnResend.appendChild(counterDisplay);
            cooldownSeconds--;
        }
    }

    updateCooldown();
    cooldownInterval = setInterval(updateCooldown, 1000);
}

// ==========================================
// OTP BOXES AUTO ADVANCE & PASTE HANDLER
// ==========================================
document.addEventListener('DOMContentLoaded', () => {
    const boxes = document.querySelectorAll('.otp-box');

    boxes.forEach((box, index) => {
        // Only allow numbers
        box.addEventListener('input', (e) => {
            const val = e.target.value.replace(/[^0-9]/g, '');
            e.target.value = val;

            if (val.length === 1) {
                if (index < boxes.length - 1) {
                    boxes[index + 1].focus();
                } else {
                    // All 6 filled -> trigger verify automatically!
                    handleVerifyOtp(new Event('submit'));
                }
            }
        });

        // Handle Backspace
        box.addEventListener('keydown', (e) => {
            if (e.key === 'Backspace' && !box.value && index > 0) {
                boxes[index - 1].focus();
            }
        });

        // Handle Paste of full 6 digits
        box.addEventListener('paste', (e) => {
            e.preventDefault();
            const pasted = (e.clipboardData || window.clipboardData).getData('text').trim();
            const digits = pasted.replace(/[^0-9]/g, '').slice(0, 6);

            if (digits.length > 0) {
                digits.split('').forEach((d, i) => {
                    if (boxes[i]) boxes[i].value = d;
                });
                const nextIndex = Math.min(digits.length, boxes.length - 1);
                boxes[nextIndex].focus();

                if (digits.length === 6) {
                    handleVerifyOtp(new Event('submit'));
                }
            }
        });
    });
});
</script>
