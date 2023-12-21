<?php

namespace Tests\DevBites\Auctions\Bidding\v2_testingManyThingsAtOnce;

use DevBites\Auctions\Bidding\Auction;
use DevBites\Auctions\Bidding\Offer;
use DevBites\Auctions\Bidding\Rejection;
use Money\Money;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

class AuctionTest extends TestCase
{
    #[Test]
    public function test(): void
    {
        $initialClosing = new \DateTimeImmutable('+10 seconds');
        $auction = new Auction(
            Uuid::uuid4(),
            new \DateTimeImmutable('-1 day'),
            $initialClosing,
            Money::EUR(10),
            Money::EUR(100),
            null,
            true
        );

        $this->assertNull($auction->leadingOfferId());

        $auction->enableBuyNow(Money::EUR(500));

        $firstOffer = $auction->submitOffer(new Offer(
            Uuid::uuid4(),
            Money::EUR(100),
            new \DateTimeImmutable('-1 hour')
        ));

        $this->assertEquals($firstOffer->amount(), $auction->leadingOfferAmount());
        $this->assertEquals($firstOffer->id(), $auction->leadingOfferId());
        $this->assertEquals($initialClosing, $auction->closing());

        $secondOffer = $auction->submitOffer(new Offer(
            Uuid::uuid4(),
            Money::EUR(1000),
            new \DateTimeImmutable()
        ));

        $this->assertEquals($secondOffer->amount(), $auction->leadingOfferAmount());
        $this->assertEquals($secondOffer->id(), $auction->leadingOfferId());
        $this->assertTrue($auction->closing() > $initialClosing);

        $this->assertEquals($secondOffer->amount(), $auction->leadingOfferAmount());
        $this->assertEquals($secondOffer->id(), $auction->leadingOfferId());
    }

    #[Test]
    public function testFail(): void
    {
        $initialClosing = new \DateTimeImmutable('+10 seconds');
        $auction = new Auction(
            Uuid::uuid4(),
            new \DateTimeImmutable('-1 day'),
            $initialClosing,
            Money::EUR(10),
            Money::EUR(100),
            null,
            true
        );

        $this->assertNull($auction->leadingOfferId());

        $result = $auction->submitOffer(new Offer(
            Uuid::uuid4(),
            Money::EUR(99),
            new \DateTimeImmutable('+20 seconds')
        ));

        $this->assertInstanceOf(Rejection::class, $result);
        $this->assertNull($auction->leadingOfferId());
    }
}