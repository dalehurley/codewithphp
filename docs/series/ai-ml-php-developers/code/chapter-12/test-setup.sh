#!/bin/bash
#
# Complete setup validation for Chapter 12 - Deep Learning with TensorFlow and PHP
#
# Tests all prerequisites and runs a simple prediction to verify everything works.
#

set -e

# Color codes
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Test results
TESTS_PASSED=0
TESTS_FAILED=0
FAILURES=()

echo "Chapter 12 Setup Validation"
echo "============================"
echo ""

# Helper function to run tests
run_test() {
    local test_name="$1"
    local test_command="$2"
    
    echo -n "Testing $test_name... "
    
    if eval "$test_command" > /dev/null 2>&1; then
        echo -e "${GREEN}✓ PASS${NC}"
        TESTS_PASSED=$((TESTS_PASSED + 1))
        return 0
    else
        echo -e "${RED}✗ FAIL${NC}"
        TESTS_FAILED=$((TESTS_FAILED + 1))
        FAILURES+=("$test_name")
        return 1
    fi
}

# 1. Check PHP
echo -e "${BLUE}[1/8] Checking PHP...${NC}"
run_test "PHP installed" "command -v php"

if command -v php > /dev/null 2>&1; then
    PHP_VERSION=$(php -v | head -n 1 | cut -d ' ' -f 2)
    echo "      PHP version: $PHP_VERSION"
    
    run_test "PHP 8.4+" "php -r 'exit(version_compare(PHP_VERSION, \"8.4.0\", \">=\") ? 0 : 1);'"
    run_test "GD extension" "php -m | grep -q gd"
    run_test "cURL extension" "php -m | grep -q curl"
    run_test "JSON extension" "php -m | grep -q json"
fi
echo ""

# 2. Check Python
echo -e "${BLUE}[2/8] Checking Python...${NC}"
run_test "Python 3 installed" "command -v python3"

if command -v python3 > /dev/null 2>&1; then
    PYTHON_VERSION=$(python3 --version 2>&1 | cut -d ' ' -f 2)
    echo "      Python version: $PYTHON_VERSION"
    
    run_test "TensorFlow installed" "python3 -c 'import tensorflow' 2>/dev/null"
fi
echo ""

# 3. Check Docker
echo -e "${BLUE}[3/8] Checking Docker...${NC}"
run_test "Docker installed" "command -v docker"

if command -v docker > /dev/null 2>&1; then
    DOCKER_VERSION=$(docker --version | cut -d ' ' -f 3 | tr -d ',')
    echo "      Docker version: $DOCKER_VERSION"
    
    run_test "Docker running" "docker info"
    run_test "Port 8501 available" "! lsof -i:8501 > /dev/null 2>&1 || docker ps | grep -q tensorflow_serving"
fi
echo ""

# 4. Check model files
echo -e "${BLUE}[4/8] Checking model files...${NC}"
run_test "Model directory exists" "[ -d /tmp/mobilenet/1 ]"

if [ -d /tmp/mobilenet/1 ]; then
    run_test "SavedModel file exists" "[ -f /tmp/mobilenet/1/saved_model.pb ]"
    run_test "Variables directory exists" "[ -d /tmp/mobilenet/1/variables ]"
else
    echo -e "      ${YELLOW}Note: Model not found. Run: python3 download_model.py${NC}"
fi
echo ""

# 5. Check/Start TensorFlow Serving
echo -e "${BLUE}[5/8] Checking TensorFlow Serving...${NC}"

if docker ps | grep -q tensorflow_serving; then
    echo -e "      ${GREEN}TensorFlow Serving already running${NC}"
    TESTS_PASSED=$((TESTS_PASSED + 1))
