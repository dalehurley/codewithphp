# filename: models.py
"""
Django ORM Relationships

This demonstrates Django ORM relationships: one-to-one, one-to-many, many-to-many.
Compare to SQLAlchemy (sqlalchemy-relationships.py) and Eloquent (eloquent-relationships.php).
"""

from django.db import models


class User(models.Model):
    """User model with one-to-many and one-to-one relationships."""
    name = models.CharField(max_length=255)
    email = models.EmailField(unique=True)
    
    def __str__(self):
        return self.name


class Post(models.Model):
    """Post model with many-to-one and many-to-many relationships."""
    title = models.CharField(max_length=200)
    content = models.TextField()
    author = models.ForeignKey(
        'User', 
        on_delete=models.CASCADE, 
        related_name='posts'  # Allows: user.posts.all()
    )
    # One-to-many: User has many Posts, Post belongs to User
    
    def __str__(self):
        return self.title


class Profile(models.Model):
    """Profile model with one-to-one relationship."""
    user = models.OneToOneField(
        'User', 
        on_delete=models.CASCADE, 
        related_name='profile'  # Allows: user.profile
    )
    bio = models.TextField()
    # One-to-one: User has one Profile, Profile belongs to User
    
    def __str__(self):
        return f"{self.user.name}'s Profile"


class Tag(models.Model):
    """Tag model for many-to-many relationship."""
    name = models.CharField(max_length=50, unique=True)
    
    def __str__(self):
        return self.name


class PostTag(models.Model):
    """
    Intermediate model for many-to-many relationship with extra fields.
    Django can use this instead of automatic through table.
    """
    post = models.ForeignKey('Post', on_delete=models.CASCADE)
    tag = models.ForeignKey('Tag', on_delete=models.CASCADE)
    created_at = models.DateTimeField(auto_now_add=True)
    
    class Meta:
        unique_together = [['post', 'tag']]


# Alternative: Using ManyToManyField (simpler, but no extra fields)
class PostWithTags(models.Model):
    """Post model using ManyToManyField (simpler approach)."""
    title = models.CharField(max_length=200)
    tags = models.ManyToManyField(
        'Tag', 
        related_name='posts'  # Allows: tag.posts.all()
    )
    # Many-to-many: Post has many Tags, Tag has many Posts
    # Django creates postwithtags_tags table automatically
    
    def __str__(self):
        return self.title


# Usage examples:
"""
# One-to-many
user = User.objects.get(id=1)
posts = user.posts.all()  # Get all posts by user

post = Post.objects.get(id=1)
author = post.author  # Get post's author

# One-to-one
user = User.objects.get(id=1)
profile = user.profile  # Get user's profile

profile = Profile.objects.get(id=1)
user = profile.user  # Get profile's user

# Many-to-many
post = PostWithTags.objects.get(id=1)
tags = post.tags.all()  # Get all tags for post

tag = Tag.objects.get(id=1)
posts = tag.posts.all()  # Get all posts with tag

# Add/remove tags
post.tags.add(tag)
post.tags.remove(tag)
post.tags.clear()  # Remove all tags
"""

