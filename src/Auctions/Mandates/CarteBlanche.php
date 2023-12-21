<?php

namespace DevBites\Auctions\Mandates;

use DevBites\Auctions\Bidding\Auction;
use DevBites\Auctions\Bidding\Offer;

class CarteBlanche implements Mandate
{
    public function authorizeToSubmit(Auction $auction, Offer $offer): bool
    {
        return true;
    }
}