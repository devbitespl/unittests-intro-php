<?php

namespace Tests\DevBites\Auctions\Bidding\v5_testingBehaviorSeparatedTestClasses;

use DevBites\Auctions\Bidding\Auction;
use DevBites\Auctions\Bidding\Offer;
use Money\Money;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

class ExtendingAuctionTimeTest extends TestCase
{
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