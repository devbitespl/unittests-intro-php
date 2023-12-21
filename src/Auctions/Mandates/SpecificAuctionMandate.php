<?php

namespace DevBites\Auctions\Mandates;

use DevBites\Auctions\Bidding\Auction;
use DevBites\Auctions\Bidding\Offer;
use Money\Money;
use Ramsey\Uuid\UuidInterface;

class SpecificAuctionMandate implements Mandate
{
    public function __construct(
        private UuidInterface $auctionId,
        private Money $axOfferedAmount
    )
    {
    }

    public function authorizeToSubmit(Auction $auction, Offer $offer): bool
    {
        return $this->auctionId->equals($auction->id()) &&
            $offer->amount->lessThan($this->axOfferedAmount);
    }
}