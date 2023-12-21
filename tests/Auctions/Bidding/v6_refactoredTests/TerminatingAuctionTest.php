<?php

namespace Tests\DevBites\Auctions\Bidding\v6_refactoredTests;

use DevBites\Auctions\Bidding\Rejection;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class TerminatingAuctionTest extends TestCase
{
    private Fixtures $fixtures;

    protected function setUp(): void
    {
        parent::setUp();

        $this->fixtures = new Fixtures();
    }


    #[Test]
    public function terminatingEndsAcceptingOffers(): void
    {
        // Given
        $auction = $this->fixtures->longRunningAuctionWithOfferForAskingPrice();

        $auction->terminate();

        // When
        $result = $auction->submitOffer($this->fixtures->offerFor(1000));

        // Then
        $this->assertInstanceOf(Rejection::class, $result);
    }
}