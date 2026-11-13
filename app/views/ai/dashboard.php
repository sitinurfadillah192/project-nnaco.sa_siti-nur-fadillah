<div class="container-fluid py-4 px-lg-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="<?= BASEURL; ?>/dashboard" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">AI Intelligence Analytics</li>
                </ol>
            </nav>
            <h2 class="fw-bold mb-0 text-dark" style="font-family: 'Cormorant Garamond', Georgia, serif; font-size: 2.2rem;">
                <span style="color:var(--color-primary);">✨ AI Intelligence</span> Dashboard
            </h2>
            <p class="text-muted small mb-0">Analitik prediktif dan riset pasar cerdas berbasis Large Language Model untuk toko perhiasan NNACOSA.</p>
        </div>
    </div>

    <!-- Status AI Engine -->
    <div class="card shadow-soft border-0 mb-4 rounded-4" style="background:var(--color-surface); border:1px solid var(--color-border) !important;">
        <div class="card-body p-4 d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div class="d-flex align-items-center">
                <div class="rounded-circle d-flex align-items-center justify-content-center me-3 shadow-sm" style="width: 48px; height: 48px; background:var(--color-primary); color:var(--color-accent);">
                    <i class="bi bi-robot fs-4"></i>
                </div>
                <div>
                    <h6 class="fw-bold mb-1 text-dark">AI Intelligence Engine Aktif</h6>
                    <small class="text-muted">Model cerdas terintegrasi via Cloud API aktif untuk analisis data penjualan dan riset tren otomatis.</small>
                </div>
            </div>
            <div>
                <span class="badge px-3 py-2 rounded-pill fw-semibold" style="background:rgba(71, 122, 91, 0.12); color:var(--color-success); border:1px solid rgba(71, 122, 91, 0.2);">
                    <i class="bi bi-circle-fill me-1" style="font-size: 8px;"></i> AI Online & Siap Digunakan
                </span>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- AI Sales Prediction Chart -->
        <div class="col-lg-8">
            <div class="card shadow-soft border-0 h-100 rounded-4" style="background:var(--color-surface); border:1px solid var(--color-border) !important;">
                <div class="card-header border-0 py-3 px-4 d-flex justify-content-between align-items-center" style="background:var(--color-surface); border-bottom:1px solid var(--color-border) !important;">
                    <h5 class="fw-bold mb-0 text-dark">Prediksi Penjualan (7 Hari Kedepan)</h5>
                    <button class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm" id="btnPredictSales">
                        <i class="bi bi-stars me-1"></i> Generate Prediksi AI
                    </button>
                </div>
                <div class="card-body p-4">
                    <div id="salesChartLoading" class="text-center py-5 d-none">
                        <div class="spinner-border text-primary mb-2" role="status"></div>
                        <p class="text-muted mb-0">Menganalisis pola historis... Menghubungi mesin kecerdasan AI...</p>
                    </div>
                    
                    <canvas id="salesChart" height="110"></canvas>
                    
                    <div class="alert mt-4 border-0 shadow-sm d-none rounded-3" id="salesAnalysisAlert" style="background:var(--color-surface-soft); color:var(--color-text); border:1px solid var(--color-border) !important;">
                        <strong class="d-block mb-1"><span class="badge me-2" style="background:var(--color-primary); color:var(--color-accent);">AI INSIGHT</span> Analisa Model Prediktif:</strong>
                        <span id="salesAnalysisText"></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- AI Trend Analysis -->
        <div class="col-lg-4">
            <div class="card shadow-soft border-0 h-100 rounded-4" style="background:var(--color-surface); border:1px solid var(--color-border) !important;">
                <div class="card-header border-0 py-3 px-4 d-flex justify-content-between align-items-center" style="background:var(--color-surface); border-bottom:1px solid var(--color-border) !important;">
                    <h5 class="fw-bold mb-0 text-dark">Tren Pasar Terkini</h5>
                    <button class="btn btn-sm btn-outline-primary rounded-pill px-3" id="btnTrend">
                        <i class="bi bi-graph-up-arrow me-1"></i> Riset Pasar AI
                    </button>
                </div>
                <div class="card-body p-4" id="trendContainer" style="background:var(--color-surface-soft);">
                    <div class="text-center py-5 text-muted h-100 d-flex align-items-center justify-content-center flex-column" id="trendEmpty">
                        <div class="display-4 text-secondary mb-3">📈</div>
                        <span class="small">Klik tombol di atas untuk memulai riset pasar otomatis.</span>
                    </div>
                    
                    <div id="trendLoading" class="text-center py-5 d-none">
                        <div class="spinner-border text-primary mb-2" role="status"></div>
                        <p class="text-muted small mb-0">Melakukan riset tren perhiasan...</p>
                    </div>
                    
                    <div id="trendContent" class="d-none">
                        <p id="trendSummary" class="fw-bold text-dark mb-3 lh-sm"></p>
                        <ul class="list-group list-group-flush bg-transparent" id="trendList"></ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js Plugin -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('salesChart');
