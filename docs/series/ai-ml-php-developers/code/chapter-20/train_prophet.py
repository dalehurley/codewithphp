#!/usr/bin/env python3
"""
Facebook Prophet forecasting script callable from PHP.
Reads sales data from JSON, trains Prophet model, outputs forecasts as JSON.
"""

import sys
import json
from datetime import datetime
from prophet import Prophet
import pandas as pd

def load_data_from_json(json_data):
    """Load and prepare data from JSON string."""
    data = json.loads(json_data)
    
    # Prophet requires columns named 'ds' (date) and 'y' (value)
    df = pd.DataFrame([
        {
            'ds': record['month'] + '-01',  # Add day for full date
            'y': record['revenue']
        }
        for record in data
    ])
    
    df['ds'] = pd.to_datetime(df['ds'])
    return df

def train_and_forecast(df, periods=6, freq='M'):
    """Train Prophet model and generate forecasts."""
    # Initialize Prophet with yearly seasonality
    model = Prophet(
        yearly_seasonality=True,
        weekly_seasonality=False,  # Not relevant for monthly data
        daily_seasonality=False,   # Not relevant for monthly data
        seasonality_mode='multiplicative',  # Better for % changes
        changepoint_prior_scale=0.05  # Control trend flexibility
    )
    
    # Train the model
    model.fit(df)
    
    # Create future dataframe
    future = model.make_future_dataframe(periods=periods, freq=freq)
    
    # Generate forecast
    forecast = model.predict(future)
    
    # Extract only the forecast periods (not fitted values)
    forecast_only = forecast.tail(periods)
    
    return forecast_only[['ds', 'yhat', 'yhat_lower', 'yhat_upper']]

def main():
    """Main execution: read from stdin, forecast, write to stdout."""
    try:
        # Read input from stdin (JSON string)
        input_json = sys.stdin.read()
        
        if not input_json.strip():
            raise ValueError("No input data provided")
        
        # Load data
        df = load_data_from_json(input_json)
        
        # Train and forecast
        forecast_df = train_and_forecast(df, periods=6, freq='MS')
        
        # Convert to JSON output
        result = []
        for _, row in forecast_df.iterrows():
            result.append({
                'month': row['ds'].strftime('%Y-%m'),
                'forecast': float(row['yhat']),
                'lower_bound': float(row['yhat_lower']),
                'upper_bound': float(row['yhat_upper']),
                'method': 'Prophet'
            })
        
        # Output JSON to stdout
        print(json.dumps({
            'success': True,
            'forecasts': result
        }))
        
    except Exception as e:
        # Output error as JSON
        print(json.dumps({
            'success': False,
            'error': str(e)
        }))
        sys.exit(1)

if __name__ == '__main__':
    main()

