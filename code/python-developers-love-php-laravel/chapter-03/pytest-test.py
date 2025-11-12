"""
pytest Test Examples

This file demonstrates pytest testing in Django.
Compare to PHPUnit tests (phpunit-test.php).

To run tests:
pytest

To run specific test:
pytest tests/test_user.py::test_user_creation

To run with coverage:
pytest --cov=app tests/
"""

import pytest
from django.contrib.auth import get_user_model
from django.test import Client
from rest_framework.test import APIClient

User = get_user_model()


"""
Example: Feature Test (HTTP/Integration)

Feature tests test HTTP endpoints and integration.
Laravel equivalent: Tests\Feature\UserApiTest
"""
@pytest.mark.django_db
class TestUserAPI:
    """
    Test creating a user via API
    
    Laravel equivalent: test_user_can_be_created_via_api()
    """
    def test_user_can_be_created_via_api(self, client):
        response = client.post('/api/users/', {
            'name': 'John Doe',
            'email': 'john@example.com',
            'password': 'password123',
        }, content_type='application/json')

        assert response.status_code == 201
        data = response.json()
        assert data['name'] == 'John Doe'
        assert data['email'] == 'john@example.com'

        # Assert database has the user
        assert User.objects.filter(email='john@example.com').exists()

    def test_user_can_be_retrieved(self, client):
        user = User.objects.create(
            name='Jane Doe',
            email='jane@example.com',
        )

        response = client.get(f'/api/users/{user.id}/')

        assert response.status_code == 200
        data = response.json()
        assert data['id'] == user.id
        assert data['name'] == 'Jane Doe'
        assert data['email'] == 'jane@example.com'

    def test_user_can_be_updated(self, client):
        user = User.objects.create(
            name='John Doe',
            email='john@example.com',
        )

        response = client.put(
            f'/api/users/{user.id}/',
            {
                'name': 'Updated Name',
                'email': 'updated@example.com',
            },
            content_type='application/json'
        )

        assert response.status_code == 200
        data = response.json()
        assert data['name'] == 'Updated Name'
        assert data['email'] == 'updated@example.com'

        # Verify database update
        user.refresh_from_db()
        assert user.name == 'Updated Name'
        assert user.email == 'updated@example.com'

    def test_user_can_be_deleted(self, client):
        user = User.objects.create(
            name='John Doe',
            email='john@example.com',
        )

        response = client.delete(f'/api/users/{user.id}/')

        assert response.status_code == 204
        assert not User.objects.filter(id=user.id).exists()

    def test_user_creation_requires_valid_email(self, client):
        response = client.post('/api/users/', {
            'name': 'John Doe',
            'email': 'invalid-email',  # Invalid email
            'password': 'password123',
        }, content_type='application/json')

        assert response.status_code == 400  # or 422 depending on framework
        # Check validation error in response


"""
Example: Unit Test (Isolated)

Unit tests test isolated code without HTTP layer.
Laravel equivalent: Tests\Unit\UserServiceTest
"""
from app.services import UserService


class TestUserService:
    """
    Test user service creates user
    
    Laravel equivalent: test_user_service_creates_user()
    """
    @pytest.mark.django_db
    def test_user_service_creates_user(self):
        service = UserService()
        user = service.create_user('John Doe', 'john@example.com')

        assert isinstance(user, User)
        assert user.name == 'John Doe'
        assert user.email == 'john@example.com'

    @pytest.mark.django_db
    def test_user_service_finds_user_by_email(self):
        User.objects.create(email='test@example.com')

        service = UserService()
        user = service.find_by_email('test@example.com')

        assert user is not None
        assert user.email == 'test@example.com'


"""
Example: Assertions Comparison
"""
@pytest.mark.django_db
class TestAssertions:
    def test_assertions(self):
        user = User.objects.create(
            name='John Doe',
            email='john@example.com',
        )

        # Equality assertions
        assert user.name == 'John Doe'
        assert user.name != 'Jane Doe'

        # Null assertions
        assert user.deleted_at is None
        assert user.id is not None

        # Boolean assertions
        assert user.is_active() is True
        assert user.is_deleted() is False

        # Type assertions
        assert isinstance(user, User)
        assert isinstance(user.name, str)
        assert isinstance(user.id, int)

        # Collection assertions
        users = User.objects.all()
        assert users.count() == 1
        assert user in users

        # Database assertions
        assert User.objects.filter(email=user.email).exists()
        assert not User.objects.filter(email='nonexistent@example.com').exists()


"""
Example: Using Fixtures
"""
@pytest.fixture
def user():
    """Create a test user"""
    return User.objects.create(
        name='Test User',
        email='test@example.com',
    )


@pytest.mark.django_db
def test_user_fixture(user):
    """Test using fixture"""
    assert user.name == 'Test User'
    assert user.email == 'test@example.com'

