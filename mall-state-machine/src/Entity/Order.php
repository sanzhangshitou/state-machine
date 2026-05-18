<?php

declare(strict_types=1);

namespace Mall\Entity;

class Order
{
    private ?int $id = null;
    private string $orderNo = '';
    private int $buyerId = 0;
    private float $totalAmount = 0.00;
    private float $payAmount = 0.00;
    private string $state = 'pending';
    private ?array $stateLog = [];
    private ?\DateTimeInterface $placedAt = null;
    private ?\DateTimeInterface $paidAt = null;
    private ?\DateTimeInterface $shippedAt = null;
    private ?\DateTimeInterface $deliveredAt = null;
    private ?\DateTimeInterface $completedAt = null;
    private ?\DateTimeInterface $cancelledAt = null;
    private \DateTimeInterface $createdAt;
    private \DateTimeInterface $updatedAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrderNo(): string
    {
        return $this->orderNo;
    }

    public function setOrderNo(string $orderNo): self
    {
        $this->orderNo = $orderNo;
        return $this;
    }

    public function getBuyerId(): int
    {
        return $this->buyerId;
    }

    public function setBuyerId(int $buyerId): self
    {
        $this->buyerId = $buyerId;
        return $this;
    }

    public function getTotalAmount(): float
    {
        return $this->totalAmount;
    }

    public function setTotalAmount(float $totalAmount): self
    {
        $this->totalAmount = $totalAmount;
        return $this;
    }

    public function getPayAmount(): float
    {
        return $this->payAmount;
    }

    public function setPayAmount(float $payAmount): self
    {
        $this->payAmount = $payAmount;
        return $this;
    }

    public function getState(): string
    {
        return $this->state;
    }

    public function setState(string $state): self
    {
        $this->state = $state;
        return $this;
    }

    public function getStateLog(): ?array
    {
        return $this->stateLog;
    }

    public function setStateLog(?array $stateLog): self
    {
        $this->stateLog = $stateLog;
        return $this;
    }

    public function getPlacedAt(): ?\DateTimeInterface { return $this->placedAt; }
    public function setPlacedAt(?\DateTimeInterface $dt): self { $this->placedAt = $dt; return $this; }
    public function getPaidAt(): ?\DateTimeInterface { return $this->paidAt; }
    public function setPaidAt(?\DateTimeInterface $dt): self { $this->paidAt = $dt; return $this; }
    public function getShippedAt(): ?\DateTimeInterface { return $this->shippedAt; }
    public function setShippedAt(?\DateTimeInterface $dt): self { $this->shippedAt = $dt; return $this; }
    public function getDeliveredAt(): ?\DateTimeInterface { return $this->deliveredAt; }
    public function setDeliveredAt(?\DateTimeInterface $dt): self { $this->deliveredAt = $dt; return $this; }
    public function getCompletedAt(): ?\DateTimeInterface { return $this->completedAt; }
    public function setCompletedAt(?\DateTimeInterface $dt): self { $this->completedAt = $dt; return $this; }
    public function getCancelledAt(): ?\DateTimeInterface { return $this->cancelledAt; }
    public function setCancelledAt(?\DateTimeInterface $dt): self { $this->cancelledAt = $dt; return $this; }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeInterface
    {
        return $this->updatedAt;
    }
}
