<?php

namespace Tests\DevBites\Auctions\Bidding\v6_refactoredTests;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ExtendingAuctionTimeTest extends TestCase
{
    private Fixtures $fixtures;

    protected function setUp(): void
    {
        parent::setUp();

        $this->fixtures = new Fixtures();
    }

    #[Test]
    public function offerAcceptedLastMinuteExtendsAuctionTime(): void
    {
        // Given
        $auction = $this->fixtures->endingVerySoonAuction(true);

        $auction->submitOffer($this->fixtures->offerFor(1000));

        // When
        $offerAfterInitialClosingTime = $auction->submitOffer(
            $this->fixtures->offerForSubmittedAt(2000, new \DateTimeImmutable('+25 seconds'))
        );

        // then
        $this->assertTrue($auction->isLeading($offerAfterInitialClosingTime));
    }
}