<?php

declare(strict_types=1);

namespace Mall\Entity;

class Payment
{
    private ?int $id = null;
    private string $paymentNo = '';
    private int $orderId = 0;
    private float $amount = 0.00;
    private string $channel = '';
    private string $state = 'pending';
    private ?array $stateLog = [];
    private ?\DateTimeInterface $paidAt = null;
    private ?\DateTimeInterface $refundedAt = null;
    private \DateTimeInterface $createdAt;
    private \DateTimeInterface $updatedAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }
    public function getPaymentNo(): string { return $this->paymentNo; }
    public function setPaymentNo(string $v): self { $this->paymentNo = $v; return $this; }
    public function getOrderId(): int { return $this->orderId; }
    public function setOrderId(int $v): self { $this->orderId = $v; return $this; }
    public function getAmount(): float { return $this->amount; }
    public function setAmount(float $v): self { $this->amount = $v; return $this; }
    public function getChannel(): string { return $this->channel; }
    public function setChannel(string $v): self { $this->channel = $v; return $this; }
    public function getState(): string { return $this->state; }
    public function setState(string $v): self { $this->state = $v; return $this; }
    public function getStateLog(): ?array { return $this->stateLog; }
    public function setStateLog(?array $v): self { $this->stateLog = $v; return $this; }
    public function getPaidAt(): ?\DateTimeInterface { return $this->paidAt; }
    public function setPaidAt(?\DateTimeInterface $v): self { $this->paidAt = $v; return $this; }
    public function getRefundedAt(): ?\DateTimeInterface { return $this->refundedAt; }
    public function setRefundedAt(?\DateTimeInterface $v): self { $this->refundedAt = $v; return $this; }
    public function getCreatedAt(): \DateTimeInterface { return $this->createdAt; }
    public function getUpdatedAt(): \DateTimeInterface { return $this->updatedAt; }
}
