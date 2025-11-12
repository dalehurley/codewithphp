# filename: app.py
"""
Flask Request Validation Example

This example demonstrates how to validate API requests in Flask using Marshmallow.
Compare this to Laravel's Form Requests.
"""

from flask import Flask, request, jsonify
from marshmallow import Schema, fields, ValidationError

app = Flask(__name__)


class UserSchema(Schema):
    """
    Schema for user validation.
    
    Similar to Laravel's Form Request rules.
    """
    name = fields.Str(required=True, validate=fields.Length(min=2, max=100))
    email = fields.Email(required=True)
    age = fields.Int(required=False, validate=fields.Range(min=0, max=150))


@app.route('/api/users', methods=['POST'])
def create_user():
    """
    Create a new user with validation.
    
    Compare to Laravel's Form Request validation.
    """
    try:
        # Validate request data
        data = UserSchema().load(request.get_json())
        
        # Process valid data
        # In a real application, save to database
        return jsonify({
            'id': 1,
            **data
        }), 201
    except ValidationError as err:
        # Return validation errors
        return jsonify({
            'errors': err.messages
        }), 400


if __name__ == '__main__':
    app.run(debug=True)

