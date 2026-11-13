<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= isset($data['seo_desc']) ? Security::escape($data['seo_desc']) : 'NNACO SA - Toko Aksesoris Terbaik di Indonesia. Temukan berbagai perhiasan dan aksesoris fashion eksklusif.' ?>">
    <meta name="keywords" content="Aksesoris, Kalung, Cincin, Gelang, NNACO SA, Fashion, Perhiasan">
    <title><?= isset($data['title']) ? Security::escape($data['title']) : 'NNACO SA - Premium Accessories'; ?></title>
    
    <!-- Google Fonts: Cormorant Garamond & Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;0,700;1,400&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    
    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <!-- Custom Style -->
    <link rel="stylesheet" href="<?= BASEURL; ?>/css/style.css">
</head>
<body>

<!-- Sticky Navbar Glassmorphism -->
<nav class="navbar navbar-expand-lg navbar-light navbar-glass fixed-top py-3">
    <div class="container">
        <!-- Logo -->
        <a class="navbar-brand fw-bold fs-4 d-flex align-items-center" href="<?= BASEURL; ?>/" style="letter-spacing: 1.5px;">
            <div class="rounded-circle d-flex align-items-center justify-content-center me-2 shadow-sm" style="width:36px; height:36px; background:var(--color-primary); color:var(--color-accent);">
                <i class="bi bi-gem fs-6"></i>
            </div>
            <span>NNACO<span style="color:var(--color-taupe); font-weight:300;">SA</span></span>
        </a>

        <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <!-- Search Bar -->
            <form class="d-flex mx-lg-auto my-3 my-lg-0 w-100" style="max-width: 400px;">
                <div class="input-group shadow-sm" style="border-radius: 50px; overflow: hidden; border: 1px solid var(--color-border);">
                    <span class="input-group-text border-0" style="background:var(--color-surface);"><i class="bi bi-search text-muted"></i></span>
                    <input class="form-control border-0 shadow-none" type="search" placeholder="Cari perhiasan..." style="background:var(--color-surface); color:var(--color-text);">
                </div>
            </form>

            <!-- Menu Kanan -->
            <ul class="navbar-nav ms-auto align-items-center gap-3">
                <li class="nav-item"><a class="nav-link fw-medium" href="<?= BASEURL; ?>/catalog">Belanja</a></li>
                
                <?php if(isset($_SESSION['user_id'])): ?>
                    <li class="nav-item">
                        <a class="nav-link position-relative" href="<?= BASEURL; ?>/cart">
                            <i class="bi bi-bag fs-5"></i>
                            <span class="position-absolute top-10 start-100 translate-middle badge rounded-pill bg-danger" style="font-size:10px;">!</span>
                        </a>
                    </li>
                    <li class="nav-item"><a class="nav-link" href="<?= BASEURL; ?>/wishlist"><i class="bi bi-heart fs-5"></i></a></li>
                    
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown">
                            <img src="https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['user_name']); ?>&background=D8BFA3&color=4B3B32" class="rounded-circle me-2 border border-2 border-white shadow-sm" width="35" height="35">
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow-soft" style="background:var(--color-surface); border:1px solid var(--color-border); border-radius:15px; min-width: 200px;">
                            <li class="px-3 py-2 text-muted small fw-bold">Hai, <?= explode(' ', $_SESSION['user_name'])[0]; ?>!</li>
                            <li><hr class="dropdown-divider opacity-25"></li>
                            <li><a class="dropdown-item py-2" href="<?= BASEURL; ?>/order"><i class="bi bi-bag-check me-2 text-muted"></i>Pesanan Saya</a></li>
                            <?php if(in_array($_SESSION['user_role'], ['admin', 'operator', 'owner'])): ?>
                                <li><a class="dropdown-item py-2" href="<?= BASEURL; ?>/dashboard"><i class="bi bi-speedometer2 me-2 text-primary"></i>Admin Panel</a></li>
                                <li><a class="dropdown-item py-2" href="<?= BASEURL; ?>/review/admin"><i class="bi bi-star-fill me-2 text-warning"></i>Ulasan Pelanggan</a></li>
                            <?php endif; ?>
                            <?php if(in_array($_SESSION['user_role'], ['admin', 'owner'])): ?>
                                <li><a class="dropdown-item py-2" href="<?= BASEURL; ?>/user"><i class="bi bi-people me-2 text-warning"></i>Manajemen User</a></li>
                                <li><a class="dropdown-item py-2" href="<?= BASEURL; ?>/chatbot"><i class="bi bi-chat-dots-fill me-2 text-primary"></i>Riwayat Chatbot</a></li>
                                <li><a class="dropdown-item py-2" href="<?= BASEURL; ?>/settings/ai"><i class="bi bi-gear-wide-connected me-2 text-info"></i>Pengaturan Sistem</a></li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider opacity-25"></li>
                            <li><a class="dropdown-item py-2 text-danger fw-medium" href="<?= BASEURL; ?>/auth/logout"><i class="bi bi-box-arrow-right me-2"></i>Keluar</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link fw-medium" href="<?= BASEURL; ?>/auth/login">Masuk</a></li>
                    <li class="nav-item"><a class="btn btn-primary-soft px-4 shadow-sm" href="<?= BASEURL; ?>/auth/register">Daftar</a></li>
                <?php endif; ?>
                
                <!-- Dark Mode Toggle Button -->
                <li class="nav-item ms-lg-2 border-start ps-lg-3">
                    <button class="btn btn-link nav-link shadow-none" id="themeToggle" title="Ganti Tema">
                        <i class="bi bi-moon-stars-fill text-muted fs-5"></i>
                    </button>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Spacer for fixed navbar -->
<div style="padding-top: 100px;"></div>

<!-- Flash Message Container -->
<div class="container mt-2 mb-4">
    <?php if(class_exists('Flasher')) Flasher::flash(); ?>
</div>

<div class="min-vh-100">
