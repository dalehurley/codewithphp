<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;
use DataScience\Statistics\DistributionAnalyzer;

class DistributionAnalyzerTest extends TestCase
{
    private DistributionAnalyzer $analyzer;
    
    protected function setUp(): void
    {
        $this->analyzer = new DistributionAnalyzer();
    }
    
    public function test_normal_distribution_detection_with_normal_data(): void
    {
        // Heights approximately normally distributed
        $normalData = [165, 170, 168, 172, 169, 171, 167, 173, 166, 170, 169, 171, 168, 170, 172];
        
        $result = $this->analyzer->isNormallyDistributed($normalData);
        
        $this->assertTrue($result['is_normal']);
        $this->assertEqualsWithDelta(169.3, $result['mean'], 1.0);
        $this->assertEqualsWithDelta(2.3, $result['std_dev'], 1.0);
        $this->assertLessThan(2, abs($result['skewness']));
    }
    
    public function test_z_score_calculation_accuracy(): void
    {
        $value = 85;
        $mean = 75;
        $stdDev = 10;
        
        $result = $this->analyzer->zScore($value, $mean, $stdDev);
        
        // Z-score should be (85-75)/10 = 1.0
        $this->assertEquals(1.0, $result['z_score']);
        // Percentile for z=1 should be ~84.1%
        $this->assertEqualsWithDelta(84.1, $result['percentile'], 1.0);
    }
    
    public function test_throws_exception_on_zero_standard_deviation(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Standard deviation cannot be zero');
        
        $this->analyzer->zScore(5.0, 5.0, 0.0);
    }
    
    public function test_throws_exception_on_invalid_alpha(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Alpha must be between 0 and 1');
        
        $this->analyzer->isNormallyDistributed([1, 2, 3], 1.5);
    }
    
    public function test_throws_exception_on_non_numeric_data(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('All data points must be numeric');
        
        $this->analyzer->isNormallyDistributed([1, 2, 'three']);
    }
    
    public function test_binomial_probability_coin_flips(): void
    {
        // 10 fair coin flips, 5 heads
        $result = $this->analyzer->binomialProbability(10, 5, 0.5);
        
        $this->assertEquals(5.0, $result['expected_value']);
        $this->assertEquals(2.5, $result['variance']);
        $this->assertEqualsWithDelta(0.246, $result['probability'], 0.01);
    }
    
    public function test_binomial_throws_on_invalid_parameters(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        
        $this->analyzer->binomialProbability(10, 15, 0.5); // k > n
    }
    
    public function test_poisson_probability(): void
    {
        // Average 5 events, probability of exactly 5
        $result = $this->analyzer->poissonProbability(5, 5);
        
        $this->assertEquals(5.0, $result['expected_value']);
        $this->assertEquals(5.0, $result['variance']);
        $this->assertGreaterThan(0, $result['probability']);
    }
    
    public function test_poisson_throws_on_negative_lambda(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        
        $this->analyzer->poissonProbability(-1, 5);
    }
    
    public function test_generate_normal_samples(): void
    {
        $samples = $this->analyzer->generateNormalSamples(1000, 100, 15);
        
        $this->assertCount(1000, $samples);
        
        $mean = array_sum($samples) / count($samples);
        $this->assertEqualsWithDelta(100, $mean, 5); // Should be close to 100
    }
    
    public function test_generate_normal_samples_throws_on_invalid_params(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        
        $this->analyzer->generateNormalSamples(10, 100, -5); // Negative std dev
    }
    
    public function test_normal_probability(): void
    {
        $result = $this->analyzer->normalProbability(170, 170, 10);
        
        // At the mean, percentile should be 50%
        $this->assertEqualsWithDelta(50, $result['percentile'], 1);
    }
    
    public function test_normal_probability_throws_on_zero_stddev(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        
        $this->analyzer->normalProbability(170, 170, 0);
    }
}
