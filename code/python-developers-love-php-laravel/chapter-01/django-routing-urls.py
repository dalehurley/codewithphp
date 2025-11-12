# filename: urls.py
"""
Django URL Configuration

This demonstrates Django URL patterns that map to Laravel routes.
Place this in your Django app's urls.py file.
"""

from django.urls import path
from . import views

urlpatterns = [
    path('', views.index, name='index'),
    path('user/<int:user_id>/', views.user_profile, name='user_profile'),
    path('post/<slug:slug>/', views.post_detail, name='post_detail'),
]

