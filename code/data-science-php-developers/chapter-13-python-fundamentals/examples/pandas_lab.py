"""
Pandas DataFrames Lab
Master pandas for tabular data manipulation
"""
import pandas as pd
import numpy as np

# 1. Creating a DataFrame from scratch
# PHP: $data = [['name' => 'A', 'sales' => 100], ...]
data = {
    'product': ['Laptop', 'Mouse', 'Monitor', 'Keyboard', 'Webcam', 'Microphone'],
    'category': ['Electronics', 'Peripherals', 'Electronics', 'Peripherals', 'Peripherals', 'Audio'],
    'sales': [1200, 25, 300, 75, 150, np.nan],  # Missing value!
    'stock': [15, 100, 8, 40, 25, 10]
}

df = pd.DataFrame(data)

# 2. Handling Missing Data (Crucial!)
# PHP devs often forget to check for nulls in math
print("--- Check for Nulls ---")
print(df.isnull().sum())

# Fill missing sales with the mean
df['sales'] = df['sales'].fillna(df['sales'].mean())
print("\n--- DataFrame after filling NaNs ---")
print(df)

# 3. Advanced Filtering (Like SQL WHERE)
# PHP: complex array_filter
high_value_peripherals = df[(df['category'] == 'Peripherals') & (df['sales'] > 50)]
print("\n--- High Value Peripherals ---")
print(high_value_peripherals)

# 4. Feature Engineering
df['revenue'] = df['sales'] * 0.8
df['status'] = np.where(df['stock'] < 20, 'LOW', 'OK')
print("\n--- Feature Engineering Result ---")
print(df[['product', 'stock', 'status']])

# 5. Grouping and Aggregation (Powerful!)
# PHP: usually requires nested loops and array_reduce
summary = df.groupby('category').agg({
    'sales': ['sum', 'mean'],
    'stock': 'min'
})
print("\n--- Category Summary ---")
print(summary)

# 6. Merging DataFrames (Like SQL JOIN)
inventory_data = pd.DataFrame({
    'product': ['Laptop', 'Mouse', 'Monitor'],
    'supplier': ['Dell', 'Logitech', 'Samsung']
})

# Left Join
merged_df = df.merge(inventory_data, on='product', how='left')
print("\n--- Merged (Joined) Data ---")
print(merged_df[['product', 'category', 'supplier']])
