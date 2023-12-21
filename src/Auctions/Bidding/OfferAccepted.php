<?php

namespace DevBites\Auctions\Bidding;

use DevBites\Auctions\Event;
use Money\Money;
use Ramsey\Uuid\UuidInterface;

class OfferAccepted extends Event
{
    public function __construct(
        private UuidInterface $auctionId,
        private UuidInterface $offerId,
        private UuidInterface $bidderId,
        private Money $amount
    )
    {
        parent::__construct();
    }

    public function auctionId(): UuidInterface
    {
        return $this->auctionId;
    }

    public function offerId(): UuidInterface
    {
        return $this->offerId;
    }

    public function bidderId(): UuidInterface
    {
        return $this->bidderId;
    }

    public function amount(): Money
    {
        return $this->amount;
    }
}