# filename: views.py
"""
Django REST Framework Authentication Example

This example demonstrates API authentication using Django REST Framework tokens.
Compare this to Laravel's Sanctum authentication.
"""

from rest_framework.authtoken.models import Token
from rest_framework.decorators import api_view, permission_classes
from rest_framework.permissions import IsAuthenticated
from rest_framework.response import Response
from rest_framework import status


@api_view(['POST'])
def login(request):
    """
    Login endpoint that returns an API token.
    
    POST /api/login
    Body: {"email": "user@example.com", "password": "password"}
    
    Compare to Laravel's Sanctum login.
    """
    # Validate credentials
    if request.data.get('email') == 'user@example.com':
        # In a real application, authenticate user
        from django.contrib.auth import get_user_model
        User = get_user_model()
        
        try:
            user = User.objects.get(email=request.data['email'])
            # Get or create token for user
            token, created = Token.objects.get_or_create(user=user)
            return Response({
                'token': token.key
            }, status=status.HTTP_200_OK)
        except User.DoesNotExist:
            pass
    
    return Response({
        'error': 'Invalid credentials'
    }, status=status.HTTP_401_UNAUTHORIZED)


@api_view(['GET'])
@permission_classes([IsAuthenticated])
def protected(request):
    """
    Protected endpoint that requires authentication.
    
    GET /api/protected
    Headers: Authorization: Token {token}
    
    Compare to Laravel's auth:sanctum middleware.
    """
    # request.user is automatically set by IsAuthenticated permission
    return Response({
        'user': request.user.email
    })


@api_view(['POST'])
@permission_classes([IsAuthenticated])
def logout(request):
    """
    Logout endpoint that deletes the user's token.
    
    POST /api/logout
    Headers: Authorization: Token {token}
    """
    # Delete the user's token
    request.user.auth_token.delete()
    return Response({
        'message': 'Logged out successfully'
    })

