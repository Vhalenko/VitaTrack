<?php

class FoodLogDTO
{
    private ?int $id;
    private int $userId;
    private int $foodId;
    private string $mealType;
    private int $quantity;
    private string $date;

    public function __construct(
        ?int $id,
        int $userId,
        int $foodId,
        string $mealType,
        int $quantity,
        string $date
    ) {
        $this->id = $id;
        $this->userId = $userId;
        $this->foodId = $foodId;
        $this->mealType = $mealType;
        $this->quantity = $quantity;
        $this->date = $date;
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

    public function getFoodId(): int
    {
        return $this->foodId;
    }

    public function setFoodId(int $foodId): void
    {
        $this->foodId = $foodId;
    }

    public function getMealType(): string
    {
        return $this->mealType;
    }

    public function setMealType(string $mealType): void
    {
        $this->mealType = $mealType;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): void
    {
        $this->quantity = $quantity;
    }

    public function getDate(): string
    {
        return $this->date;
    }

    public function setDate(string $date): void
    {
        $this->date = $date;
    }
}