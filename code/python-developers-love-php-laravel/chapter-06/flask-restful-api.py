# filename: app.py
"""
Flask-RESTful API Example

This example demonstrates how to create RESTful API endpoints using Flask-RESTful.
Compare this to Laravel's API routes and controllers.
"""

from flask import Flask, request, jsonify
from flask_restful import Resource, Api

app = Flask(__name__)
api = Api(app)

class UserResource(Resource):
    """Resource for individual user operations."""
    
    def get(self, user_id):
        """
        GET /api/users/{user_id}
        Retrieve a single user by ID.
        """
        # In a real application, fetch from database
        return {
            'id': user_id,
            'name': 'John Doe',
            'email': 'john@example.com'
        }, 200
    
    def put(self, user_id):
        """
        PUT /api/users/{user_id}
        Update a user by ID.
        """
        data = request.get_json()
        # In a real application, update database
        return {
            'id': user_id,
            **data
        }, 200
    
    def delete(self, user_id):
        """
        DELETE /api/users/{user_id}
        Delete a user by ID.
        """
        # In a real application, delete from database
        return {'message': 'User deleted'}, 204


class UserListResource(Resource):
    """Resource for user collection operations."""
    
    def get(self):
        """
        GET /api/users
        List all users.
        """
        # In a real application, fetch from database
        return {
            'users': [
                {'id': 1, 'name': 'John Doe'},
                {'id': 2, 'name': 'Jane Smith'}
            ]
        }, 200
    
    def post(self):
        """
        POST /api/users
        Create a new user.
        """
        data = request.get_json()
        # In a real application, save to database
        return {
            'id': 3,
            **data
        }, 201


# Register routes
api.add_resource(UserListResource, '/api/users')
api.add_resource(UserResource, '/api/users/<int:user_id>')

if __name__ == '__main__':
    app.run(debug=True)

