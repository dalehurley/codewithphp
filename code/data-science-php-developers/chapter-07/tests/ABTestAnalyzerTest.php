<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;
use DataScience\Statistics\ABTestAnalyzer;

class ABTestAnalyzerTest extends TestCase
{
    private ABTestAnalyzer $analyzer;
    
    protected function setUp(): void
    {
        $this->analyzer = new ABTestAnalyzer();
    }
    
    public function test_ab_test_detects_significant_difference(): void
    {
        // Clear difference: 10% vs 15% conversion
        $result = $this->analyzer->analyzeConversionTest(
            controlConversions: 200,
            controlTotal: 2000,
            variantConversions: 300,
            variantTotal: 2000
        );
        
        $this->assertTrue($result['analysis']['is_significant']);
        $this->assertLessThan(0.05, $result['analysis']['p_value']);
        $this->assertGreaterThan(0, $result['analysis']['lift']);
        $this->assertStringContainsString('WINNER', $result['recommendation']);
    }
    
    public function test_ab_test_fails_to_detect_small_difference(): void
    {
        // Small difference: 10% vs 10.5% conversion
        $result = $this->analyzer->analyzeConversionTest(
            controlConversions: 200,
            controlTotal: 2000,
            variantConversions: 210,
            variantTotal: 2000
        );
        
        $this->assertFalse($result['analysis']['is_significant']);
        $this->assertGreaterThan(0.05, $result['analysis']['p_value']);
    }
    
    public function test_sample_size_calculation(): void
    {
        $result = $this->analyzer->calculateSampleSize(
            baselineRate: 0.10,
            minimumDetectableEffect: 0.20,  // 20% relative change
            alpha: 0.05,
            power: 0.80
        );
        
        $this->assertGreaterThan(0, $result['sample_per_group']);
        $this->assertEquals(
            $result['sample_per_group'] * 2,
            $result['total_sample']
        );
    }
    
    public function test_continuous_test_with_significant_difference(): void
    {
        $control = [5, 6, 7, 8, 9];
        $variant = [10, 11, 12, 13, 14];
        
        $result = $this->analyzer->analyzeContinuousTest($control, $variant);
        
        $this->assertTrue($result['analysis']['is_significant']);
        $this->assertGreaterThan(0, $result['analysis']['difference']);
    }
    
    public function test_validates_sample_sizes(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Sample sizes too small');
        
        $this->analyzer->analyzeConversionTest(1, 2, 1, 2);
    }
    
    public function test_validates_conversions_within_total(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        
        $this->analyzer->analyzeConversionTest(150, 100, 50, 100); // More conversions than total
    }
    
    public function test_validates_alpha(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        
        $this->analyzer->analyzeConversionTest(50, 100, 50, 100, 1.5);
    }
    
    public function test_validates_baseline_rate(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        
        $this->analyzer->calculateSampleSize(1.5, 0.10); // Invalid baseline > 1
    }
    
    public function test_detects_negative_lift(): void
    {
        // Variant worse than control
        $result = $this->analyzer->analyzeConversionTest(
            controlConversions: 300,
            controlTotal: 2000,
            variantConversions: 200,
            variantTotal: 2000
        );
        
        $this->assertLessThan(0, $result['analysis']['lift']);
        if ($result['analysis']['is_significant']) {
            $this->assertStringContainsString('WARNING', $result['recommendation']);
        }
    }
    
    public function test_continuous_test_validates_sample_size(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        
        $this->analyzer->analyzeContinuousTest([1], [2, 3]); // Control too small
    }
}
