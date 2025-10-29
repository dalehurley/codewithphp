"""
Basic Python script that receives data from PHP and returns a response.

This demonstrates the fundamental pattern for PHP-Python integration.
"""

import sys
import json


def main():
    # Python can receive data via command-line arguments
    if len(sys.argv) < 2:
        print(json.dumps({'error': 'No data provided'}))
        sys.exit(1)
    
    # Parse JSON input from PHP
    try:
        input_data = json.loads(sys.argv[1])
        name = input_data.get('name', 'World')
        
        # Process the data (trivial example)
        result = {
            'greeting': f'Hello, {name}!',
            'processed_by': 'Python 3',
            'input_received': input_data
        }
        
        # Output JSON for PHP to parse
        print(json.dumps(result))
    except json.JSONDecodeError as e:
        print(json.dumps({'error': f'Invalid JSON: {str(e)}'}))
        sys.exit(1)


if __name__ == '__main__':
    main()




