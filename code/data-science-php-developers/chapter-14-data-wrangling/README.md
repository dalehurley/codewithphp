# Chapter 14: Data Wrangling with pandas and NumPy

Complete code examples for Chapter 14, demonstrating high-performance data manipulation with pandas.

## Prerequisites

- Python 3.10+
- pandas, NumPy installed
- ~2GB RAM for large dataset examples

## Setup

```bash
# Create and activate virtual environment
python3 -m venv venv
source venv/bin/activate  # On Windows: venv\Scripts\activate

# Install dependencies
pip install -r requirements.txt
```

## Examples

### 1. Performance Benchmarks

Compare pandas vectorized operations vs PHP-style loops:

```bash
python3 examples/performance_benchmarks.py
```

**Key Findings:**
- Vectorized operations: 10-100x faster than loops
- pandas groupby: 5-10x faster than manual grouping  
- Never use `iterrows()` in production!

### 2. Messy Dataset Cleaning

Real-world data cleaning with common issues:

```bash
python3 examples/messy_dataset_cleaning.py
```

**Demonstrates:**
- Email validation and normalization
- Outlier detection and capping
- Missing data strategies
- Date parsing and validation
- Categorical standardization

### 3. Memory Profiling

Optimize memory usage for large datasets:

```bash
python3 examples/memory_profiling.py
```

**Techniques:**
- Categorical dtypes (40-90% reduction)
- Numeric downcasting
- Chunked reading
- Selective column loading

### 4. PHP Integration

```bash
php examples/php_data_service.php
```

## Performance Comparison: pandas vs PHP

| Operation | PHP (1M rows) | pandas (1M rows) | Speedup |
|-----------|---------------|------------------|---------|
| Filter & transform | 5,000ms | 50ms | 100x |
| GroupBy aggregation | 2,000ms | 150ms | 13x |
| Join two tables | 8,000ms | 200ms | 40x |
| String cleaning | 3,000ms | 80ms | 37x |

## When to Use pandas vs PHP

### Use PHP When:
- Dataset < 10,000 rows
- Simple SQL-like operations
- Real-time web requests
- Existing PHP infrastructure

### Use pandas When:
- Dataset > 100,000 rows
- Complex transformations
- Statistical analysis
- Machine learning pipelines
- Batch processing

## Common pandas Operations

### Filtering
```python
# PHP: array_filter($data, fn($x) => $x['age'] > 25 && $x['city'] == 'NYC')
filtered = df[(df['age'] > 25) & (df['city'] == 'NYC')]
```

### Grouping
```python
# PHP: Complex nested loops with array_reduce
grouped = df.groupby('category').agg({
    'sales': ['sum', 'mean'],
    'quantity': 'count'
})
```

### Joining
```python
# PHP: Nested loops with lookup arrays
merged = df1.merge(df2, on='id', how='left')
```

### Time Series
```python
# PHP: Manual date grouping
daily = df.resample('D').sum()  # Group by day
```

## Memory Optimization Tips

1. **Convert to categorical:**
```python
df['category'] = df['category'].astype('category')  # 40-90% reduction
```

2. **Downcast numerics:**
```python
df['id'] = pd.to_numeric(df['id'], downcast='integer')
```

3. **Read in chunks:**
```python
for chunk in pd.read_csv('huge.csv', chunksize=100000):
    process(chunk)
```

4. **Use Parquet:**
```python
df.to_parquet('data.parquet')  # 10x smaller, 5x faster than CSV
```

## Troubleshooting

### SettingWithCopyWarning

**Problem:** Modifying a slice of a DataFrame

**Solution:** Use `.copy()` or `.loc[]`

```python
# ❌ BAD
subset = df[df['age'] > 25]
subset['new_col'] = 1

# ✅ GOOD
subset = df[df['age'] > 25].copy()
subset['new_col'] = 1

# ✅ BETTER
df.loc[df['age'] > 25, 'new_col'] = 1
```

### Memory Errors

**Problem:** Dataset larger than RAM

**Solutions:**
1. Process in chunks
2. Use Dask (Chapter 19)
3. Filter early
4. Use categorical dtypes
5. Load only necessary columns

### Slow Performance

**Problem:** Operations taking too long

**Solutions:**
1. Avoid `iterrows()` - use vectorized operations
2. Use `query()` for complex filters
3. Use `eval()` for expressions
4. Consider Polars (Chapter 19) for multi-threading

## PHP Integration Patterns

### Pattern 1: Batch Processing

```php
// PHP triggers pandas job
$service = new DataWranglingService();
$result = $service->cleanDataset('raw.csv', 'clean.csv');

// Continue with cleaned data
$cleanData = readCleanedData('clean.csv');
```

### Pattern 2: Streaming

```python
# Python processes in chunks, writes results
for chunk in pd.read_csv('input.csv', chunksize=10000):
    cleaned = clean(chunk)
    cleaned.to_csv('output.csv', mode='a', index=False)
```

```php
// PHP reads results as they're ready
$handle = fopen('output.csv', 'r');
while ($row = fgetcsv($handle)) {
    processRow($row);
}
```

### Pattern 3: API Service

```python
# Flask API for pandas operations
@app.route('/analyze', methods=['POST'])
def analyze():
    df = pd.read_json(request.json)
    result = df.groupby('category').mean()
    return result.to_json()
```

```php
// PHP calls API
$response = $client->post('http://pandas-service/analyze', [
    'json' => $data
]);
```

## Best Practices

1. ✅ **Always use vectorized operations**
2. ✅ **Check data types** - wrong types kill performance
3. ✅ **Handle missing data explicitly** - don't let NaN break math
4. ✅ **Use categorical** for string columns with low cardinality
5. ✅ **Chain operations** to minimize intermediate DataFrames
6. ✅ **Use `.loc[]` and `.iloc[]`** for clarity
7. ✅ **Profile before optimizing** - measure first!

## Anti-Patterns

1. ❌ **Never use iterrows()** - 100x slower than vectorized
2. ❌ **Don't create unnecessary copies**
3. ❌ **Avoid growing DataFrames in loops** - create from dict instead
4. ❌ **Don't use apply() for simple math** - vectorize it
5. ❌ **Don't forget to set index** for time series

## Further Reading

- [pandas Documentation: User Guide](https://pandas.pydata.org/docs/user_guide/)
- [Python for Data Analysis (3rd Edition)](https://wesmckinney.com/book/)
- [pandas Performance Tips](https://pandas.pydata.org/docs/user_guide/enhancingperf.html)

## Next Steps

- Complete exercises in Chapter 14
- Experiment with your own datasets
- Try converting a PHP data processing script to pandas
- Move on to Chapter 15: Statistical Analysis
