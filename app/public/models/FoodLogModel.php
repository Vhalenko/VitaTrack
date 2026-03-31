<?php

require_once (__DIR__ . '/BaseModel.php');

class FoodLogModel extends BaseModel
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getLogsForDate(int $userId, string $date): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT fl.id, fl.meal_category, fl.portion_grams, fl.log_date,
                    fi.name, fi.calories_per_100g, fi.protein_per_100g,
                    fi.carbs_per_100g, fi.fat_per_100g,
                    ROUND(fi.calories_per_100g * fl.portion_grams / 100, 1) AS total_calories,
                    ROUND(fi.protein_per_100g * fl.portion_grams / 100, 1) AS total_protein,
                    ROUND(fi.carbs_per_100g  * fl.portion_grams / 100, 1) AS total_carbs,
                    ROUND(fi.fat_per_100g    * fl.portion_grams / 100, 1) AS total_fat
             FROM food_logs fl
             JOIN food_items fi ON fl.food_item_id = fi.id
             WHERE fl.user_id = ? AND fl.log_date = ?
             ORDER BY fl.meal_category, fl.created_at'
        );
        $stmt->execute([$userId, $date]);
        return $stmt->fetchAll();
    }

    public function addLog(int $userId, int $foodItemId, string $mealCategory, float $portionGrams, string $date): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO food_logs (user_id, food_item_id, meal_category, portion_grams, log_date)
             VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->execute([$userId, $foodItemId, $mealCategory, $portionGrams, $date]);
        return (int) $this->pdo->lastInsertId();
    }

    public function deleteLog(int $logId, int $userId): bool
    {
        $stmt = $this->pdo->prepare(
            'DELETE FROM food_logs WHERE id = ? AND user_id = ?'
        );
        $stmt->execute([$logId, $userId]);
        return $stmt->rowCount() > 0;
    }

    public function getWeeklySummary(int $userId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT fl.log_date,
                    ROUND(SUM(fi.calories_per_100g * fl.portion_grams / 100), 0) AS total_calories,
                    ROUND(SUM(fi.protein_per_100g  * fl.portion_grams / 100), 1) AS total_protein,
                    ROUND(SUM(fi.carbs_per_100g    * fl.portion_grams / 100), 1) AS total_carbs,
                    ROUND(SUM(fi.fat_per_100g      * fl.portion_grams / 100), 1) AS total_fat
             FROM food_logs fl
             JOIN food_items fi ON fl.food_item_id = fi.id
             WHERE fl.user_id = ?
               AND fl.log_date >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
             GROUP BY fl.log_date
             ORDER BY fl.log_date'
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
}