<?php

namespace DevBites\Auctions\Bidding;

use Money\Money;
use Ramsey\Uuid\UuidInterface;

class AcceptedOffer
{
    public function __construct(
        private UuidInterface      $id,
        private UuidInterface      $auctionId,
        private UuidInterface      $bidderId,
        private Money              $amount,
        private \DateTimeImmutable $acceptedAt
    )
    {
        $this->id = $id;
        $this->auctionId = $auctionId;
        $this->bidderId = $bidderId;
        $this->amount = $amount;
        $this->acceptedAt = $acceptedAt;
    }

    /**
     * @return UuidInterface
     */
    public function id(): UuidInterface
    {
        return $this->id;
    }

    /**
     * @return UuidInterface
     */
    public function auctionId(): UuidInterface
    {
        return $this->auctionId;
    }

    /**
     * @return UuidInterface
     */
    public function bidderId(): UuidInterface
    {
        return $this->bidderId;
    }

    /**
     * @return Money
     */
    public function amount(): Money
    {
        return $this->amount;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function acceptedAt(): \DateTimeImmutable
    {
        return $this->acceptedAt;
    }
}