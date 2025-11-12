# filename: app.py
"""
Flask Middleware Example

This demonstrates Flask middleware using decorators (most common approach).
Run with: flask run (after installing Flask: pip install flask)
"""

from flask import Flask, request

app = Flask(__name__)

# Using decorators (most common Flask approach)
@app.before_request
def log_request():
    """Log incoming requests"""
    print(f"Request: {request.method} {request.path}")

@app.after_request
def log_response(response):
    """Log outgoing responses"""
    print(f"Response: {response.status_code}")
    return response

@app.route('/')
def index():
    return 'Hello, World!'

if __name__ == '__main__':
    app.run(debug=True)

# Alternative: WSGI middleware class
# class LoggingMiddleware:
#     def __init__(self, app):
#         self.app = app
#     
#     def __call__(self, environ, start_response):
#         print(f"Request: {environ['REQUEST_METHOD']} {environ['PATH_INFO']}")
#         return self.app(environ, start_response)
# 
# app.wsgi_app = LoggingMiddleware(app.wsgi_app)

