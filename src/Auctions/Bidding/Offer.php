<?php

namespace DevBites\Auctions\Bidding;

use Money\Money;
use Ramsey\Uuid\UuidInterface;

class Offer
{
    public function __construct(
        public readonly UuidInterface $bidderId,
        public readonly Money $amount,
        public readonly \DateTimeImmutable $submittedAt
    )
    {
    }
}