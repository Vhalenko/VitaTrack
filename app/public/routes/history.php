<?php

require_once(__DIR__ . '/../controllers/HistoryController.php');

Route::add('/history/monthly', function () {
    $c = new HistoryController();
    return json_encode($c->getMonthly());
}, 'get');

Route::add('/history/day', function () {
    $c = new HistoryController();
    return json_encode($c->getDayDetail());
}, 'get');

Route::add('/history/trend', function () {
    $c = new HistoryController();
    return json_encode($c->getTrend());
}, 'get');

Route::add('/history/stats', function () {
    $c = new HistoryController();
    return json_encode($c->getStats());
}, 'get');

Route::add('/weight', function () {
    $c = new HistoryController();
    return json_encode($c->getWeight());
}, 'get');

Route::add('/weight', function () {
    $c = new HistoryController();
    return json_encode($c->addWeight());
}, 'post');

Route::add('/weight', function () {
    $c = new HistoryController();
    return json_encode($c->deleteWeight());
}, 'delete');