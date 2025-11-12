# filename: test_user_service.py
"""
pytest fixtures and mocks example comparing to PHPUnit mocks and Laravel fakes.

Run with: pytest test_user_service.py -v
"""
import pytest
from unittest.mock import Mock, patch
from datetime import datetime


class UserService:
    def __init__(self, email_service):
        self.email_service = email_service
    
    def create_user(self, name, email):
        """Simulate user creation."""
        user = {'id': 1, 'name': name, 'email': email}
        self.email_service.send_welcome_email(email)
        return user


@pytest.fixture
def email_service():
    """Fixture providing a mock email service."""
    return Mock()


@pytest.fixture
def user_service(email_service):
    """Fixture providing UserService with mocked email service."""
    return UserService(email_service)


def test_create_user_sends_email(user_service, email_service):
    """Test that creating a user sends a welcome email."""
    user = user_service.create_user("John", "john@example.com")
    
    assert user['name'] == "John"
    email_service.send_welcome_email.assert_called_once_with("john@example.com")


@patch('requests.get')
def test_external_api_call(mock_get):
    """Test external API call with mocked requests."""
    mock_get.return_value.json.return_value = {'status': 'ok'}
    
    import requests
    response = requests.get('https://api.example.com/data')
    
    assert response.json()['status'] == 'ok'
    mock_get.assert_called_once_with('https://api.example.com/data')

