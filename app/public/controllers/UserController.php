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

        echo json_encode([
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

    public function getProfile(): array
    {
        $auth = requireAuth();

        $user = $this->userModel->findById((int) $auth['user_id']);

        if (!$user) jsonError('User not found', 404);

        $bmr  = null;
        $tdee = null;
        if ($user['weight'] && $user['height'] && $user['age']) {
            $bmr = 10 * $user['weight'] + 6.25 * $user['height'] - 5 * $user['age'] + 5;
            $multipliers = [
                'sedentary'   => 1.2,
                'light'       => 1.375,
                'moderate'    => 1.55,
                'active'      => 1.725,
                'very_active' => 1.9,
            ];
            $tdee = round($bmr * ($multipliers[$user['activity_level']] ?? 1.55));
            $bmr  = round($bmr);
        }

        return ['user' => $user, 'bmr' => $bmr, 'tdee' => $tdee];
    }

    public function updateProfile(): array
    {
        $auth = requireAuth();
        $body = getRequestBody();

        $allowed = ['name', 'age', 'weight', 'height', 'activity_level', 'fitness_goal', 'daily_calorie_goal'];
        $data    = [];

        foreach ($allowed as $field) {
            if (array_key_exists($field, $body)) {
                $data[$field] = $body[$field];
            }
        }

        // Validate
        if (isset($data['name']) && strlen(trim($data['name'])) < 2) {
            jsonError('Name must be at least 2 characters');
        }
        if (isset($data['age']) && ($data['age'] < 10 || $data['age'] > 120)) {
            jsonError('Age must be between 10 and 120');
        }
        if (isset($data['weight']) && ($data['weight'] < 20 || $data['weight'] > 500)) {
            jsonError('Weight must be between 20 and 500 kg');
        }
        if (isset($data['height']) && ($data['height'] < 50 || $data['height'] > 300)) {
            jsonError('Height must be between 50 and 300 cm');
        }
        if (isset($data['daily_calorie_goal']) && ($data['daily_calorie_goal'] < 500 || $data['daily_calorie_goal'] > 10000)) {
            jsonError('Calorie goal must be between 500 and 10000');
        }

        $activityLevels = ['sedentary', 'light', 'moderate', 'active', 'very_active'];
        if (isset($data['activity_level']) && !in_array($data['activity_level'], $activityLevels)) {
            jsonError('Invalid activity level');
        }

        $fitnessGoals = ['lose_weight', 'maintain', 'gain_muscle'];
        if (isset($data['fitness_goal']) && !in_array($data['fitness_goal'], $fitnessGoals)) {
            jsonError('Invalid fitness goal');
        }

        $userModel = new UserModel();
        $userModel->updateProfile((int) $auth['user_id'], $data);

        return $this->getProfile();
    }

    public function updatePassword(): array
    {
        $auth = requireAuth();
        $body = getRequestBody();

        $currentPass = $body['current_password'] ?? '';
        $newPass     = $body['new_password']     ?? '';

        if (empty($currentPass) || empty($newPass)) {
            jsonError('Current and new password are required');
        }
        if (strlen($newPass) < 8) {
            jsonError('New password must be at least 8 characters');
        }

        $userModel = new UserModel();
        $hash = $userModel->getPasswordHash((int) $auth['user_id']);

        if (!$hash || !password_verify($currentPass, $hash)) {
            jsonError('Current password is incorrect', 401);
        }

        $newHash = password_hash($newPass, PASSWORD_BCRYPT, ['cost' => 12]);
        $userModel->updatePassword((int) $auth['user_id'], $newHash);

        return ['message' => 'Password updated successfully'];
    }
}
