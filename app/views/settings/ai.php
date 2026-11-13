<div class="container-fluid py-4 px-lg-5">
    <!-- Header Title & Breadcrumb -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="<?= BASEURL; ?>/dashboard" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item">Pengaturan Sistem</li>
                    <li class="breadcrumb-item active" aria-current="page">Konfigurasi AI</li>
                </ol>
            </nav>
            <h2 class="fw-bold mb-0 text-dark">
                <i class="bi bi-robot text-primary me-2"></i>Konfigurasi Model & Provider AI
            </h2>
            <p class="text-muted small mb-0">Kelola multi-provider kecerdasan buatan, model ID, API key terenkripsi AES-256-GCM, dan uji koneksi langsung.</p>
        </div>
        <div>
            <button type="button" class="btn btn-primary rounded-pill px-4 py-2 fw-semibold shadow-sm" data-bs-toggle="modal" data-bs-target="#aiConfigModal" onclick="openCreateModal()">
                <i class="bi bi-plus-circle-fill me-2"></i>Tambah Provider AI
            </button>
        </div>
    </div>

    <!-- Navigation Tabs Pengaturan Sistem -->
    <ul class="nav nav-pills mb-4 bg-white p-2 rounded-4 shadow-sm border" style="max-width: 480px;">
        <li class="nav-item flex-fill text-center">
            <a class="nav-link active rounded-pill fw-semibold py-2" href="<?= BASEURL; ?>/settings/ai">
                <i class="bi bi-cpu me-2"></i>Konfigurasi AI
            </a>
        </li>
        <li class="nav-item flex-fill text-center">
            <a class="nav-link text-muted rounded-pill fw-semibold py-2" href="<?= BASEURL; ?>/settings/otp">
                <i class="bi bi-shield-lock me-2"></i>Autentikasi OTP
            </a>
        </li>
    </ul>

    <!-- Flash Notification -->
    <?php Flasher::flash(); ?>

    <!-- Active Engine Summary Card -->
    <?php if (!empty($data['active_config'])): ?>
    <div class="card border-0 shadow-sm rounded-4 mb-4 text-white overflow-hidden" style="background: linear-gradient(135deg, #4B3B32 0%, #2A201A 100%); border: 1px solid var(--color-border) !important;">
        <div class="card-body p-4 p-lg-5 position-relative">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill bg-success bg-opacity-25 text-success border border-success border-opacity-50 small fw-bold mb-3">
                        <span class="spinner-grow spinner-grow-sm text-success" style="width: 8px; height: 8px;"></span>
                        PROVIDER UTAMA AKTIF
                    </div>
                    <h3 class="fw-bold mb-1"><?= Security::escape($data['active_config']['config_name']); ?></h3>
                    <p class="text-white-50 small mb-3">
                        Provider: <span class="badge bg-primary bg-opacity-75"><?= strtoupper(Security::escape($data['active_config']['provider'])); ?></span>
                        &bull; Model: <code class="text-info fw-semibold"><?= Security::escape($data['active_config']['model_id']); ?></code>
                        &bull; Endpoint: <span class="text-white-50 font-monospace small"><?= Security::escape($data['active_config']['base_url']); ?></span>
                    </p>
                    <div class="d-flex flex-wrap gap-2 align-items-center">
                        <span class="badge bg-secondary bg-opacity-50 px-3 py-2">
                            <i class="bi bi-key-fill text-warning me-1"></i> API Key: Terenkripsi AES-256-GCM
                        </span>
                        <?php if (!empty($data['active_config']['last_test_latency_ms'])): ?>
                            <span class="badge bg-info bg-opacity-25 text-info border border-info border-opacity-50 px-3 py-2">
                                <i class="bi bi-speedometer2 me-1"></i> Latency: <?= (int)$data['active_config']['last_test_latency_ms']; ?> ms
                            </span>
                        <?php endif; ?>
                        <?php if (!empty($data['active_config']['last_test_status'])): ?>
                            <?php 
                                $statusBadge = $data['active_config']['last_test_status'] === 'SUCCESS' || $data['active_config']['last_test_status'] === 'CONNECTED' 
                                    ? 'bg-success' : 'bg-warning text-dark';
                            ?>
                            <span class="badge <?= $statusBadge; ?> px-3 py-2">
                                Status: <?= Security::escape($data['active_config']['last_test_status']); ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-lg-4 text-lg-end mt-4 mt-lg-0">
                    <button type="button" class="btn btn-outline-light rounded-pill px-4 py-2 fw-semibold" onclick="testActiveConfig(<?= (int)$data['active_config']['id']; ?>)">
                        <i class="bi bi-lightning-charge-fill text-warning me-2"></i>Uji Respon Engine
                    </button>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- AI Providers Table Card -->
    <div class="card border-0 shadow-sm rounded-4 bg-white mb-4">
        <div class="card-header bg-white border-0 py-3 px-4 d-flex justify-content-between align-items-center">
            <div>
                <h5 class="fw-bold mb-0 text-dark">Daftar Konfigurasi AI Provider</h5>
                <small class="text-muted">Pilih satu provider aktif yang digunakan oleh Chatbot Customer Service dan Analitik Toko.</small>
            </div>
            <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill fw-medium">
                <?= count($data['configs']); ?> Provider Terdaftar
            </span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-muted small text-uppercase">
                        <tr>
                            <th class="ps-4">Nama Konfigurasi</th>
                            <th>Provider</th>
                            <th>Model ID</th>
                            <th>API Key</th>
                            <th>Status Engine</th>
                            <th>Hasil Uji Terakhir</th>
                            <th class="text-end pe-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($data['configs'])): ?>
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="bi bi-cpu fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                    Belum ada konfigurasi AI. Klik tombol <strong>Tambah Provider AI</strong> di atas untuk memulai.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($data['configs'] as $cfg): ?>
                                <tr class="<?= !empty($cfg['is_active']) ? 'table-success table-opacity-10' : ''; ?>">
                                    <td class="ps-4 py-3">
                                        <div class="fw-bold text-dark"><?= Security::escape($cfg['config_name']); ?></div>
                                        <div class="small text-muted font-monospace" style="max-width: 260px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                            <?= Security::escape($cfg['base_url']); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if ($cfg['provider'] === 'openai'): ?>
                                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1 rounded-pill">OpenAI Official</span>
                                        <?php else: ?>
                                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2 py-1 rounded-pill">OpenAI Compatible</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <code class="fw-semibold text-dark small bg-light px-2 py-1 rounded"><?= Security::escape($cfg['model_id']); ?></code>
                                    </td>
                                    <td>
                                        <span class="text-muted font-monospace small">
                                            <?= Security::escape($cfg['masked_key'] ?: 'Belum diset'); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if (!empty($cfg['is_active'])): ?>
                                            <span class="badge bg-success px-3 py-2 rounded-pill"><i class="bi bi-check-circle-fill me-1"></i> AKTIF</span>
                                        <?php else: ?>
                                            <span class="badge bg-light text-muted border px-3 py-2 rounded-pill">Nonaktif</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php 
                                            $testStatus = $cfg['last_test_status'] ?? 'UNTESTED';
                                            $latency = $cfg['last_test_latency_ms'] ?? null;
                                        ?>
                                        <?php if ($testStatus === 'SUCCESS' || $testStatus === 'CONNECTED'): ?>
                                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1">
                                                <i class="bi bi-check2-circle me-1"></i> Terhubung <?= $latency ? "({$latency} ms)" : ''; ?>
                                            </span>
                                        <?php elseif ($testStatus === 'UNTESTED'): ?>
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 px-2 py-1">
                                                Belum Diuji
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-2 py-1" title="<?= Security::escape($cfg['last_test_message'] ?? ''); ?>">
                                                <i class="bi bi-exclamation-triangle me-1"></i> <?= Security::escape($testStatus); ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="btn-group btn-group-sm">
                                            <!-- Tombol Uji Koneksi Langsung -->
                                            <button type="button" class="btn btn-outline-info" title="Uji Koneksi Provider" onclick="testDirectConfig(<?= (int)$cfg['id']; ?>, '<?= Security::escape($cfg['config_name']); ?>')">
                                                <i class="bi bi-lightning-charge"></i> Uji
                                            </button>
                                            
                                            <!-- Tombol Edit -->
                                            <button type="button" class="btn btn-outline-secondary" title="Edit Konfigurasi" onclick='openEditModal(<?= json_encode($cfg); ?>)'>
                                                <i class="bi bi-pencil-square"></i>
                                            </button>

                                            <!-- Tombol Aktifkan (jika belum aktif) -->
                                            <?php if (empty($cfg['is_active'])): ?>
                                                <form action="<?= BASEURL; ?>/settings/ai_activate/<?= (int)$cfg['id']; ?>" method="POST" class="d-inline">
                                                    <input type="hidden" name="csrf_token" value="<?= $data['csrf_token']; ?>">
                                                    <button type="submit" class="btn btn-outline-success" title="Jadikan Provider Aktif" onclick="return confirm('Aktifkan provider <?= Security::escape($cfg['config_name']); ?> sebagai mesin utama sistem?')">
                                                        <i class="bi bi-check2"></i> Aktifkan
                                                    </button>
                                                </form>

                                                <!-- Tombol Hapus (hanya jika tidak aktif) -->
                                                <form action="<?= BASEURL; ?>/settings/ai_delete/<?= (int)$cfg['id']; ?>" method="POST" class="d-inline">
                                                    <input type="hidden" name="csrf_token" value="<?= $data['csrf_token']; ?>">
                                                    <button type="submit" class="btn btn-outline-danger" title="Hapus Konfigurasi" onclick="return confirm('Apakah Anda yakin ingin menghapus konfigurasi <?= Security::escape($cfg['config_name']); ?>?')">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
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

