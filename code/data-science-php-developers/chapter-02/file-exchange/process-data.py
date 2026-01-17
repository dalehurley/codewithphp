#!/usr/bin/env python3
"""
File Exchange Example - Python Processing

Demonstrates processing PHP-exported data.
"""

import pandas as pd

def process_data():
    # Read CSV from PHP
    df = pd.read_csv('data-export.csv')
    
    print(f"Loaded {len(df)} records")
    
    # Process: calculate mean by category
    result = df.groupby('category')['value'].agg(['mean', 'count'])
    
    # Save results for PHP
    result.to_csv('data-results.csv')
    
    print("\nResults:")
    print(result)
    print("\n✓ Results saved to data-results.csv")
    print("Run: php import-results.php")

if __name__ == '__main__':
    process_data()


