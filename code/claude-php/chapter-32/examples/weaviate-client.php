<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\WeaviateClient;

echo "=== Weaviate Vector Database Example ===\n\n";

echo "Weaviate client initialized (mock mode)\n";
echo "Example operations:\n";
echo "- Create objects with vectors\n";
echo "- Semantic search\n";
echo "- Delete objects\n";
