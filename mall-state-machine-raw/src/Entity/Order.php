<?php

declare(strict_types=1);

namespace App\Entity;

class Order
{
    private ?int $id = null;
    private string $orderNo = '';
    private int $buyerId = 0;
    private float $totalAmount = 0.00;
    private float $payAmount = 0.00;
    private string $state = 'pending';
    private array $stateLog = [];
    private ?\DateTimeImmutable $placedAt = null;
    private ?\DateTimeImmutable $paidAt = null;
    private ?\DateTimeImmutable $shippedAt = null;
    private ?\DateTimeImmutable $deliveredAt = null;
    private ?\DateTimeImmutable $completedAt = null;
    private ?\DateTimeImmutable $cancelledAt = null;
    private \DateTimeImmutable $createdAt;
    private \DateTimeImmutable $updatedAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }
    public function getOrderNo(): string { return $this->orderNo; }
    public function setOrderNo(string $v): self { $this->orderNo = $v; return $this; }
    public function getBuyerId(): int { return $this->buyerId; }
    public function setBuyerId(int $v): self { $this->buyerId = $v; return $this; }
    public function getTotalAmount(): float { return $this->totalAmount; }
    public function setTotalAmount(float $v): self { $this->totalAmount = $v; return $this; }
    public function getPayAmount(): float { return $this->payAmount; }
    public function setPayAmount(float $v): self { $this->payAmount = $v; return $this; }

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

    public function getPlacedAt(): ?\DateTimeImmutable { return $this->placedAt; }
    public function setPlacedAt(?\DateTimeImmutable $v): self { $this->placedAt = $v; return $this; }
    public function getPaidAt(): ?\DateTimeImmutable { return $this->paidAt; }
    public function setPaidAt(?\DateTimeImmutable $v): self { $this->paidAt = $v; return $this; }
    public function getShippedAt(): ?\DateTimeImmutable { return $this->shippedAt; }
    public function setShippedAt(?\DateTimeImmutable $v): self { $this->shippedAt = $v; return $this; }
    public function getDeliveredAt(): ?\DateTimeImmutable { return $this->deliveredAt; }
    public function setDeliveredAt(?\DateTimeImmutable $v): self { $this->deliveredAt = $v; return $this; }
    public function getCompletedAt(): ?\DateTimeImmutable { return $this->completedAt; }
    public function setCompletedAt(?\DateTimeImmutable $v): self { $this->completedAt = $v; return $this; }
    public function getCancelledAt(): ?\DateTimeImmutable { return $this->cancelledAt; }
    public function setCancelledAt(?\DateTimeImmutable $v): self { $this->cancelledAt = $v; return $this; }

    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): \DateTimeImmutable { return $this->updatedAt; }
}
