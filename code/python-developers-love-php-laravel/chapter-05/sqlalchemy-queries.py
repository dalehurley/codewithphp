# filename: queries.py
"""
SQLAlchemy Query Examples

This demonstrates SQLAlchemy queries: filtering, ordering, aggregations.
Compare to Django ORM (django-queries.py) and Eloquent (eloquent-queries.php).
"""

from sqlalchemy import and_, or_, func, desc, asc
from datetime import datetime, timedelta
from .models import Post, User, Tag, session


# Basic queries
posts = session.query(Post).all()  # Get all posts
post = session.query(Post).get(1)  # Get by primary key (returns None if not found)
post = session.query(Post).filter(Post.id == 1).first()  # Get one
post = session.query(Post).filter(Post.id == 1).one_or_none()  # Get one or None

# Filtering
recent_posts = session.query(Post).filter(
    Post.published_at >= datetime.now() - timedelta(days=7)
).all()

draft_posts = session.query(Post).filter(
    Post.published_at == None
).all()

user_posts = session.query(Post).filter(
    Post.author_id == 1
).all()

# Complex filtering
posts = session.query(Post).filter(
    or_(
        Post.title.contains('django'),
        Post.content.contains('django')
    ),
    Post.published_at != None
).all()

# Field operations
posts = session.query(Post).filter(
    Post.title.contains('django'),  # Contains
    Post.title.like('%Django%'),   # Like pattern
    Post.published_at >= datetime.now() - timedelta(days=7),  # Greater than or equal
    Post.published_at <= datetime.now(),  # Less than or equal
    Post.author_id.in_([1, 2, 3]),  # In list
).all()

# Ordering and limiting
posts = session.query(Post).order_by(
    desc(Post.published_at)
).limit(10).all()  # Latest 10

posts = session.query(Post).order_by(
    Post.title
).all()  # Ascending

posts = session.query(Post).order_by(
    desc(Post.published_at),
    Post.title
).all()  # Multiple ordering

# Aggregations
post_count = session.query(Post).count()

avg_posts = session.query(
    func.avg(func.count(Post.id))
).join(User).group_by(User.id).scalar()

# Annotations (add computed fields)
users = session.query(
    User,
    func.count(Post.id).label('post_count'),
    func.max(Post.published_at).label('latest_post')
).join(Post).group_by(User.id).having(
    func.count(Post.id) > 0
).all()

# Chaining
posts = session.query(Post).filter(
    Post.author_id == 1
).order_by(
    desc(Post.published_at)
).filter(
    Post.title != ''
).distinct().all()

# Subqueries
recent_authors = session.query(User).filter(
    User.id.in_(
        session.query(Post.author_id).filter(
            Post.published_at >= datetime.now() - timedelta(days=7)
        )
    )
).all()

# Group by
posts_by_author = session.query(
    Post.author_id,
    func.count(Post.id).label('count')
).group_by(Post.author_id).order_by(
    desc('count')
).all()

# Exists check
from sqlalchemy import exists
has_posts = session.query(
    exists().where(Post.author_id == 1)
).scalar()

# Update
session.query(Post).filter(
    Post.author_id == 1
).update({
    'published_at': datetime.now()
})
session.commit()

# Delete
session.query(Post).filter(
    Post.published_at < datetime.now() - timedelta(days=365)
).delete()
session.commit()

