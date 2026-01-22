---
title: "19: Working with Big Data - Dask, Polars, and Distributed Computing"
description: "Handle datasets larger than memory with Dask and Polars for parallel and distributed data processing"
sidebar:
  label: "19: Working with Big Data - Dask, Polars, and Distributed Computing"
  order: 19
  badge:
    text: Advanced
    variant: danger
---

![hero](/images/data-science-php-developers/chapter-19-hero-full.webp)

# Chapter 19: Working with Big Data - Dask, Polars, and Distributed Computing

## Overview

In the previous chapters, you mastered the "Standard Stack": pandas, NumPy, and scikit-learn. These tools are incredible, but they share a common limitation: they are **eager** and **single-core**. They load your entire dataset into RAM before performing any operation.

As a PHP developer, you've likely faced "Out of Memory" errors when trying to `json_decode` a massive file or process millions of DB rows at once. In the data science world, we call this the "RAM Wall." If your dataset is 20GB and your machine has 16GB of RAM, pandas will crash.

In this chapter, we scale up. You will learn to use **Polars** (the modern, lightning-fast, multi-threaded alternative to pandas) and **Dask** (the framework for parallel and distributed computing). We'll move from "In-Memory" processing to "Out-of-Core" processing—where data is streamed from disk and processed in parallel across all your CPU cores, or even multiple servers.

By the end of this chapter, you'll know how to build data pipelines that handle 100GB+ datasets as easily as a small CSV, and you'll learn how to orchestrate these massive jobs from your PHP application.

## Prerequisites

Before starting this chapter, you should have:

- Completed [Chapter 18: Data Visualization](/series/data-science-php-developers/chapters/18-data-visualization-mastery-matplotlib-seaborn-plotly)
- Python 3.11+ (Polars benefits significantly from newer versions)
- `polars`, `dask`, and `distributed` installed
- **Estimated Time**: ~2 hours

**Verify your setup:**

```bash
# Install big data libraries
pip install polars dask distributed pyarrow

# Verify installation
python3 -c "import polars; import dask; print('Big Data stack ready!')"
```

## What You'll Build

By the end of this chapter, you will have created:

- **Polars Performance Suite**: A multi-threaded pipeline that processes 10M+ rows in a fraction of pandas' time.
- **Dask Out-of-Core Analyzer**: A script that analyzes a 50GB dataset (simulated) on a standard laptop.
- **Lazy Data Pipeline**: A "set it and forget it" workflow that only executes when the result is needed.
- **PHP Data Orchestrator**: A PHP service that dispatches big data jobs to a distributed Python cluster.

## Objectives

- Understand the difference between **Eager** and **Lazy** evaluation.
- Master the **Polars Expressions API** for multi-threaded performance.
- Use **Dask DataFrames** to parallelize pandas operations across all CPU cores.
- Implement **Out-of-Core** processing for datasets larger than RAM.
- Scale from a local machine to a **Distributed Cluster**.
- Orchestrate heavy Python processing from **PHP 8.4** applications.

## Step 1: Blazing Fast Data with Polars (~30 min)

### Goal

Learn the Polars API and understand why multi-threading and lazy evaluation make it the new industry favorite.

### Why It Matters

Pandas is written mostly in Python and C, but it is single-threaded. If you have an 8-core CPU, pandas only uses one. **Polars** is written in Rust and is multi-threaded by default. It also uses "Lazy Evaluation"—it looks at your entire query and optimizes it (like a SQL query planner) before running a single calculation.

### Actions

**1. Create a Polars experimentation script:**

```python
# filename: examples/polars_lab.py
import polars as pl
import time

# 1. Generate 1 million rows of synthetic data
num_rows = 1_000_000
df = pl.DataFrame({
    "id": range(num_rows),
    "category": ["A", "B", "C", "D"] * (num_rows // 4),
    "value": [i * 0.1 for i in range(num_rows)]
})

# 2. Eager execution (like pandas)
start = time.time()
result_eager = df.filter(pl.col("category") == "A").group_by("category").agg(pl.col("value").sum())
print(f"Eager execution time: {time.time() - start:.4f}s")

# 3. Lazy execution (The Polars secret sauce)
# .lazy() starts a recording session. No data is processed yet.
start = time.time()
lazy_query = (
    df.lazy()
    .filter(pl.col("category") == "A")
    .with_columns((pl.col("value") * 2).alias("value_doubled"))
    .group_by("category")
    .agg([
        pl.col("value").mean().alias("avg_value"),
        pl.col("value_doubled").sum().alias("total_doubled")
    ])
)

# .collect() triggers the optimized execution
result_lazy = lazy_query.collect()
print(f"Lazy execution time (with optimization): {time.time() - start:.4f}s")
print(result_lazy)
```