let chartInstance = null;

function renderChart(historical, predicted) {
    if (chartInstance) chartInstance.destroy();
    
    chartInstance = new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['H-6', 'H-5', 'H-4', 'H-3', 'H-2', 'H-1', 'Hari Ini', 'Bsk', 'Lusa', 'H+3', 'H+4', 'H+5', 'H+6', 'H+7'],
            datasets: [
                {
                    label: 'Aktual Historis (Rp)',
                    data: [...historical, null, null, null, null, null, null, null],
                    borderColor: '#4B3B32',
                    backgroundColor: 'rgba(75, 59, 50, 0.08)',
                    fill: true,
                    tension: 0.4,
                    borderWidth: 2
                },
                {
                    label: 'Proyeksi AI (Rp)',
                    data: [null, null, null, null, null, null, historical[6], ...predicted],
                    borderColor: '#D8BFA3',
                    borderDash: [5, 5],
                    tension: 0.4,
                    borderWidth: 2.5
                }
            ]
        },
        options: {
            responsive: true,
            interaction: { mode: 'index', intersect: false },
            scales: { y: { beginAtZero: true } },
            plugins: { legend: { position: 'top' } }
        }
    });
}

// Render data historis kosongan (mock) pertama kali
const mockHist = [1500000, 1200000, 1800000, 2100000, 1600000, 1900000, 2200000];
renderChart(mockHist, []);

// Logic API Prediksi
const btnPredictSales = document.getElementById('btnPredictSales');
btnPredictSales.addEventListener('click', function() {
    this.disabled = true;
    const originalText = this.innerHTML;
    this.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Menghitung...';

    document.getElementById('salesChart').classList.add('d-none');
    document.getElementById('salesAnalysisAlert').classList.add('d-none');
    document.getElementById('salesChartLoading').classList.remove('d-none');
    
    fetch('<?= BASEURL ?>/ai/predict_sales')
    .then(res => res.json())
    .then(data => {
        if(data && data.predictions && Array.isArray(data.predictions)) {
            renderChart(mockHist, data.predictions);
            document.getElementById('salesAnalysisText').innerText = data.analysis_text || 'Prediksi deret waktu berhasil dikalkulasi oleh AI.';
            document.getElementById('salesAnalysisAlert').classList.remove('d-none');
        } else {
            alert('Gagal menghasilkan prediksi: ' + (data.error || 'Format respon AI tidak sesuai.'));
        }
    })
    .catch(err => {
        console.error(err);
        alert("Koneksi gagal atau permintaan waktu habis: " + err.message);
    })
    .finally(() => {
        this.disabled = false;
        this.innerHTML = originalText;
        document.getElementById('salesChart').classList.remove('d-none');
        document.getElementById('salesChartLoading').classList.add('d-none');
    });
});

// Logic API Trend
const btnTrend = document.getElementById('btnTrend');
btnTrend.addEventListener('click', function() {
    this.disabled = true;
    const originalText = this.innerHTML;
    this.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Meriset...';

    document.getElementById('trendEmpty').classList.add('d-none');
    document.getElementById('trendContent').classList.add('d-none');
    document.getElementById('trendLoading').classList.remove('d-none');
    
    fetch('<?= BASEURL ?>/ai/trend_analysis')
    .then(res => res.json())
    .then(data => {
        if(data && data.trends && Array.isArray(data.trends)) {
            document.getElementById('trendSummary').innerText = data.summary || '';
            let listHtml = '';
            data.trends.forEach(t => {
                listHtml += `
                <li class="list-group-item bg-transparent px-0 border-bottom-dashed">
                    <span class="d-block fw-bold text-dark mb-1">🔥 ${t.item}</span>
                    <small class="text-muted">${t.reason}</small>
                </li>`;
            });
            document.getElementById('trendList').innerHTML = listHtml;
            document.getElementById('trendContent').classList.remove('d-none');
        } else {
            alert('Gagal melakukan riset: ' + (data.error || 'Format respon tidak sesuai.'));
            document.getElementById('trendEmpty').classList.remove('d-none');
        }
    })
    .catch(err => {
        console.error(err);
        alert("Koneksi gagal atau permintaan waktu habis: " + err.message);
        document.getElementById('trendEmpty').classList.remove('d-none');
    })
    .finally(() => {
        this.disabled = false;
        this.innerHTML = originalText;
        document.getElementById('trendLoading').classList.add('d-none');
    });
});
</script>
