<?php

declare(strict_types=1);

namespace App;

class HybridSearchEngine
{
    public function __construct(
        private object $vectorDb,
        private float $semanticWeight = 0.7,
        private float $keywordWeight = 0.3
    ) {}
    
    public function search(string $query, array $queryVector, int $limit = 10): array
    {
        $semanticResults = $this->semanticSearch($queryVector, $limit * 2);
        $keywordResults = $this->keywordSearch($query, $limit * 2);
        
        return $this->mergeAndRank($semanticResults, $keywordResults, $limit);
    }
    
    private function semanticSearch(array $vector, int $limit): array
    {
        return $this->vectorDb->query($vector, $limit);
    }
    
    private function keywordSearch(string $query, int $limit): array
    {
        // Simplified keyword search
        $keywords = explode(' ', strtolower($query));
        return array_map(fn($k) => ['text' => $k, 'score' => 1.0], $keywords);
    }
    
    private function mergeAndRank(array $semantic, array $keyword, int $limit): array
    {
        $merged = [];
        $scores = [];
        
        foreach ($semantic as $result) {
            $id = $result['id'] ?? md5($result['text'] ?? '');
            $scores[$id] = ($result['score'] ?? 1.0) * $this->semanticWeight;
            $merged[$id] = $result;
        }
        
        foreach ($keyword as $result) {
            $id = $result['id'] ?? md5($result['text'] ?? '');
            if (isset($scores[$id])) {
                $scores[$id] += ($result['score'] ?? 1.0) * $this->keywordWeight;
            } else {
                $scores[$id] = ($result['score'] ?? 1.0) * $this->keywordWeight;
                $merged[$id] = $result;
            }
        }
        
        arsort($scores);
        $topIds = array_slice(array_keys($scores), 0, $limit);
        
        return array_map(fn($id) => array_merge(
            $merged[$id],
            ['hybrid_score' => $scores[$id]]
        ), $topIds);
    }
}