**2. Run the script:**

```bash
python3 examples/polars_lab.py
```

### Expected Result

```
Eager execution time: 0.0450s
Lazy execution time (with optimization): 0.0120s
shape: (1, 3)
┌──────────┬───────────┬───────────────┐
│ category ┆ avg_value ┆ total_doubled │
│ ---      ┆ ---       ┆ ---           │
│ str      ┆ f64       ┆ f64           │
╞══════════╪═══════════╪═══════════════╡
│ A        ┆ 49999.95  ┆ 2.4999e10     │
└──────────┴───────────┴───────────────┘
```

### Why It Works

- **Expressions**: `pl.col("value").sum()` isn't just a function; it's an expression that Polars can optimize.
- **Lazy Evaluation**: When you call `.lazy()`, Polars builds a **Query Plan**. If you filter for `category == "A"`, Polars will try to do that filter _before_ loading other columns or doing expensive math, saving memory and CPU cycles.
- **Multi-threading**: Polars splits the work across all available CPU cores automatically.

### Troubleshooting

**Problem: `ImportError: Missing optional dependency 'pyarrow'`**
**Cause**: Polars uses Arrow for fast memory layout.
**Solution**: `pip install pyarrow`.

## Step 2: Scaling Beyond RAM with Dask (~30 min)

### Goal

Use Dask to process datasets that are significantly larger than your computer's RAM.

### Why It Matters

If you have a 100GB CSV and 16GB of RAM, Polars (in eager mode) will still crash. **Dask** solves this by breaking the 100GB file into small "chunks" (e.g., 100MB each). It only loads a few chunks into RAM at a time, processes them, and moves on. This is called **Out-of-Core** computing.

### Actions

**1. Create a Dask out-of-core script:**

```python
# filename: examples/dask_out_of_core.py
import dask.dataframe as dd
import pandas as pd
import numpy as np
import os

# 1. Create a "large" simulated dataset (many small CSVs)
os.makedirs("data/big_dataset", exist_ok=True)
for i in range(5):
    temp_df = pd.DataFrame({
        "timestamp": pd.date_range("2025-01-01", periods=100000, freq="S"),
        "sensor_id": np.random.randint(1, 100, 100000),
        "reading": np.random.randn(100000)
    })
    temp_df.to_csv(f"data/big_dataset/part_{i}.csv", index=False)

# 2. Load with Dask (This is instantaneous because it's lazy)
# Notice the wildcard '*' - Dask loads all files as one logical DataFrame
ddf = dd.read_csv("data/big_dataset/part_*.csv", parse_dates=["timestamp"])

# 3. Define the computation
# Find the average reading per sensor
result = ddf.groupby("sensor_id").reading.mean()

# 4. Trigger the computation
# .compute() is like Polars' .collect() - it starts the parallel work
print("Computing average readings across all files...")
final_stats = result.compute()
print(final_stats.head())
```

### Why It Works

- **Logical Partitioning**: Dask treats the `part_*.csv` files as partitions of a single massive table.
- **Parallel Task Graph**: Dask builds a graph of "tasks" (Read File 1, Calculate Mean, Merge with File 2...). It then executes these tasks across your CPU cores.
- **Pandas API**: Dask DataFrames mimic the pandas API almost perfectly, so you don't have to relearn everything.

### Troubleshooting

**Problem: `MemoryError` even with Dask**
**Cause**: You might have a single task that is too large (e.g., a massive `join` or `sort` that requires all data to be in memory at once).
**Solution**: Increase the number of partitions or use `df.repartition(npartitions=...)`.

## Step 3: Distributed Computing & The Dask Cluster (~30 min)

### Goal

Move from local parallel processing to a distributed cluster.

### Why It Matters

When one machine isn't enough, you need a **Cluster**. A Dask cluster consists of:

1. **The Scheduler**: Manages the task graph.
2. **Workers**: Perform the actual computations.
3. **The Client**: Your script, which sends tasks to the scheduler.

### Actions

**1. Set up a Local Cluster:**

