<?php

declare(strict_types=1);

/**
 * Use Case 3: Article Recommender
 * 
 * This class demonstrates a simple content-based recommendation engine
 * that suggests related articles based on tag overlap.
 * 
 * Requirements:
 * - PHP 8.4+
 * - Database with 'articles' and 'article_tags' tables
 * - Laravel/Eloquent or PDO
 */

use Illuminate\Database\Connection;
use Illuminate\Support\Facades\DB;

final class ArticleRecommender
{
    public function __construct(
        private ?Connection $db = null
    ) {
        $this->db = $db ?? DB::connection();
    }

    /**
     * Find similar articles based on tag overlap.
     * 
     * This uses a simple collaborative filtering approach: articles
     * that share tags with the current article are considered similar.
     * 
     * @param int $articleId The current article ID
     * @param int $limit Maximum number of recommendations
     * @return array Array of recommended article objects
     */
    public function getSimilarArticles(int $articleId, int $limit = 5): array
    {
        // Get tags for current article
        $currentTags = DB::table('article_tags')
            ->where('article_id', $articleId)
            ->pluck('tag_id')
            ->toArray();

        if (empty($currentTags)) {
            return []; // No tags means no recommendations
        }

        // Find articles with overlapping tags
        $similar = DB::table('articles')
            ->select('articles.*', DB::raw('COUNT(article_tags.tag_id) as tag_overlap'))
            ->join('article_tags', 'articles.id', '=', 'article_tags.article_id')
            ->whereIn('article_tags.tag_id', $currentTags)
            ->where('articles.id', '!=', $articleId)
            ->where('articles.published', true) // Only recommend published articles
            ->groupBy('articles.id')
            ->orderByDesc('tag_overlap') // Most similar first
            ->orderByDesc('articles.views') // Break ties with popularity
            ->limit($limit)
            ->get();

        return $similar->toArray();
    }

    /**
     * Get recommendations with detailed scoring.
     * 
     * @param int $articleId Current article
     * @param int $limit Max recommendations
     * @return array Recommendations with scoring details
     */
    public function getSimilarWithScores(int $articleId, int $limit = 5): array
    {
        $recommendations = $this->getSimilarArticles($articleId, $limit);

        foreach ($recommendations as &$article) {
            // Calculate similarity score (0-100)
            $article->similarity_score = min(100, $article->tag_overlap * 20);

            // Add recommendation reason
            $article->reason = sprintf(
                "Shares %d tag(s) with this article",
                $article->tag_overlap
            );
        }

        return $recommendations;
    }
}

// Example usage:
// $recommender = new ArticleRecommender();
// $similar = $recommender->getSimilarWithScores(articleId: 42, limit: 5);
// foreach ($similar as $article) {
//     echo "{$article->title} (Score: {$article->similarity_score})\n";
// }
