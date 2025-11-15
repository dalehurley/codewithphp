---
title: "Chapter 07: Properties, Methods, and Initializers"
description: Master Swift's property system, methods, and strict initialization requirements.
series: swift-for-php-developers
chapter: 7
difficulty: Intermediate
tags: ["properties", "methods", "initializers", "computed-properties", "property-observers"]
---

# Chapter 07: Properties, Methods, and Initializers

Swift's property system is far more sophisticated than PHP's. Beyond simple stored properties, Swift offers computed properties, property observers, lazy properties, and strict initialization rules. This chapter explores these features and shows how they differ from PHP's approach.

## What You'll Learn

- Stored properties vs computed properties
- Property observers (willSet/didSet)
- Lazy properties
- Instance methods vs type methods
- Initializers and Swift's initialization rules
- Convenience initializers
- How all of this compares to PHP

## Prerequisites

- Completed [Chapter 06: Classes and Structs](/series/swift-for-php-developers/chapters/06-classes-structs-value-reference-types)
- Understanding of PHP constructors and properties
- Basic OOP knowledge

---

## Properties Overview

### PHP Approach

```php
<?php
class User {
    // Properties
    public string $name;
    private int $age;

    // Constructor
    public function __construct(string $name, int $age) {
        $this->name = $name;
        $this->age = $age;
    }

    // Getter
    public function getAge(): int {
        return $this->age;
    }

    // Setter with validation
    public function setAge(int $age): void {
        if ($age < 0) {
            throw new InvalidArgumentException("Age cannot be negative");
        }
        $this->age = $age;
    }
}
```

### Swift Approach

```swift
struct User {
    // Stored properties
    var name: String
    private var _age: Int

    // Computed property (like getter/setter)
    var age: Int {
        get { _age }
        set {
            if newValue < 0 {
                fatalError("Age cannot be negative")
            }
            _age = newValue
        }
    }

    // Initializer
    init(name: String, age: Int) {
        self.name = name
        self._age = age
    }
}
```

---

## Stored Properties

Properties that store actual values.

### Basic Stored Properties

```swift
struct Rectangle {
    var width: Double
    var height: Double
}

class Person {
    var name: String
    var age: Int

    init(name: String, age: Int) {
        self.name = name
        self.age = age
    }
}
```

**PHP Comparison:**
```php
<?php
class Rectangle {
    public float $width;
    public float $height;
}

class Person {
    public string $name;
    public int $age;

    public function __construct(string $name, int $age) {
        $this->name = $name;
        $this->age = $age;
    }
}
```

### Default Values

```swift
struct Config {
    var theme: String = "light"
    var fontSize: Int = 14
    var autoSave: Bool = true
}

let config = Config()  // Uses defaults
print(config.theme)  // "light"

let custom = Config(theme: "dark", fontSize: 16, autoSave: false)
```

**PHP Comparison:**
```php
<?php
class Config {
    public string $theme = 'light';
    public int $fontSize = 14;
    public bool $autoSave = true;
}
```

### Constant Properties (let)

```swift
struct User {
    let id: Int        // Cannot change after initialization
    var name: String   // Can change
}

var user = User(id: 1, name: "John")
user.name = "Jane"  // ✅ OK
// user.id = 2      // ❌ Error: Cannot assign to property: 'id' is a 'let' constant
```

---

## Computed Properties

Properties that calculate their value rather than storing it.

### Read-Only Computed Property

```swift
struct Circle {
    var radius: Double

    // Computed property (no storage)
    var area: Double {
        return Double.pi * radius * radius
    }

    var circumference: Double {
        Double.pi * 2 * radius  // Implicit return
    }
}

let circle = Circle(radius: 5)
print(circle.area)  // 78.54...
```

**PHP Comparison:**
```php
<?php
class Circle {
    public float $radius;

    public function __construct(float $radius) {
        $this->radius = $radius;
    }

    // Method, not property
    public function area(): float {
        return M_PI * $this->radius * $this->radius;
    }
}

$circle = new Circle(5);
echo $circle->area();  // Must call as method
```

### Read-Write Computed Property

```swift
struct Temperature {
    var celsius: Double

    var fahrenheit: Double {
        get {
            return celsius * 9/5 + 32
        }
        set {
            celsius = (newValue - 32) * 5/9
        }
    }
}

var temp = Temperature(celsius: 0)
print(temp.fahrenheit)  // 32.0

temp.fahrenheit = 212
print(temp.celsius)  // 100.0
```

