# filename: views.py
"""
Django Views

This demonstrates Django view functions that handle HTTP requests.
These views correspond to the URLs defined in urls.py.
"""

from django.shortcuts import render
from django.http import HttpResponse

def index(request):
    return HttpResponse('Hello, World!')

def user_profile(request, user_id):
    return HttpResponse(f'User {user_id}')

def post_detail(request, slug):
    if request.method == 'POST':
        # Handle POST
        pass
    return HttpResponse(f'Post: {slug}')