<!-- Modal Tambah / Edit Konfigurasi AI -->
<div class="modal fade" id="aiConfigModal" tabindex="-1" aria-labelledby="aiConfigModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <form id="aiConfigForm" action="<?= BASEURL; ?>/settings/ai_save" method="POST">
                <input type="hidden" name="csrf_token" id="csrf_token" value="<?= $data['csrf_token']; ?>">
                <input type="hidden" name="config_id" id="config_id" value="">

                <div class="modal-header border-0 pb-0 pt-4 px-4">
                    <h5 class="modal-title fw-bold text-dark" id="aiConfigModalLabel">
                        <i class="bi bi-cpu text-primary me-2"></i>Konfigurasi AI Provider
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body px-4 py-3">
                    <!-- Preset Quick Fill Badges -->
                    <div class="mb-3 p-3 bg-light rounded-3 border">
                        <small class="text-muted fw-bold d-block mb-2">Preset Cepat Provider:</small>
                        <div class="d-flex flex-wrap gap-2">
                            <button type="button" class="btn btn-sm btn-outline-dark rounded-pill" onclick="applyPreset('nvidia')">
                                <i class="bi bi-gpu-card me-1"></i> NVIDIA NIM (Llama 3.2 Vision)
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-dark rounded-pill" onclick="applyPreset('openai')">
                                <i class="bi bi-stars me-1"></i> OpenAI Official (GPT-4o Mini)
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-dark rounded-pill" onclick="applyPreset('groq')">
                                <i class="bi bi-lightning me-1"></i> Groq (Llama 3.3 70B)
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-dark rounded-pill" onclick="applyPreset('ollama')">
                                <i class="bi bi-hdd-network me-1"></i> Ollama Localhost
                            </button>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-7">
                            <label class="form-label small fw-semibold text-dark">Nama Konfigurasi <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="config_name" id="config_name" placeholder="Contoh: NVIDIA NIM Production" required>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label small fw-semibold text-dark">Provider Type <span class="text-danger">*</span></label>
                            <select class="form-select" name="provider" id="provider" required>
                                <option value="openai_compatible">OpenAI Compatible (NVIDIA, Groq, DeepSeek, Ollama)</option>
                                <option value="openai">OpenAI Official</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-semibold text-dark">API Base URL / Endpoint <span class="text-danger">*</span></label>
                            <input type="url" class="form-control font-monospace" name="base_url" id="base_url" placeholder="https://api.openai.com/v1/chat/completions" required>
                            <div class="form-text small">Endpoint lengkap chat completions provider. Terlindungi dari SSRF.</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-dark">Model ID <span class="text-danger">*</span></label>
                            <input type="text" class="form-control font-monospace" name="model_id" id="model_id" placeholder="meta/llama-3.2-11b-vision-instruct" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-dark">
                                API Key Secret <span id="apiKeyRequired" class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input type="password" class="form-control font-monospace" name="api_key" id="api_key" placeholder="Masukkan API Key baru">
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('api_key')">
                                    <i class="bi bi-eye" id="api_key_icon"></i>
                                </button>
                            </div>
                            <div class="form-text small" id="apiKeyHelp">Tersimpan dalam enkripsi AES-256-GCM. Kosongkan jika tidak ingin mengubah key lama.</div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-dark">Request Timeout (detik)</label>
                            <input type="number" class="form-control" name="timeout" id="timeout" value="45" min="5" max="120">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-dark">Max Output Tokens</label>
                            <input type="number" class="form-control" name="max_tokens" id="max_tokens" value="500" min="50" max="4096">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-dark">Temperature (0.0 - 2.0)</label>
                            <input type="number" class="form-control" name="temperature" id="temperature" value="0.5" step="0.1" min="0" max="2">
                        </div>

                        <div class="col-12">
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1">
                                <label class="form-check-label fw-semibold text-dark" for="is_active">
                                    Jadikan provider aktif saat ini (Primary Engine)
                                </label>
                            </div>
                        </div>

                        <!-- Live Inline Test Feedback Area -->
                        <div class="col-12" id="modalTestResult" style="display: none;">
                            <div class="alert mb-0" id="modalTestAlert" role="alert">
                                <div class="d-flex align-items-center">
                                    <div id="modalTestSpinner" class="spinner-border spinner-border-sm me-2" role="status" style="display: none;"></div>
                                    <span id="modalTestText">Menguji koneksi ke endpoint provider...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-0 px-4 pb-4 pt-2 d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-info rounded-pill px-4" id="btnTestInModal" onclick="testFromModal()">
                        <i class="bi bi-lightning-charge me-1"></i> Uji Koneksi Provider
                    </button>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold" id="btnSaveConfig">
                            <i class="bi bi-save me-1"></i> Simpan Konfigurasi
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Hasil Uji Koneksi Cepat -->
<div class="modal fade" id="quickTestModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pt-4 px-4">
                <h5 class="modal-title fw-bold" id="quickTestTitle">
                    <i class="bi bi-speedometer2 text-info me-2"></i>Pengujian Respon Provider AI
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body px-4 py-3 text-center">
                <div id="quickTestLoading" class="py-4">
                    <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status"></div>
                    <p class="mt-3 text-muted mb-0">Menghubungi endpoint AI & mengukur latensi round-trip...</p>
                </div>
                <div id="quickTestResult" style="display: none;" class="text-start">
                    <div class="alert" id="quickTestAlert" role="alert"></div>
                    <div class="bg-light p-3 rounded-3 small">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Status:</span>
                            <span class="fw-bold" id="resStatus">-</span>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Round-trip Latency:</span>
                            <span class="fw-bold text-info" id="resLatency">-</span>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Model ID:</span>
                            <span class="font-monospace" id="resModel">-</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 px-4 pb-4">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
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