**PHP Comparison:**
```php
<?php
class Temperature {
    public float $celsius;

    public function getFahrenheit(): float {
        return $this->celsius * 9/5 + 32;
    }

    public function setFahrenheit(float $value): void {
        $this->celsius = ($value - 32) * 5/9;
    }
}

$temp = new Temperature();
$temp->celsius = 0;
echo $temp->getFahrenheit();  // 32

$temp->setFahrenheit(212);
echo $temp->celsius;  // 100
```

### Shorthand Getter

```swift
struct Person {
    var firstName: String
    var lastName: String

    // Read-only computed property (shorthand)
    var fullName: String {
        "\(firstName) \(lastName)"
    }
}

let person = Person(firstName: "John", lastName: "Doe")
print(person.fullName)  // "John Doe"
```

---

## Property Observers

Execute code when property value changes. **PHP doesn't have this!**

### willSet and didSet

```swift
class BankAccount {
    var balance: Double = 0 {
        willSet {
            print("About to set balance to \(newValue)")
        }
        didSet {
            print("Balance changed from \(oldValue) to \(balance)")
            if balance < 0 {
                print("Warning: Negative balance!")
            }
        }
    }
}

let account = BankAccount()
account.balance = 100
// Prints:
// "About to set balance to 100.0"
// "Balance changed from 0.0 to 100.0"

account.balance = -50
// Prints:
// "About to set balance to -50.0"
// "Balance changed from 100.0 to -50.0"
// "Warning: Negative balance!"
```

**PHP Comparison (Workaround):**
```php
<?php
class BankAccount {
    private float $balance = 0;

    public function setBalance(float $value): void {
        $oldValue = $this->balance;
        echo "About to set balance to $value\n";

        $this->balance = $value;

        echo "Balance changed from $oldValue to $this->balance\n";
        if ($this->balance < 0) {
            echo "Warning: Negative balance!\n";
        }
    }

    public function getBalance(): float {
        return $this->balance;
    }
}

// Must use setter
$account = new BankAccount();
$account->setBalance(100);
```

### Validation with Property Observers

```swift
class User {
    var age: Int = 0 {
        didSet {
            if age < 0 {
                age = oldValue  // Revert to previous value
                print("Invalid age, keeping \(age)")
            }
        }
    }
}

let user = User()
user.age = 30   // ✅ OK
user.age = -5   // ❌ Reverts to 30
```

---

## Lazy Properties

Only computed when first accessed. **Very useful for expensive operations!**

### Lazy Stored Property

```swift
class ImageProcessor {
    let filename: String

    // Expensive operation - only runs when accessed
    lazy var image: UIImage = {
        print("Loading image...")
        return UIImage(named: filename) ?? UIImage()
    }()

    init(filename: String) {
        self.filename = filename
        print("ImageProcessor initialized")
    }
}

let processor = ImageProcessor(filename: "photo.jpg")
// Prints: "ImageProcessor initialized"
// Image NOT loaded yet!

let img = processor.image
// Now prints: "Loading image..."
// Image loaded!

let img2 = processor.image
// Doesn't print anything - already loaded
```

**PHP Comparison:**
```php
<?php
class ImageProcessor {
    private ?Image $image = null;

    public function __construct(private string $filename) {
        echo "ImageProcessor initialized\n";
    }

    public function getImage(): Image {
        if ($this->image === null) {
            echo "Loading image...\n";
            $this->image = Image::load($this->filename);
        }
        return $this->image;
    }
}
```

**Key Difference:** Swift's `lazy` is built-in and thread-safe (for value types).

---

## Type Properties (Static)

Properties that belong to the type itself, not instances.

### Static Properties

```swift
struct Math {
    static let pi = 3.14159265359
    static var computationCount = 0

    static func calculate() {
        computationCount += 1
    }
}

print(Math.pi)  // 3.14159...
Math.calculate()
print(Math.computationCount)  // 1
```

**PHP Comparison:**
```php
<?php
class Math {
    public const PI = 3.14159265359;
    public static int $computationCount = 0;

    public static function calculate(): void {
        self::$computationCount++;
    }
}

echo Math::PI;
Math::calculate();
echo Math::$computationCount;  // 1
```

### Class vs Static (Classes Only)

