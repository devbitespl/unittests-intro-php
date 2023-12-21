<?php

namespace Tests\DevBites\Auctions\Bidding\v6_refactoredTests;

use DevBites\Auctions\Bidding\AcceptedOffer;
use DevBites\Auctions\Bidding\Auction;
use DevBites\Auctions\Bidding\Offer;
use DevBites\Auctions\Bidding\Rejection;
use Money\Currency;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class AuctionBiddingTest extends TestCase
{
    private Fixtures $fixtures;

    protected function setUp(): void
    {
        parent::setUp();

        $this->fixtures = new Fixtures();
    }

    #[Test]
    public function biddingStartsWithAnInitialOffer(): void
    {
        // Given
        $auction = $this->fixtures->longRunningAuction();

        // When
        $result = $auction->submitOffer($this->fixtures->offerFor(1000));

        // Then
        $this->isInstanceOf(AcceptedOffer::class, $result);
        $this->assertTrue($auction->isLeading($result));
    }

    #[Test]
    public function betterOfferOutbidCurrentlyLeadingOne(): void
    {
        // Given
        $auction = $this->fixtures->longRunningAuction();

        $firstOffer = $auction->submitOffer($this->fixtures->offerFor(1000));

        // When
        $secondOffer = $auction->submitOffer($this->fixtures->offerFor(2000));

        // Then
        $this->assertFalse($auction->isLeading($firstOffer));
        $this->assertTrue($auction->isLeading($secondOffer));
    }

    #[Test]
    public function submittedOfferWithBuyNowPriceEndsAnAuction(): void
    {
        // Given
        $auction = $this->fixtures->longRunningAuctionWithBuyNowPrice(2000);

        $firstOffer = $auction->submitOffer($this->fixtures->offerFor(5000));

        // When
        $result = $auction->submitOffer($this->fixtures->offerFor(6000));

        // Then
        $this->isInstanceOf(Rejection::class, $result);
        $this->assertTrue($auction->isLeading($firstOffer));
    }

    #[Test]
    #[DataProvider('offersToReject')]
    public function someOffersMustBeRejected(Auction $auction, Offer $offer): void
    {
        // When
        $result = $auction->submitOffer($offer);

        // Then
        $this->assertInstanceOf(Rejection::class, $result);
    }

    public static function offersToReject(): array
    {
        $fixtures = new Fixtures();

        return [
            'First offer must equal the asking price' => [
                $fixtures->longRunningAuction(),
                $fixtures->offerFor(9),
            ],
            'Next offer must equal the minimal raise' => [
                $fixtures->longRunningAuctionWithOfferForAskingPrice(),
                $fixtures->offerFor(109),
            ],
            'Offer cannot be submitted before opening' => [
                $fixtures->notOpenedYetAuction(),
                $fixtures->offerFor(1000),
            ],
            'Offer cannot be submitted after closing' => [
                $fixtures->alreadyClosedAuction(),
                $fixtures->offerFor(1000),
            ],
            'Offer cannot be submitted in different currency' => [
                $fixtures->longRunningAuction(),
                $fixtures->offerForInCurrency(new Currency('USD'), 1000)
            ],
        ];
    }
}