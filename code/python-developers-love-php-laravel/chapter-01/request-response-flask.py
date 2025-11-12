# filename: app.py
"""
Flask Request/Response Examples

This demonstrates Flask's request object and response creation.
Run with: flask run (after installing Flask: pip install flask)
"""

from flask import Flask, request, jsonify, Response

app = Flask(__name__)

@app.route('/example', methods=['GET', 'POST'])
def example():
    # Access request data
    method = request.method  # 'GET', 'POST', etc.
    user_id = request.args.get('id')  # Query parameter
    data = request.form.get('data')  # Form data
    path = request.path  # '/example'
    headers = request.headers  # Request headers
    
    # Create simple response
    return 'Hello, World!', 200
    
    # Create JSON response
    return jsonify({'key': 'value', 'user_id': user_id})
    
    # Response with custom headers
    response = Response('Hello')
    response.headers['X-Custom-Header'] = 'value'
    return response

if __name__ == '__main__':
    app.run(debug=True)

