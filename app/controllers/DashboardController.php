<?php
class DashboardController extends Controller {
    public function index() {
        AuthMiddleware::check(); // Mesti Login
        
        // Cek Role (Hanya role back-office yang boleh mengakses Dashboard ini)
        RoleMiddleware::check(['admin', 'operator', 'owner']);

        $data['title'] = 'Dashboard - nnaco_sa';
        $this->view('templates/header', $data);
        $this->view('dashboard/index', $data);
        $this->view('templates/footer');
    }
}
