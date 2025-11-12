# filename: app.py
"""
Flask View Rendering Template

This demonstrates how Flask views render Jinja2 templates with data.
Run with: flask run (after installing Flask: pip install flask)
"""

from flask import Flask, render_template
from models import Post

app = Flask(__name__)

@app.route('/posts')
def post_list():
    posts = Post.query.all()
    return render_template('post_list.html', posts=posts)

if __name__ == '__main__':
    app.run(debug=True)

