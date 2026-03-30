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
}