```python
# filename: examples/dask_cluster.py
from dask.distributed import Client, LocalCluster
import dask.array as da

if __name__ == "__main__":
    # 1. Start a local cluster
    # This automatically detects your CPU cores and RAM
    cluster = LocalCluster()
    client = Client(cluster)

    # 2. View the Dashboard URL (Very important for monitoring!)
    print(f"Dask Dashboard is running at: {client.dashboard_link}")

    # 3. Create a massive random array (distributed across workers)
    # 10,000 x 10,000 array = 100 million elements
    x = da.random.random((10000, 10000), chunks=(1000, 1000))

    # 4. Perform a complex math operation
    y = x + x.T - x.mean(axis=0)

    # 5. Compute the result
    print("Calculating on cluster...")
    result = y.sum().compute()
    print(f"Final Sum: {result}")

    # Keep running to allow viewing the dashboard
    # input("Press Enter to close the cluster...")
```

### Why It Works

- **Dask Dashboard**: This is a beautiful web interface (usually on port 8787) that shows you exactly which worker is busy, how much RAM is being used, and which tasks are running.
- **Data Locality**: Dask tries to move the computation to where the data is, rather than moving the data to the computation, reducing network overhead.

## Step 4: PHP Job Orchestration (~30 min)

### Goal

Trigger and monitor big data Python jobs from your PHP 8.4 application.

### Why It Matters

You shouldn't run a 2-hour Dask job inside a synchronous PHP request. Instead, use PHP to **dispatch** the job and **poll** for completion.

### Actions

**1. Create a Python Worker Script:**

```python
# filename: services/big_data_worker.py
import sys
import json
import polars as pl

def process_logs(input_file, output_file):
    # Use Polars for fast processing
    df = pl.scan_csv(input_file) # .scan_csv() is the lazy version of .read_csv()

    summary = (
        df.group_by("level")
        .agg(pl.count("id").alias("count"))
        .collect()
    )

    summary.write_csv(output_file)
    return {"status": "success", "output": output_file}

if __name__ == "__main__":
    # Simulate receiving arguments from PHP
    args = json.loads(sys.stdin.read())
    result = process_logs(args['input'], args['output'])
    print(json.dumps(result))
```

**2. Create a PHP Orchestrator:**

```php
# filename: examples/php_big_data_orchestrator.php
<?php
declare(strict_types=1);

namespace App\DataScience;

class BigDataJob
{
    public function __construct(
        private string $pythonPath = 'python3',
        private string $workerScript = 'services/big_data_worker.py'
    ) {}

    public function run(string $inputFile, string $outputFile): array
    {
        $payload = json_encode([
            'input' => $inputFile,
            'output' => $outputFile
        ]);

        $descriptorspec = [
            0 => ["pipe", "r"], // stdin
            1 => ["pipe", "w"], // stdout
            2 => ["pipe", "w"]  // stderr
        ];

        $process = proc_open("{$this->pythonPath} {$this->workerScript}", $descriptorspec, $pipes);

        if (is_resource($process)) {
            fwrite($pipes[0], $payload);
            fclose($pipes[0]);

            $stdout = stream_get_contents($pipes[1]);
            $stderr = stream_get_contents($pipes[2]);

            fclose($pipes[1]);
            fclose($pipes[2]);
            $returnValue = proc_close($process);

            if ($returnValue !== 0) {
                throw new \RuntimeException("Python error: " . $stderr);
            }

            return json_decode($stdout, true);
        }

        throw new \RuntimeException("Could not start Python process");
    }
}

// Usage
try {
    $job = new BigDataJob();
    echo "Starting Big Data Job via PHP...\n";
    $result = $job->run('data/logs.csv', 'data/summary.csv');
    echo "Job Complete! Summary saved to: " . $result['output'] . "\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
```

### Why It Works

- **JSON Payload**: We use a structured JSON payload to pass complex arguments (file paths, parameters) between PHP and Python.
- **Error Handling**: We capture `stderr` to ensure that if Python crashes (e.g., File Not Found or Rust panic in Polars), PHP can log the exact error.
- **Scalability**: In a real production environment, you would use a queue (like Laravel Queues or RabbitMQ) to handle these long-running `proc_open` calls.

## Exercises

### Exercise 1: The Polars Speed Demon

**Goal**: Compare pandas and Polars on a medium dataset.

