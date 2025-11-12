# filename: tasks.py
"""
Celery task example comparing to Laravel Queues.

Start worker: celery -A tasks worker --loglevel=info
"""
from celery import Celery
import requests

app = Celery('tasks', broker='redis://localhost:6379/0')


@app.task
def send_email(to, subject, body):
    """Send email asynchronously."""
    # Simulate email sending
    print(f"Sending email to {to}: {subject}")
    return f"Email sent to {to}"


@app.task(bind=True, max_retries=3)
def process_payment(self, user_id, amount):
    """Process payment with retry logic."""
    try:
        # Simulate payment processing
        if amount < 0:
            raise ValueError("Invalid amount")
        print(f"Processing payment for user {user_id}: ${amount}")
        return f"Payment processed: ${amount}"
    except Exception as exc:
        # Retry on failure
        raise self.retry(exc=exc, countdown=60)


# Usage
if __name__ == '__main__':
    from tasks import send_email, process_payment
    
    # Dispatch task
    send_email.delay("user@example.com", "Welcome", "Welcome to our app!")
    process_payment.delay(user_id=1, amount=100.00)

