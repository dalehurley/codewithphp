# filename: app.py
"""
Flask Routing Example

This demonstrates basic Flask routing patterns that map to Laravel routes.
Run with: flask run (after installing Flask: pip install flask)
"""

from flask import Flask, request

app = Flask(__name__)

@app.route('/')
def index():
    return 'Hello, World!'

@app.route('/user/<int:user_id>')
def user_profile(user_id):
    return f'User {user_id}'

@app.route('/post/<slug>', methods=['GET', 'POST'])
def post_detail(slug):
    if request.method == 'POST':
        # Handle POST
        pass
    return f'Post: {slug}'

if __name__ == '__main__':
    app.run(debug=True)

