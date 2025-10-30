#!/bin/bash

# Script to scale worker processes based on queue depth

set -e

WORKER_COUNT=${1:-2}

if [ "$WORKER_COUNT" -lt 1 ] || [ "$WORKER_COUNT" -gt 20 ]; then
    echo "Error: Worker count must be between 1 and 20"
    exit 1
fi

echo "Scaling workers to $WORKER_COUNT..."

docker compose -f docker-compose.yml -f docker-compose.prod.yml up -d --scale worker=$WORKER_COUNT

echo "✓ Scaled to $WORKER_COUNT workers"

# Show current workers
echo ""
echo "Current worker status:"
docker compose ps worker