function openCreateModal() {
    document.getElementById('aiConfigModalLabel').innerHTML = '<i class="bi bi-cpu text-primary me-2"></i>Tambah Provider AI Baru';
    document.getElementById('config_id').value = '';
    document.getElementById('config_name').value = '';
    document.getElementById('provider').value = 'openai_compatible';
    document.getElementById('base_url').value = '';
    document.getElementById('model_id').value = '';
    document.getElementById('api_key').value = '';
    document.getElementById('api_key').setAttribute('required', 'required');
    document.getElementById('apiKeyRequired').style.display = 'inline';
    document.getElementById('apiKeyHelp').innerText = 'API Key wajib diisi untuk konfigurasi baru (Terenkripsi AES-256-GCM).';
    document.getElementById('timeout').value = '45';
    document.getElementById('max_tokens').value = '500';
    document.getElementById('temperature').value = '0.5';
    document.getElementById('is_active').checked = false;
    document.getElementById('modalTestResult').style.display = 'none';
}

function openEditModal(cfg) {
    document.getElementById('aiConfigModalLabel').innerHTML = '<i class="bi bi-pencil-square text-primary me-2"></i>Edit Konfigurasi AI';
    document.getElementById('config_id').value = cfg.id;
    document.getElementById('config_name').value = cfg.config_name;
    document.getElementById('provider').value = cfg.provider;
    document.getElementById('base_url').value = cfg.base_url;
    document.getElementById('model_id').value = cfg.model_id;
    document.getElementById('api_key').value = '';
    document.getElementById('api_key').removeAttribute('required');
    document.getElementById('apiKeyRequired').style.display = 'none';
    document.getElementById('apiKeyHelp').innerText = 'Kosongkan jika ingin mempertahankan key tersimpan (' + (cfg.masked_key || 'Tersimpan') + ').';

    const params = cfg.parameters || {};
    document.getElementById('timeout').value = params.timeout || 45;
    document.getElementById('max_tokens').value = params.max_tokens || 500;
    document.getElementById('temperature').value = params.temperature !== undefined ? params.temperature : 0.5;
    document.getElementById('is_active').checked = parseInt(cfg.is_active) === 1;
    document.getElementById('modalTestResult').style.display = 'none';

    const modal = new bootstrap.Modal(document.getElementById('aiConfigModal'));
    modal.show();
}

