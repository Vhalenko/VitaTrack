<?php

class FoodDTO
{
    private ?int $id;
    private string $name;
    private int $calories;
    private int $protein;
    private int $carbs;
    private int $fat;

    public function __construct(
        ?int $id,
        string $name,
        int $calories,
        int $protein,
        int $carbs,
        int $fat
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->calories = $calories;
        $this->protein = $protein;
        $this->carbs = $carbs;
        $this->fat = $fat;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getCalories(): int
    {
        return $this->calories;
    }

    public function setCalories(int $calories): void
    {
        $this->calories = $calories;
    }

    public function getProtein(): int
    {
        return $this->protein;
    }

    public function setProtein(int $protein): void
    {
        $this->protein = $protein;
    }

    public function getCarbs(): int
    {
        return $this->carbs;
    }

    public function setCarbs(int $carbs): void
    {
        $this->carbs = $carbs;
    }

    public function getFat(): int
    {
        return $this->fat;
    }

    public function setFat(int $fat): void
    {
        $this->fat = $fat;
    }
}