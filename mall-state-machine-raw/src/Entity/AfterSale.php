<?php

declare(strict_types=1);

namespace App\Entity;

class AfterSale
{
    private ?int $id = null;
    private string $afterSaleNo = '';
    private int $orderId = 0;
    private string $type = 'refund';
    private string $reason = '';
    private float $refundAmount = 0.00;
    private string $state = 'pending';
    private array $stateLog = [];
    private ?\DateTimeImmutable $approvedAt = null;
    private ?\DateTimeImmutable $returnedAt = null;
    private ?\DateTimeImmutable $refundedAt = null;
    private ?\DateTimeImmutable $completedAt = null;
    private ?\DateTimeImmutable $closedAt = null;
    private \DateTimeImmutable $createdAt;
    private \DateTimeImmutable $updatedAt;

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

    public function getStateLog(): array { return $this->stateLog; }
    public function setStateLog(array $v): self { $this->stateLog = $v; return $this; }
    public function appendStateLog(string $state): void
    {
        $this->stateLog[] = [
            'state' => $state,
            'at'    => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
        ];
    }

    public function getApprovedAt(): ?\DateTimeImmutable { return $this->approvedAt; }
    public function setApprovedAt(?\DateTimeImmutable $v): self { $this->approvedAt = $v; return $this; }
    public function getReturnedAt(): ?\DateTimeImmutable { return $this->returnedAt; }
    public function setReturnedAt(?\DateTimeImmutable $v): self { $this->returnedAt = $v; return $this; }
    public function getRefundedAt(): ?\DateTimeImmutable { return $this->refundedAt; }
    public function setRefundedAt(?\DateTimeImmutable $v): self { $this->refundedAt = $v; return $this; }
    public function getCompletedAt(): ?\DateTimeImmutable { return $this->completedAt; }
    public function setCompletedAt(?\DateTimeImmutable $v): self { $this->completedAt = $v; return $this; }
    public function getClosedAt(): ?\DateTimeImmutable { return $this->closedAt; }
    public function setClosedAt(?\DateTimeImmutable $v): self { $this->closedAt = $v; return $this; }

    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): \DateTimeImmutable { return $this->updatedAt; }
}