```swift
class Vehicle {
    static var count = 0
    class var description: String {  // Can be overridden
        return "A vehicle"
    }
}

class Car: Vehicle {
    override class var description: String {
        return "A car"
    }
}

print(Vehicle.description)  // "A vehicle"
print(Car.description)      // "A car"
```

**`class` allows subclasses to override, `static` doesn't.**

---

## Methods

Functions defined within types.

### Instance Methods

```swift
struct Counter {
    var count = 0

    // Non-mutating method (read-only)
    func currentValue() -> Int {
        return count
    }

    // Mutating method (modifies struct)
    mutating func increment() {
        count += 1
    }

    mutating func increment(by amount: Int) {
        count += amount
    }
}

var counter = Counter()
print(counter.currentValue())  // 0
counter.increment()
print(counter.currentValue())  // 1
counter.increment(by: 5)
print(counter.currentValue())  // 6
```

**Key Point:** Structs need `mutating` keyword for methods that modify properties.

**PHP Comparison:**
```php
<?php
class Counter {
    public int $count = 0;

    public function currentValue(): int {
        return $this->count;
    }

    public function increment(): void {
        $this->count++;
    }

    public function incrementBy(int $amount): void {
        $this->count += $amount;
    }
}
```

### Type Methods (Static)

```swift
struct Math {
    static func add(_ a: Int, _ b: Int) -> Int {
        return a + b
    }

    static func multiply(_ a: Int, _ b: Int) -> Int {
        return a * b
    }
}

let sum = Math.add(5, 3)  // 8
let product = Math.multiply(4, 7)  // 28
```

**PHP Comparison:**
```php
<?php
class Math {
    public static function add(int $a, int $b): int {
        return $a + $b;
    }

    public static function multiply(int $a, int $b): int {
        return $a * $b;
    }
}

$sum = Math::add(5, 3);
$product = Math::multiply(4, 7);
```

---

## Initializers

Swift has strict initialization rules to ensure all properties have values.

### Basic Initializer

```swift
struct User {
    let id: Int
    var name: String

    init(id: Int, name: String) {
        self.id = id
        self.name = name
    }
}

let user = User(id: 1, name: "John")
```

### Memberwise Initializer (Structs Only)

```swift
struct Point {
    var x: Int
    var y: Int
}

// Automatic memberwise initializer
let point = Point(x: 10, y: 20)
```

**Structs get this for free if you don't define custom init!**

### Multiple Initializers

```swift
struct Rectangle {
    var width: Double
    var height: Double

    // Full initializer
    init(width: Double, height: Double) {
        self.width = width
        self.height = height
    }

    // Square initializer
    init(side: Double) {
        self.width = side
        self.height = side
    }

    // Default initializer
    init() {
        self.width = 0
        self.height = 0
    }
}

let rect1 = Rectangle(width: 10, height: 20)
let square = Rectangle(side: 15)
let origin = Rectangle()
```

**PHP Comparison:**
```php
<?php
class Rectangle {
    public function __construct(
        public float $width = 0,
        public float $height = 0
    ) {
        // If only one arg, make it a square
        if (func_num_args() === 1) {
            $this->height = $width;
        }
    }
}

// PHP uses named arguments for flexibility
$rect1 = new Rectangle(width: 10, height: 20);
$square = new Rectangle(15);  // Square
$origin = new Rectangle();
```

### Failable Initializers

Return `nil` if initialization fails.

```swift
struct User {
    let id: Int
    let name: String

    init?(id: Int, name: String) {
        guard id > 0, !name.isEmpty else {
            return nil  // Initialization failed
        }
        self.id = id
        self.name = name
    }
}

if let user = User(id: 1, name: "John") {
    print("Created user: \(user.name)")
} else {
    print("Failed to create user")
}

let invalid = User(id: -1, name: "")  // nil
```

**PHP Comparison:**
```php
<?php
class User {
    public function __construct(
        public int $id,
        public string $name
    ) {
        if ($id <= 0 || empty($name)) {
            throw new InvalidArgumentException("Invalid user data");
        }
    }
}

try {
    $user = new User(1, "John");
} catch (InvalidArgumentException $e) {
    echo "Failed to create user";
}
```

### Convenience Initializers (Classes)

```swift
class Person {
    var name: String
    var age: Int

    // Designated initializer
    init(name: String, age: Int) {
        self.name = name
        self.age = age
    }

    // Convenience initializer
    convenience init(name: String) {
        self.init(name: name, age: 0)  // Calls designated init
    }
}

let person1 = Person(name: "John", age: 30)
let person2 = Person(name: "Jane")  // Age defaults to 0
```

