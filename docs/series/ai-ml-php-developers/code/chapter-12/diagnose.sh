#!/bin/bash
#
# Diagnostic tool for Chapter 12 - Deep Learning with TensorFlow and PHP
#
# Performs comprehensive checks and generates a detailed diagnostic report.
#

set -e

# Color codes
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

REPORT_FILE="diagnostic-report-$(date +%Y%m%d-%H%M%S).txt"

echo "Chapter 12 Diagnostic Tool"
echo "==========================="
echo ""
echo "Generating diagnostic report: $REPORT_FILE"
echo ""

# Start report
{
    echo "Chapter 12 Diagnostic Report"
    echo "============================"
    echo "Generated: $(date)"
    echo "Hostname: $(hostname)"
    echo "OS: $(uname -s) $(uname -r)"
    echo ""
} > "$REPORT_FILE"

# Function to check and report
check_and_report() {
    local section="$1"
    local command="$2"
    
    echo -e "${CYAN}Checking $section...${NC}"
    {
        echo "================"
        echo "$section"
        echo "================"
        echo ""
    } >> "$REPORT_FILE"
    
    if eval "$command" >> "$REPORT_FILE" 2>&1; then
        echo -e "  ${GREEN}✓${NC} $section OK"
        echo "" >> "$REPORT_FILE"
        return 0
    else
        echo -e "  ${RED}✗${NC} $section FAILED"
        echo "" >> "$REPORT_FILE"
        return 1
    fi
}

# 1. System Information
echo -e "${BLUE}[1/12] System Information${NC}"
check_and_report "System Info" "
    echo 'Hostname: $(hostname)'
    echo 'OS: $(uname -a)'
    echo 'Uptime: $(uptime)'
    echo 'Date: $(date)'
"

# 2. PHP Version and Extensions
echo -e "${BLUE}[2/12] PHP Configuration${NC}"
check_and_report "PHP Version" "php -v"
check_and_report "PHP Modules" "php -m"
check_and_report "PHP Configuration" "php -i | head -50"

# 3. Python and TensorFlow
echo -e "${BLUE}[3/12] Python Environment${NC}"
check_and_report "Python Version" "python3 --version"
check_and_report "Python Packages" "python3 -m pip list 2>/dev/null || echo 'pip not available'"
check_and_report "TensorFlow Version" "python3 -c 'import tensorflow as tf; print(f\"TensorFlow {tf.__version__}\")' 2>&1"

# 4. Docker Status
echo -e "${BLUE}[4/12] Docker Environment${NC}"
check_and_report "Docker Version" "docker --version"
check_and_report "Docker Info" "docker info 2>&1 | head -30"
check_and_report "Docker Containers" "docker ps -a"
check_and_report "Docker Images" "docker images | grep tensorflow"

# 5. TensorFlow Serving Status
echo -e "${BLUE}[5/12] TensorFlow Serving${NC}"
if docker ps | grep -q tensorflow_serving; then
    check_and_report "Container Logs (last 50 lines)" "docker logs tensorflow_serving --tail 50"
    check_and_report "Container Stats" "docker stats tensorflow_serving --no-stream"
    check_and_report "Model Status" "curl -s http://localhost:8501/v1/models/mobilenet"
else
    {
        echo "TensorFlow Serving is not running"
        echo ""
    } >> "$REPORT_FILE"
    echo -e "  ${YELLOW}⚠${NC} TensorFlow Serving not running"
fi

# 6. Network and Ports
echo -e "${BLUE}[6/12] Network Configuration${NC}"
check_and_report "Port 8501 (REST API)" "lsof -i:8501 || echo 'Port not in use'"
check_and_report "Port 8500 (gRPC)" "lsof -i:8500 || echo 'Port not in use'"
check_and_report "localhost connectivity" "curl -s -o /dev/null -w '%{http_code}' http://localhost:8501/v1/models/mobilenet || echo 'Cannot connect'"

# 7. Model Files
echo -e "${BLUE}[7/12] Model Files${NC}"
check_and_report "Model Directory Structure" "
    if [ -d /tmp/mobilenet ]; then
        echo 'Model directory: /tmp/mobilenet'
        ls -lah /tmp/mobilenet/
        echo ''
        if [ -d /tmp/mobilenet/1 ]; then
            echo 'Version 1 directory:'
            ls -lah /tmp/mobilenet/1/
            echo ''
            if [ -f /tmp/mobilenet/1/saved_model.pb ]; then
                echo 'SavedModel file size: $(du -h /tmp/mobilenet/1/saved_model.pb | cut -f1)'
            fi
        fi
    else
        echo 'Model directory not found: /tmp/mobilenet'
    fi
