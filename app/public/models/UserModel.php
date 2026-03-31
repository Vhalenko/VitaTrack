<?php

require_once(__DIR__ . "/BaseModel.php");
require_once(__DIR__ . "/../dto/UserDto.php");

class UserModel extends BaseModel
{
    public function __construct()
    {
        parent::__construct();
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
}
