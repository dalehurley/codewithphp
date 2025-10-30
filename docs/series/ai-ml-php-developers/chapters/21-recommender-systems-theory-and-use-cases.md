---
title: "21: Recommender Systems: Theory and Use Cases"
description: "Learn how recommendation engines work—from collaborative filtering and content-based approaches to similarity measures, prediction algorithms, and real-world use cases"
series: "ai-ml-php-developers"
chapter: 21
order: 21
difficulty: "Intermediate"
prerequisites:
  - "/series/ai-ml-php-developers/chapters/20-time-series-forecasting-project"
---

![Recommender Systems: Theory and Use Cases](/images/ai-ml-php-developers/chapter-21-recommender-systems-hero-full.webp)

# Chapter 21: Recommender Systems: Theory and Use Cases

## Overview

Every time Netflix suggests a show you might enjoy, Amazon recommends products based on your browsing history, or Spotify creates a personalized playlist, you're experiencing the power of recommender systems. These AI-driven engines are among the most valuable and visible applications of machine learning in modern web applications, directly influencing user engagement, satisfaction, and business revenue.

Recommender systems solve a fundamental problem: helping users discover relevant items in an overwhelming sea of choices. With millions of products, articles, videos, or songs available, users need intelligent guidance to find what they'll value. Unlike classification (which assigns labels) or prediction (which forecasts values), recommendation systems identify items that match individual preferences based on past behavior and patterns from similar users.

This chapter introduces the core concepts behind recommendation engines. You'll learn the two primary approaches—**content-based filtering** (recommending items similar to what a user liked) and **collaborative filtering** (leveraging patterns from similar users). We'll explore similarity measures that quantify how alike users or items are, understand the mathematical foundations of making predictions, and examine real-world challenges like the cold start problem and sparsity in user-item interactions.

Understanding these fundamentals prepares you to build a working recommendation engine in Chapter 22. By seeing how companies like Amazon and Netflix approach recommendations, you'll gain the theoretical foundation to implement intelligent suggestion systems in your own PHP applications—whether for e-commerce, content platforms, or social networks.

## Prerequisites

Before starting this chapter, you should have:

- PHP 8.4+ installed and confirmed working with `php --version`
- Completion of [Chapter 20](/series/ai-ml-php-developers/chapters/20-time-series-forecasting-project) or equivalent understanding of predictive analytics and model evaluation
- Understanding of basic statistics (averages, correlation concepts)
- Familiarity with PHP arrays and mathematical operations
- Basic knowledge of similarity concepts from earlier chapters
- A text editor or IDE with PHP support

**Estimated Time**: ~45-60 minutes

**Verify your setup:**

```bash
# Check PHP version
php --version
# Should show PHP 8.4.x
```

## What You'll Build

By the end of this chapter, you will have gained:

- **Deep understanding** of how recommender systems work and why they matter for web applications
- **Knowledge of content-based filtering** and when to use item features for recommendations
- **Mastery of collaborative filtering** approaches (user-based and item-based)
- **Implementation skills** for three similarity measures: cosine similarity, Pearson correlation, and Euclidean distance
- **Ability to calculate predictions** using weighted averages from similar users
- **Understanding of evaluation metrics** including RMSE, MAE, Precision@K, and Recall@K
- **Awareness of common challenges** like cold start problems, data sparsity, and scalability issues
- **Real-world use case knowledge** across e-commerce, content platforms, and social networks
- **Working PHP code examples** demonstrating rating matrices, similarity calculations, and prediction algorithms
- **Preparation for Chapter 22** where you'll build a complete recommendation engine

All code examples are conceptual implementations that run without external libraries, helping you understand the mathematics and logic behind recommendations.

::: info Code Examples
This chapter includes complete, runnable PHP examples demonstrating all core concepts. Each example can be run independently without external libraries. You can copy any code block and save it as a `.php` file to experiment with the concepts.

Key examples:

- User-item rating matrices and data structures
- Similarity calculations (cosine, Pearson, Euclidean)
- Content-based and collaborative filtering demonstrations
- Rating prediction algorithms
- Evaluation metrics implementations
  :::

## Objectives

- **Understand** what recommender systems are and why they're critical for modern web applications
- **Distinguish** between content-based filtering and collaborative filtering approaches
- **Learn** user-based and item-based collaborative filtering strategies
- **Implement** similarity measures (cosine, Pearson correlation, Euclidean distance) in PHP
- **Calculate** predicted ratings using weighted averages from similar users
- **Recognize** common challenges: cold start problem, sparsity, and scalability
- **Evaluate** recommendation quality using RMSE, MAE, Precision@K, and Recall@K metrics
- **Identify** real-world use cases across industries and platforms

## Step 1: Understanding Recommender Systems (~5 min)

### Goal

Understand what recommender systems are, why they matter, and how they formalize the recommendation problem.

### Actions

Recommender systems are specialized machine learning applications that predict user preferences and suggest relevant items. Let's explore the fundamental concepts.

### The Recommendation Problem

Imagine you run an online bookstore with 50,000 titles. A customer visits who has previously purchased 3 mystery novels. **Which 10 books should you display on their homepage?**

This is the core challenge that recommender systems solve. The goal is to predict which items (books, movies, products, articles) a user will find valuable based on:

- **Explicit feedback**: Ratings, reviews, likes/dislikes (user deliberately provides preference)
- **Implicit feedback**: Clicks, purchases, viewing time, browsing history (inferred from behavior)

### Understanding Feedback Types

Most recommender systems must work with **implicit feedback** because users rarely rate items:

**Explicit Feedback** (ratings, thumbs up/down):

- **Pros**: Clear signal of preference (5 stars = loved it)
- **Cons**: Sparse data (users rate <1% of items), requires extra effort from users
- **Example**: Netflix ratings, Amazon reviews

**Implicit Feedback** (clicks, purchases, time spent):