else
    echo "      TensorFlow Serving not running. Attempting to start..."
    
    if [ -d /tmp/mobilenet/1 ]; then
        ./start_tensorflow_serving.sh > /dev/null 2>&1
        sleep 5
        
        if docker ps | grep -q tensorflow_serving; then
            echo -e "      ${GREEN}✓ Started TensorFlow Serving${NC}"
            TESTS_PASSED=$((TESTS_PASSED + 1))
        else
            echo -e "      ${RED}✗ Failed to start TensorFlow Serving${NC}"
            TESTS_FAILED=$((TESTS_FAILED + 1))
            FAILURES+=("TensorFlow Serving startup")
        fi
    else
        echo -e "      ${RED}✗ Cannot start - model files missing${NC}"
        TESTS_FAILED=$((TESTS_FAILED + 1))
        FAILURES+=("TensorFlow Serving - no model")
    fi
fi
echo ""

# 6. Health check
echo -e "${BLUE}[6/8] Testing TensorFlow Serving health...${NC}"

if docker ps | grep -q tensorflow_serving; then
    # Wait for service to be ready
    echo -n "      Waiting for model to load..."
    for i in {1..10}; do
        if curl -s http://localhost:8501/v1/models/mobilenet | grep -q "AVAILABLE" 2>/dev/null; then
            echo -e " ${GREEN}ready${NC}"
            run_test "Model status AVAILABLE" "curl -s http://localhost:8501/v1/models/mobilenet | grep -q 'AVAILABLE'"
            break
        fi
        sleep 2
        echo -n "."
    done
    
    if [ $i -eq 10 ]; then
        echo -e " ${RED}timeout${NC}"
        TESTS_FAILED=$((TESTS_FAILED + 1))
        FAILURES+=("TensorFlow Serving health check timeout")
    fi
else
    echo -e "      ${YELLOW}Skipped - TensorFlow Serving not running${NC}"
fi
echo ""

# 7. Test data files
echo -e "${BLUE}[7/8] Checking data files...${NC}"
run_test "ImageNet labels exist" "[ -f data/imagenet_labels.json ]"
run_test "Sample images directory" "[ -d data/sample_images ]"

if [ -d data/sample_images ]; then
    SAMPLE_COUNT=$(ls data/sample_images/*.jpg 2>/dev/null | wc -l | tr -d ' ')
    echo "      Sample images: $SAMPLE_COUNT"
    run_test "At least one sample image" "[ $SAMPLE_COUNT -gt 0 ]"
fi
echo ""

# 8. Run simple prediction test
echo -e "${BLUE}[8/8] Running simple prediction test...${NC}"

if docker ps | grep -q tensorflow_serving && [ -f 02-tensorflow-client.php ]; then
    echo "      Executing test prediction..."
    
    if php 02-tensorflow-client.php 2>&1 | grep -q "Prediction successful"; then
        echo -e "      ${GREEN}✓ Prediction test PASSED${NC}"
        TESTS_PASSED=$((TESTS_PASSED + 1))
    else
        echo -e "      ${RED}✗ Prediction test FAILED${NC}"
        TESTS_FAILED=$((TESTS_FAILED + 1))
        FAILURES+=("Simple prediction test")
    fi
else
    echo -e "      ${YELLOW}Skipped - prerequisites not met${NC}"
fi
echo ""

# Summary
echo "=============================="
echo "Test Summary"
echo "=============================="
echo ""
echo -e "Tests passed: ${GREEN}$TESTS_PASSED${NC}"
echo -e "Tests failed: ${RED}$TESTS_FAILED${NC}"
echo ""

if [ $TESTS_FAILED -eq 0 ]; then
    echo -e "${GREEN}✓ All tests passed! Setup is complete.${NC}"
    echo ""
    echo "You can now run the examples:"
    echo "  php 04-image-classifier.php"
    echo "  php 05-batch-predictor.php"
    echo "  php -S localhost:8000 06-web-upload.php"
    echo ""
    exit 0
else
    echo -e "${RED}✗ Some tests failed:${NC}"
    for failure in "${FAILURES[@]}"; do
        echo "  - $failure"
    done
    echo ""
    echo "For detailed diagnostics, run:"
    echo "  ./diagnose.sh"
    echo ""
    exit 1
fi

