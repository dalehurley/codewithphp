# filename: queries.py
"""
Django ORM Query Examples

This demonstrates Django ORM queries: filtering, ordering, aggregations.
Compare to SQLAlchemy (sqlalchemy-queries.py) and Eloquent (eloquent-queries.php).
"""

from django.db.models import Q, Count, Avg, Max, Min, Sum
from django.utils import timezone
from datetime import timedelta
from .models import Post, User, Tag


# Basic queries
posts = Post.objects.all()  # Get all posts
post = Post.objects.get(id=1)  # Get one (raises DoesNotExist if not found)
post = Post.objects.filter(id=1).first()  # Get one (returns None if not found)
post = Post.objects.filter(id=1).last()  # Get last matching

# Filtering
recent_posts = Post.objects.filter(
    published_at__gte=timezone.now() - timedelta(days=7)
)
draft_posts = Post.objects.filter(published_at__isnull=True)
user_posts = Post.objects.filter(author_id=1)

# Exclude (opposite of filter)
published_posts = Post.objects.exclude(published_at__isnull=True)

# Complex filtering with Q objects
posts = Post.objects.filter(
    Q(title__icontains='django') | Q(content__icontains='django'),
    published_at__isnull=False
)

# Field lookups
posts = Post.objects.filter(
    title__icontains='django',  # Case-insensitive contains
    title__contains='django',    # Case-sensitive contains
    title__startswith='Django', # Starts with
    title__endswith='Guide',    # Ends with
    published_at__gte=timezone.now() - timedelta(days=7),  # Greater than or equal
    published_at__lte=timezone.now(),  # Less than or equal
    author_id__in=[1, 2, 3],  # In list
)

# Ordering and limiting
posts = Post.objects.order_by('-published_at')[:10]  # Latest 10
posts = Post.objects.order_by('title')  # Ascending
posts = Post.objects.order_by('-published_at', 'title')  # Multiple ordering

# Aggregations
post_count = Post.objects.count()
avg_posts = User.objects.annotate(
    post_count=Count('posts')
).aggregate(Avg('post_count'))

# Annotations (add computed fields)
users = User.objects.annotate(
    post_count=Count('posts'),
    latest_post=Max('posts__published_at')
).filter(post_count__gt=0)

# Chaining
posts = Post.objects.filter(
    author_id=1
).order_by('-published_at').exclude(
    title=''
).distinct()

# Subqueries
recent_authors = User.objects.filter(
    id__in=Post.objects.filter(
        published_at__gte=timezone.now() - timedelta(days=7)
    ).values_list('author_id', flat=True)
)

# Group by
from django.db.models import Count
posts_by_author = Post.objects.values('author_id').annotate(
    count=Count('id')
).order_by('-count')

# Exists check
has_posts = Post.objects.filter(author_id=1).exists()

# Update
Post.objects.filter(author_id=1).update(published_at=timezone.now())

# Delete
Post.objects.filter(published_at__lt=timezone.now() - timedelta(days=365)).delete()

