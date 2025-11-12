# filename: views.py
"""
Django REST Framework ViewSet Example

This example demonstrates how to create RESTful API endpoints using Django REST Framework.
Compare this to Laravel's API controllers.
"""

from rest_framework import viewsets
from rest_framework.decorators import action
from rest_framework.response import Response
from rest_framework import status


class UserViewSet(viewsets.ViewSet):
    """
    ViewSet for user operations.
    
    In urls.py:
    from django.urls import path, include
    from rest_framework.routers import DefaultRouter
    from .views import UserViewSet
    
    router = DefaultRouter()
    router.register(r'users', UserViewSet, basename='user')
    urlpatterns = [path('api/', include(router.urls))]
    """
    
    def list(self, request):
        """
        GET /api/users/
        List all users.
        """
        # In a real application, fetch from database
        return Response([
            {'id': 1, 'name': 'John Doe'},
            {'id': 2, 'name': 'Jane Smith'}
        ])
    
    def retrieve(self, request, pk=None):
        """
        GET /api/users/{pk}/
        Retrieve a single user by ID.
        """
        # In a real application, fetch from database
        return Response({
            'id': pk,
            'name': 'John Doe',
            'email': 'john@example.com'
        })
    
    def create(self, request):
        """
        POST /api/users/
        Create a new user.
        """
        data = request.data
        # In a real application, save to database
        return Response({
            'id': 3,
            **data
        }, status=status.HTTP_201_CREATED)
    
    def update(self, request, pk=None):
        """
        PUT /api/users/{pk}/
        Update a user by ID.
        """
        data = request.data
        # In a real application, update database
        return Response({
            'id': pk,
            **data
        })
    
    def destroy(self, request, pk=None):
        """
        DELETE /api/users/{pk}/
        Delete a user by ID.
        """
        # In a real application, delete from database
        return Response(status=status.HTTP_204_NO_CONTENT)

