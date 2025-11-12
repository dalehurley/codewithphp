# filename: serializers.py
"""
Django REST Framework Serializer Example

This example demonstrates how to format API responses using Django REST serializers.
Compare this to Laravel's API Resources.
"""

from rest_framework import serializers


class UserSerializer(serializers.Serializer):
    """
    Serializer for user data transformation.
    
    Similar to Laravel's JsonResource class.
    """
    id = serializers.IntegerField()
    name = serializers.CharField()
    email = serializers.EmailField()
    created_at = serializers.DateTimeField()
    
    # Computed field (similar to Laravel's computed properties)
    profile_url = serializers.SerializerMethodField()
    
    def get_profile_url(self, obj):
        """Generate profile URL from user ID."""
        return f"/users/{obj['id']}"


# Usage in view:
# from .serializers import UserSerializer
# 
# class UserViewSet(viewsets.ViewSet):
#     def retrieve(self, request, pk=None):
#         user_data = {
#             'id': 1,
#             'name': 'John Doe',
#             'email': 'john@example.com',
#             'created_at': '2024-01-01T00:00:00Z'
#         }
#         serializer = UserSerializer(user_data)
#         return Response(serializer.data)