function applyPreset(type) {
    if (type === 'nvidia') {
        document.getElementById('config_name').value = 'NVIDIA NIM (Nemotron 3.5 Bolt)';
        document.getElementById('provider').value = 'openai_compatible';
        document.getElementById('base_url').value = 'https://integrate.api.nvidia.com/v1/chat/completions';
        document.getElementById('model_id').value = 'nvidia/nemotron-3.5-bolt-e4b';
        document.getElementById('timeout').value = '20';
    } else if (type === 'openai') {
        document.getElementById('config_name').value = 'OpenAI Official (GPT-4o Mini)';
        document.getElementById('provider').value = 'openai';
        document.getElementById('base_url').value = 'https://api.openai.com/v1/chat/completions';
        document.getElementById('model_id').value = 'gpt-4o-mini';
        document.getElementById('timeout').value = '30';
    } else if (type === 'groq') {
        document.getElementById('config_name').value = 'Groq Cloud (Llama 3.3 70B)';
        document.getElementById('provider').value = 'openai_compatible';
        document.getElementById('base_url').value = 'https://api.groq.com/openai/v1/chat/completions';
        document.getElementById('model_id').value = 'llama-3.3-70b-versatile';
        document.getElementById('timeout').value = '25';
    } else if (type === 'ollama') {
        document.getElementById('config_name').value = 'Ollama Localhost';
        document.getElementById('provider').value = 'openai_compatible';
        document.getElementById('base_url').value = 'http://localhost:11434/v1/chat/completions';
        document.getElementById('model_id').value = 'llama3';
        document.getElementById('timeout').value = '60';
    }
}

