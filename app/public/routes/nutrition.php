<?php
// Add to index.php: 

require_once(__DIR__ . '/../controllers/NutritionController.php');

Route::add('/nutrition/day', function () {
    $c = new NutritionController();
    return json_encode($c->getDayBreakdown());
}, 'get');

Route::add('/nutrition/averages', function () {
    $c = new NutritionController();
    return json_encode($c->getAverages());
}, 'get');

Route::add('/nutrition/top-foods', function () {
    $c = new NutritionController();
    return json_encode($c->getTopFoods());
}, 'get');

Route::add('/nutrition/macro-trend', function () {
    $c = new NutritionController();
    return json_encode($c->getMacroTrend());
}, 'get');