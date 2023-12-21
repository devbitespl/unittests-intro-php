<?php

namespace Tests\DevBites\Auctions\Bidding\v6_refactoredTests;

use DevBites\Auctions\Bidding\Rejection;
use Money\Money;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class SwitchingBuyNowTest extends TestCase
{
    private Fixtures $fixtures;

    protected function setUp(): void
    {
        parent::setUp();

        $this->fixtures = new Fixtures();
    }

    #[Test]
    public function buyNowCannotBeEnabledWhenAtLeastOneOfferWasSubmitted(): void
    {
        // Given
        $auction = $this->fixtures->longRunningAuctionWithOfferForAskingPrice();

        // When
        $result = $auction->enableBuyNow(Money::EUR(200));

        // Then
        $this->assertInstanceOf(Rejection::class, $result);
    }

    public function buyNowCannotBeDisabledWhenAtLeastOneOfferWasSubmitted(): void
    {
        // Given
        $auction = $this->fixtures->longRunningAuctionWithBuyNowPrice(200);

        $auction->submitOffer($this->fixtures->offerFor(150));

        // When
        $result = $auction->disableBuyNow();

        // then
        $this->assertInstanceOf(Rejection::class, $result);
    }
}