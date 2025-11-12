# filename: views.py
"""
Django URL Generation Examples

This demonstrates Django's reverse() function for generating URLs.
"""

from django.urls import reverse
from django.shortcuts import redirect

def my_view(request):
    # Generate URL in Python code
    url = reverse('user_profile', args=[123])
    # Returns: '/user/123/'
    
    # With keyword arguments
    url = reverse('user_profile', kwargs={'user_id': 123})
    
    # Redirect to a named route
    return redirect('user_profile', user_id=123)

# In templates (templates/example.html):
# {% url 'user_profile' user_id=123 %}
# <a href="{% url 'user_profile' user_id=123 %}">View Profile</a>

