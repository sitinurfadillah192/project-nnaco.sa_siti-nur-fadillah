<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Error - NNACO SA</title>
    <!-- Google Fonts: Outfit -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body { 
            font-family: 'Outfit', sans-serif; 
            background-color: #FFFDF9; /* Warm Ivory */
            color: #302722; 
        }
        .btn-luxury {
            background-color: #4B3B32;
            color: #FFFFFF;
            border: none;
            transition: all 0.3s ease;
        }
        .btn-luxury:hover {
            background-color: #302722;
            color: #FFFFFF;
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(75, 59, 50, 0.2);
        }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center min-vh-100">
    <div class="container text-center py-5">
        <div class="card border shadow-lg p-5 mx-auto" style="max-width: 650px; background: #FFFFFF; border-color: #EDE1D5 !important; border-radius: 24px;">
            <div class="display-1 text-danger mb-3"><i class="bi bi-database-exclamation"></i></div>
            <h2 class="fw-bold mb-3" style="color:#4B3B32;">Koneksi Database Gagal</h2>
            <p class="text-muted fs-5 mb-4">Sistem mendeteksi bahwa database <strong style="color:#302722;">nnaco_sa</strong> belum dibuat di server MySQL Anda (Error 1049).</p>
            
            <div class="alert text-start mb-4 rounded-4 border" style="background:#FAF2E6; color:#A66A20; border-color:#EDE1D5 !important;">
                <h6 class="fw-bold mb-2"><i class="bi bi-tools me-2"></i>Langkah Perbaikan (Untuk XAMPP):</h6>
                <ol class="mb-0 ps-3 lh-lg">
                    <li>Buka <strong>XAMPP Control Panel</strong> dan pastikan modul <strong>MySQL</strong> berjalan (Berwarna hijau).</li>
                    <li>Buka tab baru dan akses <a href="http://localhost/phpmyadmin" target="_blank" class="fw-bold text-decoration-none" style="color:#A66A20;">http://localhost/phpmyadmin</a>.</li>
                    <li>Klik <strong>New</strong> (Baru) di menu kiri, buat nama basis data: <code>nnaco_sa</code> lalu Create.</li>
                    <li>Buka menu <strong>Import</strong>, lalu pilih file <code>database/nnaco_sa.sql</code> yang ada di folder project ini.</li>
                    <li>Klik <strong>Import / Go</strong> di bagian bawah halaman.</li>
                    <li>Jika sudah sukses, refresh halaman ini.</li>
                </ol>
            </div>
            
            <button onclick="location.reload()" class="btn btn-luxury px-5 py-3 rounded-pill fw-bold fs-5 shadow-sm">
                <i class="bi bi-arrow-clockwise me-2"></i> Saya Sudah Memperbaikinya
            </button>
        </div>
        <p class="text-muted mt-4 small">&copy; <?= date('Y'); ?> NNACO SA - E-Commerce System</p>
    </div>
</body>
</html>
