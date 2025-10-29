"""
Example of processing complex structured data in Python.

In a real ML scenario, this might extract features, normalize values, etc.
"""

import sys
import json
from typing import Dict, Any


def process_user_data(user: Dict[str, Any]) -> Dict[str, Any]:
    """
    Example of processing complex structured data.
    In a real ML scenario, this might extract features, normalize values, etc.
    """
    # Extract and validate fields
    name = user.get('name', 'Unknown')
    age = user.get('age', 0)
    purchases = user.get('purchases', [])
    
    # Perform calculations
    total_spent = sum(p.get('amount', 0) for p in purchases)
    avg_purchase = total_spent / len(purchases) if purchases else 0
    
    # Classify user segment (simple business logic)
    if total_spent > 1000 and len(purchases) > 10:
        segment = 'VIP'
    elif total_spent > 500:
        segment = 'Regular'
    else:
        segment = 'New'
    
    return {
        'user_id': user.get('id'),
        'name': name,
        'segment': segment,
        'metrics': {
            'total_purchases': len(purchases),
            'total_spent': round(total_spent, 2),
            'avg_purchase_value': round(avg_purchase, 2)
        },
        'recommendations': generate_recommendations(segment)
    }


def generate_recommendations(segment: str) -> list:
    """Generate product recommendations based on segment."""
    recommendations = {
        'VIP': ['Premium Bundle', 'Exclusive Access', 'Priority Support'],
        'Regular': ['Popular Items', 'Seasonal Deals', 'Member Benefits'],
        'New': ['Starter Pack', 'Welcome Offer', 'Getting Started Guide']
    }
    return recommendations.get(segment, [])


def main():
    try:
        # Read input from PHP
        if len(sys.argv) < 2:
            raise ValueError('No input data provided')
        
        input_data = json.loads(sys.argv[1])
        
        # Process single user or batch of users
        if isinstance(input_data, dict):
            # Single user
            result = process_user_data(input_data)
        elif isinstance(input_data, list):
            # Batch of users
            result = [process_user_data(user) for user in input_data]
        else:
            raise ValueError('Input must be object or array')
        
        # Return result to PHP
        print(json.dumps(result, indent=2))
        
    except Exception as e:
        error_result = {
            'error': str(e),
            'type': type(e).__name__
        }
        print(json.dumps(error_result))
        sys.exit(1)


if __name__ == '__main__':
    main()




