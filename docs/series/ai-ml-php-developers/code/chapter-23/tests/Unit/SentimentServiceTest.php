<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\ML\SentimentAnalysisService;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class SentimentServiceTest extends TestCase
{
    private SentimentAnalysisService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new SentimentAnalysisService();
        Cache::flush();
    }

    /**
     * Test sentiment service can be instantiated.
     */
    public function test_service_can_be_instantiated(): void
    {
        $this->assertInstanceOf(SentimentAnalysisService::class, $this->service);
    }

    /**
     * Test positive sentiment detection.
     */
    public function test_detects_positive_sentiment(): void
    {
        $result = $this->service->predict(
            'This is an excellent product with amazing quality! Highly recommend!'
        );

        $this->assertEquals('positive', $result['sentiment']);
        $this->assertGreaterThan(0.5, $result['confidence']);
        $this->assertEquals('😊', $result['emoji']);
    }

    /**
     * Test negative sentiment detection.
     */
    public function test_detects_negative_sentiment(): void
    {
        $result = $this->service->predict(
            'Terrible waste of money. Poor quality and horrible experience.'
        );

        $this->assertEquals('negative', $result['sentiment']);
        $this->assertGreaterThan(0.5, $result['confidence']);
        $this->assertEquals('😞', $result['emoji']);
    }

    /**
     * Test service returns required fields.
     */
    public function test_prediction_returns_required_fields(): void
    {
        $result = $this->service->predict('Great product!');

        $this->assertArrayHasKey('text', $result);
        $this->assertArrayHasKey('sentiment', $result);
        $this->assertArrayHasKey('confidence', $result);
        $this->assertArrayHasKey('emoji', $result);
        $this->assertArrayHasKey('timestamp', $result);
    }

    /**
     * Test service handles array input.
     */
    public function test_handles_array_input(): void
    {
        $result = $this->service->predict([
            'text' => 'Fantastic service!',
        ]);

        $this->assertEquals('positive', $result['sentiment']);
    }

    /**
     * Test health check returns healthy status.
     */
    public function test_health_check_returns_healthy(): void
    {
        $health = $this->service->healthCheck();

        $this->assertEquals('sentiment', $health['model']);
        $this->assertEquals('healthy', $health['status']);
        $this->assertTrue($health['loaded']);
    }

    /**
     * Test predictions are cached.
     */
    public function test_predictions_are_cached(): void
    {
        Cache::flush();

        $text = 'Amazing product quality!';

        // First call - should compute
        $result1 = $this->service->predict($text);

        // Second call - should use cache
        $result2 = $this->service->predict($text);

        // Results should be identical
        $this->assertEquals($result1['sentiment'], $result2['sentiment']);
        $this->assertEquals($result1['confidence'], $result2['confidence']);
    }

    /**
     * Test cache can be cleared.
     */
    public function test_cache_can_be_cleared(): void
    {
        $text = 'Great value!';

        // Make prediction (cached)
        $this->service->predict($text);

        // Clear cache
        $this->service->clearCache();

        // Cache should be empty
        $this->assertNull(Cache::get('ml:sentiment:' . md5(serialize($text))));
    }

    /**
     * Test confidence calculation is reasonable.
     */
    public function test_confidence_is_reasonable(): void
    {
        $result = $this->service->predict(
            'Excellent amazing perfect wonderful fantastic!'
        );

        // Confidence should be between 0 and 1
        $this->assertGreaterThanOrEqual(0, $result['confidence']);
        $this->assertLessThanOrEqual(1, $result['confidence']);
    }
}
