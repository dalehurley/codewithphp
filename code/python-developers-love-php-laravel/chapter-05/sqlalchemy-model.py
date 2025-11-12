# filename: models.py
"""
SQLAlchemy Model Definition

This demonstrates SQLAlchemy model definition with relationships.
Compare to Django ORM (django-orm-model.py) and Eloquent (eloquent-model.php).
"""

from sqlalchemy import Column, Integer, String, Text, DateTime, ForeignKey
from sqlalchemy.orm import relationship
from sqlalchemy.ext.declarative import declarative_base
from datetime import datetime

Base = declarative_base()


class User(Base):
    """User model representing blog authors."""
    __tablename__ = 'users'
    
    id = Column(Integer, primary_key=True)
    name = Column(String(255))
    email = Column(String(255), unique=True)
    created_at = Column(DateTime, default=datetime.utcnow)
    updated_at = Column(DateTime, default=datetime.utcnow, onupdate=datetime.utcnow)
    
    # One-to-many relationship: User has many Posts
    posts = relationship('Post', backref='author')


class Post(Base):
    """Post model representing blog posts."""
    __tablename__ = 'posts'
    
    id = Column(Integer, primary_key=True)
    title = Column(String(200))
    content = Column(Text)
    published_at = Column(DateTime, nullable=True)
    author_id = Column(Integer, ForeignKey('users.id'))
    created_at = Column(DateTime, default=datetime.utcnow)
    updated_at = Column(DateTime, default=datetime.utcnow, onupdate=datetime.utcnow)
    
    # Relationship is defined via backref on User model
    # This allows: post.author and user.posts
    
    def is_published(self):
        """Check if post is published."""
        return self.published_at is not None

