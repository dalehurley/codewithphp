<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;
use DataScience\Statistics\HypothesisTester;

class HypothesisTesterTest extends TestCase
{
    private HypothesisTester $tester;
    
    protected function setUp(): void
    {
        $this->tester = new HypothesisTester();
    }
    
    public function test_one_sample_t_test_rejects_null(): void
    {
        // Sample clearly different from population mean
        $data = [10, 11, 12, 13, 14]; // Mean = 12
        $populationMean = 5.0;
        
        $result = $this->tester->oneSampleTTest($data, $populationMean);
        
        $this->assertTrue($result['significant']);
        $this->assertLessThan(0.05, $result['p_value']);
        $this->assertEquals(12, $result['sample_mean']);
    }
    
    public function test_one_sample_t_test_fails_to_reject_null(): void
    {
        // Sample similar to population mean
        $data = [4.8, 5.2, 4.9, 5.1, 5.0];
        $populationMean = 5.0;
        
        $result = $this->tester->oneSampleTTest($data, $populationMean);
        
        $this->assertFalse($result['significant']);
        $this->assertGreaterThan(0.05, $result['p_value']);
    }
    
    public function test_two_sample_t_test_with_identical_groups(): void
    {
        $group1 = [5, 6, 7, 8, 9];
        $group2 = [5, 6, 7, 8, 9];
        
        $result = $this->tester->twoSampleTTest($group1, $group2);
        
        $this->assertFalse($result['significant']);
        $this->assertGreaterThan(0.05, $result['p_value']);
        $this->assertEqualsWithDelta(0.0, $result['mean_difference'], 0.001);
    }
    
    public function test_two_sample_t_test_with_different_groups(): void
    {
        $group1 = [10, 11, 12, 13, 14];
        $group2 = [5, 6, 7, 8, 9];
        
        $result = $this->tester->twoSampleTTest($group1, $group2);
        
        $this->assertTrue($result['significant']);
        $this->assertLessThan(0.05, $result['p_value']);
        $this->assertEquals(5, $result['mean_difference']);
    }
    
    public function test_cohens_d_small_effect(): void
    {
        $group1 = [5.0, 5.2, 5.1, 5.3, 5.2];
        $group2 = [5.3, 5.5, 5.4, 5.6, 5.5];
        
        $result = $this->tester->cohensD($group1, $group2);
        
        $this->assertLessThan(0, $result['cohens_d']); // Group 1 < Group 2
        $this->assertContains($result['effect_size'], ['negligible', 'small', 'medium', 'large']);
    }
    
    public function test_cohens_d_large_effect(): void
    {
        $group1 = [10, 11, 12, 13, 14];
        $group2 = [5, 6, 7, 8, 9];
        
        $result = $this->tester->cohensD($group1, $group2);
        
        $this->assertGreaterThan(0, $result['cohens_d']); // Group 1 > Group 2
        $this->assertEquals('large', $result['effect_size']);
    }
    
    public function test_z_test_proportion_rejects_null(): void
    {
        $result = $this->tester->zTestProportion(200, 1000, 0.10); // 20% vs expected 10%
        
        $this->assertTrue($result['significant']);
        $this->assertEquals(0.20, $result['observed_proportion']);
        $this->assertEquals(0.10, $result['expected_proportion']);
    }
    
    public function test_z_test_proportion_fails_to_reject(): void
    {
        $result = $this->tester->zTestProportion(105, 1000, 0.10); // 10.5% vs expected 10%
        
        $this->assertFalse($result['significant']);
        $this->assertGreaterThan(0.05, $result['p_value']);
    }
    
    public function test_chi_square_test_validates_expected_frequencies(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected frequencies cannot be zero');
        
        $observed = [10, 20, 30];
        $expected = [0, 20, 30];  // Zero in expected
        
        $this->tester->chiSquareTest($observed, $expected);
    }
    
    public function test_chi_square_test_with_good_fit(): void
    {
        $observed = [50, 50, 50, 50];
        $expected = [50, 50, 50, 50];
        
        $result = $this->tester->chiSquareTest($observed, $expected);
        
        $this->assertFalse($result['significant']);
        $this->assertEquals(0, $result['chi_square_statistic']);
    }
    
    public function test_chi_square_test_with_poor_fit(): void
    {
        $observed = [90, 10, 10, 10];
        $expected = [50, 50, 50, 50];
        
        $result = $this->tester->chiSquareTest($observed, $expected);
        
        $this->assertTrue($result['significant']);
        $this->assertGreaterThan(0, $result['chi_square_statistic']);
    }
    
    public function test_calculate_power_with_large_effect(): void
    {
        $result = $this->tester->calculatePower(0.8, 50); // Large effect, moderate sample
        
        $this->assertGreaterThan(0.8, $result['power']);
        $this->assertStringContainsString('Good power', $result['interpretation']);
    }
    
    public function test_calculate_power_with_small_effect(): void
    {
        $result = $this->tester->calculatePower(0.2, 20); // Small effect, small sample
        
        $this->assertLessThan(0.5, $result['power']);
        $this->assertStringContainsString('Low power', $result['interpretation']);
    }
    
    public function test_throws_exception_on_invalid_alpha(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        
        $this->tester->oneSampleTTest([1, 2, 3], 2, 1.5);
    }
    
    public function test_throws_exception_on_insufficient_data(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        
        $this->tester->oneSampleTTest([1], 2);
    }
    
    public function test_throws_exception_on_non_numeric_data(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        
        $this->tester->oneSampleTTest([1, 2, 'three'], 2);
    }
}
