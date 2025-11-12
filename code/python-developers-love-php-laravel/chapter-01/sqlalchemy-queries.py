# filename: queries.py
"""
SQLAlchemy Query Examples

This demonstrates common SQLAlchemy query patterns.
These queries correspond to Eloquent queries in Laravel.
"""

from datetime import datetime, timedelta
from .models import Post

# Get all posts
posts = Post.query.all()

# Get a single post by ID
post = Post.query.get(1)

# Filter posts published in the last 7 days
recent_posts = Post.query.filter(
    Post.published_at >= datetime.now() - timedelta(days=7)
).all()

# Filter posts by author
author_posts = Post.query.filter_by(author_id=user_id).all()

# Get posts with related author (eager loading)
from sqlalchemy.orm import joinedload
posts_with_author = Post.query.options(joinedload(Post.author)).all()

# Get posts with related comments (subquery loading)
from sqlalchemy.orm import subqueryload
posts_with_comments = Post.query.options(subqueryload(Post.comments)).all()

