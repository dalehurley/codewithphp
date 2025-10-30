<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Prediction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class MLIntegrationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test sentiment analysis endpoint returns valid response.
     */
    public function test_sentiment_analysis_returns_valid_response(): void
    {
        $response = $this->postJson('/api/ml/sentiment', [
            'text' => 'This product is absolutely amazing! I love it!',
        ]);

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'text',
                    'sentiment',
                    'confidence',
                    'emoji',
                    'timestamp',
                ],
            ]);

        $this->assertEquals(true, $response->json('success'));
        $this->assertEquals('positive', $response->json('data.sentiment'));
    }

    /**
     * Test sentiment analysis validates input.
     */
    public function test_sentiment_analysis_validates_input(): void
    {
        // Too short
        $response = $this->postJson('/api/ml/sentiment', [
            'text' => 'Good',
        ]);

        $response->assertStatus(422);

        // Missing text
        $response = $this->postJson('/api/ml/sentiment', []);

        $response->assertStatus(422);
    }

    /**
     * Test sentiment analysis caches results.
     */
    public function test_sentiment_analysis_caches_results(): void
    {
        Cache::flush();

        $text = 'Excellent product, highly recommend to everyone!';

        // First request (cache miss)
        $response1 = $this->postJson('/api/ml/sentiment', [
            'text' => $text,
        ]);

        $response1->assertStatus(200);

        // Second request (should hit cache)
        $response2 = $this->postJson('/api/ml/sentiment', [
            'text' => $text,
        ]);

        $response2->assertStatus(200);

        // Results should be identical
        $this->assertEquals(
            $response1->json('data.sentiment'),
            $response2->json('data.sentiment')
        );

        // Check predictions were logged
        $this->assertDatabaseHas('predictions', [
            'service' => 'sentiment',
            'status' => 'success',
        ]);

        // Second prediction should be cache hit
        $cacheHit = Prediction::where('cache_hit', true)->first();
        $this->assertNotNull($cacheHit);
    }

    /**
     * Test async sentiment analysis queues job.
     */
    public function test_async_sentiment_analysis_queues_job(): void
    {
        $response = $this->postJson('/api/ml/sentiment/async', [
            'text' => 'Great product, very satisfied with purchase!',
        ]);

        $response
            ->assertStatus(202)
            ->assertJsonStructure([
                'success',
                'message',
                'job_id',
                'status_url',
            ]);
    }

    /**
     * Test health check endpoint.
     */
    public function test_health_check_returns_status(): void
    {
        $response = $this->getJson('/api/ml/health');

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'services' => [
                    'sentiment' => [
                        'model',
                        'status',
                        'loaded',
                    ],
                ],
                'overall_status',
            ]);
    }

    /**
     * Test metrics endpoint returns data.
     */
    public function test_metrics_endpoint_returns_data(): void
    {
        // Create some predictions
        Prediction::create([
            'service' => 'sentiment',
            'input_hash' => md5('test'),
            'output_data' => ['sentiment' => 'positive'],
            'latency_ms' => 100,
            'cache_hit' => false,
            'status' => 'success',
        ]);

        $response = $this->getJson('/api/ml/metrics?service=sentiment&hours=24');

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'service',
                'period_hours',
                'metrics' => [
                    'total_predictions',
                    'cache_hit_rate_percent',
                    'avg_latency_ms',
                    'error_count',
                    'error_rate_percent',
                ],
            ]);
    }

    /**
     * Test rate limiting on sentiment endpoint.
     */
    public function test_sentiment_endpoint_has_rate_limiting(): void
    {
        // Make 61 requests (exceeds 60/min limit)
        for ($i = 0; $i < 61; $i++) {
            $response = $this->postJson('/api/ml/sentiment', [
                'text' => "Test message number {$i} with enough characters",
            ]);

            if ($i < 60) {
                $this->assertTrue(in_array($response->status(), [200, 500]));
            } else {
                // 61st request should be rate limited
                $response->assertStatus(429);
            }
        }
    }
}
