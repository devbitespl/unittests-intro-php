<?php

namespace Tests\DevBites\Auctions\Bidding\v5_testingBehaviorSeparatedTestClasses;

use DevBites\Auctions\Bidding\Auction;
use DevBites\Auctions\Bidding\Offer;
use DevBites\Auctions\Bidding\Rejection;
use Money\Money;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

class SwitchingBuyNowTest extends TestCase
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
    public function buyNowCannotBeEnabledWhenAtLeastOneOfferWasSubmitted(): void
    {
        // Given
        $buyNowPrice = Money::EUR(200);

        $this->auction->submitOffer(new Offer(
            Uuid::uuid4(),
            Money::EUR(1000),
            new \DateTimeImmutable('+1 days')
        ));

        // When
        $result = $this->auction->enableBuyNow($buyNowPrice);

        // Then
        $this->assertInstanceOf(Rejection::class, $result);
    }

    public function buyNowCannotBeDisabledWhenAtLeastOneOfferWasSubmitted(): void
    {
        // Given
        $buyNowPrice = Money::EUR(200);

        $this->auction->enableBuyNow($buyNowPrice);
        $this->auction->submitOffer(new Offer(
            Uuid::uuid4(),
            Money::EUR(1000),
            new \DateTimeImmutable('+1 days')
        ));

        // When
        $result = $this->auction->disableBuyNow();

        // then
        $this->assertInstanceOf(Rejection::class, $result);
    }

}