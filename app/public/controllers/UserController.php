<?php

require_once(__DIR__ . "/../models/UserModel.php");

class UserController
{
    private $userModel;
    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    public function login()
    {
        setCORSHeaders();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonError('Method not allowed', 405);
        }

        $body  = getRequestBody();
        $email = trim($body['email'] ?? '');
        $pass  = $body['password'] ?? '';

        if (empty($email) || empty($pass)) {
            jsonError('Email and password are required');
        }

        $userModel = new UserModel();
        $user = $userModel->findByEmail($email);

        if (!$user || !password_verify($pass, $user['password'])) {
            jsonError('Invalid email or password', 401);
        }

        $token = generateToken((int) $user['id']);

        return [
            'message' => 'Login successful',
            'token'   => $token,
            'user'    => [
                'id'    => $user['id'],
                'name'  => $user['name'],
                'email' => $user['email'],
                'daily_calorie_goal' => $user['daily_calorie_goal'],
            ],
        ];
    }

}