**PHP doesn't have convenience initializers—use default parameters instead.**

---

## Initialization Rules (Critical!)

Swift has **strict rules** to ensure properties are initialized:

1. **All stored properties must be set** before init completes
2. **Cannot call instance methods** until fully initialized
3. **Cannot access self** until fully initialized

```swift
struct User {
    let id: Int
    var name: String
    var email: String

    init(id: Int, name: String) {
        self.id = id
        self.name = name
        // self.email missing!
        // ❌ Error: Return from initializer without initializing all stored properties
    }
}

// ✅ Correct:
struct User {
    let id: Int
    var name: String
    var email: String

    init(id: Int, name: String, email: String) {
        self.id = id
        self.name = name
        self.email = email  // All properties set!
    }
}
```

---

## Deinitializers (Classes Only)

Called when class instance is deallocated.

```swift
class FileHandle {
    let filename: String

    init(filename: String) {
        self.filename = filename
        print("Opening \(filename)")
    }

    deinit {
        print("Closing \(filename)")
    }
}

do {
    let file = FileHandle(filename: "data.txt")
    // Use file...
}  // deinit called here
// Prints: "Closing data.txt"
```

**PHP Comparison:**
```php
<?php
class FileHandle {
    public function __construct(public string $filename) {
        echo "Opening $filename\n";
    }

    public function __destruct() {
        echo "Closing $this->filename\n";
    }
}

{
    $file = new FileHandle("data.txt");
}  // __destruct called
```

---

## Property Wrappers (Advanced)

Reusable property logic. **Swift 5.1+**

```swift
@propertyWrapper
struct Clamped<Value: Comparable> {
    private var value: Value
    private let range: ClosedRange<Value>

    init(wrappedValue: Value, _ range: ClosedRange<Value>) {
        self.range = range
        self.value = min(max(wrappedValue, range.lowerBound), range.upperBound)
    }

    var wrappedValue: Value {
        get { value }
        set { value = min(max(newValue, range.lowerBound), range.upperBound) }
    }
}

struct Game {
    @Clamped(0...100) var health = 100
    @Clamped(0...10) var level = 1
}

var game = Game()
game.health = 150  // Clamped to 100
game.health = -10  // Clamped to 0
print(game.health)  // 0
```

**No PHP equivalent—must implement manually.**

---

## Real-World Example: User Model

```swift
struct User {
    // Stored properties
    let id: Int
    private var _name: String
    private var _email: String

    // Computed property with validation
    var name: String {
        get { _name }
        set {
            guard !newValue.isEmpty else {
                print("Name cannot be empty")
                return
            }
            _name = newValue
        }
    }

    var email: String {
        get { _email }
        set {
            guard newValue.contains("@") else {
                print("Invalid email")
                return
            }
            _email = newValue
        }
    }

    // Computed property
    var displayName: String {
        "\(_name) (\(_email))"
    }

    // Initializer
    init(id: Int, name: String, email: String) {
        self.id = id
        self._name = name
        self._email = email
    }

    // Method
    func greet() -> String {
        "Hello, \(_name)!"
    }
}

var user = User(id: 1, name: "John", email: "john@example.com")
print(user.displayName)  // "John (john@example.com)"
user.email = "invalid"   // Prints: "Invalid email"
```

---

## Summary

You've mastered Swift properties and methods:

✅ **Stored properties** hold actual values
✅ **Computed properties** calculate values on-the-fly
✅ **Property observers** (willSet/didSet) react to changes
✅ **Lazy properties** delay expensive initialization
✅ **Type properties** belong to the type, not instances
✅ **Mutating methods** required for struct modifications
✅ **Strict initialization** ensures all properties are set
✅ **Failable initializers** return nil on failure

**Key Takeaway:** Swift's property system is far more sophisticated than PHP's simple properties and getters/setters. Understanding these features enables cleaner, safer code.

---

## What's Next?

In [Chapter 08: Protocols](/series/swift-for-php-developers/chapters/08-protocols-interfaces), you'll learn about Swift's most powerful feature—protocols and protocol-oriented programming. This is fundamentally different from PHP interfaces!

---

**Next Chapter:** [08 — Protocols: Swift's Answer to Interfaces](/series/swift-for-php-developers/chapters/08-protocols-interfaces)
