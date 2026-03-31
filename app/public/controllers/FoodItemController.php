<?php

require_once __DIR__ . '/../models/FoodItemModel.php';
require_once __DIR__ . '/../lib/helpers.php';

class FoodItemController
{
    private FoodItemModel $model;

    public function __construct()
    {
        $this->model = new FoodItemModel();
    }

    // GET /foods/search?q=chicken
    public function search(): array
    {
        requireAuth();
        $query = trim($_GET['q'] ?? '');

        if (strlen($query) < 1) {
            jsonError('Search query is required');
        }

        $results = $this->model->search($query);
        return ['results' => $results];
    }
}