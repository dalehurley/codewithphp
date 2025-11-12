# Chapter 22: Building a Recommendation Engine in PHP

Complete, working code examples for building recommendation systems using collaborative filtering.

## Prerequisites

- PHP 8.4+
- Composer
- Rubix ML library (optional, for example 08)

## Setup

```bash
# Navigate to this directory
cd docs/series/ai-ml-php-developers/code/chapter-22

# Install dependencies (for Rubix ML examples)
composer install

# Verify PHP version
php --version  # Should be 8.4+
```

## Files Overview

### Core Examples

- `01-load-ratings-dataset.php` — Load and inspect movie ratings dataset
- `02-user-similarity.php` — Calculate cosine and Pearson similarity between users
- `03-collaborative-filtering-scratch.php` — Complete user-based CF implementation from scratch
- `04-predict-ratings.php` — Rating prediction and accuracy testing
- `05-generate-recommendations.php` — Generate personalized recommendations for users
- `06-evaluation-metrics.php` — Comprehensive evaluation (RMSE, MAE, Precision@K, Recall@K)
- `07-item-based-filtering.php` — Item-based collaborative filtering approach
- `08-rubixml-recommender.php` — Using Rubix ML KNNRegressor for recommendations
- `09-cold-start-handling.php` — Strategies for new users/items
- `10-matrix-operations.php` — Efficient matrix operations and sparse data handling
- `11-model-persistence.php` — Save and load trained models with caching
- `12-production-recommender.php` — Complete production-ready recommender class
- `13-batch-recommendations.php` — Batch processing for multiple users
- `14-hybrid-recommender.php` — Hybrid approach combining CF + content-based

### Data Files

- `data/movie_ratings.csv` — Training ratings (100 users × 50 movies, ~1600 ratings)
- `data/test_ratings.csv` — Test set for evaluation (~400 ratings)
- `data/movies.csv` — Movie metadata (id, title, genre, year)

### Utility Files

- `generate-dataset.php` — Script to regenerate the synthetic dataset with patterns
- `composer.json` — Project dependencies

## Running the Examples

### Quick Start

```bash
# Load and explore the dataset
php 01-load-ratings-dataset.php

# Calculate user similarity
php 02-user-similarity.php

# Build complete recommender from scratch
php 03-collaborative-filtering-scratch.php

# Generate recommendations
php 05-generate-recommendations.php
```

### Understanding the Algorithm

```bash
# Compare similarity metrics
php 02-user-similarity.php

# Predict ratings with evaluation
php 04-predict-ratings.php

# Comprehensive metrics
php 06-evaluation-metrics.php
```

### Advanced Techniques

```bash
# Item-based vs user-based comparison
php 07-item-based-filtering.php

# Handle cold start problems
php 09-cold-start-handling.php

# Matrix operations and optimization
php 10-matrix-operations.php

# Hybrid recommendation approach
php 14-hybrid-recommender.php
```

### Production Examples

```bash
# Model persistence and caching
php 11-model-persistence.php

# Production-ready recommender
php 12-production-recommender.php

# Batch processing
php 13-batch-recommendations.php
```

### Using ML Libraries

```bash
# Rubix ML integration
php 08-rubixml-recommender.php
```

## Expected Performance

With the provided dataset (1,600 training ratings):

| Approach      | Training Time | Prediction Time | MAE   | RMSE  |
| ------------- | ------------- | --------------- | ----- | ----- |
| User-Based CF | N/A           | ~10-50ms        | ~0.65 | ~0.85 |
| Item-Based CF | ~2-5s         | ~5-10ms         | ~0.70 | ~0.90 |
| With Caching  | ~2-5s         | ~1-2ms          | ~0.65 | ~0.85 |
| Rubix ML KNN  | ~1-2s         | ~20-40ms        | ~0.75 | ~0.95 |
| Hybrid        | N/A           | ~15-60ms        | ~0.60 | ~0.80 |

## Dataset Format

`data/movie_ratings.csv`:

```csv
user_id,movie_id,rating
1,1,4.5
1,5,5.0
2,3,3.5
```

- **Training set**: ~1,600 ratings (80% split)
- **Test set**: ~400 ratings (20% split)
- **Sparsity**: ~40% (realistic for recommendation systems)
- **Rating scale**: 1.0 to 5.0 (half-star increments)

`data/movies.csv`:

```csv
movie_id,title,genre,year
1,The Matrix Revolution,sci-fi,1999
2,Interstellar Journey,sci-fi,2014
```

- **Movies**: 50 titles
- **Genres**: sci-fi, comedy, drama, action, horror
- **Years**: 1972-2018

## Common Issues

### "Fatal error: Class not found"

**Solution**: Install dependencies

```bash
composer install
```

### "Dataset file not found"

**Solution**: Run from the correct directory

```bash
cd docs/series/ai-ml-php-developers/code/chapter-22
php 01-load-ratings-dataset.php
```

### PHP 8.4 fgetcsv() deprecation warnings

