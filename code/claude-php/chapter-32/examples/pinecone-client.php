<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\PineconeClient;

echo "=== Pinecone Vector Database Example ===\n\n";

// Mock example - in production, use real API credentials
echo "Pinecone client initialized (mock mode)\n";
echo "Example operations:\n";
echo "- Upsert vectors\n";
echo "- Query similar vectors\n";
echo "- Delete vectors\n";
echo "- Get index stats\n";
