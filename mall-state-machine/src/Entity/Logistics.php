<?php

declare(strict_types=1);

namespace Mall\Entity;

class Logistics
{
    private ?int $id = null;
    private string $logisticsNo = '';
    private int $orderId = 0;
    private string $carrier = '';
    private string $trackingNo = '';
    private string $state = 'pending';
    private ?array $stateLog = [];
    private ?\DateTimeInterface $pickedAt = null;
    private ?\DateTimeInterface $packedAt = null;
    private ?\DateTimeInterface $shippedAt = null;
    private ?\DateTimeInterface $deliveredAt = null;
    private ?\DateTimeInterface $returnedAt = null;
    private \DateTimeInterface $createdAt;
    private \DateTimeInterface $updatedAt;

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
    public function getStateLog(): ?array { return $this->stateLog; }
    public function setStateLog(?array $v): self { $this->stateLog = $v; return $this; }
    public function getPickedAt(): ?\DateTimeInterface { return $this->pickedAt; }
    public function setPickedAt(?\DateTimeInterface $v): self { $this->pickedAt = $v; return $this; }
    public function getPackedAt(): ?\DateTimeInterface { return $this->packedAt; }
    public function setPackedAt(?\DateTimeInterface $v): self { $this->packedAt = $v; return $this; }
    public function getShippedAt(): ?\DateTimeInterface { return $this->shippedAt; }
    public function setShippedAt(?\DateTimeInterface $v): self { $this->shippedAt = $v; return $this; }
    public function getDeliveredAt(): ?\DateTimeInterface { return $this->deliveredAt; }
    public function setDeliveredAt(?\DateTimeInterface $v): self { $this->deliveredAt = $v; return $this; }
    public function getReturnedAt(): ?\DateTimeInterface { return $this->returnedAt; }
    public function setReturnedAt(?\DateTimeInterface $v): self { $this->returnedAt = $v; return $this; }
    public function getCreatedAt(): \DateTimeInterface { return $this->createdAt; }
    public function getUpdatedAt(): \DateTimeInterface { return $this->updatedAt; }
}
