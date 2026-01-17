# Chapter 01: Data Science for PHP Developers - Code Examples

This directory contains working PHP code examples from Chapter 01, demonstrating real-world data science use cases that PHP developers build every day.

## Examples

### 1. Customer Analytics Dashboard (`customer-analytics.php`)

Segment customers based on purchasing behavior using database queries and statistical analysis.

**What it demonstrates:**
- Data extraction from databases
- Customer segmentation logic
- Calculating metrics (lifetime value, order frequency)
- Identifying high-value and at-risk customers

**To run:**
```bash
php customer-analytics.php
```

**Requirements:**
- Laravel/Eloquent or adapt SQL to PDO
- Database with `orders` table containing customer data

---

### 2. A/B Test Analysis (`ab-test-analysis.php`)

Analyze A/B test results to determine if a variant (like a new button color) improves conversions.

**What it demonstrates:**
- Experimental data collection
- Conversion rate calculation
- Lift measurement
- Basic statistical significance assessment

**To run:**
```bash
php ab-test-analysis.php
```

**Requirements:**
- Database with `events` table tracking variants and conversions
- Event data with `variant`, `event_type` fields

---

### 3. Article Recommender (`article-recommender.php`)

Content-based recommendation engine that suggests related articles based on tag overlap.

**What it demonstrates:**
- Collaborative filtering concepts
- Similarity scoring using shared attributes
- Ranking algorithms
- Production-ready class structure

**To run:**
```php
require 'article-recommender.php';

$recommender = new ArticleRecommender();
$similar = $recommender->getSimilarWithScores(articleId: 42, limit: 5);

foreach ($similar as $article) {
    echo "{$article->title} (Score: {$article->similarity_score})\n";
}
```

**Requirements:**
- Database with `articles` and `article_tags` tables
- Articles with associated tags

---

### 4. Log Anomaly Detector (`log-anomaly-detector.php`)

Detect unusual patterns in application logs using statistical analysis (3-sigma rule).

**What it demonstrates:**
- Time series analysis
- Statistical anomaly detection
- Mean and standard deviation calculations
- Severity classification

**To run:**
```php
require 'log-anomaly-detector.php';

$detector = new LogAnomalyDetector();
$anomalies = $detector->detectSpikes('error', thresholdMultiplier: 3, daysBack: 7);

foreach ($anomalies as $anomaly) {
    echo "Spike at {$anomaly['hour']}: {$anomaly['count']} errors\n";
    echo "Severity: {$anomaly['severity']}\n\n";
}
```

**Requirements:**
- Database with `logs` table containing timestamped log entries
- Sufficient historical data (at least 7 days recommended)

---

### 5. Churn Predictor (`churn-predictor.php`)

Predict which customers are at risk of churning using feature engineering and risk scoring.

**What it demonstrates:**
- Feature engineering (extracting meaningful signals from raw data)
- Rule-based prediction (basis for ML models)
- Risk scoring and classification
- Actionable recommendations

**To run:**
```php
require 'churn-predictor.php';

$predictor = new ChurnPredictor();
$risk = $predictor->calculateChurnRisk(customerId: 12345);

echo "Risk Level: {$risk['risk_level']} ({$risk['risk_score']}/100)\n";
foreach ($risk['recommendations'] as $action) {
    echo "- {$action}\n";
}
```

**Requirements:**
- Database with `customers` and `tickets` tables
- Customer activity data (last_login_at, created_at, etc.)

---

## Database Setup

These examples assume you have Laravel/Eloquent configured. To adapt for vanilla PHP with PDO:

```php
// Replace Eloquent queries:
$results = DB::table('table_name')->where(...)->get();

// With PDO:
$stmt = $pdo->prepare("SELECT * FROM table_name WHERE ...");
$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_OBJ);
```

## Common Patterns

All examples follow these data science patterns:

1. **Data Collection**: Query databases or APIs
2. **Data Transformation**: Process and aggregate raw data
3. **Analysis/Modeling**: Apply statistical methods or ML algorithms
4. **Action/Visualization**: Present insights or trigger interventions

## Production Considerations

These examples are educational and simplified. For production use, consider:

- **Error handling**: Add try-catch blocks and validation
- **Caching**: Store computed results (Redis, Memcached)
- **Queue processing**: Run expensive analysis asynchronously
- **Logging**: Track model performance and data quality
- **Testing**: Write unit tests for business logic
- **Scalability**: Use streaming for large datasets (see Chapter 06)

## Next Steps

- Chapter 02: Set up a complete data science environment
- Chapter 03: Learn advanced data collection techniques
- Chapter 04: Master data cleaning and preprocessing
- Chapter 06: Handle large datasets efficiently in PHP

## Related Resources

- [Chapter 01 Full Content](/series/data-science-php-developers/chapters/01-what-data-science-is-and-why-it-matters)
- [PHP Basics Series](/series/php-basics/) - PHP fundamentals
- [AI/ML for PHP Developers](/series/ai-ml-php-developers/) - Deep dive into ML

---

**Note**: These examples use Laravel/Eloquent syntax for clarity. All concepts apply to vanilla PHP with PDO or other frameworks (Symfony, CodeIgniter, etc.).
