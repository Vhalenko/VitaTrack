<?php

require_once(__DIR__ . "/BaseModel.php");

class HistoryModel extends BaseModel
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getMonthlySummary(int $userId, string $yearMonth): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT fl.log_date,
                    ROUND(SUM(fi.calories_per_100g * fl.portion_grams / 100), 0) AS total_calories,
                    ROUND(SUM(fi.protein_per_100g  * fl.portion_grams / 100), 1) AS total_protein,
                    ROUND(SUM(fi.carbs_per_100g    * fl.portion_grams / 100), 1) AS total_carbs,
                    ROUND(SUM(fi.fat_per_100g      * fl.portion_grams / 100), 1) AS total_fat,
                    COUNT(DISTINCT fl.id) AS food_entries
             FROM food_logs fl
             JOIN food_items fi ON fl.food_item_id = fi.id
             WHERE fl.user_id = ?
               AND DATE_FORMAT(fl.log_date, "%Y-%m") = ?
             GROUP BY fl.log_date
             ORDER BY fl.log_date'
        );
        $stmt->execute([$userId, $yearMonth]);
        return $stmt->fetchAll();
    }

    public function getDayDetail(int $userId, string $date): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT fl.id, fl.meal_category, fl.portion_grams,
                    fi.name,
                    ROUND(fi.calories_per_100g * fl.portion_grams / 100, 1) AS total_calories,
                    ROUND(fi.protein_per_100g  * fl.portion_grams / 100, 1) AS total_protein,
                    ROUND(fi.carbs_per_100g    * fl.portion_grams / 100, 1) AS total_carbs,
                    ROUND(fi.fat_per_100g      * fl.portion_grams / 100, 1) AS total_fat
             FROM food_logs fl
             JOIN food_items fi ON fl.food_item_id = fi.id
             WHERE fl.user_id = ? AND fl.log_date = ?
             ORDER BY FIELD(fl.meal_category, "breakfast","lunch","dinner","snack"), fl.created_at'
        );
        $stmt->execute([$userId, $date]);
        return $stmt->fetchAll();
    }

    public function getRecentTrend(int $userId, int $days = 30): array
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
               AND fl.log_date >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
             GROUP BY fl.log_date
             ORDER BY fl.log_date'
        );
        $stmt->execute([$userId, $days]);
        return $stmt->fetchAll();
    }

    public function getStats(int $userId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT
                COUNT(DISTINCT fl.log_date) AS days_tracked,
                ROUND(AVG(daily.total_cal), 0) AS avg_daily_calories,
                MAX(daily.total_cal) AS max_daily_calories,
                MIN(daily.total_cal) AS min_daily_calories
             FROM food_logs fl
             JOIN food_items fi ON fl.food_item_id = fi.id
             JOIN (
                SELECT fl2.log_date,
                       SUM(fi2.calories_per_100g * fl2.portion_grams / 100) AS total_cal
                FROM food_logs fl2
                JOIN food_items fi2 ON fl2.food_item_id = fi2.id
                WHERE fl2.user_id = ?
                GROUP BY fl2.log_date
             ) daily ON daily.log_date = fl.log_date
             WHERE fl.user_id = ?'
        );
        $stmt->execute([$userId, $userId]);
        return $stmt->fetch() ?: [];
    }

    public function addWeightLog(int $userId, float $weight, string $date): int
    {
        // Upsert: replace if same date exists
        $stmt = $this->pdo->prepare(
            'INSERT INTO weight_logs (user_id, weight, log_date)
             VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE weight = VALUES(weight)'
        );
        $stmt->execute([$userId, $weight, $date]);
        return (int) $this->pdo->lastInsertId();
    }

    public function getWeightLogs(int $userId, int $days = 90): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT weight, log_date
             FROM weight_logs
             WHERE user_id = ?
               AND log_date >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
             ORDER BY log_date'
        );
        $stmt->execute([$userId, $days]);
        return $stmt->fetchAll();
    }

    public function deleteWeightLog(int $logId, int $userId): bool
    {
        $stmt = $this->pdo->prepare(
            'DELETE FROM weight_logs WHERE id = ? AND user_id = ?'
        );
        $stmt->execute([$logId, $userId]);
        return $stmt->rowCount() > 0;
    }
}