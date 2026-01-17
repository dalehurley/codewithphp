<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;
use DataScience\Statistics\ConfidenceIntervalCalculator;

class ConfidenceIntervalCalculatorTest extends TestCase
{
    private ConfidenceIntervalCalculator $calculator;
    
    protected function setUp(): void
    {
        $this->calculator = new ConfidenceIntervalCalculator();
    }
    
    public function test_confidence_interval_for_mean_with_known_data(): void
    {
        // Known dataset: [1, 2, 3, 4, 5]
        // Mean = 3
        $data = [1, 2, 3, 4, 5];
        
        $ci = $this->calculator->forMean($data, 0.95);
        
        $this->assertEquals(3.0, $ci['mean']);
        $this->assertEqualsWithDelta(1.58, $ci['std_dev'], 0.1);
        $this->assertLessThan($ci['mean'], $ci['lower_bound']);
        $this->assertGreaterThan($ci['mean'], $ci['upper_bound']);
    }
    
    public function test_confidence_interval_for_proportion(): void
    {
        // 50 successes out of 100 trials
        // Proportion = 0.5
        $ci = $this->calculator->forProportion(50, 100, 0.95);
        
        $this->assertEquals(0.5, $ci['proportion']);
        $this->assertEquals(50, $ci['percentage']);
        $this->assertEqualsWithDelta(40, $ci['lower_bound'] * 100, 5);
        $this->assertEqualsWithDelta(60, $ci['upper_bound'] * 100, 5);
    }
    
    public function test_throws_exception_on_insufficient_data(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Need at least 2 data points');
        
        $this->calculator->forMean([5.0]);
    }
    
    public function test_throws_exception_on_invalid_confidence_level(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Confidence level must be between 0 and 1');
        
        $this->calculator->forMean([1, 2, 3], 1.5);
    }
    
    public function test_throws_exception_on_non_numeric_data(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        
        $this->calculator->forMean([1, 2, 'three']);
    }
    
    public function test_confidence_interval_for_mean_difference(): void
    {
        $group1 = [5, 6, 7, 8, 9];
        $group2 = [10, 11, 12, 13, 14];
        
        $ci = $this->calculator->forMeanDifference($group1, $group2, 0.95);
        
        $this->assertEquals(7, $ci['mean1']);
        $this->assertEquals(12, $ci['mean2']);
        $this->assertEquals(-5, $ci['mean_difference']);
        $this->assertLessThan(0, $ci['upper_bound']); // Difference is negative
    }
    
    public function test_proportion_validates_successes(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        
        $this->calculator->forProportion(150, 100, 0.95); // More successes than total
    }
    
    public function test_proportion_validates_sample_size(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Sample size too small');
        
        $this->calculator->forProportion(2, 3, 0.95); // Too small for normal approximation
    }
    
    public function test_format_ci_for_mean(): void
    {
        $ci = [
            'mean' => 100,
            'margin_of_error' => 5,
            'lower_bound' => 95,
            'upper_bound' => 105
        ];
        
        $formatted = $this->calculator->format($ci, 0);
        
        $this->assertStringContainsString('100', $formatted);
        $this->assertStringContainsString('95', $formatted);
        $this->assertStringContainsString('105', $formatted);
    }
    
    public function test_format_ci_for_proportion(): void
    {
        $ci = [
            'proportion' => 0.5,
            'percentage' => 50,
            'margin_of_error' => 0.05,
            'lower_bound' => 0.45,
            'upper_bound' => 0.55
        ];
        
        $formatted = $this->calculator->format($ci, 0);
        
        $this->assertStringContainsString('50%', $formatted);
        $this->assertStringContainsString('45%', $formatted);
        $this->assertStringContainsString('55%', $formatted);
    }
}
