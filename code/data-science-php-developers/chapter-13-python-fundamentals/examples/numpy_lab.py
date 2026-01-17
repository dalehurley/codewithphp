"""
NumPy Fundamentals Lab
Learn NumPy arrays and vectorized operations from a PHP perspective
"""
import numpy as np
import time

# 1. Creating Arrays
# PHP: $arr = range(1, 5);
arr = np.array([1, 2, 3, 4, 5])
print(f"NumPy Array: {arr}")
print(f"Data type: {arr.dtype}")  # Int64 or similar

# 2. Vectorized Operations (The "Magic")
# PHP: $multiplied = array_map(fn($x) => $x * 10, $arr);
multiplied = arr * 10
print(f"Multiplied (* 10): {multiplied}")

added = arr + 5
print(f"Added (+ 5): {added}")

# 3. Indexing and Slicing (Powerful!)
# PHP: array_slice($arr, 1, 2);
print(f"\nFirst 3 elements: {arr[:3]}")  # [1 2 3]
print(f"Last 2 elements: {arr[-2:]}")  # [4 5]
print(f"Elements from index 1 to 3: {arr[1:4]}")  # [2 3 4]

# 4. Multi-Dimensional Arrays (Matrices)
matrix = np.array([
    [1, 2, 3],
    [4, 5, 6]
])
print(f"\nMatrix:\n{matrix}")
print(f"Matrix Shape: {matrix.shape}")  # (2, 3)
print(f"Sum of columns: {matrix.sum(axis=0)}")  # [5 7 9]
print(f"Mean of matrix: {matrix.mean()}")

# 5. Masking (Conditional selection)
# PHP: array_filter logic
mask = arr > 3
print(f"\nMask: {mask}")  # [False False False  True  True]
print(f"Values > 3: {arr[mask]}")  # [4 5]

# 6. Performance Comparison: PHP-style Loop vs NumPy
size = 1_000_000
data = list(range(size))  # Standard Python list (like PHP array)
np_data = np.array(data)  # NumPy array

# Standard loop speed
start = time.time()
loop_result = [x * 2 for x in data]
print(f"\nStandard Loop time: {(time.time() - start)*1000:.2f}ms")

# NumPy vectorized speed
start = time.time()
np_result = np_data * 2
print(f"NumPy Vectorized time: {(time.time() - start)*1000:.2f}ms")
