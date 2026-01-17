#!/bin/bash

# Python Environment Verification Script
# Checks Python version and packages

echo "Python Environment Verification"
echo "================================"
echo ""

# Check Python version
echo "1. Python Version:"
if command -v python3 &> /dev/null; then
    PYTHON_VERSION=$(python3 --version 2>&1 | awk '{print $2}')
    echo "   ✓ Python $PYTHON_VERSION"
    
    # Check if version is 3.10+
    MAJOR=$(echo $PYTHON_VERSION | cut -d. -f1)
    MINOR=$(echo $PYTHON_VERSION | cut -d. -f2)
    
    if [ "$MAJOR" -ge 3 ] && [ "$MINOR" -ge 10 ]; then
        echo "   ✓ Version meets requirement (3.10+)"
    else
        echo "   ⚠️  Python 3.10+ recommended"
    fi
else
    echo "   ✗ Python 3 not found"
    echo "   Install from: https://www.python.org/downloads/"
    exit 1
fi

echo ""

# Check virtual environment
echo "2. Virtual Environment:"
if [ -d "venv" ]; then
    echo "   ✓ Virtual environment exists"
    
    # Check if activated
    if [ -n "$VIRTUAL_ENV" ]; then
        echo "   ✓ Virtual environment activated"
    else
        echo "   ⚠️  Activate with: source venv/bin/activate"
    fi
else
    echo "   ✗ Virtual environment not found"
    echo "   Create with: python3 -m venv venv"
fi

echo ""

# Check packages
echo "3. Required Packages:"
PACKAGES=("pandas" "numpy" "scikit-learn" "jupyter")

for package in "${PACKAGES[@]}"; do
    if python3 -c "import $package" 2>/dev/null; then
        VERSION=$(python3 -c "import $package; print($package.__version__)" 2>/dev/null)
        echo "   ✓ $package ($VERSION)"
    else
        echo "   ✗ $package not installed"
        echo "   Install with: pip install $package"
    fi
done

echo ""

# Check pip
echo "4. Package Manager:"
if command -v pip &> /dev/null || command -v pip3 &> /dev/null; then
    PIP_VERSION=$(pip3 --version 2>&1 | awk '{print $2}')
    echo "   ✓ pip $PIP_VERSION"
else
    echo "   ✗ pip not found"
fi

echo ""

# Summary
echo "Summary:"
if command -v python3 &> /dev/null && python3 -c "import pandas, numpy, sklearn" 2>/dev/null; then
    echo "✓ Python environment is ready for data science work!"
else
    echo "✗ Some packages need to be installed."
    echo ""
    echo "Quick setup:"
    echo "  python3 -m venv venv"
    echo "  source venv/bin/activate"
    echo "  pip install pandas numpy scikit-learn jupyter"
    exit 1
fi


