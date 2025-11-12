# filename: middleware.py
"""
Django Middleware Example

This demonstrates Django middleware for request/response processing.
Place this in your Django app's middleware.py file.
"""

class LoggingMiddleware:
    """
    Django middleware class that logs requests and responses.
    """
    def __init__(self, get_response):
        self.get_response = get_response

    def __call__(self, request):
        # Before view
        print(f"Request: {request.method} {request.path}")
        
        response = self.get_response(request)
        
        # After view
        print(f"Response: {response.status_code}")
        return response

# Register in settings.py:
# MIDDLEWARE = [
#     'django.middleware.security.SecurityMiddleware',
#     'django.middleware.common.CommonMiddleware',
#     'myapp.middleware.LoggingMiddleware',
#     # ...
# ]

