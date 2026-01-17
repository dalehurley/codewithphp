# PHP-Python Integration Patterns for Data Science

## Overview

This document provides standardized patterns for integrating Python data science code with PHP applications. These patterns apply to all Python-based chapters (13-20).

## Integration Methods Comparison

| Method | Latency | Scalability | Complexity | Best For |
|--------|---------|-------------|------------|----------|
| **Command Line** | ~50-100ms | Low | Low | Quick scripts, prototypes |
| **Process Communication** | ~30-50ms | Medium | Medium | Production, error handling |
| **HTTP API** | ~20-100ms | High | High | Microservices, scale-out |
| **Message Queue** | Async | Very High | Very High | Batch jobs, async processing |

---

## Method 1: Command Line (Simple)

### Use Case
- Quick prototypes
- One-off scripts
- Development/testing

### Implementation

**Python:**
```python
# script.py
import sys
import json

data = json.loads(sys.argv[1])
result = process(data)
print(json.dumps(result))
```

**PHP:**
```php
$data = ['values' => [1, 2, 3]];
$json = escapeshellarg(json_encode($data));
$result = shell_exec("python3 script.py {$json}");
$output = json_decode($result, true);
```

### Pros & Cons

✅ **Pros:**
- Simple to implement
- No dependencies
- Good for quick tasks

❌ **Cons:**
- No error handling
- No stderr capture
- Security risks with shell escaping
- No streaming data

---

## Method 2: Process Communication (Recommended)

### Use Case
- Production environments
- Need error handling
- Large data transfer
- Most PHP-Python integrations

### Implementation

**Python:**
```python
# service.py
import sys
import json

def main():
    try:
        # Read from stdin
        input_data = json.load(sys.stdin)
        
        # Process
        result = process(input_data)
        
        # Write to stdout
        print(json.dumps({
            'status': 'success',
            'result': result
        }))
        
    except Exception as e:
        # Write error to stderr
        sys.stderr.write(f"Error: {str(e)}\n")
        sys.exit(1)

if __name__ == '__main__':
    main()
```

**PHP:**
```php
class PythonService
{
    public function call(string $script, array $data): array
    {
        $descriptors = [
            0 => ['pipe', 'r'], // stdin
            1 => ['pipe', 'w'], // stdout
            2 => ['pipe', 'w']  // stderr
        ];

        $process = proc_open("python3 {$script}", $descriptors, $pipes);

        if (!is_resource($process)) {
            throw new \RuntimeException("Failed to start Python process");
        }

        // Send data
        fwrite($pipes[0], json_encode($data));
        fclose($pipes[0]);

        // Read output
        $stdout = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);

        fclose($pipes[1]);
        fclose($pipes[2]);

        $returnCode = proc_close($process);

        if ($returnCode !== 0) {
            throw new \RuntimeException("Python error: {$stderr}");
        }

        return json_decode($stdout, true);
    }
}

// Usage
$service = new PythonService();
$result = $service->call('service.py', ['data' => $myData]);
```

### Advanced: With Timeout

```php
class PythonServiceWithTimeout
{
    public function callWithTimeout(
        string $script,
        array $data,
        int $timeoutSeconds = 30
    ): array {
        $descriptors = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w']
        ];

        $process = proc_open("python3 {$script}", $descriptors, $pipes);

        if (!is_resource($process)) {
            throw new \RuntimeException("Failed to start process");
        }

        // Set non-blocking
        stream_set_blocking($pipes[1], false);
        stream_set_blocking($pipes[2], false);

        // Send data
        fwrite($pipes[0], json_encode($data));
        fclose($pipes[0]);

        // Wait with timeout
        $startTime = time();
        $stdout = '';
        $stderr = '';

        while (true) {
            $status = proc_get_status($process);
            
            if (!$status['running']) {
                $stdout .= stream_get_contents($pipes[1]);
                $stderr .= stream_get_contents($pipes[2]);
                break;
            }

            if (time() - $startTime > $timeoutSeconds) {
                proc_terminate($process);
                throw new \RuntimeException("Process timeout after {$timeoutSeconds}s");
            }

            $stdout .= stream_get_contents($pipes[1]);
            $stderr .= stream_get_contents($pipes[2]);
            usleep(100000); // 100ms
        }

        fclose($pipes[1]);
        fclose($pipes[2]);
        proc_close($process);

        if (!empty($stderr)) {
            throw new \RuntimeException("Python error: {$stderr}");
        }

        return json_decode($stdout, true);
    }
}
```

