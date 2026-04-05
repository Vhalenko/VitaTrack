<?php

require_once(__DIR__ . '/BaseModel.php');

class NutritionModel extends BaseModel
{

    public function __construct()
    {
        parent::__construct();
    }

    public function getDayBreakdown(int $userId, string $date): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT
                fl.id,
                fl.meal_category,
                fl.portion_grams,
                fi.name,
                fi.calories_per_100g,
                fi.protein_per_100g,
                fi.carbs_per_100g,
                fi.fat_per_100g,
                ROUND(fi.calories_per_100g * fl.portion_grams / 100, 1) AS calories,
                ROUND(fi.protein_per_100g  * fl.portion_grams / 100, 1) AS protein,
                ROUND(fi.carbs_per_100g   * fl.portion_grams / 100, 1) AS carbs,
                ROUND(fi.fat_per_100g     * fl.portion_grams / 100, 1) AS fat,
                ROUND(fi.protein_per_100g  * fl.portion_grams / 100 * 4, 1) AS protein_kcal,
                ROUND(fi.carbs_per_100g   * fl.portion_grams / 100 * 4, 1) AS carbs_kcal,
                ROUND(fi.fat_per_100g     * fl.portion_grams / 100 * 9, 1) AS fat_kcal
             FROM food_logs fl
             JOIN food_items fi ON fl.food_item_id = fi.id
             WHERE fl.user_id = ? AND fl.log_date = ?
             ORDER BY FIELD(fl.meal_category,"breakfast","lunch","dinner","snack"), fl.created_at'
        );
        $stmt->execute([$userId, $date]);
        return $stmt->fetchAll();
    }

    public function getAverageMacros(int $userId, int $days = 7): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT
                ROUND(AVG(d.calories), 0) AS avg_calories,
                ROUND(AVG(d.protein),  1) AS avg_protein,
                ROUND(AVG(d.carbs),    1) AS avg_carbs,
                ROUND(AVG(d.fat),      1) AS avg_fat,
                COUNT(*) AS days_with_data
             FROM (
                SELECT
                    fl.log_date,
                    SUM(fi.calories_per_100g * fl.portion_grams / 100) AS calories,
                    SUM(fi.protein_per_100g  * fl.portion_grams / 100) AS protein,
                    SUM(fi.carbs_per_100g    * fl.portion_grams / 100) AS carbs,
                    SUM(fi.fat_per_100g      * fl.portion_grams / 100) AS fat
                FROM food_logs fl
                JOIN food_items fi ON fl.food_item_id = fi.id
                WHERE fl.user_id = ?
                  AND fl.log_date >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
                GROUP BY fl.log_date
             ) d'
        );
        $stmt->execute([$userId, $days]);
        return $stmt->fetch() ?: [];
    }

    public function getTopFoods(int $userId, int $limit = 8): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT
            fi.name,
            COUNT(*) AS times_logged,
            ROUND(AVG(fl.portion_grams), 0) AS avg_portion,
            ROUND(AVG(fi.calories_per_100g * fl.portion_grams / 100), 0) AS avg_calories,
            ROUND(AVG(fi.protein_per_100g  * fl.portion_grams / 100), 1) AS avg_protein,
            ROUND(AVG(fi.carbs_per_100g    * fl.portion_grams / 100), 1) AS avg_carbs,
            ROUND(AVG(fi.fat_per_100g      * fl.portion_grams / 100), 1) AS avg_fat
         FROM food_logs fl
         JOIN food_items fi ON fl.food_item_id = fi.id
         WHERE fl.user_id = ?
         GROUP BY fi.id, fi.name
         ORDER BY times_logged DESC
         LIMIT ' . (int) $limit
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function getMacroTrend(int $userId, int $days = 14): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT
                fl.log_date,
                ROUND(SUM(fi.protein_per_100g * fl.portion_grams / 100 * 4), 0) AS protein_kcal,
                ROUND(SUM(fi.carbs_per_100g   * fl.portion_grams / 100 * 4), 0) AS carbs_kcal,
                ROUND(SUM(fi.fat_per_100g     * fl.portion_grams / 100 * 9), 0) AS fat_kcal,
                ROUND(SUM(fi.calories_per_100g * fl.portion_grams / 100), 0)    AS total_kcal
             FROM food_logs fl
             JOIN food_items fi ON fl.food_item_id = fi.id
             WHERE fl.user_id = ?
               AND fl.log_date >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
             GROUP BY fl.log_date
             ORDER BY fl.log_date'
        );
        $stmt->execute([$userId, $days]);
        return $stmt->fetchAll();
    }
}
