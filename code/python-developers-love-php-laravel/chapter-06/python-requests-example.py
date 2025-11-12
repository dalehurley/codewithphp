# filename: external_api.py
"""
Python requests Library Example

This example demonstrates how to make HTTP requests to external APIs using Python's requests library.
Compare this to Laravel's HTTP Client (Guzzle).
"""

import requests
from requests.exceptions import RequestException, HTTPError


def fetch_user_data(user_id):
    """
    Fetch user data from external API.
    
    Compare to Laravel's Http::get().
    """
    try:
        response = requests.get(
            f'https://api.example.com/users/{user_id}',
            headers={'Authorization': 'Bearer token123'},
            timeout=10
        )
        # Raise exception for bad status codes (4xx, 5xx)
        response.raise_for_status()
        return response.json()
    except HTTPError as e:
        print(f'HTTP error: {e}')
        return None
    except RequestException as e:
        print(f'Request error: {e}')
        return None


def create_user(user_data):
    """
    Create a user in external API.
    
    Compare to Laravel's Http::post().
    """
    response = requests.post(
        'https://api.example.com/users',
        json=user_data,
        headers={
            'Authorization': 'Bearer token123',
            'Content-Type': 'application/json'
        },
        timeout=10
    )
    return response.json(), response.status_code


def fetch_with_retry(url, max_retries=3):
    """
    Fetch data with retry logic.
    
    Compare to Laravel's Http::retry().
    """
    for attempt in range(max_retries):
        try:
            response = requests.get(url, timeout=10)
            response.raise_for_status()
            return response.json()
        except RequestException as e:
            if attempt == max_retries - 1:
                raise
            print(f'Attempt {attempt + 1} failed: {e}')
            # Wait before retry (exponential backoff)
            import time
            time.sleep(2 ** attempt)
    return None


if __name__ == '__main__':
    # Example usage
    user = fetch_user_data(1)
    print(user)
    
    new_user = create_user({'name': 'John Doe', 'email': 'john@example.com'})
    print(new_user)

