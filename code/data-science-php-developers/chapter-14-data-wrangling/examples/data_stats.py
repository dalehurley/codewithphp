"""
Python Data Service Scripts
Supporting scripts for PHP integration
"""
import sys
import json
import pandas as pd
import numpy as np

def get_statistics(file_path):
    """Get comprehensive statistics for a CSV file"""
    df = pd.read_csv(file_path)
    
    stats = {
        'row_count': len(df),
        'column_count': len(df.columns),
        'columns': list(df.columns),
        'dtypes': {col: str(dtype) for col, dtype in df.dtypes.items()},
        'missing_values': df.isnull().sum().to_dict(),
        'numeric_stats': {}
    }
    
    # Add numeric column statistics
    numeric_cols = df.select_dtypes(include=[np.number]).columns
    for col in numeric_cols:
        stats['numeric_stats'][col] = {
            'mean': float(df[col].mean()),
            'median': float(df[col].median()),
            'std': float(df[col].std()),
            'min': float(df[col].min()),
            'max': float(df[col].max())
        }
    
    return stats

def clean_dataset(input_file, output_file):
    """Basic cleaning operations"""
    df = pd.read_csv(input_file)
    
    # Remove duplicates
    before_count = len(df)
    df = df.drop_duplicates()
    after_count = len(df)
    
    # Fill numeric NaNs with median
    numeric_cols = df.select_dtypes(include=[np.number]).columns
    for col in numeric_cols:
        df[col] = df[col].fillna(df[col].median())
    
    # Save cleaned data
    df.to_csv(output_file, index=False)
    
    return {
        'rows_before': before_count,
        'rows_after': after_count,
        'rows_cleaned': before_count - after_count,
        'output_file': output_file
    }

def main():
    try:
        # Read input from PHP
        input_data = json.load(sys.stdin)
        task = input_data.get('task')
        
        if task == 'stats':
            result = get_statistics(input_data['file'])
        elif task == 'clean':
            result = clean_dataset(input_data['input_file'], input_data['output_file'])
        else:
            result = {'error': f'Unknown task: {task}'}
        
        print(json.dumps(result))
        
    except Exception as e:
        print(json.dumps({'error': str(e)}))
        sys.exit(1)

if __name__ == '__main__':
    main()
