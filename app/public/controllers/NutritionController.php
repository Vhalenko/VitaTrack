<?php

require_once __DIR__ . '/../models/NutritionModel.php';
require_once __DIR__ . '/../lib/helpers.php';

class NutritionController
{
    private NutritionModel $model;

    public function __construct()
    {
        $this->model = new NutritionModel();
    }

    // GET /nutrition/day?date=2024-01-15
    public function getDayBreakdown(): array
    {
        $auth = requireAuth();
        $date = $_GET['date'] ?? date('Y-m-d');

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            jsonError('Invalid date format. Use YYYY-MM-DD');
        }

        $items = $this->model->getDayBreakdown((int) $auth['user_id'], $date);

        // Aggregate totals
        $totals = ['calories' => 0, 'protein' => 0, 'carbs' => 0, 'fat' => 0,
                   'protein_kcal' => 0, 'carbs_kcal' => 0, 'fat_kcal' => 0];

        foreach ($items as $item) {
            $totals['calories']     += $item['calories'];
            $totals['protein']      += $item['protein'];
            $totals['carbs']        += $item['carbs'];
            $totals['fat']          += $item['fat'];
            $totals['protein_kcal'] += $item['protein_kcal'];
            $totals['carbs_kcal']   += $item['carbs_kcal'];
            $totals['fat_kcal']     += $item['fat_kcal'];
        }

        $totals = array_map(fn($v) => round($v, 1), $totals);

        // Macro % of total kcal from macros
        $macroKcal = $totals['protein_kcal'] + $totals['carbs_kcal'] + $totals['fat_kcal'];
        $percentages = [
            'protein' => $macroKcal > 0 ? round($totals['protein_kcal'] / $macroKcal * 100) : 0,
            'carbs'   => $macroKcal > 0 ? round($totals['carbs_kcal']   / $macroKcal * 100) : 0,
            'fat'     => $macroKcal > 0 ? round($totals['fat_kcal']     / $macroKcal * 100) : 0,
        ];

        return [
            'date'        => $date,
            'items'       => $items,
            'totals'      => $totals,
            'percentages' => $percentages,
        ];
    }

    // GET /nutrition/averages?days=7
    public function getAverages(): array
    {
        $auth = requireAuth();
        $days = min((int) ($_GET['days'] ?? 7), 90);
        $avgs = $this->model->getAverageMacros((int) $auth['user_id'], $days);
        return ['days' => $days, 'averages' => $avgs];
    }

    // GET /nutrition/top-foods
    public function getTopFoods(): array
    {
        $auth  = requireAuth();
        $foods = $this->model->getTopFoods((int) $auth['user_id']);
        return ['foods' => $foods];
    }

    // GET /nutrition/macro-trend?days=14
    public function getMacroTrend(): array
    {
        $auth  = requireAuth();
        $days  = min((int) ($_GET['days'] ?? 14), 60);
        $trend = $this->model->getMacroTrend((int) $auth['user_id'], $days);
        return ['days' => $days, 'trend' => $trend];
    }
}