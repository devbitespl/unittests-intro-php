<?php

namespace DevBites\Auctions\Mandates;

use DevBites\Auctions\Bidding\Auction;
use DevBites\Auctions\Bidding\Offer;

class TimeLimitedMandate implements Mandate
{
    public function __construct(
        private \DateTimeImmutable $validTo
    )
    {
    }

    public function authorizeToSubmit(Auction $auction, Offer $offer): bool
    {
        return $offer->submittedAt < $this->validTo;
    }
}