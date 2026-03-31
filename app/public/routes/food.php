<?php

// Add these to your routes/user.php or a new routes/food.php file
// Make sure to require the controllers at the top

require_once(__DIR__ . '/../controllers/FoodLogController.php');
require_once(__DIR__ . '/../controllers/FoodItemController.php');

// Food search
Route::add('/foods/search', function () {
    $controller = new FoodItemController();
    return json_encode($controller->search());
}, 'get');

// Get daily food logs
Route::add('/food-logs', function () {
    $controller = new FoodLogController();
    return json_encode($controller->getLogs());
}, 'get');

// Add food log
Route::add('/food-logs', function () {
    $controller = new FoodLogController();
    return json_encode($controller->addLog());
}, 'post');

// Delete food log
Route::add('/food-logs', function () {
    $controller = new FoodLogController();
    return json_encode($controller->deleteLog());
}, 'delete');

// Weekly summary
Route::add('/food-logs/weekly', function () {
    $controller = new FoodLogController();
    return json_encode($controller->getWeekly());
}, 'get');