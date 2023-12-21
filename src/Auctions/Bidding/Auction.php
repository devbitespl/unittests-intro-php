<?php

namespace DevBites\Auctions\Bidding;

use Money\Money;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class Auction
{
    private UuidInterface $id;

    private \DateTimeImmutable $opening;

    private Money $askingPrice;

    private Money $minimalRaise;

    private \DateTimeImmutable $closing;

    private bool $finalized = false;

    private ?UuidInterface $leadingOfferId = null;

    private ?Money $leadingOfferAmount = null;

    private ?Money $buyNowPrice;

    private bool $extraTime;

    public function __construct(
        UuidInterface      $id,
        \DateTimeImmutable $opening,
        \DateTimeImmutable $closing,
        Money              $askingPrice,
        Money              $minimalRaise,
        ?Money             $buyNowPrice = null,
        bool               $extraTime = false
    )
    {
        $this->minimalRaise = $minimalRaise;
        $this->askingPrice = $askingPrice;
        $this->opening = $opening;
        $this->id = $id;
        $this->closing = $closing;
        $this->buyNowPrice = $buyNowPrice;
        $this->extraTime = $extraTime;
    }

    public function submitOffer(Offer $offer): AcceptedOffer|Rejection
    {
        // Blocking changes
        if (!$offer->amount->isSameCurrency($this->askingPrice)) {
            return new Rejection('Offer must be in the same currency as auction');
        }

        if ($offer->submittedAt < $this->opening ||
            $offer->submittedAt > $this->closing ||
            $this->finalized
        ) {
            return new Rejection('Offer must be submitted when auction is open');
        }

        if ($this->leadingOfferId === null) {
            if ($offer->amount->lessThan($this->askingPrice)) {
                return new Rejection('First offer amount must equal asking price');
            }
        }

        if ($this->buyNowPrice === null &&
            $this->leadingOfferId !== null &&
            $offer->amount->lessThan($this->leadingOfferAmount->add($this->minimalRaise))
        ) {
            return new Rejection('Next offer amount must equal minimal raise');
        }

        // State changes:
        // a) Extending auction time when offer was submitted in last 30 minutes
        if ($this->extraTime &&
            $offer->submittedAt > $this->closing->sub(\DateInterval::createFromDateString('30 minutes'))
        ) {
            $this->closing = $this->closing->add(\DateInterval::createFromDateString('30 seconds'));
        }

        // b) Closing auction when offer beats buy now price
        if ($this->buyNowPrice !== null && $offer->amount->greaterThan($this->buyNowPrice)) {
            $this->finalized = true;
        }

        // c) Changing leading offer
        $acceptedOffer = new AcceptedOffer(
            Uuid::uuid4(),
            $this->id,
            $offer->bidderId,
            $offer->amount,
            $offer->submittedAt
        );

        $this->leadingOfferId = $acceptedOffer->id();
        $this->leadingOfferAmount = $acceptedOffer->amount();

        return $acceptedOffer;
    }

    public function enableBuyNow(Money $buyNowPrice): Success|Rejection
    {
        $now = new \DateTimeImmutable();

        if ($now > $this->closing || $this->finalized) {
            return new Rejection('Offer must be submitted when auction is open');
        }

        if (!$buyNowPrice->isSameCurrency($this->askingPrice)) {
            return new Rejection('Whole bidding must be in the same currency');
        }

        if ($this->leadingOfferId !== null) {
            return new Rejection('At least one offer was submitted');
        }

        $minimum = $this->askingPrice->add($this->minimalRaise);

        if (!$buyNowPrice->greaterThan($minimum)) {
            return new Rejection('Buy now price is too low');
        }

        $this->buyNowPrice = $buyNowPrice;

        return new Success();
    }

    public function disableBuyNow(): Success|Rejection
    {
        $now = new \DateTimeImmutable();

        if ($now > $this->closing || $this->finalized
        ) {
            return new Rejection('Offer must be submitted when auction is open');
        }

        if ($this->leadingOfferId !== null) {
            return new Rejection('At least one offer was submitted');
        }

        $this->buyNowPrice = null;

        return new Success();
    }

    public function terminate(): void
    {
        $this->finalized = true;
    }

    public function id(): UuidInterface
    {
        return $this->id;
    }

    public function askingPrice(): Money
    {
        return $this->askingPrice;
    }

    public function minimalRaise(): Money
    {
        return $this->minimalRaise;
    }

    public function closing(): \DateTimeImmutable
    {
        return $this->closing;
    }

    public function leadingOfferId(): ?UuidInterface
    {
        return $this->leadingOfferId;
    }

    public function leadingOfferAmount(): ?Money
    {
        return $this->leadingOfferAmount;
    }

    /**
     * Method introduced currently for testing purposes to hide internal details
     * of class implementation.
     *
     * Can be used in future also to support UI needs.
     */
    public function isLeading(AcceptedOffer $offer): bool
    {
        return $this->leadingOfferId->equals($offer->id());
    }
}