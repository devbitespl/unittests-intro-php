<?php

namespace Tests\DevBites\Auctions\Bidding\v4_testingBehaviorSingleTestClass;

use DevBites\Auctions\Bidding\AcceptedOffer;
use DevBites\Auctions\Bidding\Auction;
use DevBites\Auctions\Bidding\Offer;
use DevBites\Auctions\Bidding\Rejection;
use Money\Money;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

class AuctionTest extends TestCase
{
    private Auction $auction;

    protected function setUp(): void
    {
        parent::setUp();

        $this->auction = new Auction(
            Uuid::uuid4(),
            new \DateTimeImmutable(),
            new \DateTimeImmutable('+10 days'),
            Money::EUR(10),
            Money::EUR(100),
            null,
            false
        );
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
    public function wholeBiddingMustBeConductedInTheSameCurrency(): void
    {
        // When
        $result = $this->auction->enableBuyNow(Money::USD(1000));

        // Then
        $this->assertInstanceOf(Rejection::class, $result);
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
        $auction = new Auction(
            Uuid::uuid4(),
            new \DateTimeImmutable('+1 day'),
            new \DateTimeImmutable('+10 days'),
            Money::EUR(10),
            Money::EUR(100),
            null,
            false
        );

        // When
        $result = $auction->submitOffer(new Offer(
            Uuid::uuid4(),
            Money::EUR(1000),
            new \DateTimeImmutable()
        ));

        // Then
        $this->assertInstanceOf(Rejection::class, $result);
    }

    #[Test]
    public function offerCannotBeSubmittedAfterClosing(): void
    {
        // Given
        $auction = new Auction(
            Uuid::uuid4(),
            new \DateTimeImmutable('-1 day'),
            new \DateTimeImmutable('+10 days'),
            Money::EUR(10),
            Money::EUR(100),
            null,
            false
        );

        // When
        $result = $auction->submitOffer(new Offer(
            Uuid::uuid4(),
            Money::EUR(1000),
            new \DateTimeImmutable('+11 days')
        ));

        // Then
        $this->assertInstanceOf(Rejection::class, $result);
    }

    #[Test]
    public function offerMustBeSubmittedInTheSameCurrencyAsAuction(): void
    {
        // Given
        $auction = new Auction(
            Uuid::uuid4(),
            new \DateTimeImmutable('-1 day'),
            new \DateTimeImmutable('+10 days'),
            Money::EUR(10),
            Money::EUR(100),
            null,
            false
        );

        // When
        $result = $auction->submitOffer(new Offer(
            Uuid::uuid4(),
            Money::USD(1000),
            new \DateTimeImmutable('+11 days')
        ));

        // Then
        $this->assertInstanceOf(Rejection::class, $result);
    }

    #[Test]
    public function offerAcceptedLastMinuteExtendsAuctionTime(): void
    {
        // Given
        $auction = new Auction(
            Uuid::uuid4(),
            new \DateTimeImmutable('-10 days'),
            new \DateTimeImmutable('+10 seconds'),
            Money::EUR(10),
            Money::EUR(100),
            null,
            true
        );

        $auction->submitOffer(new Offer(
            Uuid::uuid4(),
            Money::EUR(1000),
            new \DateTimeImmutable()
        ));

        // When
        $offerAfterInitialClosingTime = $auction->submitOffer(new Offer(
            Uuid::uuid4(),
            Money::EUR(2000),
            new \DateTimeImmutable('+25 seconds')
        ));

        // then
        $this->assertTrue($auction->isLeading($offerAfterInitialClosingTime));
    }
}