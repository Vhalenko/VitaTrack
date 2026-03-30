<?php

class UserGoalDTO
{
    private ?int $id;
    private int $userId;
    private int $calorieGoal;
    private int $proteinGoal;
    private int $carbsGoal;
    private int $fatGoal;

    public function __construct(
        ?int $id,
        int $userId,
        int $calorieGoal,
        int $proteinGoal,
        int $carbsGoal,
        int $fatGoal
    ) {
        $this->id = $id;
        $this->userId = $userId;
        $this->calorieGoal = $calorieGoal;
        $this->proteinGoal = $proteinGoal;
        $this->carbsGoal = $carbsGoal;
        $this->fatGoal = $fatGoal;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): void
    {
        $this->userId = $userId;
    }

    public function getCalorieGoal(): int
    {
        return $this->calorieGoal;
    }

    public function setCalorieGoal(int $calorieGoal): void
    {
        $this->calorieGoal = $calorieGoal;
    }

    public function getProteinGoal(): int
    {
        return $this->proteinGoal;
    }

    public function setProteinGoal(int $proteinGoal): void
    {
        $this->proteinGoal = $proteinGoal;
    }

    public function getCarbsGoal(): int
    {
        return $this->carbsGoal;
    }

    public function setCarbsGoal(int $carbsGoal): void
    {
        $this->carbsGoal = $carbsGoal;
    }

    public function getFatGoal(): int
    {
        return $this->fatGoal;
    }

    public function setFatGoal(int $fatGoal): void
    {
        $this->fatGoal = $fatGoal;
    }
}