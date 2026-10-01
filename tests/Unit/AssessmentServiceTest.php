<?php

declare(strict_types=1);

namespace CarMoneyLab\Tests\Unit;

use CarMoneyLab\Domain\ApplicationValidator;
use CarMoneyLab\Domain\AssessmentService;
use CarMoneyLab\Domain\DecisionEngine;
use CarMoneyLab\Domain\LtvCalculator;
use CarMoneyLab\Domain\VehicleAge;
use CarMoneyLab\Domain\VinValidator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class AssessmentServiceTest extends TestCase
{
    private AssessmentService $service;

    protected function setUp(): void
    {
        $rules = require __DIR__ . '/../../backend/config/rules.php';
        $age = new VehicleAge((int) date('Y'));

        $this->service = new AssessmentService(
            new ApplicationValidator($rules, new VinValidator($rules['vin']), $age),
            new LtvCalculator(),
            new DecisionEngine($rules['ltv'], $rules['vehicle']['review_mileage_threshold_km']),
            $age,
        );
    }

    /** @return array<string,mixed> */
    private function payload(int $amount, int $marketValue, int $mileage = 96000): array
    {
        return [
            'vin' => 'XTA21099998765432',
            'year' => (int) date('Y') - 4,
            'mileage' => $mileage,
            'market_value' => $marketValue,
            'requested_amount' => $amount,
            'term_months' => 24,
        ];
    }

    public function testApprovesLowLtvAndSetsLimitToRequestedAmount(): void
    {
        $result = $this->service->assess($this->payload(450000, 900000));

        self::assertSame(50.0, $result['ltv']);
        self::assertSame(DecisionEngine::APPROVE, $result['decision']);
        self::assertSame(450000, $result['approved_limit']);
        self::assertSame(4, $result['vehicle_age']);
    }

    public function testSendsMiddleLtvToReviewWithZeroLimit(): void
    {
        $result = $this->service->assess($this->payload(675000, 900000));

        self::assertSame(75.0, $result['ltv']);
        self::assertSame(DecisionEngine::REVIEW, $result['decision']);
        self::assertSame(0, $result['approved_limit']);
    }

    #[DataProvider('mileageBoundaries')]
    public function testAppliesMileageRuleOnTheBoundary(int $mileage, string $expectedDecision, int $expectedLimit): void
    {
        $result = $this->service->assess($this->payload(450000, 900000, $mileage));

        self::assertSame(50.0, $result['ltv']);
        self::assertSame($expectedDecision, $result['decision']);
        self::assertSame($expectedLimit, $result['approved_limit']);
    }

    /** @return array<string,array{int,string,int}> */
    public static function mileageBoundaries(): array
    {
        return [
            'пробег 399 999 — до порога' => [399999, DecisionEngine::APPROVE, 450000],
            'пробег 400 000 — ровно на пороге, включительно' => [400000, DecisionEngine::APPROVE, 450000],
            'пробег 400 001 — сразу за порогом, approve понижается до review' => [400001, DecisionEngine::REVIEW, 0],
        ];
    }

    #[DataProvider('nonApproveDecisionsWithHighMileage')]
    public function testKeepsNonApproveDecisionWithHighMileage(
        int $amount,
        string $expectedDecision,
    ): void {
        $result = $this->service->assess($this->payload($amount, 900000, 450000));

        self::assertSame($expectedDecision, $result['decision']);
        self::assertSame(0, $result['approved_limit']);
    }

    /** @return array<string,array{int,string}> */
    public static function nonApproveDecisionsWithHighMileage(): array
    {
        return [
            'review по LTV сохраняется' => [675000, DecisionEngine::REVIEW],
            'reject по LTV сохраняется' => [855000, DecisionEngine::REJECT],
        ];
    }
}
