<?php

namespace DevBites\Auctions\Mandates;


use DevBites\Auctions\Bidding\Auction;
use DevBites\Auctions\Bidding\Offer;

interface Mandate
{
    public function authorizeToSubmit(Auction $auction, Offer $offer): bool;
}