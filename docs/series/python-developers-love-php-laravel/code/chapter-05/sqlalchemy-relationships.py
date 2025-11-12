# filename: models.py
"""
SQLAlchemy Relationships

This demonstrates SQLAlchemy relationships: one-to-one, one-to-many, many-to-many.
Compare to Django ORM (django-relationships.py) and Eloquent (eloquent-relationships.php).
"""

from sqlalchemy import Column, Integer, String, Text, ForeignKey, Table
from sqlalchemy.orm import relationship
from sqlalchemy.ext.declarative import declarative_base

Base = declarative_base()


# Many-to-many association table
post_tag = Table(
    'post_tag',
    Base.metadata,
    Column('post_id', Integer, ForeignKey('posts.id'), primary_key=True),
    Column('tag_id', Integer, ForeignKey('tags.id'), primary_key=True),
)


class User(Base):
    """User model with one-to-many and one-to-one relationships."""
    __tablename__ = 'users'
    
    id = Column(Integer, primary_key=True)
    name = Column(String(255))
    email = Column(String(255), unique=True)
    
    # One-to-many: User has many Posts
    # backref creates reverse relationship: post.author
    posts = relationship('Post', backref='author')
    
    # One-to-one: User has one Profile
    # uselist=False makes it one-to-one instead of one-to-many
    profile = relationship('Profile', back_populates='user', uselist=False)


class Post(Base):
    """Post model with many-to-one and many-to-many relationships."""
    __tablename__ = 'posts'
    
    id = Column(Integer, primary_key=True)
    title = Column(String(200))
    content = Column(Text)
    author_id = Column(Integer, ForeignKey('users.id'))
    
    # Many-to-many: Post has many Tags
    # secondary specifies the association table
    tags = relationship('Tag', secondary=post_tag, back_populates='posts')


class Profile(Base):
    """Profile model with one-to-one relationship."""
    __tablename__ = 'profiles'
    
    id = Column(Integer, primary_key=True)
    user_id = Column(Integer, ForeignKey('users.id'), unique=True)  # unique=True makes it one-to-one
    bio = Column(Text)
    
    # One-to-one inverse: Profile belongs to User
    user = relationship('User', back_populates='profile')


class Tag(Base):
    """Tag model for many-to-many relationship."""
    __tablename__ = 'tags'
    
    id = Column(Integer, primary_key=True)
    name = Column(String(50), unique=True)
    
    # Many-to-many inverse: Tag has many Posts
    posts = relationship('Post', secondary=post_tag, back_populates='tags')


# Usage examples:
"""
from sqlalchemy.orm import sessionmaker

Session = sessionmaker(bind=engine)
session = Session()

# One-to-many
user = session.query(User).get(1)
posts = user.posts  # Get all posts by user (list)

post = session.query(Post).get(1)
author = post.author  # Get post's author (via backref)

# One-to-one
user = session.query(User).get(1)
profile = user.profile  # Get user's profile

profile = session.query(Profile).get(1)
user = profile.user  # Get profile's user

# Many-to-many
post = session.query(Post).get(1)
tags = post.tags  # Get all tags for post (list)

tag = session.query(Tag).get(1)
posts = tag.posts  # Get all posts with tag (list)

# Add/remove tags
post.tags.append(tag)
session.commit()

post.tags.remove(tag)
session.commit()

# Clear all tags
post.tags = []
session.commit()
"""

