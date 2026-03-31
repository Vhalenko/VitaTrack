<?php

// require the user controller so we can use it in this file
require_once(__DIR__ . "/../controllers/UserController.php");
require_once(__DIR__ . "/../lib/helpers.php");

Route::add('/login', function () {
    $userController = new UserController();
    $result = $userController->login();
    echo json_encode($result);
}, 'post');

Route::add('/register', function () {
    $userController = new UserController();
    $userController->register();
}, 'post');