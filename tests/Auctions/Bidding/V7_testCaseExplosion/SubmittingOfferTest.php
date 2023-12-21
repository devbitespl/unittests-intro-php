<?php

namespace Tests\DevBites\Auctions\Bidding\V7_testCaseExplosion;

use DevBites\Auctions\Bidding\AcceptedOffer;
use DevBites\Auctions\Bidding\Rejection;
use DevBites\Auctions\EventPublisher;
use DevBites\Auctions\Mandates\CarteBlanche;
use DevBites\Auctions\Mandates\Mandate;
use DevBites\Auctions\Mandates\SpecificAuctionMandate;
use DevBites\Auctions\Mandates\TimeLimitedMandate;
use DevBites\Auctions\SubmittingOffer;
use Money\Money;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Ramsey\Uuid\Nonstandard\Uuid;

class SubmittingOfferTest extends TestCase
{
    use ProphecyTrait;

    private Fixtures $fixtures;

    protected function setUp(): void
    {
        parent::setUp();

        $this->fixtures = new Fixtures();
    }

    #[Test]
    #[DataProvider('mandates')]
    public function offerCanBeSubmittedWithMandate(Mandate $mandate): void
    {
        // Given
        $eventPublisher = $this->prophesize(EventPublisher::class);
        $auction = $this->fixtures->longRunningAuction();
        $service = new SubmittingOffer($eventPublisher->reveal());

        // When
        $result = $service->submitOffer($auction, $this->fixtures->offerFor(100), $mandate);

        // Then
        $this->assertInstanceOf(AcceptedOffer::class, $result);
    }

    #[Test]
    public function offerCannotBeSubmittedAfterExpirationOfMandate(): void
    {
        // Given
        $eventPublisher = $this->prophesize(EventPublisher::class);
        $auction = $this->fixtures->longRunningAuction();
        $mandate = new TimeLimitedMandate(new \DateTimeImmutable('-1 day'));
        $service = new SubmittingOffer($eventPublisher->reveal());

        // When
        $result = $service->submitOffer($auction, $this->fixtures->offerFor(100), $mandate);

        // Then
        $this->assertInstanceOf(Rejection::class, $result);
    }

    #[Test]
    public function offerCannotBeSubmittedWhenMandateIsLimitedToGivenAmount(): void
    {
        // Given
        $eventPublisher = $this->prophesize(EventPublisher::class);
        $auction = $this->fixtures->longRunningAuction();
        $mandate = new SpecificAuctionMandate($auction->id(), Money::EUR(500));
        $service = new SubmittingOffer($eventPublisher->reveal());

        // When
        $result = $service->submitOffer($auction, $this->fixtures->offerFor(1000), $mandate);

        // Then
        $this->assertInstanceOf(Rejection::class, $result);
    }

    /**
     * The structure of this test can be slightly improved so that it directly reveals (and absolutely guarantees)
     * the auction behavior tested here.
     *
     * What can you propose?
     */
    #[Test]
    public function offerCannotBeSubmittedWhenMandateAllowsToBidInDifferentAuction(): void
    {
        // Given
        $eventPublisher = $this->prophesize(EventPublisher::class);
        $auction = $this->fixtures->longRunningAuction();
        $mandate = new SpecificAuctionMandate(Uuid::uuid4(), Money::EUR(500));
        $service = new SubmittingOffer($eventPublisher->reveal());

        // When
        $result = $service->submitOffer($auction, $this->fixtures->offerFor(100), $mandate);

        // Then
        $this->assertInstanceOf(Rejection::class, $result);
    }

    public static function mandates(): array
    {
        return [
            [new TimeLimitedMandate(new \DateTimeImmutable('+1 day'))],
            [new CarteBlanche()],
        ];
    }
}