<?php

namespace Tests\DevBites\Auctions\Bidding\v3_testingStructures;

use DevBites\Auctions\Bidding\AcceptedOffer;
use DevBites\Auctions\Bidding\Auction;
use DevBites\Auctions\Bidding\Offer;
use Money\Money;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

class AuctionTest extends TestCase
{
    #[Test]
    public function auctionHasNoLeaderAtTheBeginning(): void
    {
        // Given & When
        $auction = new Auction(
            Uuid::uuid4(),
            new \DateTimeImmutable(),
            new \DateTimeImmutable('+10 seconds'),
            Money::EUR(10),
            Money::EUR(100),
            null,
            false
        );

        // Then
        $this->assertNull($auction->leadingOfferAmount());
        $this->assertNull($auction->leadingOfferId());
    }

    #[Test]
    public function offerAcceptedLastMinuteExtendsAuctionTime(): void
    {
        // Given
        $initialClosinbg = new \DateTimeImmutable('+10 seconds');
        $auction = new Auction(
            Uuid::uuid4(),
            new \DateTimeImmutable('-1 day'),
            $initialClosinbg,
            Money::EUR(10),
            Money::EUR(100),
            null,
            true
        );

        $auction->submitOffer(new Offer(
            Uuid::uuid4(),
            Money::EUR(100),
            new \DateTimeImmutable()
        ));

        // When
        $result = $auction->submitOffer(new Offer(
            Uuid::uuid4(),
            Money::EUR(200),
            new \DateTimeImmutable('+25 seconds')
        ));

        // then
        $this->assertInstanceOf(AcceptedOffer::class, $result);
    }

    #[Test]
    public function betterOfferOutbidCurrentlyLeadingOne(): void
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

        $auction->submitOffer(new Offer(
            Uuid::uuid4(),
            Money::EUR(1000),
            new \DateTimeImmutable()
        ));

        // When
        $acceptedOffer = $auction->submitOffer(new Offer(
            Uuid::uuid4(),
            Money::EUR(2000),
            new \DateTimeImmutable()
        ));

        // Then
        $this->assertEquals($acceptedOffer->amount(), $auction->leadingOfferAmount());
        $this->assertEquals($acceptedOffer->id(), $auction->leadingOfferId());
    }
}