// Test Connection inside form modal
function testFromModal() {
    const baseUrl = document.getElementById('base_url').value.trim();
    const modelId = document.getElementById('model_id').value.trim();
    const apiKey = document.getElementById('api_key').value.trim();
    const configId = document.getElementById('config_id').value;
    const provider = document.getElementById('provider').value;
    const timeout = document.getElementById('timeout').value;

    if (!baseUrl || !modelId) {
        alert('Mohon isi Base URL dan Model ID terlebih dahulu.');
        return;
    }

    if (!apiKey && !configId) {
        alert('Mohon masukkan API Key untuk pengujian.');
        return;
    }

    const resArea = document.getElementById('modalTestResult');
    const alertBox = document.getElementById('modalTestAlert');
    const sp = document.getElementById('modalTestSpinner');
    const text = document.getElementById('modalTestText');
    const btn = document.getElementById('btnTestInModal');

    resArea.style.display = 'block';
    alertBox.className = 'alert alert-info mb-0';
    sp.style.display = 'inline-block';
    text.innerText = 'Menghubungi endpoint provider AI...';
    btn.disabled = true;

    const formData = new FormData();
    formData.append('csrf_token', CSRF_TOKEN);
    formData.append('config_id', configId);
    formData.append('base_url', baseUrl);
    formData.append('model_id', modelId);
    formData.append('api_key', apiKey);
    formData.append('provider', provider);
    formData.append('timeout', timeout);

    fetch(BASE_URL + '/settings/ai_test', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        sp.style.display = 'none';
        btn.disabled = false;
        if (data.success) {
            alertBox.className = 'alert alert-success mb-0';
            text.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i> ' + (data.message || 'Koneksi berhasil!') + ' <strong>(' + data.latency_ms + ' ms)</strong>';
        } else {
            alertBox.className = 'alert alert-danger mb-0';
            text.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-1"></i> ' + (data.message || 'Pengujian gagal.');
        }
    })
    .catch(err => {
        sp.style.display = 'none';
        btn.disabled = false;
        alertBox.className = 'alert alert-danger mb-0';
        text.innerHTML = '<i class="bi bi-x-circle-fill me-1"></i> Gagal melakukan pengujian: ' + err.message;
    });
}

