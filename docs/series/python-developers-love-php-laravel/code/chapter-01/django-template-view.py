# filename: views.py
"""
Django View Rendering Template

This demonstrates how Django views render templates with data.
Place this in your Django app's views.py file.
"""

from django.shortcuts import render
from .models import Post

def post_list(request):
    posts = Post.objects.all()
    return render(request, 'blog/post_list.html', {'posts': posts})

