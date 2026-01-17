<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;
use DataScience\Statistics\ANOVAAnalyzer;

class ANOVAAnalyzerTest extends TestCase
{
    private ANOVAAnalyzer $anova;
    
    protected function setUp(): void
    {
        $this->anova = new ANOVAAnalyzer();
    }
    
    public function test_one_way_anova_detects_significant_difference(): void
    {
        // Three clearly different groups
        $groups = [
            [5, 6, 7, 8, 9],
            [10, 11, 12, 13, 14],
            [15, 16, 17, 18, 19],
        ];
        
        $result = $this->anova->oneWayANOVA($groups);
        
        $this->assertTrue($result['significant']);
        $this->assertLessThan(0.05, $result['p_value']);
        $this->assertGreaterThan(0, $result['F_statistic']);
    }
    
    public function test_one_way_anova_fails_to_detect_no_difference(): void
    {
        // Three identical groups
        $groups = [
            [5, 6, 7, 8, 9],
            [5, 6, 7, 8, 9],
            [5, 6, 7, 8, 9],
        ];
        
        $result = $this->anova->oneWayANOVA($groups);
        
        $this->assertFalse($result['significant']);
        $this->assertEqualsWithDelta(0, $result['F_statistic'], 0.01);
    }
    
    public function test_anova_calculates_sum_of_squares(): void
    {
        $groups = [
            [1, 2, 3],
            [4, 5, 6],
            [7, 8, 9],
        ];
        
        $result = $this->anova->oneWayANOVA($groups);
        
        $this->assertGreaterThan(0, $result['ss_between']);
        $this->assertGreaterThan(0, $result['ss_within']);
        $this->assertEquals(
            $result['ss_between'] / $result['df_between'],
            $result['ms_between']
        );
    }
    
    public function test_eta_squared_calculation(): void
    {
        $groups = [
            [1, 2, 3],
            [4, 5, 6],
            [7, 8, 9],
        ];
        
        $result = $this->anova->oneWayANOVA($groups);
        $etaSquared = $this->anova->calculateEtaSquared($result['ss_between'], $result['ss_within']);
        
        $this->assertGreaterThanOrEqual(0, $etaSquared['eta_squared']);
        $this->assertLessThanOrEqual(1, $etaSquared['eta_squared']);
        $this->assertContains($etaSquared['effect_size'], ['negligible', 'small', 'medium', 'large']);
    }
    
    public function test_validates_minimum_groups(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        
        $this->anova->oneWayANOVA([[1, 2, 3]]); // Only 1 group
    }
    
    public function test_validates_group_sample_size(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        
        $this->anova->oneWayANOVA([
            [1, 2, 3],
            [4]  // Too small
        ]);
    }
    
    public function test_validates_alpha(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        
        $this->anova->oneWayANOVA([[1, 2, 3], [4, 5, 6]], 1.5);
    }
    
    public function test_validates_numeric_data(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        
        $this->anova->oneWayANOVA([
            [1, 2, 3],
            [4, 'five', 6]
        ]);
    }
}
