<?php

declare(strict_types=1);

namespace App\Entity;

class Logistics
{
    private ?int $id = null;
    private string $logisticsNo = '';
    private int $orderId = 0;
    private string $carrier = '';
    private string $trackingNo = '';
    private string $state = 'pending';
    private array $stateLog = [];
    private ?\DateTimeImmutable $pickedAt = null;
    private ?\DateTimeImmutable $packedAt = null;
    private ?\DateTimeImmutable $shippedAt = null;
    private ?\DateTimeImmutable $deliveredAt = null;
    private ?\DateTimeImmutable $returnedAt = null;
    private \DateTimeImmutable $createdAt;
    private \DateTimeImmutable $updatedAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }
    public function getLogisticsNo(): string { return $this->logisticsNo; }
    public function setLogisticsNo(string $v): self { $this->logisticsNo = $v; return $this; }
    public function getOrderId(): int { return $this->orderId; }
    public function setOrderId(int $v): self { $this->orderId = $v; return $this; }
    public function getCarrier(): string { return $this->carrier; }
    public function setCarrier(string $v): self { $this->carrier = $v; return $this; }
    public function getTrackingNo(): string { return $this->trackingNo; }
    public function setTrackingNo(string $v): self { $this->trackingNo = $v; return $this; }

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

    public function getPickedAt(): ?\DateTimeImmutable { return $this->pickedAt; }
    public function setPickedAt(?\DateTimeImmutable $v): self { $this->pickedAt = $v; return $this; }
    public function getPackedAt(): ?\DateTimeImmutable { return $this->packedAt; }
    public function setPackedAt(?\DateTimeImmutable $v): self { $this->packedAt = $v; return $this; }
    public function getShippedAt(): ?\DateTimeImmutable { return $this->shippedAt; }
    public function setShippedAt(?\DateTimeImmutable $v): self { $this->shippedAt = $v; return $this; }
    public function getDeliveredAt(): ?\DateTimeImmutable { return $this->deliveredAt; }
    public function setDeliveredAt(?\DateTimeImmutable $v): self { $this->deliveredAt = $v; return $this; }
    public function getReturnedAt(): ?\DateTimeImmutable { return $this->returnedAt; }
    public function setReturnedAt(?\DateTimeImmutable $v): self { $this->returnedAt = $v; return $this; }

    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): \DateTimeImmutable { return $this->updatedAt; }
}
