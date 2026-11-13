<?php
class ErrorController extends Controller {
    public function notFound() {
        $data['title'] = '404 - Halaman Tidak Ditemukan';
        $data['seo_desc'] = 'Halaman yang Anda cari tidak tersedia di NNACO SA.';
        
        $this->view('templates/header', $data);
        echo '<div class="container py-5 text-center mt-5 mb-5">
                <h1 class="display-1 fw-bold text-danger mb-3">404</h1>
                <h2 class="fw-bold">Oops! Halaman Tidak Ditemukan.</h2>
                <p class="text-muted mb-4">Maaf, halaman yang Anda cari tidak ada, telah dipindahkan, atau Anda tidak memiliki akses.</p>
                <a href="'.BASEURL.'" class="btn btn-primary rounded-pill px-4 py-2 fw-bold shadow-sm">Kembali ke Beranda</a>
              </div>';
        $this->view('templates/footer');
    }
}
