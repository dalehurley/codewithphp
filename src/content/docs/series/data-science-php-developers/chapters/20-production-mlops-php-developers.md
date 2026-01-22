---
title: "20: Production ML Systems - MLOps for PHP Developers"
description: "Deploy, monitor, and maintain machine learning models in production with MLOps best practices"
sidebar:
  label: "20: Production ML Systems - MLOps for PHP Developers"
  order: 20
  badge:
    text: Advanced
    variant: danger
---

![hero](/images/data-science-php-developers/chapter-20-hero-full.webp)

# Chapter 20: Production ML Systems - MLOps for PHP Developers

## Overview

Congratulations on reaching the final chapter! You have traveled from basic PHP statistics to deep learning with TensorFlow and processing big data with Polars. But in the real world, building a model is only 20% of the challenge. The remaining 80% is **MLOps**—the practice of reliably deploying, monitoring, and maintaining those models in production.

As a PHP developer, you're used to DevOps: CI/CD, unit testing, and server monitoring. MLOps is the data science equivalent. Models are not static code; they are "living" entities that degrade over time as the world changes (a phenomenon called **Model Drift**). If you deploy a price predictor today and inflation spikes tomorrow, your model will become dangerously inaccurate.

In this chapter, we will bridge the final gap. You will learn how to version your models so you can roll back just like a Git commit, how to containerize your AI services with Docker, and how to build automated retraining pipelines triggered by your PHP application. We are moving from "experimental AI" to "production-grade intelligence."

## Prerequisites

Before starting this chapter, you should have:

- Completed [Chapter 19: Big Data Processing](/series/data-science-php-developers/chapters/19-big-data-dask-polars-distributed)
- Understanding of production deployment from [Chapter 12](/series/data-science-php-developers/chapters/12-deploying-data-science-systems-in-production-with-php)
- Python with `mlflow`, `scikit-learn`, and `docker` installed
- **Estimated Time**: ~2.5 hours

**Verify your setup:**

```bash
# Install MLOps tools
pip install mlflow flask gunicorn

# Verify MLflow
mlflow --version
```

## What You'll Build

By the end of this chapter, you will have created:

- **Experiment Tracker**: A centralized dashboard (MLflow) to track every model version and its accuracy.
- **Dockerized Inference Service**: A scalable container that serves model predictions via a professional Gunicorn server.
- **Drift Monitor**: A PHP-monitored script that detects when model accuracy drops below a threshold.
- **Automated Pipeline**: A PHP-triggered system that collects new production data and triggers a Python retraining job.

## Objectives

- Manage the **ML Lifecycle** from training to retirement.
- Implement **Model Versioning** to ensure reproducibility.
- Build **High-Availability** model serving architectures.
- Detect and handle **Model Drift** in production.
- Implement **A/B Testing** for different model versions.
- Orchestrate **Continuous Training** using PHP 8.4 workers.

## Step 1: Experiment Tracking and Versioning (~30 min)

### Goal

Use **MLflow** to track your training runs and save "Versioned" models that can be easily retrieved by your PHP app.

### Why It Matters

In regular development, you use Git. In ML, you need to track not just the code, but the **Data** and the **Hyperparameters**. If Model V2 performs worse than V1, you need a single command to roll back your production API.

### Actions

**1. Create a versioned training script:**

```python
# filename: examples/versioned_training.py
import mlflow
import mlflow.sklearn
from sklearn.ensemble import RandomForestClassifier
from sklearn.datasets import load_iris
from sklearn.model_selection import train_test_split

# 1. Set the experiment name
mlflow.set_experiment("Customer_Churn_v2")

def train_and_log(n_estimators, max_depth):
    with mlflow.start_run():
        # Load data
        iris = load_iris()
        X_train, X_test, y_train, y_test = train_test_split(iris.data, iris.target, test_size=0.2)

        # 2. Log Parameters
        mlflow.log_param("n_estimators", n_estimators)
        mlflow.log_param("max_depth", max_depth)

        # Train model
        model = RandomForestClassifier(n_estimators=n_estimators, max_depth=max_depth)
        model.fit(X_train, y_train)

        # 3. Log Metrics
        accuracy = model.score(X_test, y_test)
        mlflow.log_metric("accuracy", accuracy)
        print(f"Run complete. Accuracy: {accuracy}")

        # 4. Log Model (The Artifact)
        mlflow.sklearn.log_model(model, "model")

if __name__ == "__main__":
    train_and_log(n_estimators=100, max_depth=5)
    train_and_log(n_estimators=200, max_depth=10)
```

