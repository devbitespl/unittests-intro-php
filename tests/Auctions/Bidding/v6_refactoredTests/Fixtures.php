<?php

namespace Tests\DevBites\Auctions\Bidding\v6_refactoredTests;

use DevBites\Auctions\Bidding\Auction;
use DevBites\Auctions\Bidding\Offer;
use Money\Currency;
use Money\Money;
use Ramsey\Uuid\Uuid;

class Fixtures
{
    private Money $askingPrice;
    private Money $minimalRise;

    public function __construct()
    {
        $this->askingPrice = Money::EUR(100);
        $this->minimalRise = Money::EUR(10);
    }

    public function longRunningAuction(): Auction
    {
        return new Auction(
            Uuid::uuid4(),
            new \DateTimeImmutable(),
            new \DateTimeImmutable('+10 days'),
            $this->askingPrice,
            $this->minimalRise,
            null,
            false
        );
    }

    public function longRunningAuctionWithBuyNowPrice(int $amount): Auction
    {
        $auction = $this->longRunningAuction();

        $auction->enableBuyNow(Money::EUR($amount));

        return $auction;
    }

    public function notOpenedYetAuction(): Auction
    {
        return new Auction(
            Uuid::uuid4(),
            new \DateTimeImmutable('+1 day'),
            new \DateTimeImmutable('+10 days'),
            Money::EUR(10),
            Money::EUR(100),
            null,
            false
        );
    }

    public function alreadyClosedAuction(): Auction
    {
        return new Auction(
            Uuid::uuid4(),
            new \DateTimeImmutable('-10 days'),
            new \DateTimeImmutable('-1 day'),
            Money::EUR(10),
            Money::EUR(100),
            null,
            false
        );
    }

    public function endingVerySoonAuction(bool $extratime): Auction
    {
        return new Auction(
            Uuid::uuid4(),
            new \DateTimeImmutable('-10 days'),
            new \DateTimeImmutable('+10 seconds'),
            Money::EUR(10),
            Money::EUR(100),
            null,
            $extratime
        );
    }

    public function terminatedAuction(): Auction
    {
        $auction = $this->longRunningAuction();

        $auction->terminate();

        return $auction;
    }

    public function longRunningAuctionWithOfferForAskingPrice(): Auction
    {
        $auction = $this->longRunningAuction();

        $auction->submitOffer($this->offerFor($this->askingPrice->getAmount()));

        return $auction;
    }

    public function offerFor(int $amount): Offer
    {
        return new Offer(
            Uuid::uuid4(),
            Money::EUR($amount),
            new \DateTimeImmutable()
        );
    }

    public function offerForInCurrency(Currency $currency, int $amount): Offer
    {
        return new Offer(
            Uuid::uuid4(),
            new Money($amount, new Currency($currency)),
            new \DateTimeImmutable()
        );
    }

    public function offerForSubmittedAt(int $amount, \DateTimeImmutable $at): Offer
    {
        return new Offer(
            Uuid::uuid4(),
            Money::EUR($amount),
            $at
        );
    }
}