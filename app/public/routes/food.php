<?php

require_once(__DIR__ . '/../controllers/FoodLogController.php');
require_once(__DIR__ . '/../controllers/FoodItemController.php');

Route::add('/foods/search', function () {
    $controller = new FoodItemController();
    return json_encode($controller->search());
}, 'get');

Route::add('/food-logs', function () {
    $controller = new FoodLogController();
    return json_encode($controller->getLogs());
}, 'get');

Route::add('/food-logs', function () {
    $controller = new FoodLogController();
    return json_encode($controller->addLog());
}, 'post');

Route::add('/food-logs', function () {
    $controller = new FoodLogController();
    return json_encode($controller->deleteLog());
}, 'delete');

Route::add('/food-logs/weekly', function () {
    $controller = new FoodLogController();
    return json_encode($controller->getWeekly());
}, 'get');