### Pros & Cons

✅ **Pros:**
- Full error handling
- Can stream large data
- Production-ready
- Good performance

❌ **Cons:**
- More complex code
- Blocking (synchronous)
- Process overhead

---

## Method 3: HTTP API (Flask/FastAPI)

### Use Case
- Microservices architecture
- Need horizontal scaling
- Multiple clients (not just PHP)
- Stateful services (keep models in memory)

### Implementation

**Python (Flask):**
```python
# api.py
from flask import Flask, request, jsonify
import joblib

app = Flask(__name__)

# Load model once at startup
model = joblib.load('model.pkl')

@app.route('/predict', methods=['POST'])
def predict():
    try:
        data = request.get_json()
        prediction = model.predict([data['features']])
        
        return jsonify({
            'prediction': int(prediction[0]),
            'confidence': 0.95
        })
    except Exception as e:
        return jsonify({'error': str(e)}), 500

@app.route('/health', methods=['GET'])
def health():
    return jsonify({'status': 'healthy'})

if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5000)
```

**Python (FastAPI - Modern Alternative):**
```python
# api.py
from fastapi import FastAPI, HTTPException
from pydantic import BaseModel
import joblib

app = FastAPI()
model = joblib.load('model.pkl')

class PredictionRequest(BaseModel):
    features: list[float]

class PredictionResponse(BaseModel):
    prediction: int
    confidence: float

@app.post('/predict', response_model=PredictionResponse)
async def predict(request: PredictionRequest):
    try:
        prediction = model.predict([request.features])
        return PredictionResponse(
            prediction=int(prediction[0]),
            confidence=0.95
        )
    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))

@app.get('/health')
async def health():
    return {'status': 'healthy'}
```

**PHP:**
```php
class MLApiClient
{
    private string $baseUrl;
    private \GuzzleHttp\Client $client;

    public function __construct(string $baseUrl = 'http://localhost:5000')
    {
        $this->baseUrl = $baseUrl;
        $this->client = new \GuzzleHttp\Client([
            'base_uri' => $baseUrl,
            'timeout' => 10.0,
        ]);
    }

    public function predict(array $features): array
    {
        try {
            $response = $this->client->post('/predict', [
                'json' => ['features' => $features]
            ]);

            return json_decode($response->getBody(), true);
            
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            throw new \RuntimeException(
                "ML API error: " . $e->getMessage()
            );
        }
    }

    public function healthCheck(): bool
    {
        try {
            $response = $this->client->get('/health');
            $data = json_decode($response->getBody(), true);
            return $data['status'] === 'healthy';
        } catch (\Exception $e) {
            return false;
        }
    }
}

// Usage
$client = new MLApiClient();
$result = $client->predict([1.5, 2.3, 3.1, 4.2]);
echo "Prediction: {$result['prediction']}\n";
```

### Production Deployment

**Using Docker:**
```dockerfile
FROM python:3.11-slim

WORKDIR /app
COPY requirements.txt .
RUN pip install -r requirements.txt

COPY . .

CMD ["gunicorn", "--bind", "0.0.0.0:5000", "--workers", "4", "api:app"]
```

**docker-compose.yml:**
```yaml
version: '3.8'

services:
  ml-api:
    build: ./python-ml-service
    ports:
      - "5000:5000"
    environment:
      - MODEL_PATH=/models/latest.pkl
    volumes:
      - ./models:/models
    healthcheck:
      test: ["CMD", "curl", "-f", "http://localhost:5000/health"]
      interval: 30s
      timeout: 10s
      retries: 3

  php-app:
    build: ./php-app
    ports:
      - "8000:8000"
    depends_on:
      - ml-api
    environment:
      - ML_API_URL=http://ml-api:5000
```

### Pros & Cons

✅ **Pros:**
- Horizontal scaling
- Language-agnostic
- Stateful (keep models loaded)
- Async-friendly
- Load balancing
- Health checks

