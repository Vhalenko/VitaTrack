<?php

require_once __DIR__ . '/BaseModel.php';

class FoodItemModel extends BaseModel
{

    public function __construct()
    {
        parent::__construct();
    }

    public function search(string $query): array
    {
        $like = '%' . $query . '%';
        $stmt = $this->pdo->prepare(
            'SELECT id, name, calories_per_100g, protein_per_100g, carbs_per_100g, fat_per_100g
             FROM food_items
             WHERE name LIKE ?
             ORDER BY name
             LIMIT 20'
        );
        $stmt->execute([$like]);
        return $stmt->fetchAll();
    }

    public function findById(int $id): array|false
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, name, calories_per_100g, protein_per_100g, carbs_per_100g, fat_per_100g
             FROM food_items WHERE id = ?'
        );
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
}