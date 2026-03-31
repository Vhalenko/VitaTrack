<?php

require_once __DIR__ . '/../models/FoodLogModel.php';
require_once __DIR__ . '/../models/FoodItemModel.php';
require_once __DIR__ . '/../lib/helpers.php';

class FoodLogController
{
    private FoodLogModel $logModel;
    private FoodItemModel $foodModel;

    public function __construct()
    {
        $this->logModel  = new FoodLogModel();
        $this->foodModel = new FoodItemModel();
    }

    // GET /food-logs?date=2024-01-15
    public function getLogs(): array
    {
        $auth = requireAuth();
        $date = $_GET['date'] ?? date('Y-m-d');

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            jsonError('Invalid date format. Use YYYY-MM-DD');
        }

        $logs = $this->logModel->getLogsForDate((int) $auth['user_id'], $date);

        // Group by meal category
        $grouped = ['breakfast' => [], 'lunch' => [], 'dinner' => [], 'snack' => []];
        $totals  = ['calories' => 0, 'protein' => 0, 'carbs' => 0, 'fat' => 0];

        foreach ($logs as $log) {
            $grouped[$log['meal_category']][] = $log;
            $totals['calories'] += $log['total_calories'];
            $totals['protein']  += $log['total_protein'];
            $totals['carbs']    += $log['total_carbs'];
            $totals['fat']      += $log['total_fat'];
        }

        return [
            'date'    => $date,
            'logs'    => $grouped,
            'totals'  => array_map(fn($v) => round($v, 1), $totals),
        ];
    }

    // POST /food-logs
    public function addLog(): array
    {
        $auth = requireAuth();
        $body = getRequestBody();

        $foodItemId   = (int)    ($body['food_item_id']   ?? 0);
        $mealCategory = trim($body['meal_category'] ?? '');
        $portionGrams = (float)  ($body['portion_grams']  ?? 0);
        $date         = trim($body['date'] ?? date('Y-m-d'));

        if (!$foodItemId || !$mealCategory || !$portionGrams) {
            jsonError('food_item_id, meal_category, and portion_grams are required');
        }

        $allowed = ['breakfast', 'lunch', 'dinner', 'snack'];
        if (!in_array($mealCategory, $allowed)) {
            jsonError('Invalid meal category');
        }

        if ($portionGrams <= 0 || $portionGrams > 5000) {
            jsonError('Portion must be between 1 and 5000 grams');
        }

        if (!$this->foodModel->findById($foodItemId)) {
            jsonError('Food item not found', 404);
        }

        $logId = $this->logModel->addLog(
            (int) $auth['user_id'],
            $foodItemId,
            $mealCategory,
            $portionGrams,
            $date
        );

        return ['message' => 'Food logged successfully', 'log_id' => $logId];
    }

    // DELETE /food-logs?id=5
    public function deleteLog(): array
    {
        $auth  = requireAuth();
        $logId = (int) ($_GET['id'] ?? 0);

        if (!$logId) jsonError('Log ID is required');

        $deleted = $this->logModel->deleteLog($logId, (int) $auth['user_id']);

        if (!$deleted) jsonError('Log not found or unauthorized', 404);

        return ['message' => 'Log deleted'];
    }

    // GET /food-logs/weekly
    public function getWeekly(): array
    {
        $auth    = requireAuth();
        $summary = $this->logModel->getWeeklySummary((int) $auth['user_id']);
        return ['weekly' => $summary];
    }
}