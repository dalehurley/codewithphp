"""
Real-World Messy Dataset Example
Demonstrates cleaning a realistic dataset with common data quality issues
"""
import pandas as pd
import numpy as np
import re

# Create a messy e-commerce dataset (simulates real-world data)
print("Creating messy e-commerce dataset...")

np.random.seed(42)
size = 5000

# Intentionally messy data
data = {
    'order_id': range(1, size + 1),
    'customer_email': [
        f"user{i}@EXAMPLE.com" if i % 3 == 0 else 
        f"  user{i}@example.COM  " if i % 5 == 0 else
        f"user{i}@example.com" if i % 7 == 0 else
        "invalid-email" if i % 50 == 0 else
        np.nan if i % 100 == 0 else
        f"user{i}@example.com"
        for i in range(size)
    ],
    'product_name': [
        "   iPhone 13   " if i % 5 == 0 else
        "iPhone 13" if i % 7 == 0 else
        "IPHONE 13" if i % 11 == 0 else
        "iPhone13" if i % 13 == 0 else
        "iPhone 13"
        for i in range(size)
    ],
    'price': [
        np.random.uniform(10, 1000) if i % 20 != 0 else
        -50 if i % 100 == 0 else  # Invalid negative price
        99999 if i % 200 == 0 else  # Outlier
        np.nan  # Missing value
        for i in range(size)
    ],
    'quantity': [
        np.random.randint(1, 10) if i % 30 != 0 else
        0 if i % 100 == 0 else  # Invalid zero
        np.nan  # Missing value
        for i in range(size)
    ],
    'order_date': [
        pd.Timestamp('2024-01-01') + pd.Timedelta(days=np.random.randint(0, 365))
        if i % 40 != 0 else
        "2024-13-45" if i % 200 == 0 else  # Invalid date
        np.nan  # Missing date
        for i in range(size)
    ],
    'status': [
        np.random.choice(['completed', 'pending', 'cancelled'])
        if i % 50 != 0 else
        "COMPLETED" if i % 100 == 0 else  # Case inconsistency
        "unknown" if i % 150 == 0 else  # Invalid status
        np.nan  # Missing status
        for i in range(size)
    ]
}

df = pd.DataFrame(data)

print(f"Original dataset: {len(df)} rows")
print("\n" + "="*60)
print("DATA QUALITY ISSUES")
print("="*60)

# 1. Check missing values
print("\n1. Missing Values:")
missing = df.isnull().sum()
print(missing[missing > 0])

# 2. Check duplicates
print(f"\n2. Duplicate order_ids: {df['order_id'].duplicated().sum()}")

# 3. Check data types
print("\n3. Data Types:")
print(df.dtypes)

# 4. Check for invalid emails
print("\n4. Email Quality:")
valid_email_pattern = r'^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$'
invalid_emails = df['customer_email'].apply(
    lambda x: not bool(re.match(valid_email_pattern, str(x))) if pd.notna(x) else True
)
print(f"  Invalid emails: {invalid_emails.sum()}")

# 5. Check for outliers
print("\n5. Price Outliers:")
print(f"  Negative prices: {(df['price'] < 0).sum()}")
print(f"  Suspiciously high (>10000): {(df['price'] > 10000).sum()}")
print(f"  Price stats:\n{df['price'].describe()}")

print("\n" + "="*60)
print("CLEANING PIPELINE")
print("="*60)

# Create a copy for cleaning
df_clean = df.copy()

# Step 1: Clean emails
print("\n1. Cleaning emails...")
df_clean['customer_email'] = df_clean['customer_email'].str.strip().str.lower()

# Remove clearly invalid emails
df_clean.loc[
    ~df_clean['customer_email'].str.contains('@', na=False),
    'customer_email'
] = np.nan

print(f"  Cleaned {invalid_emails.sum()} invalid emails")

# Step 2: Standardize product names
print("\n2. Standardizing product names...")
df_clean['product_name'] = (
    df_clean['product_name']
    .str.strip()
    .str.replace(' +', ' ', regex=True)  # Multiple spaces to one
    .str.title()  # Title case
)
print(f"  Product name variations reduced")

# Step 3: Handle missing prices
print("\n3. Handling missing and invalid prices...")
# Remove negative prices
df_clean.loc[df_clean['price'] < 0, 'price'] = np.nan

