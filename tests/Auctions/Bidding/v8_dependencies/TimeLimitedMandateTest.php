<?php

namespace Tests\DevBites\Auctions\Bidding\v8_dependencies;

use DevBites\Auctions\Mandates\TimeLimitedMandate;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class TimeLimitedMandateTest extends TestCase
{
    use ProphecyTrait;

    private Fixtures $fixtures;

    protected function setUp(): void
    {
        parent::setUp();

        $this->fixtures = new Fixtures();
    }

    #[Test]
    #[DataProvider('dataProvider')]
    public function test(\DateTimeImmutable $validTo, bool $expectedResult): void
    {
        // Given
        $auction = $this->fixtures->endingVerySoonAuction(false);
        $offer = $this->fixtures->offerFor(100);
        $mandate = new TimeLimitedMandate($validTo);

        // When & Then
        $this->assertEquals($expectedResult, $mandate->authorizeToSubmit($auction, $offer));
    }

    public static function dataProvider(): array
    {
        return [
            'Mandate expires very soon' => [new \DateTimeImmutable('+1 seconds'), true],
            'Mandate already expired' => [new \DateTimeImmutable('-1 seconds'), false],
        ];
    }
}