**Symptom**: Many `Deprecated: fgetcsv(): the $escape parameter must be provided` warnings

**Solution**: This is a PHP 8.4 deprecation warning (harmless). The code still works correctly. To suppress warnings, update `fgetcsv()` calls:

```php
// Old (PHP 8.3 and earlier)
fgetcsv($file);

// New (PHP 8.4+)
fgetcsv($file, 0, ',', '"', '\\');
```

Examples have been updated where needed. These warnings don't affect functionality.

### "Memory exhausted"

**Solution**: Increase PHP memory limit

```php
ini_set('memory_limit', '512M');
```

Or in php.ini:

```ini
memory_limit = 512M
```

### Slow similarity computations

**Solutions**:

- Use caching (example 11)
- Reduce k neighbors parameter
- Pre-compute similarities offline
- Use item-based instead of user-based (better for more users than items)

### Missing test_ratings.csv

**Solution**: Regenerate dataset

```bash
php generate-dataset.php
```

## Exercises

Try these extensions to deepen your understanding:

1. **Tune Parameters**: Experiment with different k values (5, 10, 20, 50)
2. **Different Metrics**: Implement Manhattan distance or Jaccard similarity
3. **Temporal Dynamics**: Add timestamp weighting for recent ratings
4. **Context-Aware**: Incorporate user demographics or item attributes
5. **Matrix Factorization**: Implement SVD or NMF decomposition
6. **Implicit Feedback**: Handle view counts instead of explicit ratings
7. **API Integration**: Build a REST API endpoint for recommendations
8. **A/B Testing**: Implement framework to test different algorithms

## Learning Path

Recommended order for working through the examples:

1. **Understand the data** (01-load-ratings-dataset.php)
2. **Learn similarity** (02-user-similarity.php)
3. **Build from scratch** (03-collaborative-filtering-scratch.php)
4. **Test predictions** (04-predict-ratings.php)
5. **Generate recommendations** (05-generate-recommendations.php)
6. **Evaluate quality** (06-evaluation-metrics.php)
7. **Compare approaches** (07-item-based-filtering.php, 08-rubixml-recommender.php)
8. **Handle edge cases** (09-cold-start-handling.php)
9. **Optimize performance** (10-matrix-operations.php, 11-model-persistence.php)
10. **Production deployment** (12-production-recommender.php, 13-batch-recommendations.php)
11. **Advanced techniques** (14-hybrid-recommender.php)

## Key Concepts

### Collaborative Filtering

- **User-based**: Find similar users, recommend items they liked
- **Item-based**: Find similar items to what user liked
- **Similarity metrics**: Cosine, Pearson correlation
- **Prediction**: Weighted average of similar users' ratings

### Cold Start Problem

- **Complete cold start**: New user with no ratings → use popularity
- **Partial cold start**: Few ratings → blend genre-based + popular
- **Warm users**: Sufficient ratings → use full collaborative filtering

### Evaluation Metrics

- **MAE/RMSE**: Rating prediction accuracy (lower is better)
- **Precision@K**: Fraction of recommended items that are relevant
- **Recall@K**: Fraction of relevant items that are recommended
- **F1-Score**: Harmonic mean of precision and recall
- **Coverage**: Percentage of catalog that can be recommended
- **Diversity**: Variety of genres in recommendations

### Production Considerations

- **Caching**: Pre-compute similarities for fast predictions
- **Batch processing**: Generate recommendations offline
- **Incremental updates**: Update model with new ratings
- **A/B testing**: Compare algorithm performance
- **Monitoring**: Track prediction quality over time

## Further Resources

- [Chapter 22 Tutorial](../../chapters/22-building-a-recommendation-engine-in-php.md)
- [Chapter 21: Recommender Systems Theory](../../chapters/21-recommender-systems-theory-and-use-cases.md)
- [Rubix ML Documentation](https://docs.rubixml.com/)
- [PHP-ML Documentation](https://php-ml.readthedocs.io/)
- [Collaborative Filtering - Wikipedia](https://en.wikipedia.org/wiki/Collaborative_filtering)

## Troubleshooting Performance

### Slow Recommendations

1. **Enable caching**: Use example 11 (model persistence)
2. **Reduce k**: Lower k_neighbors parameter (try 5 instead of 10)
3. **Use item-based**: Better scaling for user-heavy datasets
4. **Batch processing**: Pre-compute recommendations offline (example 13)

### Memory Issues

1. **Sparse matrices**: Only store non-zero ratings
2. **Limit dataset**: Process subset of users/items
3. **Incremental processing**: Process in batches
4. **External storage**: Store similarity matrix on disk

### Poor Accuracy

1. **More data**: Collect more ratings per user
2. **Better similarity**: Try Pearson instead of cosine
3. **Hybrid approach**: Combine with content-based (example 14)
4. **Tune parameters**: Experiment with k and similarity thresholds
5. **Handle bias**: Mean-center ratings (example 10)

## License

Educational code samples for Code with PHP tutorial series.

## Contributing

Found a bug or want to improve an example? See the main repository for contribution guidelines.
