<?php

namespace Tests\DevBites\Auctions\Bidding\v8_dependencies;

use DevBites\Auctions\Bidding\AcceptedOffer;
use DevBites\Auctions\Bidding\Rejection;
use DevBites\Auctions\EventPublisher;
use DevBites\Auctions\Mandates\Mandate;
use DevBites\Auctions\SubmittingOffer;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Tests\DevBites\Auctions\Bidding\v6_refactoredTests\Fixtures;

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
    public function mandateAllowsToSubmitOfferInAuction(): void
    {
        // Given
        $eventPublisher = $this->prophesize(EventPublisher::class);
        $auction = $this->fixtures->longRunningAuction();
        $offer = $this->fixtures->offerFor(100);
        $service = new SubmittingOffer($eventPublisher->reveal());
        $mandate = $this->prophesize(Mandate::class);

        $mandate->authorizeToSubmit($auction, $offer)->willReturn(true);

        // When
        $result = $service->submitOffer($auction, $offer, $mandate->reveal());

        // Then
        $this->assertInstanceOf(AcceptedOffer::class, $result);
    }

    #[Test]
    public function offerCannotBeSubmittedWithIncorrectMandate(): void
    {
        // Given
        $eventPublisher = $this->prophesize(EventPublisher::class);
        $auction = $this->fixtures->longRunningAuction();
        $offer = $this->fixtures->offerFor(100);
        $service = new SubmittingOffer($eventPublisher->reveal());
        $mandate = $this->prophesize(Mandate::class);

        $mandate->authorizeToSubmit($auction, $offer)->willReturn(false);

        // When
        $result = $service->submitOffer($auction, $offer, $mandate->reveal());

        // Then
        $this->assertInstanceOf(Rejection::class, $result);
    }
}