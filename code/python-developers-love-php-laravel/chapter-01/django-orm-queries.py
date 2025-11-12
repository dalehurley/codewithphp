# filename: queries.py
"""
Django ORM Query Examples

This demonstrates common Django ORM query patterns.
These queries correspond to Eloquent queries in Laravel.
"""

from django.utils import timezone
from datetime import timedelta
from .models import Post

# Get all posts
posts = Post.objects.all()

# Get a single post by ID
post = Post.objects.get(id=1)

# Filter posts published in the last 7 days
recent_posts = Post.objects.filter(
    published_at__gte=timezone.now() - timedelta(days=7)
)

# Filter posts by author
author_posts = Post.objects.filter(author_id=user_id)

# Get posts with related author (eager loading)
posts_with_author = Post.objects.select_related('author').all()

# Get posts with related comments (prefetch for many-to-many or reverse FK)
posts_with_comments = Post.objects.prefetch_related('comments').all()

