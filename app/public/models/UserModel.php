<?php

require_once(__DIR__ . "/BaseModel.php");

class UserModel extends BaseModel
{
    public function __construct()
    {
        parent::__construct();
    }

    public function findById(int $id): array|false
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, name, email, age, weight, height,
                activity_level, fitness_goal, daily_calorie_goal, created_at
         FROM users WHERE id = ?'
        );
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function findByEmail($email)
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, name, email, password, daily_calorie_goal 
             FROM users 
             WHERE email = ?'
        );

        $stmt->execute([$email]);

        return $stmt->fetch();
    }

    public function create($name, $email, $hashedPassword)
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO users (name, email, password) VALUES (?, ?, ?)'
        );
        $stmt->execute([$name, $email, $hashedPassword]); // no return here
        return (int) $this->pdo->lastInsertId();          // only this
    }

    public function updateProfile(int $userId, array $data): bool
    {
        $allowed = ['name', 'age', 'weight', 'height', 'activity_level', 'fitness_goal', 'daily_calorie_goal'];
        $fields  = [];
        $values  = [];

        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "$field = ?";
                $values[] = $data[$field];
            }
        }

        if (empty($fields)) return false;

        $values[] = $userId;
        $stmt = $this->pdo->prepare(
            'UPDATE users SET ' . implode(', ', $fields) . ' WHERE id = ?'
        );
        return $stmt->execute($values);
    }

    public function updatePassword(int $userId, string $hashedPassword): bool
    {
        $stmt = $this->pdo->prepare('UPDATE users SET password = ? WHERE id = ?');
        return $stmt->execute([$hashedPassword, $userId]);
    }

    public function getPasswordHash(int $userId): string|false
    {
        $stmt = $this->pdo->prepare('SELECT password FROM users WHERE id = ?');
        $stmt->execute([$userId]);
        $row = $stmt->fetch();
        return $row ? $row['password'] : false;
    }
}