**2. Start the MLflow UI:**

```bash
mlflow ui
```

Open `http://localhost:5000` in your browser. You can now see every run, compare accuracies, and download the model files.

### Why It Works

- **Artifact Tracking**: MLflow saves the exact binary of your model.
- **Reproducibility**: You can see exactly what parameters led to that 98% accuracy.
- **Model Registry**: You can tag a specific run as "Production" or "Staging."

## Step 2: Scalable Serving with Docker (~30 min)

### Goal

Wrap your model in a **Flask** API and containerize it using **Docker** for consistent deployment.

### Actions

**1. Create the Production API:**

```python
# filename: services/model_server.py
from flask import Flask, request, jsonify
import joblib
import pandas as pd
import os

app = Flask(__name__)

# Load the "Production" model
MODEL_PATH = os.getenv('MODEL_PATH', 'models/latest_model.pkl')
model = joblib.load(MODEL_PATH)

@app.route('/predict', methods=['POST'])
def predict():
    data = request.get_json()
    df = pd.DataFrame([data])
    prediction = model.predict(df)
    return jsonify({"prediction": int(prediction[0])})

@app.route('/health', methods=['GET'])
def health():
    return jsonify({"status": "healthy", "model": MODEL_PATH})

if __name__ == "__main__":
    app.run(host='0.0.0.0', port=8000)
```

**2. Create the Dockerfile:**

```dockerfile
# filename: Dockerfile
FROM python:3.11-slim

WORKDIR /app
COPY requirements.txt .
RUN pip install -r requirements.txt

COPY . .

# Use Gunicorn for production-grade concurrency
CMD ["gunicorn", "--bind", "0.0.0.0:8000", "services.model_server:app"]
```

### Why It Works

- **Gunicorn**: Unlike the Flask dev server, Gunicorn uses multiple workers to handle concurrent PHP requests.
- **Portability**: Docker ensures your Python ML environment (versions of NumPy, sklearn, etc.) is identical on your laptop and the production server.

## Step 3: Monitoring Performance and Drift (~30 min)

### Goal

Build a monitoring script that checks if production accuracy is slipping and notifies your PHP application.

### Why It Matters

**Data Drift** occurs when the input data changes (e.g., users start using your app differently). **Concept Drift** occurs when the rules change (e.g., a new law makes your fraud detection obsolete).

### Actions

**1. Create a Drift Detector:**

```python
# filename: services/monitor_drift.py
import numpy as np
from sklearn.metrics import accuracy_score
import json
import sys

def check_drift(y_true, y_pred, threshold=0.85):
    accuracy = accuracy_score(y_true, y_pred)

    status = "OK"
    if accuracy < threshold:
        status = "DRIFT_DETECTED"

    return {
        "accuracy": float(accuracy),
        "status": status,
        "action_required": bool(accuracy < threshold)
    }

if __name__ == "__main__":
    # Simulate receiving production feedback data
    # In a real app, this would come from your DB via PHP
    test_data = json.loads(sys.stdin.read())
    result = check_drift(test_data['true_labels'], test_data['predictions'])
    print(json.dumps(result))
```

### Why It Works

By regularly piping a sample of "Ground Truth" (what actually happened) and "Predictions" (what the AI guessed) into this script, you can catch accuracy drops _before_ they hurt your business revenue.

## Step 4: A/B Testing Models (~30 min)

### Goal

Implement a PHP-side router that splits traffic between two different model versions.

### Actions

**1. Implement the PHP Router:**

```php
# filename: examples/php_ab_router.php
<?php
declare(strict_types=1);

class ModelRouter {
    public function getPrediction(array $data): int {
        // Simple 50/50 split
        $version = (rand(1, 100) <= 50) ? 'v1' : 'v2';

        $url = ($version === 'v1')
            ? 'http://model-v1:8000/predict'
            : 'http://model-v2:8000/predict';

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        $result = json_decode($response, true);

        // Log which version made the prediction for later analysis
        $this->logMetric($version, $result['prediction']);

        return $result['prediction'];
    }

    private function logMetric(string $version, int $pred): void {
        // Log to Prometheus or DB
    }
}
```

### Why It Works

This is a **Canary Deployment**. You can send 5% of traffic to a new, experimental model and 95% to your stable one. If the 5% group sees better business results (higher conversion, lower churn), you slowly increase the traffic to the new model.

## Step 5: Automated Retraining Pipeline (~30 min)

### Goal

Orchestrate a full loop: PHP detects drift → PHP triggers Python retraining → Python updates the model file.