// Quick Test for Table items
function testDirectConfig(id, name) {
    const modalEl = document.getElementById('quickTestModal');
    const modal = new bootstrap.Modal(modalEl);
    document.getElementById('quickTestTitle').innerHTML = '<i class="bi bi-speedometer2 text-info me-2"></i>Uji Koneksi: ' + name;
    document.getElementById('quickTestLoading').style.display = 'block';
    document.getElementById('quickTestResult').style.display = 'none';
    modal.show();

    const formData = new FormData();
    formData.append('csrf_token', CSRF_TOKEN);
    formData.append('config_id', id);

    fetch(BASE_URL + '/settings/ai_test', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        document.getElementById('quickTestLoading').style.display = 'none';
        document.getElementById('quickTestResult').style.display = 'block';

        const alertBox = document.getElementById('quickTestAlert');
        if (data.success) {
            alertBox.className = 'alert alert-success';
            alertBox.innerHTML = '<i class="bi bi-check-circle-fill me-2"></i>' + data.message;
        } else {
            alertBox.className = 'alert alert-danger';
            alertBox.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-2"></i>' + data.message;
        }

        document.getElementById('resStatus').innerText = data.status || (data.success ? 'CONNECTED' : 'FAILED');
        document.getElementById('resLatency').innerText = (data.latency_ms || 0) + ' ms';
        document.getElementById('resModel').innerText = data.model || '-';
    })
    .catch(err => {
        document.getElementById('quickTestLoading').style.display = 'none';
        document.getElementById('quickTestResult').style.display = 'block';
        const alertBox = document.getElementById('quickTestAlert');
        alertBox.className = 'alert alert-danger';
        alertBox.innerHTML = '<i class="bi bi-x-circle-fill me-2"></i>Terjadi kesalahan AJAX: ' + err.message;
    });
}

function testActiveConfig(id) {
    testDirectConfig(id, 'Engine Aktif');
}
</script>
