<?php

namespace Tests\DevBites\Auctions\Bidding\v5_testingBehaviorSeparatedTestClasses;

use DevBites\Auctions\Bidding\AcceptedOffer;
use DevBites\Auctions\Bidding\Auction;
use DevBites\Auctions\Bidding\Offer;
use DevBites\Auctions\Bidding\Rejection;
use Money\Money;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

class AuctionBiddingTest extends TestCase
{
    private Auction $auction;

    protected function setUp(): void
    {
        parent::setUp();

        $this->auction = $this->longRunningAuction();
    }

    #[Test]
    public function biddingStartsWithAnInitialOffer(): void
    {
        // Given
        $offer = new Offer(
            Uuid::uuid4(),
            Money::EUR(100),
            new \DateTimeImmutable('+1 day')
        );

        // When
        $result = $this->auction->submitOffer($offer);

        // Then
        $this->isInstanceOf(AcceptedOffer::class, $result);
        $this->assertTrue($this->auction->isLeading($result));
    }

    #[Test]
    public function betterOfferOutbidCurrentlyLeadingOne(): void
    {
        // Given
        $firstOffer = $this->auction->submitOffer(new Offer(
            Uuid::uuid4(),
            Money::EUR(1000),
            new \DateTimeImmutable()
        ));

        // When
        $secondOffer = $this->auction->submitOffer(new Offer(
            Uuid::uuid4(),
            Money::EUR(2000),
            new \DateTimeImmutable()
        ));

        // Then
        $this->assertFalse($this->auction->isLeading($firstOffer));
        $this->assertTrue($this->auction->isLeading($secondOffer));
    }

    #[Test]
    public function submittedOfferWithBuyNowPriceEndsAnAuction(): void
    {
        // Given
        $auction = new Auction(
            Uuid::uuid4(),
            new \DateTimeImmutable(),
            new \DateTimeImmutable('+10 days'),
            Money::EUR(10),
            Money::EUR(100),
            Money::EUR(2000),
            false
        );

        $firstOffer = $auction->submitOffer(new Offer(
            Uuid::uuid4(),
            Money::EUR(5000),
            new \DateTimeImmutable('+1 day')
        ));

        // When
        $result = $this->auction->submitOffer(new Offer(
            Uuid::uuid4(),
            Money::EUR(5000),
            new \DateTimeImmutable('+1 day 1 second')
        ));

        // Then
        $this->isInstanceOf(Rejection::class, $result);
        $this->assertTrue($auction->isLeading($firstOffer));
    }

    #[Test]
    public function firstOfferMustEqualTheAskingPrice(): void
    {
        // Given
        $auction = new Auction(
            Uuid::uuid4(),
            new \DateTimeImmutable(),
            new \DateTimeImmutable('+10 days'),
            Money::EUR(10),
            Money::EUR(100),
            null,
            false
        );

        // When
        $result = $auction->submitOffer(new Offer(
            Uuid::uuid4(),
            Money::EUR(9),
            new \DateTimeImmutable('+1 day')
        ));

        // Then
        $this->assertInstanceOf(Rejection::class, $result);
    }

    #[Test]
    public function offerCannotBeSubmittedBeforeOpening(): void
    {
        // Given
        $auction = $this->notOpenedYetAuction();

        // When
        $result = $auction->submitOffer($this->offerFor(1000));

        // Then
        $this->assertInstanceOf(Rejection::class, $result);
    }

    private function offerFor(int $amount): Offer
    {
        return new Offer(
            Uuid::uuid4(),
            Money::EUR($amount),
            new \DateTimeImmutable()
        );
    }

    private function notOpenedYetAuction(): Auction
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

    private function longRunningAuction(): Auction
    {
        return new Auction(
            Uuid::uuid4(),
            new \DateTimeImmutable(),
            new \DateTimeImmutable('+10 days'),
            Money::EUR(10),
            Money::EUR(100),
            null,
            false
        );
    }
}