### Actions

**1. The Retraining Orchestrator (PHP):**

```php
# filename: services/RetrainingService.php
<?php
declare(strict_types=1);

namespace App\Services;

class RetrainingService {
    public function monitorAndRetrain(): void {
        // 1. Check current performance
        $perf = $this->getCurrentAccuracy();

        if ($perf < 0.80) {
            echo "Drift detected (Acc: $perf). Triggering retraining...\n";

            // 2. Export new data from SQL to CSV
            $this->exportProductionData('data/new_training_data.csv');

            // 3. Trigger Python Training Job
            exec('python3 examples/versioned_training.py');

            // 4. Update the "latest" model pointer
            // (In a real system, you'd update a symlink or K8s deployment)
            rename('models/model_new.pkl', 'models/latest_model.pkl');

            $this->notifyTeam("Model successfully retrained and updated.");
        }
    }
}
```

### Why It Works

This creates a **Self-Healing AI System**. Your PHP application acts as the "Manager," watching the metrics and calling in the Python "Specialist" to fix the brain whenever it gets outdated.

## Exercises

### Exercise 1: The Rollback Script

**Goal**: Use PHP to roll back to a previous model version.

1. Create a directory `models/archive/` with two files: `v1.pkl` and `v2.pkl`.
2. Write a PHP script that accepts a version number from the command line.
3. Update a `models/active_model.pkl` symlink to point to the requested version.

### Exercise 2: Dashboard integration

**Goal**: Display MLflow metrics in a custom PHP dashboard.

1. Use the [MLflow REST API](https://mlflow.org/docs/latest/rest-api.html) to fetch the latest run's accuracy.
2. Display it as a progress bar in an HTML page.

### Exercise 3: Load Testing the AI

**Goal**: Test your Dockerized model under pressure.

1. Start your Docker container.
2. Use a tool like **Apache Benchmark (ab)** to send 1,000 requests to your prediction endpoint.
3. **Validation**: How many requests per second can your model handle before the PHP client times out?

## Wrap-up


### What You've Learned

In this final chapter, you transformed from a data experimenter to a machine learning engineer:

1.  **Lifecycle Management**: Understanding that models require continuous maintenance.
2.  **Experiment Tracking**: Using MLflow to ensure you never lose a winning model again.
3.  **Production Serving**: Using Docker and Gunicorn to build robust, scalable AI microservices.
4.  **Drift Detection**: Knowing how to catch your AI when it starts making mistakes.
5.  **Hybrid Orchestration**: Using PHP to manage the high-level business logic and Python for the heavy mathematical lifting.

### Series Summary: The Path You've Taken

1.  **Chapters 1-5**: Data fundamentals and the PHP-first mindset.
2.  **Chapters 6-12**: Building real data systems, statistics, and visualizations in PHP.
3.  **Chapters 13-15**: Mastering the Python "Math & Stats" stack.
4.  **Chapters 16-17**: Deep dives into Machine Learning and Deep Learning.
5.  **Chapters 18-19**: Scaling up with Visualization and Big Data.
6.  **Chapter 20**: **Shipping Production AI (MLOps)**.

### Final Key Principles

**1. Trust, but Verify**
Never assume your model is working just because it worked yesterday. Monitor your metrics constantly.

**2. Decouple your Systems**
Keep your Python ML services separate from your PHP web app. This allows them to scale independently and prevents a memory leak in a model from crashing your user's checkout flow.

**3. Version Everything**
Version your code (Git), your data (DVC), and your models (MLflow). Reproducibility is the soul of science.

**4. PHP is your Orchestrator**
Don't feel like you need to "leave" PHP. PHP is world-class at routing, security, and business logic. Use it to command your Python models like a conductor leads an orchestra.

## Further Reading

- [MLflow Documentation](https://mlflow.org/docs/latest/index.html) — The guide to professional experiment tracking.
- [Google's MLOps Whitepaper](https://cloud.google.com/architecture/mlops-continuous-delivery-and-automation-pipelines-in-machine-learning) — The definitive industry standard.
- [Designing Data-Intensive Applications](https://www.oreilly.com/library/view/designing-data-intensive-applications/9781491903063/) — Essential reading for any backend developer.
- [DVC (Data Version Control)](https://dvc.org/) — Managing large datasets like Git.

::: tip Series Complete!
Congratulations! You've completed all 20 chapters of **Data Science for PHP Developers**. You now possess a rare and powerful skillset: the ability to build massive web applications in PHP and power them with cutting-edge data science in Python. Go forth and build something intelligent!
:::
