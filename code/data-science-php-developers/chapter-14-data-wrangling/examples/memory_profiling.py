"""
Memory Profiling for Large Datasets
Demonstrates memory-efficient pandas operations
"""
import pandas as pd
import numpy as np
import sys

def get_memory_usage(df):
    """Calculate memory usage in MB"""
    return df.memory_usage(deep=True).sum() / 1024**2

print("="*60)
print("MEMORY OPTIMIZATION TECHNIQUES")
print("="*60)

# Create a large dataset
print("\nCreating test dataset with 1,000,000 rows...")
size = 1_000_000

df = pd.DataFrame({
    'id': range(size),
    'category': np.random.choice(['A', 'B', 'C', 'D'], size),
    'amount': np.random.uniform(0, 1000, size),
    'status': np.random.choice(['active', 'inactive', 'pending'], size),
    'description': [f'Product description {i}' * 5 for i in range(size)]
})

print(f"\nOriginal memory usage: {get_memory_usage(df):.2f} MB")
print("\nColumn dtypes:")
print(df.dtypes)
print("\nDetailed memory by column:")
print(df.memory_usage(deep=True))

print("\n" + "="*60)
print("OPTIMIZATION 1: Categorical Dtypes")
print("="*60)

df_optimized = df.copy()

# Convert string columns with few unique values to category
print("\nConverting to categorical dtypes...")
for col in ['category', 'status']:
    print(f"  {col}: {df_optimized[col].nunique()} unique values")
    df_optimized[col] = df_optimized[col].astype('category')

print(f"\nMemory after categorical conversion: {get_memory_usage(df_optimized):.2f} MB")
print(f"Savings: {get_memory_usage(df) - get_memory_usage(df_optimized):.2f} MB")

print("\n" + "="*60)
print("OPTIMIZATION 2: Numeric Downcasting")
print("="*60)

# Downcast numeric types
print("\nDowncasting numeric columns...")
df_optimized['id'] = pd.to_numeric(df_optimized['id'], downcast='integer')
df_optimized['amount'] = pd.to_numeric(df_optimized['amount'], downcast='float')

print(f"\nMemory after numeric downcasting: {get_memory_usage(df_optimized):.2f} MB")
print(f"Total savings: {get_memory_usage(df) - get_memory_usage(df_optimized):.2f} MB")
print(f"Reduction: {(1 - get_memory_usage(df_optimized) / get_memory_usage(df)) * 100:.1f}%")

print("\n" + "="*60)
print("OPTIMIZATION 3: Chunked Reading")
print("="*60)

print("\nDemonstrating chunked reading for files larger than RAM...")

# Save dataset to CSV
df.to_csv('data/large_dataset.csv', index=False)

# Read in chunks
chunk_size = 100000
total_amount = 0
row_count = 0

print(f"Reading CSV in chunks of {chunk_size:,} rows...")

for i, chunk in enumerate(pd.read_csv('data/large_dataset.csv', chunksize=chunk_size)):
    total_amount += chunk['amount'].sum()
    row_count += len(chunk)
    print(f"  Processed chunk {i+1}: {len(chunk):,} rows (Total: {row_count:,})")

print(f"\nTotal amount calculated: ${total_amount:,.2f}")
print(f"Total rows processed: {row_count:,}")

print("\n" + "="*60)
print("OPTIMIZATION 4: Selective Column Loading")
print("="*60)

print("\nLoading only needed columns...")
df_selective = pd.read_csv('data/large_dataset.csv', usecols=['id', 'category', 'amount'])

print(f"Full dataset memory: {get_memory_usage(df):.2f} MB")
print(f"Selective columns memory: {get_memory_usage(df_selective):.2f} MB")
print(f"Savings: {(1 - get_memory_usage(df_selective) / get_memory_usage(df)) * 100:.1f}%")

print("\n" + "="*60)
print("OPTIMIZATION 5: Efficient Aggregations")
print("="*60)

print("\nComparing aggregation approaches...")

import time

# Method 1: Multiple passes
start = time.time()
mean_amount = df_optimized['amount'].mean()
sum_amount = df_optimized['amount'].sum()
count_amount = df_optimized['amount'].count()
elapsed_multiple = time.time() - start

# Method 2: Single pass with agg
start = time.time()
stats = df_optimized['amount'].agg(['mean', 'sum', 'count'])
elapsed_single = time.time() - start

print(f"  Multiple passes: {elapsed_multiple*1000:.2f}ms")
print(f"  Single agg() call: {elapsed_single*1000:.2f}ms")
print(f"  Speedup: {elapsed_multiple / elapsed_single:.2f}x")

print("\n" + "="*60)
print("OPTIMIZATION SUMMARY")
print("="*60)

print(f"""
Original DataFrame:
  Rows: {len(df):,}
  Memory: {get_memory_usage(df):.2f} MB

Optimized DataFrame:
  Rows: {len(df_optimized):,}
  Memory: {get_memory_usage(df_optimized):.2f} MB

Total Reduction: {(1 - get_memory_usage(df_optimized) / get_memory_usage(df)) * 100:.1f}%

Best Practices for Large Datasets:
1. ✓ Convert string columns to 'category' dtype
2. ✓ Downcast numeric types (int64 → int32, float64 → float32)
3. ✓ Read files in chunks for datasets larger than RAM
4. ✓ Load only necessary columns with usecols
5. ✓ Use efficient aggregations (single agg() vs multiple calls)
6. ✓ Use Parquet format instead of CSV (10x faster, smaller)
7. ✓ Filter early to reduce data size
8. ✓ Use query() instead of boolean masking for complex filters
9. ✓ Avoid creating unnecessary copies
10. ✓ Use inplace=True when appropriate (carefully!)

For Datasets Larger Than RAM:
- Use Dask (Chapter 19)
- Use Polars (Chapter 19)
- Use chunked processing
- Use database queries to pre-filter
- Consider cloud-based solutions (BigQuery, Snowflake)
""")

# Clean up
import os
if os.path.exists('data/large_dataset.csv'):
    os.remove('data/large_dataset.csv')
    print("\nCleaned up temporary files.")
