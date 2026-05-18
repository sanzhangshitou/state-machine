<?php

declare(strict_types=1);

namespace App\Entity;

class Product
{
    private ?int $id = null;
    private string $title = '';
    private string $sku = '';
    private float $price = 0.00;
    private int $stock = 0;
    private string $state = 'draft';
    private array $stateLog = [];
    private \DateTimeImmutable $createdAt;
    private \DateTimeImmutable $updatedAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    // ---- getters / setters ----

    public function getId(): ?int { return $this->id; }
    public function getTitle(): string { return $this->title; }
    public function setTitle(string $v): self { $this->title = $v; return $this; }
    public function getSku(): string { return $this->sku; }
    public function setSku(string $v): self { $this->sku = $v; return $this; }
    public function getPrice(): float { return $this->price; }
    public function setPrice(float $v): self { $this->price = $v; return $this; }
    public function getStock(): int { return $this->stock; }
    public function setStock(int $v): self { $this->stock = $v; return $this; }

    public function getState(): string { return $this->state; }
    public function setState(string $v): self { $this->state = $v; return $this; }

    public function getStateLog(): array { return $this->stateLog; }
    public function setStateLog(array $v): self { $this->stateLog = $v; return $this; }
    public function appendStateLog(string $state): void
    {
        $this->stateLog[] = [
            'state' => $state,
            'at'    => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
        ];
    }

    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): \DateTimeImmutable { return $this->updatedAt; }
}
