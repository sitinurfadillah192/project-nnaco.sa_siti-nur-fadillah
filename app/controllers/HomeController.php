<?php
class HomeController extends Controller {
    public function index() {
        $data['title'] = 'Home - nnaco_sa';
        try {
            $data['latest_products'] = $this->model('Product')->getProductsWithCategory(4, 0);
            $productIds = !empty($data['latest_products']) ? array_column($data['latest_products'], 'id') : [];
            $data['ratings'] = !empty($productIds) ? $this->model('Review')->getAverageRatingsForProducts($productIds) : [];
            $data['featured_reviews'] = $this->model('Review')->getFeaturedReviews(6);
        } catch (Exception $e) {
            $data['latest_products'] = [];
            $data['ratings'] = [];
            $data['featured_reviews'] = [];
        }
        $this->view('templates/header', $data);
        $this->view('home/index', $data);
        $this->view('templates/footer');
    }
}
