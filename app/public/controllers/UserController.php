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
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonError('Method not allowed', 405);
        }

        $body  = getRequestBody();
        $email = trim($body['email'] ?? '');
        $pass  = $body['password'] ?? '';

        if (empty($email) || empty($pass)) {
            jsonError('Email and password are required');
        }

        $user = $this->userModel->findByEmail($email);

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

    public function register()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonError('Method not allowed', 405);
        }

        $body = getRequestBody();

        $name  = trim($body['name'] ?? '');
        $email = trim($body['email'] ?? '');
        $pass  = $body['password'] ?? '';

        // Validation
        if (empty($name) || empty($email) || empty($pass)) {
            jsonError('Name, email, and password are required');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            jsonError('Invalid email address');
        }

        if (strlen($pass) < 8) {
            jsonError('Password must be at least 8 characters');
        }

        if (strlen($name) < 2 || strlen($name) > 100) {
            jsonError('Name must be between 2 and 100 characters');
        }

        // Check if exists
        if ($this->userModel->findByEmail($email)) {
            jsonError('An account with this email already exists', 409);
        }

        $hashed = password_hash($pass, PASSWORD_BCRYPT, ['cost' => 12]);

        $userId = $this->userModel->create($name, $email, $hashed);

        $token = generateToken($userId);

        echo json_encode ([
            'message' => 'Account created successfully',
            'token'   => $token,
            'user'    => [
                'id'    => $userId,
                'name'  => $name,
                'email' => $email,
                'daily_calorie_goal' => 2000,
            ],
        ]);
    }
}
