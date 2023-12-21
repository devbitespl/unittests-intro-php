<?php

namespace Tests\DevBites\Auctions\Bidding\v5_testingBehaviorSeparatedTestClasses;

use DevBites\Auctions\Bidding\Auction;
use DevBites\Auctions\Bidding\Offer;
use DevBites\Auctions\Bidding\Rejection;
use Money\Money;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

class TerminatingAuctionTest extends TestCase
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
    public function terminatingEndsAcceptingOffers(): void
    {
        // Given
        $this->auction->submitOffer(new Offer(
            Uuid::uuid4(),
            Money::EUR(1000),
            new \DateTimeImmutable()
        ));
        $this->auction->terminate();

        // When
        $result = $this->auction->submitOffer(new Offer(
            Uuid::uuid4(),
            Money::EUR(2000),
            new \DateTimeImmutable()
        ));

        // Then
        $this->assertInstanceOf(Rejection::class, $result);
    }
}