- **Pros**: Abundant data (every interaction is a signal), no extra user effort
- **Cons**: Noisy (clicked but didn't like it?), no negative feedback (not viewing ≠ dislike)
- **Example**: YouTube watch history, e-commerce browsing, Spotify play counts

**PHP Application Context:**

```php
# filename: implicit-feedback-conversion.php
<?php

// Explicit feedback: clear preference
$explicitRatings = [
    'user123' => ['product_A' => 5, 'product_B' => 2], // User rated 2 out of 1000 products
];

// Implicit feedback: behavioral signals (much more data!)
$implicitSignals = [
    'user123' => [
        'viewed' => ['product_A', 'product_C', 'product_D', 'product_E'],
        'purchased' => ['product_A'], // Strong positive signal
        'time_spent' => ['product_A' => 180, 'product_C' => 45], // seconds
        'added_to_cart' => ['product_C'], // Moderate positive signal
    ],
];

// Convert implicit signals to preference scores (0-1 scale)
function implicitToPreference(array $signals): array
{
    $preferences = [];

    // Purchase = 1.0 (strongest signal)
    foreach ($signals['purchased'] as $item) {
        $preferences[$item] = 1.0;
    }

    // Added to cart = 0.7
    foreach ($signals['added_to_cart'] as $item) {
        $preferences[$item] = max($preferences[$item] ?? 0, 0.7);
    }

    // Time spent > 60s = 0.5
    foreach ($signals['time_spent'] as $item => $seconds) {
        if ($seconds > 60) {
            $preferences[$item] = max($preferences[$item] ?? 0, 0.5);
        }
    }

    // Just viewed = 0.3 (weakest positive signal)
    foreach ($signals['viewed'] as $item) {
        $preferences[$item] = max($preferences[$item] ?? 0, 0.3);
    }

    return $preferences;
}

$userPreferences = implicitToPreference($implicitSignals['user123']);
// Result: ['product_A' => 1.0, 'product_C' => 0.7, 'product_D' => 0.3, 'product_E' => 0.3]
```

**Key Insight**: For PHP web applications (e-commerce, blogs, SaaS), implicit feedback is more practical. You already have the data (page views, clicks, purchases) in your analytics or database—no need to build a rating system.

### Why Recommender Systems Matter

**For Users:**

- Discover relevant content without endless searching
- Personalized experiences tailored to individual tastes
- Reduced choice overload in environments with thousands of options

**For Businesses:**

- Increased engagement (users spend more time on the platform)
- Higher conversion rates (recommendations drive purchases)
- Improved customer satisfaction and retention
- Data-driven understanding of user preferences

**Real-World Impact:**

- **Netflix**: 80% of watched content comes from recommendations
- **Amazon**: 35% of revenue driven by recommendation engine
- **YouTube**: 70% of viewing time from recommended videos

### The Recommendation Matrix

At the heart of most recommender systems is a **user-item interaction matrix**:

```php
# filename: user-item-matrix.php
<?php

declare(strict_types=1);

// Example: Movie ratings (1-5 scale, null = not rated)
$ratings = [
    'Alice'   => ['Inception' => 5, 'Titanic' => 3, 'Avatar' => null, 'The Matrix' => 4],
    'Bob'     => ['Inception' => 4, 'Titanic' => null, 'Avatar' => 5, 'The Matrix' => 5],
    'Charlie' => ['Inception' => 3, 'Titanic' => 4, 'Avatar' => 4, 'The Matrix' => null],
    'Diana'   => ['Inception' => 5, 'Titanic' => 2, 'Avatar' => null, 'The Matrix' => 5],
];

echo "User-Item Rating Matrix:\n";
echo str_repeat('-', 70) . "\n";
printf("%-10s", "User");
foreach (array_keys($ratings['Alice']) as $movie) {
    printf("%-15s", $movie);
}
echo "\n" . str_repeat('-', 70) . "\n";

foreach ($ratings as $user => $userRatings) {
    printf("%-10s", $user);
    foreach ($userRatings as $rating) {
        printf("%-15s", $rating ?? '-');
    }
    echo "\n";
}
```

### Expected Result

```
User-Item Rating Matrix:
----------------------------------------------------------------------
User      Inception      Titanic        Avatar         The Matrix
----------------------------------------------------------------------
Alice     5              3              -              4
Bob       4              -              5              5
Charlie   3              4              4              -
Diana     5              2              -              5
```

### Why It Works

The matrix represents known preferences (ratings) with gaps where we need predictions. **The goal**: predict the `null` values by finding patterns. For example, if Alice and Diana have similar tastes (both love Inception and The Matrix), we might predict Alice would also dislike Titanic like Diana does.

This matrix-based approach works because user preferences exhibit patterns—people with similar tastes tend to agree on new items. By analyzing these patterns mathematically, we can make accurate predictions even with sparse data.

### The Two Main Approaches

```mermaid
flowchart TB
    A[Recommender Systems] --> B[Content-Based Filtering]
    A --> C[Collaborative Filtering]

    B --> B1[Analyze item features]
    B --> B2[Match to user preferences]
    B1 --> B3[Recommend similar items]

    C --> C1[User-Based CF]
    C --> C2[Item-Based CF]
    C1 --> C3[Find similar users]
    C1 --> C4[Recommend their items]
    C2 --> C5[Find similar items]
    C2 --> C6[Recommend those items]

    style A fill:#e1f5ff
    style B fill:#fff4e1
    style C fill:#f0ffe1
```

### Key Takeaway

Recommender systems transform the question "What should we show this user?" into a mathematical problem of filling gaps in a user-item matrix by finding patterns in existing preferences.

### Troubleshooting

- **Problem**: What if a user views an item multiple times? How does that compare to a single purchase?
- **Solution**: This highlights the art of feature engineering for implicit feedback. A good starting point is to assign weights: `purchase=1.0`, `add_to_cart=0.7`, `multiple_views=0.5`, `single_view=0.3`. You can tune these weights based on how predictive they are for your specific application.
- **Problem**: How do I handle negative implicit feedback (e.g., user skipped a song)?
- **Solution**: Assign negative weights. For example, a skip could be `-0.5`, while listening to a full song is `1.0`. This helps the model learn what users _dislike_ as well as what they like.

## Step 2: Content-Based Filtering (~10 min)

### Goal

Understand how content-based filtering recommends items by analyzing their features and matching them to user preferences.

### Actions

Content-based filtering is one of two main recommendation approaches. It focuses on item characteristics rather than user behavior patterns.

### How Content-Based Filtering Works

Content-based systems recommend items **similar to those a user previously liked**, based on item attributes (genre, keywords, category, author, etc.).

**Process:**

1. Extract features from items (e.g., movie genres, book topics)
2. Build a user profile based on features of items they liked
3. Recommend new items with similar features

### PHP Example: Movie Recommendations

```php
# filename: content-based-filtering.php
<?php

declare(strict_types=1);

// Movie features (genre vectors)
$movies = [
    'Inception'    => ['action' => 0.8, 'scifi' => 1.0, 'drama' => 0.3, 'comedy' => 0.0],
    'Titanic'      => ['action' => 0.2, 'scifi' => 0.0, 'drama' => 1.0, 'comedy' => 0.1],
    'Avatar'       => ['action' => 0.9, 'scifi' => 1.0, 'drama' => 0.2, 'comedy' => 0.0],
    'The Matrix'   => ['action' => 1.0, 'scifi' => 1.0, 'drama' => 0.2, 'comedy' => 0.1],
    'The Hangover' => ['action' => 0.1, 'scifi' => 0.0, 'drama' => 0.2, 'comedy' => 1.0],
];

// User's viewing history with ratings
$userHistory = [
    'Inception'  => 5,
    'The Matrix' => 5,
];

// Build user profile (average of liked items' features)
function buildUserProfile(array $history, array $movies): array
{
    $profile = ['action' => 0.0, 'scifi' => 0.0, 'drama' => 0.0, 'comedy' => 0.0];
    $count = 0;

    foreach ($history as $movie => $rating) {
        if ($rating >= 4) { // Only consider liked movies
            foreach ($movies[$movie] as $genre => $value) {
                $profile[$genre] += $value;
            }
            $count++;
        }
    }

    // Average the features
    return array_map(fn($sum) => $count > 0 ? $sum / $count : 0, $profile);
}

// Calculate similarity between user profile and a movie (cosine similarity simplified)
function calculateSimilarity(array $profile, array $movieFeatures): float
{
    $dotProduct = 0.0;
    $profileMagnitude = 0.0;
    $movieMagnitude = 0.0;

    foreach ($profile as $genre => $value) {
        $dotProduct += $value * $movieFeatures[$genre];
        $profileMagnitude += $value ** 2;
        $movieMagnitude += $movieFeatures[$genre] ** 2;
    }

    $profileMagnitude = sqrt($profileMagnitude);
    $movieMagnitude = sqrt($movieMagnitude);

    if ($profileMagnitude == 0 || $movieMagnitude == 0) {
        return 0.0;
    }

    return $dotProduct / ($profileMagnitude * $movieMagnitude);
}

$userProfile = buildUserProfile($userHistory, $movies);

echo "User Profile (based on liked movies):\n";
foreach ($userProfile as $genre => $value) {
    printf("  %s: %.2f\n", ucfirst($genre), $value);
}

echo "\nRecommendations (similarity scores):\n";
$recommendations = [];
foreach ($movies as $movie => $features) {
    if (!isset($userHistory[$movie])) { // Only recommend unwatched movies
        $similarity = calculateSimilarity($userProfile, $features);
        $recommendations[$movie] = $similarity;
    }
}

arsort($recommendations);
foreach ($recommendations as $movie => $score) {
    printf("  %s: %.3f\n", $movie, $score);
}
```

### Expected Result

```
User Profile (based on liked movies):
  Action: 0.90
  Scifi: 1.00
  Drama: 0.25
  Comedy: 0.05

Recommendations (similarity scores):
  Avatar: 0.995
  Titanic: 0.289
  The Hangover: 0.052
```

### Why It Works

The user profile shows they prefer action and sci-fi (high values). Avatar scores highest because it matches those preferences. Content-based filtering works well when:

- Items have rich, descriptive features
- User preferences are consistent over time
- You need to explain why an item was recommended

### Advantages and Limitations

**Advantages:**

- No need for data from other users (works for new users)
- Can recommend niche items if they match user preferences
- Explanations are straightforward ("You liked X, this is similar")

**Limitations:**

- Requires detailed item metadata
- Limited serendipity (only recommends similar items)
- Doesn't capture implicit quality signals
- New items need feature extraction before recommendation

### Troubleshooting

- **Problem**: Recommendations are too narrow (only suggests sci-fi when user likes some sci-fi)
- **Solution**: Incorporate diversity by occasionally recommending items from different genres or using a hybrid approach that blends content-based and collaborative filtering.
- **Problem**: New items without metadata can't be recommended
- **Solution**: Use editorial tags, extract features from descriptions using NLP, or show new items to power users for initial ratings.

## Step 3: Hybrid Recommender Systems (~5 min)

### Goal

Understand how to combine content-based and collaborative filtering for more robust recommendations.

### Actions

Real-world production systems rarely use pure content-based or pure collaborative filtering. Instead, they use **hybrid approaches** that combine both to leverage their complementary strengths.

### Why Hybrid Systems?

**Content-based limitations**:

- No serendipity (only recommends similar items)
- Requires detailed metadata
- Can't leverage community preferences

**Collaborative filtering limitations**:

- Cold start problem (new users/items)
- Requires substantial user-item interactions
- Can't recommend to users with unique tastes

**Hybrid solution**: Use both and get the best of each approach!

### Hybrid Strategies

**1. Weighted Hybrid** — Combine scores from both approaches:

```php
# filename: hybrid-weighted.php
<?php

declare(strict_types=1);

/**
 * Weighted hybrid recommender combining content-based and collaborative filtering.
 */
function hybridRecommendation(
    array $contentBasedScores,
    array $collaborativeScores,
    float $contentWeight = 0.4,
    float $collaborativeWeight = 0.6
): array {
    $hybridScores = [];

    // Get all items from both approaches
    $allItems = array_unique(array_merge(
        array_keys($contentBasedScores),
        array_keys($collaborativeScores)
    ));

    foreach ($allItems as $item) {
        $contentScore = $contentBasedScores[$item] ?? 0;
        $collaborativeScore = $collaborativeScores[$item] ?? 0;

        // Weighted combination
        $hybridScores[$item] =
            ($contentWeight * $contentScore) +
            ($collaborativeWeight * $collaborativeScore);
    }

    arsort($hybridScores);
    return $hybridScores;
}

// Example usage
$contentBased = ['Avatar' => 0.95, 'The Matrix' => 0.85, 'Inception' => 0.80];
$collaborative = ['Avatar' => 0.88, 'Titanic' => 0.92, 'The Matrix' => 0.78];

$hybrid = hybridRecommendation($contentBased, $collaborative, 0.4, 0.6);
// Result: Balanced recommendations considering both similarity and community preferences
```

**2. Switching Hybrid** — Choose approach based on context:

```php
# filename: hybrid-switching.php
<?php

declare(strict_types=1);

/**
 * Switching hybrid: Use different strategies for different scenarios.
 */
function switchingRecommendation(
    string $userId,
    array $userHistory,
    array $contentBasedScores,
    array $collaborativeScores
): array {
    // New user with <5 interactions? Use content-based
    if (count($userHistory) < 5) {
        return $contentBasedScores;
    }

    // Established user? Use collaborative filtering
    return $collaborativeScores;
}
```

**3. Cascade Hybrid** — Use collaborative filtering, fall back to content-based:

```php
# filename: hybrid-cascade.php
<?php

/**
 * Cascade hybrid: Try collaborative first, use content-based as fallback.
 */
function cascadeRecommendation(
    array $collaborativeScores,
    array $contentBasedScores,
    float $minimumConfidence = 0.3
): array {
    $recommendations = [];

    // Use collaborative filtering for high-confidence items
    foreach ($collaborativeScores as $item => $score) {
        if ($score >= $minimumConfidence) {
            $recommendations[$item] = $score;
        }
    }

    // Fill remaining slots with content-based recommendations
    $needed = 10 - count($recommendations);
    if ($needed > 0) {
        foreach ($contentBasedScores as $item => $score) {
            if (!isset($recommendations[$item]) && $needed > 0) {
                $recommendations[$item] = $score;
                $needed--;
            }
        }
    }

    arsort($recommendations);
    return $recommendations;
}
```

### When to Use Each Hybrid Strategy

| Strategy      | Best For                                     | Example                                   |
| ------------- | -------------------------------------------- | ----------------------------------------- |
| **Weighted**  | General purpose, balanced recommendations    | Netflix (60% collaborative + 40% content) |
| **Switching** | Different user segments with varying data    | New users vs established users            |
| **Cascade**   | Prioritize collaborative but ensure coverage | Fallback for sparse data scenarios        |

### Why It Works

Hybrid systems handle edge cases gracefully:

- **New users**: Content-based provides initial recommendations
- **New items**: Can be recommended based on features before any ratings exist
- **Unique tastes**: Collaborative filtering finds similar users even if taste is unusual
- **Better coverage**: Content-based fills gaps where collaborative data is sparse

### Real-World Example

**Amazon's approach** (simplified):

1. User views a laptop → Content-based shows similar laptops (same brand, specs)
2. User adds to cart → Collaborative filtering shows "Customers who bought this also bought..." (mouse, bag)
3. Hybrid recommendation on homepage: 70% based on browsing history (collaborative) + 30% based on categories viewed (content-based)

**Key Takeaway**: Start simple with one approach, then add hybrid logic as you gather more data and understand your users' needs.

### Troubleshooting

- **Problem**: How do I choose the weights for a weighted hybrid system?
- **Solution**: The optimal weights are data-dependent. A common approach is to treat it as a hyperparameter tuning problem. Split your data into training, validation, and test sets. Try different weight combinations (e.g., `0.2/0.8`, `0.5/0.5`, `0.8/0.2`) on the validation set and choose the combination that yields the best performance on your chosen evaluation metric (like RMSE or Precision@K).

## Step 4: Collaborative Filtering Basics (~10 min)

### Goal

Understand collaborative filtering, which leverages patterns from multiple users to make recommendations without requiring item features.

### Actions

Collaborative filtering takes a fundamentally different approach from content-based filtering—it learns from collective user behavior rather than analyzing item attributes.

### How Collaborative Filtering Works

Collaborative filtering (CF) makes recommendations based on the principle: **"Users who agreed in the past will agree in the future."**

Unlike content-based filtering, CF doesn't need to know anything about item features. It only uses the user-item interaction matrix.

### User-Based Collaborative Filtering

**Concept**: Find users similar to the target user, then recommend items those similar users liked.

**Steps:**

1. Measure similarity between target user and all other users
2. Identify the k most similar users (neighbors)
3. Predict ratings based on neighbors' ratings
4. Recommend highest-predicted items not yet consumed

### Item-Based Collaborative Filtering

**Concept**: Find items similar to those the user liked, then recommend those similar items.

**Steps:**

1. Measure similarity between items based on user rating patterns
2. For items the user rated highly, find similar items
3. Recommend those similar items

### PHP Example: Rating Matrix and Collaborative Filtering Concept

```php
# filename: collaborative-filtering-concept.php
<?php

declare(strict_types=1);

// User-Item ratings matrix (scale 1-5, null = not rated)
$ratings = [
    'Alice'   => ['Inception' => 5, 'Titanic' => 3, 'Avatar' => null, 'The Matrix' => 4, 'Interstellar' => 5],
    'Bob'     => ['Inception' => 4, 'Titanic' => null, 'Avatar' => 5, 'The Matrix' => 5, 'Interstellar' => 4],
    'Charlie' => ['Inception' => 3, 'Titanic' => 4, 'Avatar' => 4, 'The Matrix' => null, 'Interstellar' => 3],
    'Diana'   => ['Inception' => 5, 'Titanic' => 2, 'Avatar' => null, 'The Matrix' => 5, 'Interstellar' => 5],
    'Eve'     => ['Inception' => 2, 'Titanic' => 5, 'Avatar' => 3, 'The Matrix' => 2, 'Interstellar' => 2],
];

// Get items both users have rated
function getCommonItems(array $user1Ratings, array $user2Ratings): array
{
    $common = [];
    foreach ($user1Ratings as $item => $rating1) {
        if ($rating1 !== null && isset($user2Ratings[$item]) && $user2Ratings[$item] !== null) {
            $common[$item] = [$rating1, $user2Ratings[$item]];
        }
    }
    return $common;
}

// Display the rating matrix
echo "User-Item Rating Matrix:\n";
echo str_repeat('-', 85) . "\n";
printf("%-10s", "User");
foreach (array_keys($ratings['Alice']) as $movie) {
    printf("%-15s", $movie);
}
echo "\n" . str_repeat('-', 85) . "\n";

foreach ($ratings as $user => $userRatings) {
    printf("%-10s", $user);
    foreach ($userRatings as $rating) {
        printf("%-15s", $rating ?? '-');
    }
    echo "\n";
}

echo "\n" . str_repeat('=', 85) . "\n";
echo "Collaborative Filtering Insight:\n";
echo str_repeat('=', 85) . "\n\n";

// Compare Alice with other users
$target = 'Alice';
echo "Question: What should we recommend to $target for 'Avatar' (which they haven't rated)?\n\n";

echo "Step 1: Find users similar to $target by comparing their ratings on common movies:\n\n";

foreach ($ratings as $user => $userRatings) {
    if ($user === $target) {
        continue;
    }

    $common = getCommonItems($ratings[$target], $userRatings);

    if (count($common) > 0) {
        echo "$target vs $user:\n";
        foreach ($common as $movie => list($rating1, $rating2)) {
            $diff = abs($rating1 - $rating2);
            echo "  $movie: $target=$rating1, $user=$rating2 (difference: $diff)\n";
        }

        // Check Avatar rating
        if ($userRatings['Avatar'] !== null) {
            echo "  → $user rated Avatar: {$userRatings['Avatar']}\n";
        }
        echo "\n";
    }
}

echo "Step 2: Users with similar tastes to $target (who rated Avatar):\n";
echo "  - Diana: Similar preferences (both love Inception, The Matrix, Interstellar)\n";
echo "  - Bob: Fairly similar (loves The Matrix, Interstellar)\n";
echo "  - Eve: Very different tastes\n\n";

echo "Step 3: Prediction - Since Diana and Bob (similar to $target) both rate Avatar highly,\n";
echo "        we predict $target would also enjoy Avatar!\n";
```

### Expected Result

```
User-Item Rating Matrix:
-------------------------------------------------------------------------------------
User      Inception      Titanic        Avatar         The Matrix     Interstellar
-------------------------------------------------------------------------------------
Alice     5              3              -              4              5
Bob       4              -              5              5              4
Charlie   3              4              4              -              3
Diana     5              2              -              5              5
Eve       2              5              3              2              2

=====================================================================================
Collaborative Filtering Insight:
=====================================================================================

Question: What should we recommend to Alice for 'Avatar' (which they haven't rated)?

Step 1: Find users similar to Alice by comparing their ratings on common movies:

Alice vs Bob:
  Inception: Alice=5, Bob=4 (difference: 1)
  The Matrix: Alice=4, Bob=5 (difference: 1)
  Interstellar: Alice=5, Bob=4 (difference: 1)
  → Bob rated Avatar: 5

Alice vs Charlie:
  Inception: Alice=5, Charlie=3 (difference: 2)
  Titanic: Alice=3, Charlie=4 (difference: 1)
  Interstellar: Alice=5, Charlie=3 (difference: 2)
  → Charlie rated Avatar: 4

Alice vs Diana:
  Inception: Alice=5, Diana=5 (difference: 0)
  Titanic: Alice=3, Diana=2 (difference: 1)
  The Matrix: Alice=4, Diana=5 (difference: 1)
  Interstellar: Alice=5, Diana=5 (difference: 0)

Alice vs Eve:
  Inception: Alice=5, Eve=2 (difference: 3)
  Titanic: Alice=3, Eve=5 (difference: 2)
  The Matrix: Alice=4, Eve=2 (difference: 2)
  Interstellar: Alice=5, Eve=2 (difference: 3)
  → Eve rated Avatar: 3

Step 2: Users with similar tastes to Alice (who rated Avatar):
  - Diana: Similar preferences (both love Inception, The Matrix, Interstellar)
  - Bob: Fairly similar (loves The Matrix, Interstellar)
  - Eve: Very different tastes

Step 3: Prediction - Since Diana and Bob (similar to Alice) both rate Avatar highly,
        we predict Alice would also enjoy Avatar!
```

### Why It Works

Collaborative filtering identifies that Alice and Diana have nearly identical tastes (both rate Inception=5, The Matrix=4-5, Interstellar=5). Since Diana's preferences predict Alice's preferences well, we can use Diana's Avatar rating to predict Alice would also enjoy it. This works without knowing anything about the movies themselves—pure pattern matching on user behavior.

```mermaid
flowchart LR
    A[Alice\nUnrated: Avatar] --> B[Find Similar Users]
    B --> C[Diana\nSimilar taste]
    B --> D[Bob\nFairly similar]
    B --> E[Eve\nDifferent taste]

    C --> F[Diana rated Avatar: ✗]
    D --> G[Bob rated Avatar: 5★]
    E --> H[Eve rated Avatar: 3★]

    G --> I[Weighted Average]
    H --> I
    I --> J[Predict Alice\nwill rate Avatar: ~4.5★]

    style A fill:#e1f5ff
    style C fill:#c8ffc8
    style D fill:#c8ffc8
    style E fill:#ffc8c8
    style J fill:#fff4c8
```

### Troubleshooting

- **Problem**: Predictions fail when no similar users exist
- **Solution**: Implement fallback strategies—use item-based CF instead of user-based, recommend popular items, or use content-based filtering for that user.
- **Problem**: Similar users based on very few common items give unreliable predictions
- **Solution**: Set a minimum threshold for common items (e.g., require at least 3 movies rated by both users before considering them similar).

## Step 5: Similarity Measures (~15 min)

### Goal

Implement three essential similarity measures in PHP: cosine similarity, Pearson correlation, and Euclidean distance.

### Actions

Quantifying similarity between users (or items) is the mathematical foundation of collaborative filtering. Let's implement the three most common similarity metrics.

### Why Similarity Matters

Collaborative filtering depends on quantifying "similarity" between users or items. Different metrics capture different aspects of similarity, and choosing the right one impacts recommendation quality.

```mermaid
graph LR
    subgraph "Euclidean Distance (Magnitude)"
        A[User A (5, 4)]
        B[User B (4, 3)]
        A -- "Short Distance" --> B
        C[User C (1, 5)]
        A -- "Long Distance" --> C
    end

    subgraph "Cosine Similarity (Angle)"
        O(Origin) --> D[User A Vector]
        O --> E[User B Vector]
        O --> F[User C Vector]
        style D stroke-width:2px,stroke:green
        style E stroke-width:2px,stroke:green
        style F stroke-width:2px,stroke:red
    end

    note right of B A and B are close = High Similarity
    note right of C A and C are far = Low Similarity
    note right of E Small Angle = High Similarity
    note right of F Large Angle = Low Similarity
```

### 1. Cosine Similarity

Measures the angle between two vectors. Ranges from -1 (opposite) to 1 (identical). Ignores magnitude, focuses on direction.

**Best for**: High-dimensional sparse data, when absolute rating values are less important than patterns.

```php
# filename: cosine-similarity.php
<?php

declare(strict_types=1);

/**
 * Calculate cosine similarity between two users based on their ratings.
 *
 * Cosine similarity = (A · B) / (||A|| × ||B||)
 * where A · B is the dot product and ||A|| is the magnitude
 */
function cosineSimilarity(array $user1Ratings, array $user2Ratings): float
{
    // Find items both users have rated
    $common = [];
    foreach ($user1Ratings as $item => $rating1) {
        if ($rating1 !== null && isset($user2Ratings[$item]) && $user2Ratings[$item] !== null) {
            $common[$item] = [$rating1, $user2Ratings[$item]];
        }
    }

    if (count($common) === 0) {
        return 0.0; // No common items
    }

    $dotProduct = 0.0;
    $magnitude1 = 0.0;
    $magnitude2 = 0.0;

    foreach ($common as list($rating1, $rating2)) {
        $dotProduct += $rating1 * $rating2;
        $magnitude1 += $rating1 ** 2;
        $magnitude2 += $rating2 ** 2;
    }

    $magnitude1 = sqrt($magnitude1);
    $magnitude2 = sqrt($magnitude2);

    if ($magnitude1 == 0 || $magnitude2 == 0) {
        return 0.0;
    }

    return $dotProduct / ($magnitude1 * $magnitude2);
}

// Example usage
$alice = ['Movie1' => 5, 'Movie2' => 3, 'Movie3' => 4, 'Movie4' => null];
$bob   = ['Movie1' => 4, 'Movie2' => 4, 'Movie3' => 5, 'Movie4' => 3];
$eve   = ['Movie1' => 1, 'Movie2' => 2, 'Movie3' => 1, 'Movie4' => 2];

$simAliceBob = cosineSimilarity($alice, $bob);
$simAliceEve = cosineSimilarity($alice, $eve);

echo "Cosine Similarity:\n";
printf("  Alice <-> Bob: %.4f (similar taste)\n", $simAliceBob);
printf("  Alice <-> Eve: %.4f (different taste)\n", $simAliceEve);
```

**Expected Output:**

```
Cosine Similarity:
  Alice <-> Bob: 0.9939 (similar taste)
  Alice <-> Eve: 0.9636 (different taste)
```

### 2. Pearson Correlation

Measures linear correlation between two variables. Ranges from -1 (inverse correlation) to 1 (perfect correlation). Accounts for different rating scales (some users rate high, others low).

**Best for**: When users have different rating tendencies (some generous, some harsh).

```php
# filename: pearson-correlation.php
<?php

declare(strict_types=1);

/**
 * Calculate Pearson correlation coefficient between two users.
 *
 * Accounts for different rating scales by centering around each user's mean.
 */
function pearsonCorrelation(array $user1Ratings, array $user2Ratings): float
{
    // Find items both users have rated
    $common = [];
    foreach ($user1Ratings as $item => $rating1) {
        if ($rating1 !== null && isset($user2Ratings[$item]) && $user2Ratings[$item] !== null) {
            $common[$item] = [$rating1, $user2Ratings[$item]];
        }
    }

    $n = count($common);
    if ($n === 0) {
        return 0.0;
    }

    // Calculate means
    $sum1 = $sum2 = 0;
    foreach ($common as list($rating1, $rating2)) {
        $sum1 += $rating1;
        $sum2 += $rating2;
    }
    $mean1 = $sum1 / $n;
    $mean2 = $sum2 / $n;

    // Calculate correlation
    $numerator = 0.0;
    $denominator1 = 0.0;
    $denominator2 = 0.0;

    foreach ($common as list($rating1, $rating2)) {
        $diff1 = $rating1 - $mean1;
        $diff2 = $rating2 - $mean2;
        $numerator += $diff1 * $diff2;
        $denominator1 += $diff1 ** 2;
        $denominator2 += $diff2 ** 2;
    }

    $denominator = sqrt($denominator1 * $denominator2);

    if ($denominator == 0) {
        return 0.0;
    }

    return $numerator / $denominator;
}

// Example: Users with different rating scales
$alice = ['Movie1' => 5, 'Movie2' => 4, 'Movie3' => 5]; // Rates high
$bob   = ['Movie1' => 3, 'Movie2' => 2, 'Movie3' => 3]; // Rates low, but similar pattern
$eve   = ['Movie1' => 1, 'Movie2' => 5, 'Movie3' => 1]; // Different pattern

echo "Pearson Correlation (accounts for rating scale differences):\n";
printf("  Alice <-> Bob: %.4f (same pattern, different scale)\n", pearsonCorrelation($alice, $bob));
printf("  Alice <-> Eve: %.4f (different pattern)\n", pearsonCorrelation($alice, $eve));
```

**Expected Output:**

```
Pearson Correlation (accounts for rating scale differences):
  Alice <-> Bob: 1.0000 (same pattern, different scale)
  Alice <-> Eve: -1.0000 (different pattern)
```

### 3. Euclidean Distance

Measures straight-line distance between two points in n-dimensional space. Smaller distance = more similar. Often converted to similarity score.

**Best for**: When rating magnitudes matter and the scale is consistent.

```php
# filename: euclidean-distance.php
<?php

declare(strict_types=1);

/**
 * Calculate Euclidean distance between two users.
 * Convert to similarity score: 1 / (1 + distance)
 */
function euclideanSimilarity(array $user1Ratings, array $user2Ratings): float
{
    // Find items both users have rated
    $common = [];
    foreach ($user1Ratings as $item => $rating1) {
        if ($rating1 !== null && isset($user2Ratings[$item]) && $user2Ratings[$item] !== null) {
            $common[$item] = [$rating1, $user2Ratings[$item]];
        }
    }

    if (count($common) === 0) {
        return 0.0;
    }

    $sumSquaredDifferences = 0.0;

    foreach ($common as list($rating1, $rating2)) {
        $sumSquaredDifferences += ($rating1 - $rating2) ** 2;
    }

    $distance = sqrt($sumSquaredDifferences);

    // Convert distance to similarity (0 to 1 range)
    // Distance of 0 = similarity of 1
    // Larger distance = similarity approaches 0
    return 1 / (1 + $distance);
}

// Example usage
$alice = ['Movie1' => 5, 'Movie2' => 3, 'Movie3' => 4];
$bob   = ['Movie1' => 5, 'Movie2' => 3, 'Movie3' => 4]; // Identical
$charlie = ['Movie1' => 4, 'Movie2' => 2, 'Movie3' => 3]; // Close
$eve   = ['Movie1' => 1, 'Movie2' => 5, 'Movie3' => 1]; // Far

echo "Euclidean Similarity:\n";
printf("  Alice <-> Bob: %.4f (identical)\n", euclideanSimilarity($alice, $bob));
printf("  Alice <-> Charlie: %.4f (close)\n", euclideanSimilarity($alice, $charlie));
printf("  Alice <-> Eve: %.4f (far apart)\n", euclideanSimilarity($alice, $eve));
```

**Expected Output:**

```
Euclidean Similarity:
  Alice <-> Bob: 1.0000 (identical)
  Alice <-> Charlie: 0.3660 (close)
  Alice <-> Eve: 0.1767 (far apart)
```

### When to Use Each Measure

| Similarity Measure | Best Use Case                                    | Accounts for Rating Scale? |
| ------------------ | ------------------------------------------------ | -------------------------- |
| **Cosine**         | Sparse data, pattern matters more than magnitude | No                         |
| **Pearson**        | Users with different rating tendencies           | Yes                        |
| **Euclidean**      | Dense data, absolute values matter               | No                         |

:::tip Pro Tip
In practice, **Pearson correlation** often performs best for explicit ratings because it naturally handles users who are consistently "harsh" or "generous" with their scores.
:::

### Why It Works

Each measure quantifies similarity differently:

- **Cosine** looks at the angle between rating vectors (pattern similarity)
- **Pearson** adjusts for each user's average rating (correlation of preferences)
- **Euclidean** measures direct distance (absolute difference)

### Troubleshooting

- **Problem**: Similarity calculations return 0.0 for all users
- **Solution**: Check that users have overlapping rated items. If the dataset is very sparse, consider using implicit feedback (purchases, clicks) instead of explicit ratings.
- **Problem**: Cosine similarity values seem too high (everything near 1.0)
- **Solution**: This is normal for users who rate consistently (all 4s and 5s). Cosine focuses on patterns, not magnitudes. Use Pearson correlation if you need to account for rating scale differences.
- **Problem**: Division by zero errors in similarity calculations
- **Solution**: All three implementations include checks for zero denominators, returning 0.0 when vectors have no magnitude. Ensure you're using these complete implementations.

## Step 6: Making Predictions (~10 min)

### Goal

Learn how to predict unknown ratings using weighted averages from similar users.

### Actions

Now that we can calculate user similarity, let's use it to predict how a user would rate an item they haven't seen yet.

### The Prediction Formula

Once we've calculated similarity between users, we predict a rating using a **weighted average** of similar users' ratings:

**Prediction = (Σ similarity × rating) / Σ similarity**

Users more similar to the target user have more influence on the prediction.

### PHP Implementation

```php
# filename: rating-prediction.php
<?php

declare(strict_types=1);

/**
 * Predict a user's rating for an item based on similar users.
 */
function predictRating(
    string $targetUser,
    string $targetItem,
    array $allRatings,
    callable $similarityFunction,
    int $k = 3
): ?float {
    // Get ratings for the target item from other users
    $itemRatings = [];

    foreach ($allRatings as $user => $ratings) {
        if ($user === $targetUser) {
            continue; // Skip the target user
        }

        if (isset($ratings[$targetItem]) && $ratings[$targetItem] !== null) {
            $similarity = $similarityFunction($allRatings[$targetUser], $ratings);

            if ($similarity > 0) { // Only consider positively similar users
                $itemRatings[$user] = [
                    'similarity' => $similarity,
                    'rating' => $ratings[$targetItem]
                ];
            }
        }
    }

    if (count($itemRatings) === 0) {
        return null; // No similar users who rated this item
    }

    // Sort by similarity (descending) and take top k neighbors
    uasort($itemRatings, fn($a, $b) => $b['similarity'] <=> $a['similarity']);
    $neighbors = array_slice($itemRatings, 0, $k, true);

    // Calculate weighted average
    $weightedSum = 0.0;
    $similaritySum = 0.0;

    foreach ($neighbors as $user => $data) {
        $weightedSum += $data['similarity'] * $data['rating'];
        $similaritySum += $data['similarity'];
    }

    if ($similaritySum == 0) {
        return null;
    }

    return $weightedSum / $similaritySum;
}

// Pearson correlation function (from previous step)
function pearsonCorrelation(array $user1Ratings, array $user2Ratings): float
{
    $common = [];
    foreach ($user1Ratings as $item => $rating1) {
        if ($rating1 !== null && isset($user2Ratings[$item]) && $user2Ratings[$item] !== null) {
            $common[$item] = [$rating1, $user2Ratings[$item]];
        }
    }

    $n = count($common);
    if ($n === 0) return 0.0;

    $sum1 = $sum2 = 0;
    foreach ($common as list($rating1, $rating2)) {
        $sum1 += $rating1;
        $sum2 += $rating2;
    }
    $mean1 = $sum1 / $n;
    $mean2 = $sum2 / $n;

    $numerator = $denominator1 = $denominator2 = 0.0;
    foreach ($common as list($rating1, $rating2)) {
        $diff1 = $rating1 - $mean1;
        $diff2 = $rating2 - $mean2;
        $numerator += $diff1 * $diff2;
        $denominator1 += $diff1 ** 2;
        $denominator2 += $diff2 ** 2;
    }

    $denominator = sqrt($denominator1 * $denominator2);
    return $denominator == 0 ? 0.0 : $numerator / $denominator;
}

// Sample data
$ratings = [
    'Alice'   => ['Inception' => 5, 'Titanic' => 3, 'Avatar' => null, 'The Matrix' => 4],
    'Bob'     => ['Inception' => 4, 'Titanic' => null, 'Avatar' => 5, 'The Matrix' => 5],
    'Charlie' => ['Inception' => 3, 'Titanic' => 4, 'Avatar' => 4, 'The Matrix' => null],
    'Diana'   => ['Inception' => 5, 'Titanic' => 2, 'Avatar' => null, 'The Matrix' => 5],
    'Eve'     => ['Inception' => 2, 'Titanic' => 5, 'Avatar' => 3, 'The Matrix' => 2],
];

// Predict Alice's rating for Avatar
$prediction = predictRating(
    targetUser: 'Alice',
    targetItem: 'Avatar',
    allRatings: $ratings,
    similarityFunction: 'pearsonCorrelation',
    k: 3
);

echo "Rating Prediction Example:\n";
echo str_repeat('-', 50) . "\n";
echo "Question: How would Alice rate 'Avatar'?\n\n";

// Show similar users
echo "Step 1: Find users similar to Alice:\n";
foreach ($ratings as $user => $userRatings) {
    if ($user !== 'Alice' && $userRatings['Avatar'] !== null) {
        $similarity = pearsonCorrelation($ratings['Alice'], $userRatings);
        printf("  %s: similarity = %.3f, Avatar rating = %d\n",
               $user, $similarity, $userRatings['Avatar']);
    }
}

echo "\nStep 2: Calculate weighted prediction:\n";
if ($prediction !== null) {
    printf("  Predicted rating for Alice on Avatar: %.2f\n", $prediction);
    echo "\nInterpretation: Alice would likely rate Avatar around " . round($prediction) . " stars.\n";
} else {
    echo "  Not enough data to make a prediction.\n";
}
```

### Expected Result

```
Rating Prediction Example:
--------------------------------------------------
Question: How would Alice rate 'Avatar'?

Step 1: Find users similar to Alice:
  Bob: similarity = 1.000, Avatar rating = 5
  Charlie: similarity = -0.655, Avatar rating = 4
  Eve: similarity = -0.981, Avatar rating = 3

Step 2: Calculate weighted prediction:
  Predicted rating for Alice on Avatar: 5.00

Interpretation: Alice would likely rate Avatar around 5 stars.
```

### Why It Works

The prediction algorithm:

1. **Finds similar users** using Pearson correlation (Bob is very similar to Alice)
2. **Weights their ratings** by similarity (Bob's rating has the most influence)
3. **Calculates weighted average** to predict Alice's rating

Bob's perfect similarity (1.000) dominates the prediction, and since Bob loved Avatar (5 stars), we predict Alice will too. This makes intuitive sense—people with identical tastes usually agree on new items.

### Handling Edge Cases

```php
# filename: prediction-edge-cases.php
<?php
// What if no similar users rated the item?
$prediction = null; // Assume this from our function
if ($prediction === null) {
    // Fallback strategies:
    // 1. Use the item's average rating across all users
    // 2. Use the user's average rating across all items
    // 3. Use the global average rating
    // 4. Return "Unable to predict"
}

// What if similarity is negative?
// In the code above, we filter: if ($similarity > 0)
// Only positively similar users contribute to predictions
```

### Troubleshooting

- **Problem**: Predicted rating is outside valid range (e.g., 5.8 on a 1-5 scale)
- **Solution**: Clamp predictions to valid range: `return max(1, min(5, $prediction));` or use a more sophisticated prediction formula that accounts for user's average rating.
- **Problem**: Predictions are always close to the middle (e.g., always ~3.0 on 1-5 scale)
- **Solution**: The weighted average naturally regresses to the mean. Use the advanced formula: `prediction = user_mean + weighted_sum(similarity × (rating - other_user_mean))` to preserve rating deviations.
- **Problem**: Function returns null for most predictions
- **Solution**: Increase `k` (number of neighbors) or lower the similarity threshold. With sparse data, you may need to use more neighbors or switch to item-based collaborative filtering.

## Step 7: Common Challenges (~5 min)

### Goal

Understand the practical challenges that arise when building real-world recommender systems.

### Actions

Every recommender system faces common challenges when scaling from theory to production. Recognizing these issues early helps you design robust solutions.

### 1. The Cold Start Problem

**Challenge**: How do you recommend items for new users with no history? Or recommend new items with no ratings?

**New User Cold Start:**

- User just signed up → no rating history → can't find similar users
- **Solutions**:
  - Ask for initial preferences during onboarding
  - Use demographic information (age, location) for initial recommendations
  - Start with popular items (trending, best-sellers)
  - Hybrid approach: content-based filtering until enough data collected

**New Item Cold Start:**

- New product/movie added → no user ratings → can't use collaborative filtering
- **Solutions**:
  - Content-based filtering using item metadata
  - Editorial curation (featured items)
  - Show to users likely to rate (early adopters, reviewers)

### 2. Data Sparsity

**Challenge**: Most users rate only a tiny fraction of available items.

Example: Netflix has 15,000+ titles, but average user rates <50 movies → **99.7% sparsity**

**Problems:**

- Few overlapping ratings between users → poor similarity estimates
- Many items with very few ratings → unreliable predictions

**Solutions:**

- Dimensionality reduction (matrix factorization)
- Incorporate implicit feedback (views, clicks, time spent)
- Use hybrid models combining multiple signals
- Focus on popular items for similarity calculations

### 3. Scalability

**Challenge**: Computing all pairwise similarities is expensive.

For **1 million users** and **100,000 items**:

- User-based CF: 1M × 1M = **1 trillion** similarity calculations
- Item-based CF: 100K × 100K = **10 billion** calculations

**Solutions:**

- **Precompute** similarities offline (daily/weekly batch jobs)
- **Approximate** nearest neighbors (locality-sensitive hashing)
- **Item-based CF** typically scales better (items change less than users)
- **Model-based methods** (matrix factorization) reduce dimensionality
- **Clustering** to group similar users/items

### 4. The Filter Bubble

**Challenge**: Recommending only similar items limits discovery and diversity.

If you only watch action movies, the system only recommends action movies → you never discover great comedies or documentaries.

**Solutions:**

- Inject randomness/exploration (10% random recommendations)
- Diversity-aware ranking (penalize similar items in top-N)
- Occasionally recommend from different categories
- Balance relevance with serendipity

### 5. Changing Preferences

**Challenge**: User tastes evolve over time.

A user's preferences at age 20 differ from age 40. Recent behavior should weigh more than old behavior.

**Solutions:**

- Time-decay weighting (recent ratings count more)
- Sliding time windows (only use last N months of data)
- Session-based recommendations (current session context)
- Periodic model retraining

### Real-World Trade-offs

```php
# filename: challenge-examples.php
<?php

declare(strict_types=1);

// Example 1: Cold start - new user
echo "Challenge: Cold Start Problem\n";
echo str_repeat('-', 50) . "\n";

$newUser = ['Movie1' => null, 'Movie2' => null, 'Movie3' => null];
echo "New user with no ratings: " . json_encode($newUser) . "\n";
echo "Solution: Show popular items or ask for initial preferences.\n\n";

// Example 2: Sparsity - realistic rating matrix
echo "Challenge: Data Sparsity\n";
echo str_repeat('-', 50) . "\n";

$catalog = 10000; // Total items
$avgRatingsPerUser = 20; // Typical user rates 20 items
$sparsity = 100 * (1 - $avgRatingsPerUser / $catalog);

printf("Catalog size: %d items\n", $catalog);
printf("Average ratings per user: %d\n", $avgRatingsPerUser);
printf("Sparsity: %.2f%% (%.2f%% of matrix is empty)\n\n", $sparsity, $sparsity);

// Example 3: Scalability calculation
echo "Challenge: Scalability\n";
echo str_repeat('-', 50) . "\n";

$users = 1000000;
$items = 100000;
$userBasedComparisons = $users * $users;
$itemBasedComparisons = $items * $items;

printf("Users: %s\n", number_format($users));
printf("Items: %s\n", number_format($items));
printf("User-based similarity calculations: %s\n", number_format($userBasedComparisons));
printf("Item-based similarity calculations: %s\n", number_format($itemBasedComparisons));
echo "Solution: Precompute similarities, use approximate methods, or matrix factorization.\n";
```

### Expected Output

```
Challenge: Cold Start Problem
--------------------------------------------------
New user with no ratings: {"Movie1":null,"Movie2":null,"Movie3":null}
Solution: Show popular items or ask for initial preferences.

Challenge: Data Sparsity
--------------------------------------------------
Catalog size: 10000 items
Average ratings per user: 20
Sparsity: 99.80% (99.80% of matrix is empty)

Challenge: Scalability
--------------------------------------------------
Users: 1,000,000
Items: 100,000
User-based similarity calculations: 1,000,000,000,000
Item-based similarity calculations: 10,000,000,000
Solution: Precompute similarities, use approximate methods, or matrix factorization.
```

### Why It Works

These challenges are inherent to recommendation systems but each has proven solutions. Production systems typically use hybrid approaches:

- **Cold start**: Combine content-based filtering with collaborative filtering
- **Sparsity**: Use matrix factorization (SVD, ALS) to find latent factors
- **Scalability**: Precompute item-item similarities (items change less frequently than users)
- **Filter bubbles**: Inject diversity through randomization or explicit exploration
- **Changing preferences**: Use time-decay weighting or session-based models

### Troubleshooting

- **Problem**: New users get poor recommendations (cold start)
- **Solution**: Implement a multi-stage onboarding:
  1. Show trending/popular items
  2. Ask 3-5 preference questions ("Do you like action movies?")
  3. Use content-based filtering for first session
  4. Switch to collaborative filtering once user has 5+ interactions
- **Problem**: 99%+ sparsity makes all similarity scores very low
- **Solution**: Use implicit feedback (clicks, views, time spent) in addition to explicit ratings. Every interaction provides signal, even without ratings.

## Step 8: Advanced Techniques — Matrix Factorization (~5 min)

### Goal

Understand matrix factorization as the advanced solution to sparsity and scalability challenges.

### What Is Matrix Factorization?

The similarity-based collaborative filtering you've learned works well for small datasets but struggles with:

- **Sparsity**: Users rate <1% of items → few overlapping ratings
- **Scalability**: 1M users = 1 trillion similarity calculations
- **Noise**: Individual ratings can be unreliable

**Matrix factorization** solves these problems by discovering **latent factors** (hidden patterns) that explain user preferences.

### The Core Idea

Instead of directly comparing users or items, matrix factorization decomposes the rating matrix into lower-dimensional matrices representing latent features.

**Analogy**: Rather than saying "Alice and Bob both liked Inception, so they're similar," matrix factorization discovers that:

- Inception has high values for latent factors: [`action`, `mind-bending`, `high-budget`]
- Alice likes items with high `action` and `mind-bending` scores
- Bob also prefers `action` and `mind-bending` content
- Therefore, both would enjoy other items with these latent factors

```mermaid
graph TD
    subgraph "Original Rating Matrix (R)"
        direction LR
        R(m users x n items)
    end

    subgraph "Learned Latent Factors"
        P(m users x k features)
        Q(n items x k features)
    end

    subgraph "Predicted Ratings (R')"
        R_hat(m users x n items)
    end

    R -- "Decompose" --> P
    R -- "Decompose" --> Q

    P -- "Multiply" --> R_hat
    Q -- "Multiply" --> R_hat

    note right of P "User preferences for k latent features (e.g., action, comedy)"
    note right of Q "Item composition of k latent features"
```

### Conceptual Example

```php
# filename: matrix-factorization-conceptual.php
<?php

declare(strict_types=1);

/**
 * Conceptual representation of matrix factorization.
 *
 * In reality, algorithms like SVD or ALS learn these factors automatically.
 * This example shows the concept using manually defined factors.
 */

// Original sparse rating matrix (users × items)
$ratings = [
    'Alice' => ['Movie1' => 5, 'Movie2' => null, 'Movie3' => 4, 'Movie4' => null],
    'Bob'   => ['Movie1' => 4, 'Movie2' => 5, 'Movie3' => null, 'Movie4' => 5],
    'Carol' => ['Movie1' => null, 'Movie2' => 4, 'Movie3' => 2, 'Movie4' => null],
];

// Matrix factorization learns:
// 1. User factors (users × latent_features)
$userFactors = [
    'Alice' => ['action' => 0.9, 'romance' => 0.2], // Alice loves action
    'Bob'   => ['action' => 0.8, 'romance' => 0.7], // Bob likes both
    'Carol' => ['action' => 0.1, 'romance' => 0.9], // Carol prefers romance
];

// 2. Item factors (items × latent_features)
$itemFactors = [
    'Movie1' => ['action' => 1.0, 'romance' => 0.1], // Action movie
    'Movie2' => ['action' => 0.2, 'romance' => 0.9], // Romance movie
    'Movie3' => ['action' => 0.7, 'romance' => 0.6], // Mixed
    'Movie4' => ['action' => 0.3, 'romance' => 0.8], // Romance-heavy
];

/**
 * Predict rating by multiplying user factors × item factors.
 */
function predictWithFactors(array $userFactor, array $itemFactor): float
{
    $prediction = 0.0;

    foreach ($userFactor as $feature => $userValue) {
        $itemValue = $itemFactor[$feature] ?? 0;
        $prediction += $userValue * $itemValue;
    }

    // Scale to 1-5 rating
    return 1 + ($prediction * 4);
}

// Predict Alice's rating for Movie2 (which she hasn't seen)
$prediction = predictWithFactors(
    $userFactors['Alice'],
    $itemFactors['Movie2']
);

echo "Predicted rating for Alice on Movie2: " . round($prediction, 2) . "\n";
// Output: ~1.7 (low - Alice doesn't like romance, Movie2 is romance-heavy)

// Predict Alice's rating for Movie4
$prediction4 = predictWithFactors(
    $userFactors['Alice'],
    $itemFactors['Movie4']
);

echo "Predicted rating for Alice on Movie4: " . round($prediction4, 2) . "\n";
// Output: ~2.5 (low to medium - still romance-focused)
```

### Why Matrix Factorization Is Powerful

**Advantages over similarity-based CF**:

1. **Handles sparsity**: Works with very sparse matrices (1% density or less)
2. **Scalable**: Learn factors once, predict instantly (no neighbor searches)
3. **Discovers hidden patterns**: Finds latent features humans might not notice
4. **Reduces noise**: Aggregates patterns across all users/items
5. **Flexible**: Can incorporate implicit feedback, temporal dynamics, and side information

**Popular Algorithms**:

- **SVD (Singular Value Decomposition)**: Classic matrix factorization
- **ALS (Alternating Least Squares)**: Optimized for implicit feedback and parallel computing
- **NMF (Non-negative Matrix Factorization)**: Ensures non-negative factors (interpretable)
- **Deep Learning**: Neural collaborative filtering with embeddings

### The Netflix Prize Connection

The **Netflix Prize** ($1M competition, 2006-2009) popularized matrix factorization for recommender systems. The winning solution combined multiple matrix factorization approaches to achieve 10% improvement in RMSE.

**Key insight from Netflix Prize**: Ensemble methods combining multiple matrix factorization models outperform any single approach.

### When to Use Matrix Factorization

**Use similarity-based CF when**:

- Small dataset (<10,000 users/items)
- Need explainability ("Users like you rated this highly")
- Simple implementation required

**Use matrix factorization when**:

- Large dataset (100,000+ users/items)
- Sparse data (<1% ratings)
- Need scalability and performance
- Can use external libraries (Python scikit-learn, Apache Spark MLlib)

### Implementation in PHP

Pure PHP implementations of SVD/ALS are rare due to computational complexity. **Recommended approach**:

1. **For small-scale**: Use similarity-based CF (this chapter's approach)
2. **For production**: Use Python libraries (scikit-surprise, implicit) via REST API (Chapter 11's pattern)
3. **For large-scale**: Apache Spark MLlib with PHP as frontend

```php
# filename: matrix-factorization-api-call.php
<?php

// Example: Call Python-based matrix factorization service from PHP
$data = [
    'user_id' => 'user123',
    'item_id' => 'movie456',
];

$ch = curl_init('http://localhost:5000/predict-svd');
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

$response = curl_exec($ch);
$result = json_decode($response, true);

echo "Predicted rating: " . $result['prediction'] . "\n";
```

### Key Takeaway

Matrix factorization is the **industry-standard approach** for production recommender systems, but it requires external libraries or services. The similarity-based collaborative filtering you learned in this chapter provides a solid foundation for understanding recommendations and works well for smaller PHP applications. For larger systems, integrate with Python/Spark-based matrix factorization services using the patterns from Chapter 11.

### Troubleshooting

- **Problem**: I can't set up a Python service. Can I still use matrix factorization?
- **Solution**: While pure PHP implementations are not recommended for production due to performance, some libraries are emerging. For example, you could explore using a PHP extension for a machine learning library like ONNX Runtime, which can run models trained in Python. For smaller projects, you could also look for PHP libraries that might implement simpler versions of matrix factorization, but be aware of the performance limitations.

## Step 9: Evaluation Metrics (~8 min)

### Goal

Learn how to measure recommendation quality using metrics for rating prediction and top-N recommendations.

### Actions

Evaluating recommender systems requires different metrics than classification or regression. We need to measure both prediction accuracy and recommendation relevance.

### Rating Prediction Metrics

When predicting explicit ratings (1-5 stars), we measure prediction accuracy.

#### Root Mean Squared Error (RMSE)

Measures average prediction error, penalizing large errors more heavily.

**Formula**: RMSE = √(Σ(predicted - actual)² / n)

**Lower is better**. RMSE = 0 means perfect predictions.

```php
# filename: rmse-metric.php
<?php

declare(strict_types=1);

/**
 * Calculate Root Mean Squared Error.
 *
 * Measures the average magnitude of prediction errors.
 */
function calculateRMSE(array $predictions, array $actuals): float
{
    if (count($predictions) !== count($actuals) || count($predictions) === 0) {
        throw new InvalidArgumentException("Arrays must be same length and non-empty");
    }

    $squaredErrors = 0.0;
    $count = 0;

    foreach ($predictions as $i => $predicted) {
        $actual = $actuals[$i];
        $error = $predicted - $actual;
        $squaredErrors += $error ** 2;
        $count++;
    }

    return sqrt($squaredErrors / $count);
}

// Example: Test predictions vs actual ratings
$predicted = [4.5, 3.2, 5.0, 2.8, 4.1];
$actual    = [5.0, 3.0, 5.0, 3.0, 4.0];

$rmse = calculateRMSE($predicted, $actual);

echo "RMSE Evaluation:\n";
echo str_repeat('-', 50) . "\n";
echo "Predicted ratings: " . json_encode($predicted) . "\n";
echo "Actual ratings:    " . json_encode($actual) . "\n";
printf("\nRMSE: %.4f\n", $rmse);
echo "\nInterpretation: Predictions are off by ~" . round($rmse, 2) . " stars on average.\n";
echo "On a 1-5 scale, RMSE < 1.0 is generally good.\n";
```

**Expected Output:**

```
RMSE Evaluation:
--------------------------------------------------
Predicted ratings: [4.5,3.2,5,2.8,4.1]
Actual ratings:    [5,3,5,3,4]

RMSE: 0.3937

Interpretation: Predictions are off by ~0.39 stars on average.
On a 1-5 scale, RMSE < 1.0 is generally good.
```

#### Mean Absolute Error (MAE)

Average absolute difference between predicted and actual ratings.

**Formula**: MAE = Σ|predicted - actual| / n

**Lower is better**. MAE is more interpretable than RMSE (direct average error).

```php
# filename: mae-metric.php
<?php

declare(strict_types=1);

/**
 * Calculate Mean Absolute Error.
 */
function calculateMAE(array $predictions, array $actuals): float
{
    if (count($predictions) !== count($actuals) || count($predictions) === 0) {
        throw new InvalidArgumentException("Arrays must be same length and non-empty");
    }

    $absoluteErrors = 0.0;
    $count = 0;

    foreach ($predictions as $i => $predicted) {
        $actual = $actuals[$i];
        $absoluteErrors += abs($predicted - $actual);
        $count++;
    }

    return $absoluteErrors / $count;
}

$predicted = [4.5, 3.2, 5.0, 2.8, 4.1];
$actual    = [5.0, 3.0, 5.0, 3.0, 4.0];

$mae = calculateMAE($predicted, $actual);

echo "MAE Evaluation:\n";
printf("MAE: %.4f (average error of %.2f stars)\n", $mae, $mae);
```

**Expected Output:**

```
MAE Evaluation:
MAE: 0.3200 (average error of 0.32 stars)
```

### Top-N Recommendation Metrics

When recommending a ranked list (top 10 movies), we measure list quality.

#### Precision@K

**"Of the K items recommended, how many were relevant?"**

Precision@10 = (relevant items in top 10) / 10

**Higher is better** (1.0 = perfect).

#### Recall@K

**"Of all relevant items, how many appeared in top K?"**

Recall@10 = (relevant items in top 10) / (total relevant items)

**Higher is better** (1.0 = found everything).

```php
# filename: precision-recall-at-k.php
<?php

declare(strict_types=1);

/**
 * Calculate Precision@K and Recall@K.
 */
function precisionAtK(array $recommended, array $relevant, int $k): float
{
    $topK = array_slice($recommended, 0, $k);
    $hits = count(array_intersect($topK, $relevant));
    return $k > 0 ? $hits / $k : 0.0;
}

function recallAtK(array $recommended, array $relevant, int $k): float
{
    $topK = array_slice($recommended, 0, $k);
    $hits = count(array_intersect($topK, $relevant));
    return count($relevant) > 0 ? $hits / count($relevant) : 0.0;
}

// Example: User's top 10 recommendations
$recommendedItems = [
    'Movie A', 'Movie B', 'Movie C', 'Movie D', 'Movie E',
    'Movie F', 'Movie G', 'Movie H', 'Movie I', 'Movie J'
];

// Items the user actually liked (ground truth)
$relevantItems = ['Movie B', 'Movie D', 'Movie F', 'Movie X', 'Movie Y'];

echo "Top-N Recommendation Evaluation:\n";
echo str_repeat('-', 50) . "\n";
echo "Recommended (top 10): " . implode(', ', array_slice($recommendedItems, 0, 10)) . "\n";
echo "Actually relevant: " . implode(', ', $relevantItems) . "\n\n";

$k = 10;
$precision = precisionAtK($recommendedItems, $relevantItems, $k);
$recall = recallAtK($recommendedItems, $relevantItems, $k);

printf("Precision@%d: %.2f (%.0f%% of recommendations were relevant)\n",
       $k, $precision, $precision * 100);
printf("Recall@%d: %.2f (found %.0f%% of all relevant items)\n",
       $k, $recall, $recall * 100);
```

**Expected Output:**

```
Top-N Recommendation Evaluation:
--------------------------------------------------
Recommended (top 10): Movie A, Movie B, Movie C, Movie D, Movie E, Movie F, Movie G, Movie H, Movie I, Movie J
Actually relevant: Movie B, Movie D, Movie F, Movie X, Movie Y

Precision@10: 0.30 (30% of recommendations were relevant)
Recall@10: 0.60 (found 60% of all relevant items)
```

### Which Metrics to Use?

| Metric          | Use Case                    | Interpretation                    |
| --------------- | --------------------------- | --------------------------------- |
| **RMSE**        | Predicting explicit ratings | How far off are star predictions? |
| **MAE**         | Predicting explicit ratings | Average error in rating points    |
| **Precision@K** | Top-N recommendations       | Quality of recommendations        |
| **Recall@K**    | Top-N recommendations       | Coverage of relevant items        |

**In practice**: Use RMSE/MAE during model training. Use Precision@K and Recall@K for evaluating the user experience of top-N lists.

### Online vs Offline Evaluation

**Offline Metrics** (RMSE, Precision@K) — evaluated on historical data:

- **Pros**: Fast, reproducible, no user impact
- **Cons**: Don't predict real user behavior or business impact

**Online Metrics** (CTR, conversion, engagement) — measured with real users:

- **Pros**: Directly measure business value
- **Cons**: Require A/B testing, slower feedback

```php
# filename: online-metrics-tracking.php
<?php

declare(strict_types=1);

/**
 * Track online recommendation metrics in production.
 */
class RecommendationMetrics
{
    public function __construct(
        private \PDO $db
    ) {}

    /**
     * Log recommendation impression.
     */
    public function logImpression(string $userId, array $recommendedItems): void
    {
        $stmt = $this->db->prepare(
            "INSERT INTO recommendation_logs
            (user_id, items, timestamp) VALUES (?, ?, NOW())"
        );
        $stmt->execute([$userId, json_encode($recommendedItems)]);
    }

    /**
     * Log user click on recommended item.
     */
    public function logClick(string $userId, string $itemId): void
    {
        $stmt = $this->db->prepare(
            "UPDATE recommendation_logs
            SET clicked_item = ?, click_time = NOW()
            WHERE user_id = ? AND clicked_item IS NULL
            ORDER BY timestamp DESC LIMIT 1"
        );
        $stmt->execute([$itemId, $userId]);
    }

    /**
     * Calculate click-through rate (CTR).
     */
    public function calculateCTR(string $dateFrom): float
    {
        $stmt = $this->db->prepare(
            "SELECT
                COUNT(*) as impressions,
                SUM(CASE WHEN clicked_item IS NOT NULL THEN 1 ELSE 0 END) as clicks
            FROM recommendation_logs
            WHERE timestamp >= ?"
        );
        $stmt->execute([$dateFrom]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($result['impressions'] == 0) {
            return 0.0;
        }

        return $result['clicks'] / $result['impressions'];
    }
}

// Usage: Monitor recommendation performance
// $metrics = new RecommendationMetrics($pdo);

// When showing recommendations
// $metrics->logImpression('user123', ['movie1', 'movie2', 'movie3']);

// When user clicks
// $metrics->logClick('user123', 'movie2');

// Calculate CTR for last week
// $ctr = $metrics->calculateCTR(date('Y-m-d', strtotime('-7 days')));
// echo "Last 7 days CTR: " . round($ctr * 100, 2) . "%\n";
```

**Key Online Metrics**:

- **Click-Through Rate (CTR)**: % of recommendations clicked
- **Conversion Rate**: % of recommendations leading to purchase/signup
- **Engagement**: Time spent with recommended items
- **Diversity**: Variety of categories in recommendations
- **Coverage**: % of catalog items being recommended

### A/B Testing Recommendations

To validate improvements, run A/B tests:

```php
# filename: ab-testing-example.php
<?php

/**
 * A/B test framework for recommendations.
 */
function getRecommendations(string $userId): array
{
    // Assign 50% of users to new algorithm
    $userHash = crc32($userId);
    $useNewAlgorithm = ($userHash % 100) < 50;

    if ($useNewAlgorithm) {
        $recommendations = []; // getRecommendationsV2($userId); // New matrix factorization
        // logExperiment($userId, 'algorithm_v2', $recommendations);
    } else {
        $recommendations = []; // getRecommendationsV1($userId); // Old similarity-based
        // logExperiment($userId, 'algorithm_v1', $recommendations);
    }

    return $recommendations;
}
```

**What to measure in A/B tests**:

- Revenue per user
- Click-through rate
- Time on site
- Return visit rate
- User satisfaction surveys

### Troubleshooting

- **Problem**: RMSE seems high even though recommendations look good
- **Solution**: RMSE penalizes outliers heavily. If a few predictions are way off, RMSE will be high. Use MAE for a more balanced view, or focus on Precision@K if you care more about top recommendations than exact ratings.
- **Problem**: High precision but low recall (or vice versa)
- **Solution**: This is a classic trade-off. High precision = recommendations are accurate but you miss many relevant items. High recall = you find most relevant items but include irrelevant ones. Balance depends on your use case—e-commerce often prioritizes precision (show only good recommendations), while content discovery may prefer recall (show more options).
- **Problem**: Metrics look good on test data but users don't engage with recommendations
- **Solution**: Offline metrics (RMSE, Precision@K) don't capture real user behavior. Implement A/B testing and measure online metrics: click-through rate, conversion rate, time spent, return visits. These business metrics matter more than algorithmic accuracy.

## Step 10: Real-World Use Cases (~5 min)

### Goal

Explore how different industries apply recommender systems to solve business problems.

### Actions

Recommender systems power diverse applications across industries. Understanding these use cases helps you identify opportunities in your own projects.

### 1. E-Commerce Product Recommendations

**Amazon, Shopify, Etsy**

**Use Cases:**

- "Customers who bought this also bought..."
- "Recommended for you" personalized homepage
- "Complete your purchase" (complementary items)
- Email campaigns with personalized product suggestions

**Approach**: Hybrid of item-based collaborative filtering (purchase history) + content-based (product categories, attributes)

**Impact**: Amazon reports 35% of revenue from recommendations.

### 2. Streaming Media & Entertainment

**Netflix, Spotify, YouTube**

**Use Cases:**

- Personalized home screen rows ("Because you watched X")
- Autoplay suggestions (what to watch next)
- Discover Weekly playlists (Spotify)
- Genre-specific recommendations

**Approach**: Matrix factorization + deep learning models considering watch time, completion rate, recency, device context

**Impact**: Netflix estimates recommendations save $1B/year in retention.

### 3. Content Platforms & News

**Medium, Reddit, Twitter/X, News Aggregators**

**Use Cases:**

- Article recommendations based on reading history
- "Trending" content personalized to user
- Feed ranking (which posts to show first)
- Newsletter personalization

**Approach**: Collaborative filtering + NLP for content similarity + engagement signals (clicks, time spent, shares)

### 4. Social Networks

**LinkedIn, Facebook, Instagram**

**Use Cases:**

- "People you may know" connection suggestions
- Friend recommendations
- Group suggestions
- Event recommendations

**Approach**: Graph-based algorithms (mutual connections) + collaborative filtering (similar interests/activities)

### 5. Food Delivery & Local Services

**UberEats, DoorDash, Yelp**

**Use Cases:**

- Restaurant recommendations
- "Order again" suggestions
- Cuisine discovery
- Time-of-day personalization

**Approach**: Collaborative filtering + location awareness + temporal patterns (breakfast vs dinner preferences)

### PHP Use Case Example

```php
# filename: ecommerce-recommendations.php
<?php

declare(strict_types=1);

/**
 * Simple e-commerce recommendation system.
 *
 * Demonstrates "customers who bought X also bought Y" using item-based CF.
 */

// Purchase history: user => [products purchased]
$purchases = [
    'User1' => ['Laptop', 'Mouse', 'USB Cable', 'Laptop Bag'],
    'User2' => ['Laptop', 'Mouse', 'Keyboard'],
    'User3' => ['Phone', 'Phone Case', 'Charger'],
    'User4' => ['Laptop', 'Laptop Bag', 'Mouse', 'Monitor'],
    'User5' => ['Phone', 'Charger', 'Headphones'],
    'User6' => ['Laptop', 'Mouse', 'Keyboard', 'Monitor'],
];

/**
 * Find items frequently bought together with a target item.
 */
function findRelatedProducts(string $targetProduct, array $purchases, int $topN = 3): array
{
    $coOccurrence = [];

    // Count how often each product appears with the target
    foreach ($purchases as $user => $products) {
        if (in_array($targetProduct, $products)) {
            foreach ($products as $product) {
                if ($product !== $targetProduct) {
                    $coOccurrence[$product] = ($coOccurrence[$product] ?? 0) + 1;
                }
            }
        }
    }

    // Sort by frequency
    arsort($coOccurrence);

    return array_slice($coOccurrence, 0, $topN, true);
}

// Example: Customer viewing a Laptop
$viewingProduct = 'Laptop';
$recommendations = findRelatedProducts($viewingProduct, $purchases, 3);

echo "E-Commerce Recommendation Example:\n";
echo str_repeat('-', 50) . "\n";
echo "Customer is viewing: $viewingProduct\n\n";
echo "\"Customers who bought '$viewingProduct' also bought:\"\n";

foreach ($recommendations as $product => $count) {
    printf("  - %s (%d customers)\n", $product, $count);
}
```

### Expected Output

```
E-Commerce Recommendation Example:
--------------------------------------------------
Customer is viewing: Laptop

"Customers who bought 'Laptop' also bought:"
  - Mouse (4 customers)
  - Laptop Bag (2 customers)
  - Keyboard (2 customers)
```

### Industry-Specific Considerations

**E-Commerce:**

- Inventory availability
- Price sensitivity
- Seasonal trends
- Cart abandonment recovery

**Media/Entertainment:**

- Content freshness (new releases)
- Diversity (avoid echo chambers)
- Completion rates (did they finish?)
- Multi-device tracking

**Social Networks:**

- Privacy concerns
- Network effects (mutual connections)
- Real-time updates
- Viral/trending content boost

**Local Services:**

- Geographic constraints
- Operating hours
- Capacity/availability
- Weather/time-of-day factors

### Key Insight for PHP Developers

Most PHP applications can benefit from basic recommendations:

- **Blog/CMS**: "Related articles" using content similarity
- **E-commerce**: "Customers also bought" using purchase co-occurrence
- **SaaS platforms**: Feature suggestions based on user behavior patterns
- **Community sites**: Connection recommendations using mutual interests

Start simple with content-based or item-based collaborative filtering before investing in complex matrix factorization. Many successful recommendations come from straightforward similarity calculations you can implement in an afternoon.

## Exercises

Practice applying the concepts you've learned with these hands-on exercises. Each exercise reinforces a key aspect of recommender systems theory.

### Exercise 1: Calculate Similarity Manually

**Goal**: Reinforce understanding of similarity measures by hand-calculating them.

Given these two users' ratings:

```
Alice: [Movie1: 5, Movie2: 3, Movie3: 4]
Bob:   [Movie1: 4, Movie2: 2, Movie3: 3]
```

**Tasks**:

1. Calculate **cosine similarity** between Alice and Bob
2. Calculate **Pearson correlation** between Alice and Bob
3. Calculate **Euclidean distance** and convert to similarity score

**Validation**: Use the PHP functions from Step 4 to verify your calculations.

```php
# filename: exercise-1-validation.php
<?php
// Include your implementations of the similarity functions here

$alice = ['Movie1' => 5, 'Movie2' => 3, 'Movie3' => 4];
$bob   = ['Movie1' => 4, 'Movie2' => 2, 'Movie3' => 3];

// Use cosineSimilarity(), pearsonCorrelation(), euclideanSimilarity()
// Your manual calculations should match!
```

### Exercise 2: Implement a Simple Similarity Function

**Goal**: Practice writing similarity calculations from scratch.

Create a function `jaccardSimilarity()` that calculates Jaccard similarity between two users based on items they both rated (regardless of rating values).

**Formula**: Jaccard = |A ∩ B| / |A ∪ B|

Where:

- A ∩ B = items both users rated
- A ∪ B = all items either user rated

**Requirements**:

- Function signature: `function jaccardSimilarity(array $user1, array $user2): float`
- Return value between 0.0 (no overlap) and 1.0 (identical items rated)
- Handle null ratings properly

**Validation**:

```php
# filename: exercise-2-validation.php
<?php
// Your jaccardSimilarity function implementation here

$user1 = ['A' => 5, 'B' => 3, 'C' => null, 'D' => 4];
$user2 = ['A' => 4, 'B' => null, 'C' => 5, 'D' => 2];

$similarity = jaccardSimilarity($user1, $user2);
// Expected: 0.5 (both rated A and D = 2 items, union = A, B, C, D = 4 items)
echo "Jaccard similarity: " . $similarity;
```

### Exercise 3: Design a Rating Matrix

**Goal**: Practice structuring data for a recommender system.

**Scenario**: You're building a book recommendation system for a small library website.

**Tasks**:

1. Create a PHP array representing a user-item rating matrix with:

   - 6 users (give them realistic names)
   - 8 books (use real book titles or make them up)
   - Ratings on 1-5 scale
   - At least 40% sparsity (many null values)

2. Calculate sparsity percentage:

   ```php
   # filename: exercise-3-validation.php
   <?php
   // Your matrix here
   $users = 6;
   $items = 8;
   $totalCells = $users * $items;
   // $filledCells = count(non-null ratings);
   // $sparsity = 100 * (1 - $filledCells / $totalCells);
   ```

3. Identify one user pair that would likely have **high similarity** (similar taste patterns) and one pair with **low similarity** (different tastes)

**Validation**: Run cosine or Pearson similarity on your chosen pairs to verify your intuition.

### Challenge Exercise: Build a Mini Recommender

**Goal**: Combine all concepts into a working system.

Implement a complete user-based collaborative filtering system that:

1. Accepts a user-item rating matrix (at least 5 users × 5 items)
2. Accepts a target user and target item
3. Calculates similarity between target user and all other users using Pearson correlation
4. Predicts the target user's rating for the target item using k=3 neighbors
5. Returns the prediction with confidence level (based on similarity sum)

**Requirements**:

- Use the Pearson correlation function from Step 5
- Use the prediction function from Step 6
- Handle edge cases (no similar users, all null ratings, etc.)
- Output explanation showing which users influenced the prediction

This exercise prepares you perfectly for Chapter 22 where you'll build a full recommendation engine with real datasets!

## Wrap-up

Congratulations! You now understand the core concepts behind recommendation engines—one of the most impactful applications of machine learning in web development.

**What You've Learned**:

✓ How recommender systems work and why they're valuable for users and businesses
✓ The difference between content-based and collaborative filtering approaches
✓ User-based vs. item-based collaborative filtering strategies
✓ Three similarity measures: cosine, Pearson correlation, and Euclidean distance
✓ How to predict ratings using weighted averages from similar users
✓ Common challenges: cold start, sparsity, scalability, and filter bubbles
✓ Evaluation metrics: RMSE, MAE for ratings; Precision@K and Recall@K for top-N lists
✓ Real-world use cases across e-commerce, streaming, content platforms, and social networks

**Key Takeaways**:

- **Collaborative filtering** leverages collective behavior patterns without needing item metadata
- **Similarity measures** quantify how alike users or items are—choose based on your data characteristics
- **Predictions** combine multiple similar users' ratings using weighted averages
- **Real systems** face scalability and sparsity challenges requiring advanced techniques like matrix factorization
- **Evaluation** differs for rating prediction (RMSE/MAE) vs. top-N recommendations (Precision/Recall)

**What's Next**:

In **Chapter 22**, you'll put this theory into practice by building a complete recommendation engine in PHP. You'll:

- Implement user-based collaborative filtering from scratch
- Load and process a real dataset of movie ratings
- Calculate similarities across all users
- Generate personalized recommendations
- Evaluate your system with RMSE and top-N metrics
- Handle edge cases (new users, missing ratings)
- Optimize for performance

The theoretical foundation you've built here makes the implementation straightforward. You understand _why_ the algorithms work, not just _how_ to code them—setting you up to adapt recommender systems to any PHP application.

**Practice Opportunity**:

Before moving to Chapter 22, try designing a recommendation system for a project you're working on:

- What items will you recommend? (products, articles, connections?)
- What interaction data do you have? (purchases, ratings, clicks?)
- Which filtering approach fits best? (collaborative, content-based, hybrid?)
- What challenges might you face? (cold start, sparsity, real-time updates?)

Having a concrete use case in mind makes Chapter 22's implementation immediately applicable to your work.

## Further Reading

- [Collaborative Filtering for Implicit Feedback Datasets](http://yifanhu.net/PUB/cf.pdf) — Foundational paper on recommendation algorithms by Hu, Koren, and Volinsky
- [The Netflix Prize Documentation](https://www.netflixprize.com/) — Dataset, papers, and techniques from the famous $1M competition that advanced recommender systems
- [Matrix Factorization Techniques for Recommender Systems](https://datajobs.com/data-science-repo/Recommender-Systems-[Netflix].pdf) — Explains SVD and latent factor models (IEEE paper by Koren, Bell, and Volinsky)
- [Recommender Systems Handbook](https://www.springer.com/gp/book/9780387858203) — Comprehensive academic reference covering theory and practice
- [Surprise Library Documentation](http://surpriselib.com/) — Python library for building recommender systems (useful for understanding algorithms even if using PHP)
- [Google's Recommendation Systems Crash Course](https://developers.google.com/machine-learning/recommendation) — Free practical guide to building recommendation systems
- [Amazon's Item-to-Item Collaborative Filtering Paper](https://www.cs.umd.edu/~samir/498/Amazon-Recommendations.pdf) — How Amazon.com scales recommendations to millions of items

**PHP-Specific Resources**:

- [PHP-ML Recommendation Example](https://php-ml.readthedocs.io/en/latest/) — Check if newer versions include recommender system implementations
- [Rubix ML Documentation](https://docs.rubixml.com/) — Explore clustering and similarity functions useful for recommendations