# Cap outliers at 99th percentile
price_99th = df_clean['price'].quantile(0.99)
df_clean.loc[df_clean['price'] > price_99th, 'price'] = price_99th

# Fill missing prices with median per product
df_clean['price'] = df_clean.groupby('product_name')['price'].transform(
    lambda x: x.fillna(x.median())
)

print(f"  Prices cleaned and outliers capped at {price_99th:.2f}")

# Step 4: Handle quantities
print("\n4. Handling quantities...")
# Replace 0 with NaN, then fill with median
df_clean.loc[df_clean['quantity'] == 0, 'quantity'] = np.nan
df_clean['quantity'] = df_clean['quantity'].fillna(df_clean['quantity'].median())

# Step 5: Parse and validate dates
print("\n5. Cleaning dates...")
df_clean['order_date'] = pd.to_datetime(df_clean['order_date'], errors='coerce')

# Remove future dates
today = pd.Timestamp.now()
df_clean.loc[df_clean['order_date'] > today, 'order_date'] = np.nan

# Fill missing dates with mode
date_mode = df_clean['order_date'].mode()[0]
df_clean['order_date'] = df_clean['order_date'].fillna(date_mode)

# Step 6: Standardize status
print("\n6. Standardizing status...")
valid_statuses = ['completed', 'pending', 'cancelled']
df_clean['status'] = df_clean['status'].str.lower()
df_clean.loc[~df_clean['status'].isin(valid_statuses), 'status'] = 'pending'
df_clean['status'] = df_clean['status'].fillna('pending')

# Step 7: Add derived features
print("\n7. Adding derived features...")
df_clean['total_amount'] = df_clean['price'] * df_clean['quantity']
df_clean['order_year'] = df_clean['order_date'].dt.year
df_clean['order_month'] = df_clean['order_date'].dt.month
df_clean['order_day_of_week'] = df_clean['order_date'].dt.day_name()

# Step 8: Remove rows with critical missing data
print("\n8. Removing rows with critical missing data...")
initial_count = len(df_clean)
df_clean = df_clean.dropna(subset=['customer_email', 'price'])
removed = initial_count - len(df_clean)
print(f"  Removed {removed} rows with missing critical data")

print("\n" + "="*60)
print("CLEANED DATASET SUMMARY")
print("="*60)

print(f"\nFinal dataset: {len(df_clean)} rows ({(len(df_clean)/len(df)*100):.1f}% retained)")
print("\nMissing values after cleaning:")
print(df_clean.isnull().sum())

print("\nData quality scores:")
print(f"  Emails valid: {(~df_clean['customer_email'].isnull()).sum() / len(df_clean) * 100:.1f}%")
print(f"  Prices valid: {(~df_clean['price'].isnull()).sum() / len(df_clean) * 100:.1f}%")
print(f"  Dates valid: {(~df_clean['order_date'].isnull()).sum() / len(df_clean) * 100:.1f}%")

# Save cleaned data
print("\nSaving cleaned data...")
df_clean.to_csv('data/cleaned_ecommerce.csv', index=False)
print("  Saved to data/cleaned_ecommerce.csv")

# Generate summary statistics
print("\n" + "="*60)
print("BUSINESS INSIGHTS")
print("="*60)

print("\nTop 5 Products by Revenue:")
product_revenue = df_clean.groupby('product_name').agg({
    'total_amount': 'sum',
    'quantity': 'sum'
}).sort_values('total_amount', ascending=False).head()
print(product_revenue)

print("\nMonthly Order Trends:")
monthly = df_clean.groupby('order_month').agg({
    'order_id': 'count',
    'total_amount': 'sum'
}).rename(columns={'order_id': 'order_count'})
print(monthly)

print("\nStatus Distribution:")
print(df_clean['status'].value_counts())

print("\n" + "="*60)
print("CLEANING PIPELINE COMPLETE!")
print("="*60)
print("""
Key Cleaning Steps Applied:
1. ✓ Trimmed and normalized text (emails, product names)
2. ✓ Validated and standardized email formats
3. ✓ Removed negative prices and capped outliers
4. ✓ Filled missing values using statistical methods
5. ✓ Parsed and validated dates
6. ✓ Standardized categorical values (status)
7. ✓ Added derived features for analysis
8. ✓ Removed rows with critical missing data

This dataset is now ready for:
- Machine learning models
- Statistical analysis
- Business intelligence dashboards
- Predictive modeling
""")
