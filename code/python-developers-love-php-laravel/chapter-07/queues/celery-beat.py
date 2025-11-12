# filename: celery_beat.py
"""
Celery Beat scheduled tasks example comparing to Laravel Scheduler.

Start beat: celery -A celery_beat beat --loglevel=info
"""
from celery import Celery
from celery.schedules import crontab

app = Celery('tasks', broker='redis://localhost:6379/0')

app.conf.beat_schedule = {
    'send-daily-report': {
        'task': 'tasks.send_daily_report',
        'schedule': crontab(hour=9, minute=0),  # 9 AM daily
    },
    'cleanup-old-data': {
        'task': 'tasks.cleanup_old_data',
        'schedule': crontab(hour=2, minute=0, day_of_week=1),  # 2 AM Monday
    },
}


@app.task
def send_daily_report():
    """Send daily report task."""
    print("Sending daily report...")


@app.task
def cleanup_old_data():
    """Cleanup old data task."""
    print("Cleaning up old data...")

