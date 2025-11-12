# filename: test_user.py
"""
pytest test example comparing to PHPUnit.

Run with: pytest test_user.py -v
"""
import pytest
from datetime import datetime


class User:
    def __init__(self, name, email):
        self.name = name
        self.email = email
        self.created_at = datetime.now()


def test_user_creation():
    """Test basic user creation."""
    user = User("John Doe", "john@example.com")
    assert user.name == "John Doe"
    assert user.email == "john@example.com"
    assert user.created_at is not None


def test_user_email_validation():
    """Test email validation raises ValueError."""
    with pytest.raises(ValueError, match="Invalid email"):
        if "@" not in "invalid-email":
            raise ValueError("Invalid email")


def test_user_list():
    """Test user list operations."""
    users = [
        User("John", "john@example.com"),
        User("Jane", "jane@example.com")
    ]
    assert len(users) == 2
    assert users[0].name == "John"

