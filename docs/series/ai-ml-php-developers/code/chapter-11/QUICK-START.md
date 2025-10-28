# Chapter 11 Quick Start Guide

Get up and running with PHP-Python integration in 5 minutes!

## Prerequisites Check

```bash
# Check versions
php --version    # Need 8.4+
python3 --version # Need 3.10+

# Install Python packages
pip install pandas scikit-learn joblib flask
```

## 1. Test Basic Integration (2 min)

```bash
cd 01-simple-shell
php hello.php
```

✅ You should see: "Hello, PHP Developer!" and "✅ Integration working successfully!"

## 2. Try Data Exchange (2 min)

```bash
cd ../02-data-passing
php exchange.php
```

✅ You should see user segmentation results with VIP/Regular/New classifications.

## 3. Run Sentiment Analyzer (5 min)

```bash
cd ../03-sentiment-analysis
php analyze.php
```

✅ First run trains a model, then predicts sentiments with emojis (😊/😞/😐).

## 4. Test REST API (optional, 5 min)

**Terminal 1** (start server):

```bash
cd ../04-rest-api-example
python3 flask_server.py
```

**Terminal 2** (run client):

```bash
cd ../04-rest-api-example
php php_client.php
```

✅ You should see ~10-15ms latency predictions.

## Troubleshooting

**"python3: command not found"**

- Find Python: `which python3` or `where python`
- Update path in PHP files if needed

**"ModuleNotFoundError"**

```bash
pip install pandas scikit-learn joblib
```

**"Model files not found"**

- Run the sentiment analyzer once to train: `php analyze.php`

## What's Next?

- Read the full [README.md](README.md) for detailed docs
- Try exercises from Chapter 11
- Customize training data in `03-sentiment-analysis/data/reviews.csv`
- Deploy to production with Docker or gunicorn

## Integration Strategy Decision Tree

```
Need real-time predictions with high traffic?
└─ YES → Use REST API (04-rest-api-example)
└─ NO
   └─ Task takes > 5 seconds?
      └─ YES → Use Message Queue (05-production-patterns)
      └─ NO → Use Shell Execution (01-simple-shell)
```

## Performance Summary

| Method   | Latency | Best For                  |
| -------- | ------- | ------------------------- |
| Shell    | ~50ms   | Development, low traffic  |
| REST API | ~15ms   | Production, high traffic  |
| Queue    | Async   | Background jobs, training |

## Need Help?

1. Check [README.md](README.md) Troubleshooting section
2. Verify Python packages: `pip list | grep sklearn`
3. Test Python standalone: `python3 03-sentiment-analysis/predict.py '{"text":"test"}'`
4. Review Chapter 11 content for detailed explanations

Happy integrating! 🚀