❌ **Cons:**
- Higher latency (~50-200ms)
- Network overhead
- Need infrastructure
- More complex deployment

---

## Method 4: Message Queue (RabbitMQ/Redis)

### Use Case
- Batch processing
- Async operations
- Decoupled systems
- Need retry logic
- High-volume processing

### Implementation

**Python Worker:**
```python
# worker.py
import pika
import json
import joblib

model = joblib.load('model.pkl')

connection = pika.BlockingConnection(
    pika.ConnectionParameters('localhost')
)
channel = connection.channel()
channel.queue_declare(queue='ml_predictions')

def callback(ch, method, properties, body):
    try:
        data = json.loads(body)
        prediction = model.predict([data['features']])
        
        result = {
            'id': data['id'],
            'prediction': int(prediction[0])
        }
        
        # Publish result
        channel.basic_publish(
            exchange='',
            routing_key='ml_results',
            body=json.dumps(result)
        )
        
        ch.basic_ack(delivery_tag=method.delivery_tag)
        
    except Exception as e:
        print(f"Error: {e}")
        ch.basic_nack(delivery_tag=method.delivery_tag, requeue=True)

channel.basic_consume(queue='ml_predictions', on_message_callback=callback)
print('Waiting for messages...')
channel.start_consuming()
```

**PHP:**
```php
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class MLQueueService
{
    private AMQPStreamConnection $connection;
    private $channel;

    public function __construct()
    {
        $this->connection = new AMQPStreamConnection(
            'localhost', 5672, 'guest', 'guest'
        );
        $this->channel = $this->connection->channel();
        $this->channel->queue_declare('ml_predictions', false, false, false, false);
    }

    public function requestPrediction(string $id, array $features): void
    {
        $data = [
            'id' => $id,
            'features' => $features
        ];

        $msg = new AMQPMessage(
            json_encode($data),
            ['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT]
        );

        $this->channel->basic_publish($msg, '', 'ml_predictions');
    }

    public function listenForResults(callable $callback): void
    {
        $this->channel->queue_declare('ml_results', false, false, false, false);

        $this->channel->basic_consume(
            'ml_results',
            '',
            false,
            true,
            false,
            false,
            function ($msg) use ($callback) {
                $data = json_decode($msg->body, true);
                $callback($data);
            }
        );

        while ($this->channel->is_consuming()) {
            $this->channel->wait();
        }
    }
}

// Usage
$service = new MLQueueService();

// Send prediction request
$service->requestPrediction('user-123', [1.5, 2.3, 3.1]);

// Listen for results (in a separate process/worker)
$service->listenForResults(function($result) {
    echo "Prediction for {$result['id']}: {$result['prediction']}\n";
});
```

### Pros & Cons

✅ **Pros:**
- Fully async
- Natural retry logic
- Scales horizontally
- Decoupled systems
- Handles spikes

❌ **Cons:**
- Most complex
- Need message broker
- Eventual consistency
- Harder to debug

---

## Security Considerations

### 1. Input Validation

```php
class SecurePythonService
{
    public function call(string $script, array $data): array
    {
        // Validate script path
        $allowedScripts = ['predict.py', 'analyze.py'];
        if (!in_array(basename($script), $allowedScripts)) {
            throw new \InvalidArgumentException("Script not allowed");
        }

        // Validate data structure
        $this->validateInput($data);

        // Call Python
        return $this->executePython($script, $data);
    }

    private function validateInput(array $data): void
    {
        // Add validation logic
        if (!isset($data['features']) || !is_array($data['features'])) {
            throw new \InvalidArgumentException("Invalid data structure");
        }
    }
}
```

### 2. Sandboxing

```bash
# Run Python in a container
docker run --rm \
  --network=none \
  --memory=512m \
  --cpus=1.0 \
  -v /path/to/script:/script:ro \
  python:3.11-slim \
  python /script/predict.py
```

### 3. Rate Limiting

