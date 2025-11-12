# filename: models.py
"""
Django ORM Model Definition

This demonstrates Django ORM model definition with relationships.
Place this in your Django app's models.py file.
"""

from django.db import models
from django.utils import timezone
from datetime import timedelta

class Post(models.Model):
    title = models.CharField(max_length=200)
    content = models.TextField()
    published_at = models.DateTimeField(auto_now_add=True)
    author = models.ForeignKey('User', on_delete=models.CASCADE)
    
    class Meta:
        ordering = ['-published_at']
    
    def __str__(self):
        return self.title