"

# 8. Data Files
echo -e "${BLUE}[8/12] Data Files${NC}"
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
check_and_report "Data Directory Structure" "
    echo 'Script directory: $SCRIPT_DIR'
    echo ''
    if [ -f '$SCRIPT_DIR/data/imagenet_labels.json' ]; then
        echo 'ImageNet labels: EXISTS'
        LABEL_COUNT=\$(cat '$SCRIPT_DIR/data/imagenet_labels.json' | grep -o '\"' | wc -l)
        echo 'Label count: \$((LABEL_COUNT / 2))'
    else
        echo 'ImageNet labels: MISSING'
    fi
    echo ''
    if [ -d '$SCRIPT_DIR/data/sample_images' ]; then
        echo 'Sample images directory: EXISTS'
        SAMPLE_COUNT=\$(ls '$SCRIPT_DIR/data/sample_images'/*.jpg 2>/dev/null | wc -l)
        echo 'Sample images: \$SAMPLE_COUNT'
        ls -lh '$SCRIPT_DIR/data/sample_images'/*.jpg 2>/dev/null || echo 'No images found'
    else
        echo 'Sample images directory: MISSING'
    fi
"

# 9. PHP Code Files
echo -e "${BLUE}[9/12] PHP Code Files${NC}"
check_and_report "PHP Example Files" "
    cd '$SCRIPT_DIR'
    echo 'Example files:'
    for file in 0*.php; do
        if [ -f \"\$file\" ]; then
            echo \"  \$file - \$(wc -l < \"\$file\") lines\"
        fi
    done
    echo ''
    echo 'Syntax check (first 3 files):'
    for file in 01-simple-prediction.php 02-tensorflow-client.php 03-image-preprocessor.php; do
        if [ -f \"\$file\" ]; then
            php -l \"\$file\" 2>&1 | grep -v '^No syntax errors'
        fi
    done
"

# 10. Disk Space
echo -e "${BLUE}[10/12] Disk Space${NC}"
check_and_report "Disk Usage" "
    df -h | grep -E '(Filesystem|/$|/tmp)'
    echo ''
    echo 'Model directory size:'
    du -sh /tmp/mobilenet 2>/dev/null || echo 'Model not downloaded'
    echo ''
    echo 'Cache directories:'
    du -sh /tmp/*cache* 2>/dev/null || echo 'No cache directories'
"

# 11. Recent Errors
echo -e "${BLUE}[11/12] Recent Errors${NC}"
check_and_report "Docker Logs (errors)" "
    if docker ps | grep -q tensorflow_serving; then
        echo 'Recent errors in TensorFlow Serving logs:'
        docker logs tensorflow_serving 2>&1 | grep -i error | tail -20 || echo 'No recent errors'
    else
        echo 'TensorFlow Serving not running'
    fi
"

# 12. Test Prediction
echo -e "${BLUE}[12/12] Test Prediction${NC}"
if docker ps | grep -q tensorflow_serving && [ -f "$SCRIPT_DIR/02-tensorflow-client.php" ]; then
    check_and_report "Simple Prediction Test" "
        cd '$SCRIPT_DIR'
        timeout 30 php 02-tensorflow-client.php 2>&1
    "
else
    {
        echo "Cannot run prediction test - prerequisites not met"
        echo ""
    } >> "$REPORT_FILE"
    echo -e "  ${YELLOW}⚠${NC} Prediction test skipped"
fi

# Summary
echo ""
echo -e "${GREEN}Diagnostic report generated: $REPORT_FILE${NC}"
echo ""
echo "Report contents:"
wc -l "$REPORT_FILE"
echo ""
echo "Common issues to check in the report:"
echo "  1. PHP GD extension missing"
echo "  2. Docker not running"
echo "  3. TensorFlow Serving port conflicts"
echo "  4. Model files not downloaded"
echo "  5. Insufficient disk space"
echo ""
echo "To view the report:"
echo "  less $REPORT_FILE"
echo "  cat $REPORT_FILE"
echo ""

