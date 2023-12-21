<?php

namespace Tests\DevBites\Auctions\Bidding\v3_testingStructures;

use DevBites\Auctions\Bidding\AcceptedOffer;
use Money\Money;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

class AcceptedOfferTest extends TestCase
{
    #[Test]
    public function test(): void
    {
        $id = Uuid::uuid4();
        $auctionId = Uuid::uuid4();
        $bidderId = Uuid::uuid4();
        $amount = Money::EUR(100);
        $acceptedAt = new \DateTimeImmutable();

        $offer = new AcceptedOffer(
            $id,
            $auctionId,
            $bidderId,
            $amount,
            $acceptedAt
        );

        $this->assertEquals($id, $offer->id());
        $this->assertEquals($auctionId, $offer->auctionId());
        $this->assertEquals($bidderId, $offer->bidderId());
        $this->assertEquals($amount, $offer->amount());
        $this->assertEquals($acceptedAt, $offer->acceptedAt());
    }
}