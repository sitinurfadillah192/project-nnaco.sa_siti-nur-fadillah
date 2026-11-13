<?php
class Controller {
    public function view($view, $data = []) {
        $viewPath = dirname(__DIR__) . '/views/' . $view . '.php';
        if (file_exists($viewPath)) {
            require_once $viewPath;
        } else {
            require_once '../app/views/' . $view . '.php';
        }
    }

    public function model($model) {
        $modelPath = dirname(__DIR__) . '/models/' . $model . '.php';
        if (file_exists($modelPath)) {
            require_once $modelPath;
        } else {
            require_once '../app/models/' . $model . '.php';
        }
        return new $model;
    }
}
