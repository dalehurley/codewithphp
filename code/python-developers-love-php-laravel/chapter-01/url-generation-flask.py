# filename: app.py
"""
Flask URL Generation Examples

This demonstrates Flask's url_for() function for generating URLs.
Run with: flask run (after installing Flask: pip install flask)
"""

from flask import Flask, url_for, redirect

app = Flask(__name__)

@app.route('/user/<int:user_id>')
def user_profile(user_id):
    return f'User {user_id}'

@app.route('/example')
def example():
    # Generate URL in Python code
    url = url_for('user_profile', user_id=123)
    # Returns: '/user/123'
    
    # Redirect to a named route
    return redirect(url_for('user_profile', user_id=123))

if __name__ == '__main__':
    app.run(debug=True)

# In templates (templates/example.html):
# {{ url_for('user_profile', user_id=123) }}
# <a href="{{ url_for('user_profile', user_id=123) }}">View Profile</a>

