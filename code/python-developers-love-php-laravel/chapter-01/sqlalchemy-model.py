# filename: models.py
"""
SQLAlchemy Model Definition

This demonstrates SQLAlchemy model definition with relationships.
Used with Flask applications.
"""

from sqlalchemy import Column, Integer, String, Text, DateTime, ForeignKey
from sqlalchemy.orm import relationship
from datetime import datetime

class Post(db.Model):
    """
    SQLAlchemy Post model
    
    Note: 'db' is typically the SQLAlchemy instance created with:
    db = SQLAlchemy(app)
    """
    __tablename__ = 'posts'
    
    id = Column(Integer, primary_key=True)
    title = Column(String(200))
    content = Column(Text)
    published_at = Column(DateTime, default=datetime.utcnow)
    author_id = Column(Integer, ForeignKey('users.id'))
    
    # Relationship definition
    author = relationship('User', backref='posts')
    
    def __repr__(self):
        return f'<Post {self.title}>'

