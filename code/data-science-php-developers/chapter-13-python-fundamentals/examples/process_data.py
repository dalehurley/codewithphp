"""
Python Data Processing Script
Receives data from PHP via stdin, processes with NumPy, returns JSON
"""
import sys
import json
import numpy as np

# Read from PHP's stdin
input_data = json.load(sys.stdin)
arr = np.array(input_data)

# Perform heavy math
result = {
    "mean": float(arr.mean()),
    "sum": int(arr.sum())
}

# Output to PHP's stdout
print(json.dumps(result))
