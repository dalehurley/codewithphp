"""
Python vs PHP Syntax Comparison
Demonstrates core Python concepts from a PHP developer's perspective
"""

# 1. Variables and Data Types
name = "PHP Developer"
age = 30
is_ready = True
price = 19.99

# 2. Lists (PHP Indexed Arrays)
fruits = ["apple", "banana", "cherry"]
fruits.append("date")  # $fruits[] = 'date';
print(f"Second fruit: {fruits[1]}")  # Indices start at 0

# 3. Dictionaries (PHP Associative Arrays)
user = {
    "name": "Dale",
    "role": "Developer",
    "skills": ["PHP", "SQL", "Python"]
}
print(f"User Name: {user['name']}")

# 4. Control Structures (Note the indentation!)
if age >= 18:
    print(f"{name} is an adult")  # f-string like "{$name}"
elif age > 13:
    print(f"{name} is a teen")    # PHP: elseif
else:
    print(f"{name} is a minor")

# 5. Loops
print("\nLooping through fruits with index:")
for i, fruit in enumerate(fruits):  # PHP: foreach($fruits as $i => $fruit)
    print(f"  {i}: {fruit}")

# 6. List Comprehensions (Powerful Python idiom!)
# PHP: $squared = array_map(fn($x) => $x * $x, [1, 2, 3, 4, 5]);
numbers = [1, 2, 3, 4, 5]
squared = [x * x for x in numbers]
print(f"\nSquared numbers: {squared}")

# Filtering with list comprehensions
# PHP: $evens = array_filter($numbers, fn($x) => $x % 2 == 0);
evens = [x for x in numbers if x % 2 == 0]
print(f"Even numbers: {evens}")

# 7. Functions and Type Hinting (Modern Python)
def calculate_total(price: float, tax_rate: float = 0.05) -> float:
    """Calculates total with tax (Python uses -> for return types)"""
    return price * (1 + tax_rate)

final_price = calculate_total(100.0, 0.2)
print(f"Final price: ${final_price:.2f}")

# 8. Classes (Briefly)
class DataAnalyzer:
    def __init__(self, data_source):  # Constructor (__construct)
        self.data_source = data_source
        self.is_processed = False

    def process(self):
        print(f"Processing data from {self.data_source}...")
        self.is_processed = True

analyzer = DataAnalyzer("sales.csv")
analyzer.process()
