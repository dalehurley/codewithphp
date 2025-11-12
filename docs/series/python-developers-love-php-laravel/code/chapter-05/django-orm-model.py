# filename: models.py
"""
Django ORM Model Definition

This demonstrates Django ORM model definition with relationships.
Place this in your Django app's models.py file.
"""

from django.db import models
from django.utils import timezone


class User(models.Model):
    """User model representing blog authors."""
    name = models.CharField(max_length=255)
    email = models.EmailField(unique=True)
    created_at = models.DateTimeField(auto_now_add=True)
    updated_at = models.DateTimeField(auto_now=True)
    
    def __str__(self):
        return self.name


class Post(models.Model):
    """Post model representing blog posts."""
    title = models.CharField(max_length=200)
    content = models.TextField()
    published_at = models.DateTimeField(null=True, blank=True)
    author = models.ForeignKey('User', on_delete=models.CASCADE, related_name='posts')
    created_at = models.DateTimeField(auto_now_add=True)
    updated_at = models.DateTimeField(auto_now=True)
    
    class Meta:
        ordering = ['-published_at']  # Order by published_at descending
        db_table = 'posts'  # Explicit table name
    
    def __str__(self):
        return self.title
    
    def is_published(self):
        """Check if post is published."""
        return self.published_at is not None

