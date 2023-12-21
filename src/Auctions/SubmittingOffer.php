<?php

namespace DevBites\Auctions;

use DevBites\Auctions\Bidding\AcceptedOffer;
use DevBites\Auctions\Bidding\Auction;
use DevBites\Auctions\Bidding\Offer;
use DevBites\Auctions\Bidding\OfferAccepted;
use DevBites\Auctions\Bidding\Rejection;
use DevBites\Auctions\Bidding\Success;
use DevBites\Auctions\Mandates\Mandate;

class SubmittingOffer
{
    public function __construct(
        private EventPublisher $eventPublisher
    )
    {
    }

    public function submitOffer(Auction $auction, Offer $offer, Mandate $mandate): AcceptedOffer|Rejection
    {
        if (!$mandate->authorizeToSubmit($auction, $offer)) {
            return new Rejection('Mandate is not sufficient to submit an offer');
        }

        $result = $auction->submitOffer($offer);

        if ($result instanceof AcceptedOffer) {
            $this->eventPublisher->publish(new OfferAccepted(
                $auction->id(),
                $result->id(),
                $offer->bidderId,
                $offer->amount,
            ));
        }

        return $result;
    }
}