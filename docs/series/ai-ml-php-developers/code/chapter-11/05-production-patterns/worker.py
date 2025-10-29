"""
Python worker that processes tasks from Redis queue.

This runs continuously in the background:
1. Polls Redis queue for new tasks
2. Executes ML tasks (prediction, training, etc.)
3. Stores results back in Redis
4. Sends callbacks if provided

Usage:
    python3 worker.py

Run multiple workers for parallel processing:
    python3 worker.py &
    python3 worker.py &
    python3 worker.py &
"""

import redis
import json
import time
import sys
from pathlib import Path

# Add sentiment analysis to path
sys.path.insert(0, str(Path(__file__).parent.parent / '03-sentiment-analysis'))

try:
    import joblib
    SENTIMENT_MODEL = None
    SENTIMENT_VECTORIZER = None
    
    # Try to load sentiment model
    model_path = Path(__file__).parent.parent / '03-sentiment-analysis' / 'models' / 'sentiment_model.pkl'
    if model_path.exists():
        SENTIMENT_MODEL = joblib.load(model_path)
        vectorizer_path = model_path.parent / 'vectorizer.pkl'
        SENTIMENT_VECTORIZER = joblib.load(vectorizer_path)
        print("✅ Sentiment model loaded")
except ImportError:
    print("⚠️  joblib not installed. Sentiment tasks will be simulated.")


def process_sentiment_analysis(data):
    """Process sentiment analysis task."""
    text = data.get('text', '')
    
    if SENTIMENT_MODEL and SENTIMENT_VECTORIZER:
        # Real prediction
        text_vec = SENTIMENT_VECTORIZER.transform([text])
        prediction = SENTIMENT_MODEL.predict(text_vec)[0]
        probabilities = SENTIMENT_MODEL.predict_proba(text_vec)[0]
        
        return {
            'text': text,
            'sentiment': prediction,
            'confidence': float(probabilities.max())
        }
    else:
        # Simulated prediction
        return {
            'text': text,
            'sentiment': 'positive',
            'confidence': 0.85,
            'note': 'Simulated (model not loaded)'
        }


def process_task(task):
    """Process a task based on its type."""
    task_type = task['type']
    data = task['data']
    
    if task_type == 'sentiment_analysis':
        return process_sentiment_analysis(data)
    elif task_type == 'image_classification':
        # Simulated
        time.sleep(1)
        return {'label': 'cat', 'confidence': 0.92}
    else:
        raise ValueError(f"Unknown task type: {task_type}")


def main():
    """Main worker loop."""
    print("🔧 Starting ML Worker...")
    
    # Connect to Redis
    try:
        r = redis.Redis(host='127.0.0.1', port=6379, decode_responses=True)
        r.ping()
        print("✅ Connected to Redis")
    except redis.ConnectionError:
        print("❌ Failed to connect to Redis")
        print("   Start Redis: brew services start redis  # macOS")
        return
    
    print("👂 Listening for tasks on queue 'ml_tasks'...\n")
    
    while True:
        try:
            # Blocking pop with 1 second timeout
            task_data = r.brpop('ml_tasks', timeout=1)
            
            if not task_data:
                continue  # No task, keep waiting
            
            task = json.loads(task_data[1])
            task_id = task['id']
            
            print(f"📥 Received task {task_id} ({task['type']})")
            
            # Update status
            task['status'] = 'processing'
            task['started_at'] = int(time.time())
            r.setex(f"task:{task_id}", 3600, json.dumps(task))
            
            # Process task
            result = process_task(task)
            
            # Store result
            r.setex(f"result:{task_id}", 3600, json.dumps(result))
            
            # Update task status
            task['status'] = 'completed'
            task['completed_at'] = int(time.time())
            r.setex(f"task:{task_id}", 3600, json.dumps(task))
            
            print(f"✅ Completed task {task_id}\n")
            
        except KeyboardInterrupt:
            print("\n🛑 Worker stopped")
            break
        except Exception as e:
            print(f"❌ Error processing task: {e}\n")
            if 'task_id' in locals():
                task['status'] = 'failed'
                task['error'] = str(e)
                r.setex(f"task:{task_id}", 3600, json.dumps(task))


if __name__ == '__main__':
    main()




