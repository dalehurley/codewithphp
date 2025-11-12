#!/bin/bash
#
# Cleanup script for Chapter 12 - Deep Learning with TensorFlow and PHP
#
# Removes temporary files, caches, and test images created during examples.
#

set -e

echo "Chapter 12 Cleanup Utility"
echo "=========================="
echo ""

# Color codes for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Counters
FILES_REMOVED=0
DIRS_REMOVED=0
SPACE_FREED=0

# Function to calculate directory size
get_dir_size() {
    if [ -d "$1" ]; then
        du -sk "$1" 2>/dev/null | cut -f1
    else
        echo "0"
    fi
}

# Confirmation prompt
echo "This script will remove:"
echo "  - Temporary prediction files in /tmp"
echo "  - Cache directories"
echo "  - Test images created by examples"
echo "  - Uploaded images from web interface"
echo ""
echo -e "${YELLOW}Note: Downloaded models in /tmp/mobilenet will NOT be removed${NC}"
echo ""
read -p "Continue? (y/n) " -n 1 -r
echo ""

if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    echo "Cleanup cancelled"
    exit 0
fi

echo ""
echo "Starting cleanup..."
echo ""

# 1. Remove temporary prediction files
echo "Cleaning temporary prediction files..."
if [ -d "/tmp" ]; then
    BEFORE=$(get_dir_size "/tmp")
    
    # Remove test images
    rm -f /tmp/test_*.jpg 2>/dev/null && FILES_REMOVED=$((FILES_REMOVED + $(ls /tmp/test_*.jpg 2>/dev/null | wc -l)))
    rm -f /tmp/uploaded_*.jpg 2>/dev/null && FILES_REMOVED=$((FILES_REMOVED + $(ls /tmp/uploaded_*.jpg 2>/dev/null | wc -l)))
    
    AFTER=$(get_dir_size "/tmp")
    FREED=$((BEFORE - AFTER))
    SPACE_FREED=$((SPACE_FREED + FREED))
    
    echo -e "${GREEN}✓${NC} Removed temporary test images"
fi

# 2. Remove cache directories
echo "Cleaning cache directories..."

# Predictions cache
if [ -d "/tmp/predictions_cache" ]; then
    BEFORE=$(get_dir_size "/tmp/predictions_cache")
    rm -rf /tmp/predictions_cache
    DIRS_REMOVED=$((DIRS_REMOVED + 1))
    SPACE_FREED=$((SPACE_FREED + BEFORE))
    echo -e "${GREEN}✓${NC} Removed /tmp/predictions_cache"
fi

# Chapter 12 specific caches
if [ -d "/tmp/ch12_cache_test" ]; then
    BEFORE=$(get_dir_size "/tmp/ch12_cache_test")
    rm -rf /tmp/ch12_cache_test
    DIRS_REMOVED=$((DIRS_REMOVED + 1))
    SPACE_FREED=$((SPACE_FREED + BEFORE))
    echo -e "${GREEN}✓${NC} Removed /tmp/ch12_cache_test"
fi

if [ -d "/tmp/ch12_cache_images" ]; then
    BEFORE=$(get_dir_size "/tmp/ch12_cache_images")
    rm -rf /tmp/ch12_cache_images
    DIRS_REMOVED=$((DIRS_REMOVED + 1))
    SPACE_FREED=$((SPACE_FREED + BEFORE))
    echo -e "${GREEN}✓${NC} Removed /tmp/ch12_cache_images"
fi

# 3. Remove test images in sample_images directory
echo "Cleaning test images in data/sample_images..."
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
SAMPLE_DIR="$SCRIPT_DIR/data/sample_images"

if [ -d "$SAMPLE_DIR" ]; then
    # Remove test-generated images (but keep the 6 sample images)
    find "$SAMPLE_DIR" -name "test_*.jpg" -delete 2>/dev/null && echo -e "${GREEN}✓${NC} Removed test_*.jpg"
    find "$SAMPLE_DIR" -name "*_test*.jpg" -delete 2>/dev/null && echo -e "${GREEN}✓${NC} Removed *_test*.jpg"
    find "$SAMPLE_DIR" -name "uploaded_*.jpg" -delete 2>/dev/null && echo -e "${GREEN}✓${NC} Removed uploaded_*.jpg"
    find "$SAMPLE_DIR" -name "dup_*.jpg" -delete 2>/dev/null && echo -e "${GREEN}✓${NC} Removed dup_*.jpg"
fi

# 4. Clean Python cache
echo "Cleaning Python cache..."
if [ -d "$SCRIPT_DIR/__pycache__" ]; then
    rm -rf "$SCRIPT_DIR/__pycache__"
    echo -e "${GREEN}✓${NC} Removed __pycache__"
fi

find "$SCRIPT_DIR" -name "*.pyc" -delete 2>/dev/null && echo -e "${GREEN}✓${NC} Removed .pyc files"

# 5. Optional: Remove downloaded models
echo ""
echo -e "${YELLOW}Optional: Remove downloaded models?${NC}"
echo "  This will delete /tmp/mobilenet (you'll need to re-download)"
echo "  Frees approximately 14 MB"
read -p "Remove models? (y/n) " -n 1 -r
echo ""

if [[ $REPLY =~ ^[Yy]$ ]]; then
    if [ -d "/tmp/mobilenet" ]; then
        BEFORE=$(get_dir_size "/tmp/mobilenet")
        rm -rf /tmp/mobilenet
        DIRS_REMOVED=$((DIRS_REMOVED + 1))
        SPACE_FREED=$((SPACE_FREED + BEFORE))
        echo -e "${GREEN}✓${NC} Removed /tmp/mobilenet"
    fi
    
    if [ -d "/tmp/resnet50" ]; then
        BEFORE=$(get_dir_size "/tmp/resnet50")
        rm -rf /tmp/resnet50
        DIRS_REMOVED=$((DIRS_REMOVED + 1))
        SPACE_FREED=$((SPACE_FREED + BEFORE))
        echo -e "${GREEN}✓${NC} Removed /tmp/resnet50"
    fi
fi

# Summary
echo ""
echo "Cleanup Summary"
echo "==============="
echo -e "Files removed: ${GREEN}$FILES_REMOVED${NC}"
echo -e "Directories removed: ${GREEN}$DIRS_REMOVED${NC}"
echo -e "Disk space freed: ${GREEN}$(($SPACE_FREED / 1024)) MB${NC}"
echo ""
echo -e "${GREEN}✓ Cleanup complete!${NC}"

