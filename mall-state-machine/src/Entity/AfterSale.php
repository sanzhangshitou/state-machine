<?php

declare(strict_types=1);

namespace Mall\Entity;

class AfterSale
{
    private ?int $id = null;
    private string $afterSaleNo = '';
    private int $orderId = 0;
    private string $type = 'refund'; // refund / return / exchange
    private string $reason = '';
    private float $refundAmount = 0.00;
    private string $state = 'pending';
    private ?array $stateLog = [];
    private ?\DateTimeInterface $approvedAt = null;
    private ?\DateTimeInterface $returnedAt = null;
    private ?\DateTimeInterface $refundedAt = null;
    private ?\DateTimeInterface $completedAt = null;
    private ?\DateTimeInterface $closedAt = null;
    private \DateTimeInterface $createdAt;
    private \DateTimeInterface $updatedAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }
    public function getAfterSaleNo(): string { return $this->afterSaleNo; }
    public function setAfterSaleNo(string $v): self { $this->afterSaleNo = $v; return $this; }
    public function getOrderId(): int { return $this->orderId; }
    public function setOrderId(int $v): self { $this->orderId = $v; return $this; }
    public function getType(): string { return $this->type; }
    public function setType(string $v): self { $this->type = $v; return $this; }
    public function getReason(): string { return $this->reason; }
    public function setReason(string $v): self { $this->reason = $v; return $this; }
    public function getRefundAmount(): float { return $this->refundAmount; }
    public function setRefundAmount(float $v): self { $this->refundAmount = $v; return $this; }
    public function getState(): string { return $this->state; }
    public function setState(string $v): self { $this->state = $v; return $this; }
    public function getStateLog(): ?array { return $this->stateLog; }
    public function setStateLog(?array $v): self { $this->stateLog = $v; return $this; }
    public function getApprovedAt(): ?\DateTimeInterface { return $this->approvedAt; }
    public function setApprovedAt(?\DateTimeInterface $v): self { $this->approvedAt = $v; return $this; }
    public function getReturnedAt(): ?\DateTimeInterface { return $this->returnedAt; }
    public function setReturnedAt(?\DateTimeInterface $v): self { $this->returnedAt = $v; return $this; }
    public function getRefundedAt(): ?\DateTimeInterface { return $this->refundedAt; }
    public function setRefundedAt(?\DateTimeInterface $v): self { $this->refundedAt = $v; return $this; }
    public function getCompletedAt(): ?\DateTimeInterface { return $this->completedAt; }
    public function setCompletedAt(?\DateTimeInterface $v): self { $this->completedAt = $v; return $this; }
    public function getClosedAt(): ?\DateTimeInterface { return $this->closedAt; }
    public function setClosedAt(?\DateTimeInterface $v): self { $this->closedAt = $v; return $this; }
    public function getCreatedAt(): \DateTimeInterface { return $this->createdAt; }
    public function getUpdatedAt(): \DateTimeInterface { return $this->updatedAt; }
}
