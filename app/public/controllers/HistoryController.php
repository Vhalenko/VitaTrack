<?php

require_once __DIR__ . '/../models/HistoryModel.php';
require_once __DIR__ . '/../lib/helpers.php';

class HistoryController
{
    private HistoryModel $model;

    public function __construct()
    {
        $this->model = new HistoryModel();
    }

    public function getMonthly(): array
    {
        $auth       = requireAuth();
        $yearMonth  = $_GET['month'] ?? date('Y-m');

        if (!preg_match('/^\d{4}-\d{2}$/', $yearMonth)) {
            jsonError('Invalid month format. Use YYYY-MM');
        }

        $summary = $this->model->getMonthlySummary((int) $auth['user_id'], $yearMonth);

        return [
            'month'   => $yearMonth,
            'days'    => $summary,
        ];
    }

    public function getDayDetail(): array
    {
        $auth = requireAuth();
        $date = $_GET['date'] ?? date('Y-m-d');

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            jsonError('Invalid date format. Use YYYY-MM-DD');
        }

        $logs = $this->model->getDayDetail((int) $auth['user_id'], $date);

        // Group by meal
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
            'date'   => $date,
            'logs'   => $grouped,
            'totals' => array_map(fn($v) => round($v, 1), $totals),
        ];
    }

    public function getTrend(): array
    {
        $auth = requireAuth();
        $days = min((int) ($_GET['days'] ?? 30), 365);

        $trend = $this->model->getRecentTrend((int) $auth['user_id'], $days);

        return ['days' => $days, 'trend' => $trend];
    }

    public function getStats(): array
    {
        $auth  = requireAuth();
        $stats = $this->model->getStats((int) $auth['user_id']);
        return ['stats' => $stats];
    }

    public function addWeight(): array
    {
        $auth = requireAuth();
        $body = getRequestBody();

        $weight = (float) ($body['weight'] ?? 0);
        $date   = trim($body['date'] ?? date('Y-m-d'));

        if ($weight <= 0 || $weight > 500) {
            jsonError('Weight must be between 1 and 500 kg');
        }
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            jsonError('Invalid date format');
        }

        $this->model->addWeightLog((int) $auth['user_id'], $weight, $date);

        return ['message' => 'Weight logged successfully'];
    }

    public function getWeight(): array
    {
        $auth = requireAuth();
        $days = min((int) ($_GET['days'] ?? 90), 365);
        $logs = $this->model->getWeightLogs((int) $auth['user_id'], $days);
        return ['logs' => $logs];
    }

    public function deleteWeight(): array
    {
        $auth  = requireAuth();
        $logId = (int) ($_GET['id'] ?? 0);
        if (!$logId) jsonError('Log ID is required');

        $deleted = $this->model->deleteWeightLog($logId, (int) $auth['user_id']);
        if (!$deleted) jsonError('Log not found', 404);

        return ['message' => 'Weight log deleted'];
    }
}