```php
class RateLimitedMLService
{
    private $redis;
    private int $maxRequestsPerMinute = 60;

    public function predict(array $features): array
    {
        $key = 'ml_api_limit:' . $this->getUserId();
        $current = $this->redis->incr($key);

        if ($current === 1) {
            $this->redis->expire($key, 60);
        }

        if ($current > $this->maxRequestsPerMinute) {
            throw new \RuntimeException("Rate limit exceeded");
        }

        return $this->mlClient->predict($features);
    }
}
```

---

## Performance Optimization

### 1. Connection Pooling (HTTP API)

```php
class PooledMLClient
{
    private static ?\GuzzleHttp\Client $client = null;

    private function getClient(): \GuzzleHttp\Client
    {
        if (self::$client === null) {
            self::$client = new \GuzzleHttp\Client([
                'base_uri' => 'http://ml-api:5000',
                'timeout' => 10.0,
                'pool_size' => 10, // Connection pooling
            ]);
        }

        return self::$client;
    }
}
```

### 2. Caching

```php
class CachedMLService
{
    public function predict(array $features): array
    {
        $cacheKey = 'ml_prediction:' . md5(json_encode($features));

        // Check cache
        $cached = $this->cache->get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        // Call ML service
        $result = $this->mlClient->predict($features);

        // Cache for 1 hour
        $this->cache->set($cacheKey, $result, 3600);

        return $result;
    }
}
```

### 3. Batch Processing

```python
# Python: Batch predictions
@app.post('/predict_batch')
def predict_batch(requests: list[PredictionRequest]):
    features = [req.features for req in requests]
    predictions = model.predict(features)  # Vectorized!
    
    return [
        {'prediction': int(pred)}
        for pred in predictions
    ]
```

```php
// PHP: Send batch
$features = [
    [1.5, 2.3, 3.1],
    [2.1, 3.4, 4.2],
    [3.2, 4.5, 5.3]
];

$results = $client->predictBatch($features);  // Single API call
```

---

## Monitoring & Observability

### 1. Logging

```python
import logging

logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

@app.post('/predict')
def predict(request: PredictionRequest):
    logger.info(f"Prediction request: {request.features}")
    start_time = time.time()
    
    prediction = model.predict([request.features])
    
    duration = time.time() - start_time
    logger.info(f"Prediction completed in {duration:.3f}s")
    
    return {'prediction': int(prediction[0])}
```

### 2. Metrics

```python
from prometheus_client import Counter, Histogram

prediction_counter = Counter('ml_predictions_total', 'Total predictions')
prediction_duration = Histogram('ml_prediction_duration_seconds', 'Prediction duration')

@app.post('/predict')
def predict(request: PredictionRequest):
    with prediction_duration.time():
        prediction = model.predict([request.features])
    
    prediction_counter.inc()
    
    return {'prediction': int(prediction[0])}
```

---

## Troubleshooting Guide

### Common Issues

#### 1. Python not found

**Problem:** `proc_open()` can't find Python

**Solution:**
```php
// Use absolute path
$pythonPath = '/usr/bin/python3';
// Or find it
$pythonPath = trim(shell_exec('which python3'));
```

#### 2. Module not found

**Problem:** Python can't import libraries

**Solution:**
```php
// Use venv Python
$pythonPath = __DIR__ . '/venv/bin/python';
```

#### 3. JSON decode error

**Problem:** `json_decode()` returns null

**Solution:**
```php
$result = json_decode($output, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    throw new \RuntimeException(
        'JSON error: ' . json_last_error_msg() . "\n" .
        'Output: ' . $output
    );
}
```

#### 4. Timeout

**Problem:** Python script takes too long

**Solutions:**
1. Increase timeout
2. Use async processing
3. Optimize Python code
4. Use caching

---

## Summary

| Method | Setup Time | Best For | Complexity |
|--------|------------|----------|------------|
| Command Line | 5 min | Prototypes | ⭐ |
| Process Comm | 30 min | Production | ⭐⭐⭐ |
| HTTP API | 2 hours | Microservices | ⭐⭐⭐⭐ |
| Message Queue | 1 day | Enterprise | ⭐⭐⭐⭐⭐ |

**Recommendation for most PHP developers:**

- **Development:** Start with Process Communication (Method 2)
- **Production (Small):** HTTP API with Docker (Method 3)
- **Production (Large):** Message Queue + HTTP API (Methods 3 + 4)