1. Create a CSV with 5 million rows of random data.
2. Write a script that reads the CSV and calculates the group-by mean of one column.
3. Benchmark both pandas and Polars (lazy mode).
4. **Validation**: Polars should be at least 3-5x faster.

### Exercise 2: Out-of-Core Filter

**Goal**: Use Dask to process data that "doesn't exist" yet.

1. Write a Dask script that scans a directory of CSVs.
2. Apply a filter (e.g., `value > 100`).
3. Save the result to a _new_ set of Parquet files using `df.to_parquet('data/output/')`.
4. **Validation**: Check that the output directory contains multiple `.parquet` files.

### Exercise 3: PHP Cluster Monitor

**Goal**: Create a PHP script that checks the status of a Dask cluster.

1. Start a Dask `LocalCluster` in a background Python process.
2. Write a PHP script that pings the Dask Dashboard API (usually `http://localhost:8787/api/v1/status`) using `curl`.
3. Display the number of active workers in your terminal.

## Wrap-up


### What You've Learned

In this chapter, you broke through the "RAM Wall" and learned to handle data at scale:

1. **The Polars Revolution**: Why Rust-backed multi-threading is the future of DataFrames.
2. **Lazy Evaluation**: How to build efficient query plans that only execute when needed.
3. **Out-of-Core Processing**: Using Dask to handle datasets larger than your physical memory.
4. **Parallelism**: Utilizing 100% of your CPU cores instead of just one.
5. **Distributed Thinking**: Moving from a single machine to a cluster of workers.
6. **PHP Integration**: Scaling your web application to handle massive analytical workloads.

### What You've Built

1. **Multi-threaded Data Pipelines**: Blazing fast analytical code.
2. **Out-of-Core Analyzers**: Tools that handle 100GB+ files on standard laptops.
3. **Lazy Execution Graphs**: Optimized data workflows.
4. **PHP Orchestration Layer**: A robust bridge between your web app and your big data workers.

### Key Big Data Principles

**1. Don't Collect until You Must**
In both Polars and Dask, keep your data "Lazy" for as long as possible. Only call `.collect()` or `.compute()` at the very end.

**2. Parquet over CSV**
For big data, CSVs are slow. **Parquet** is a columnar format that is 10-50x faster to read and much smaller on disk. Use `df.to_parquet()`.

**3. Small Chunks, Big Throughput**
Ensure your partitions are small enough to fit in RAM (e.g., 100MB-500MB) but large enough that the overhead of the scheduler doesn't dominate the computation.

**4. Monitor the Dashboard**
The Dask Dashboard is your best friend. If your job is slow, the dashboard will show you the bottleneck (Network, CPU, or Disk).

### Connection to Data Science Workflow

You are now a full-stack data engineer:

1. ✅ **Chapter 1-12**: Built data systems in PHP.
2. ✅ **Chapter 13-18**: Mastered Python, Stats, ML, and Viz.
3. ✅ **Chapter 19**: **Mastered Big Data & Scale** ← You are here
4. ➡️ **Chapter 20**: Final Chapter: **Production ML Systems (MLOps)**.

### Next Steps

**Immediate Practice**:

1. Try converting one of your pandas scripts from Chapter 14 into a Polars lazy script.
2. Install the `dask-labextension` in Jupyter Lab to see your cluster status directly in your notebook.
3. Read the [Polars User Guide](https://docs.pola.rs/)—it's incredibly well-written and covers advanced expressions.

**Chapter 20 Preview**:

In the final chapter, we'll cover **Production ML Systems (MLOps)**. You'll learn:

- Model versioning and experiment tracking.
- Deploying models as Dockerized microservices.
- Monitoring for "Model Drift" (when your AI starts getting dumber).
- CI/CD for data science.

You'll move from "experimenting" to "shipping production AI"!

## Further Reading

- [Polars Official Documentation](https://docs.pola.rs/) — Modern, fast, and Rust-powered.
- [Dask Documentation](https://docs.dask.org/) — The industry standard for distributed Python.
- [Dask Tutorial (GitHub)](https://github.com/dask/dask-tutorial) — Hands-on examples.
- [High Performance Python](https://www.oreilly.com/library/view/high-performance-python/9781492055013/) — Excellent deep dive into scaling Python code.

::: tip Next Chapter
Continue to [Chapter 20: Production ML Systems - MLOps for PHP Developers](/series/data-science-php-developers/chapters/20-production-mlops-php-developers) to learn production ML deployment!
:::
