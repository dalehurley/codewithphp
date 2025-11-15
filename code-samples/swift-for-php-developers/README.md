# Swift for PHP Developers - Code Samples

Production-ready Swift code samples for the **[Swift for PHP Developers Series](https://github.com/dalehurley/codewithphp/tree/main/docs/series/swift-for-php-developers)**.

## 📊 Statistics

- **Total Chapters**: 38 (Chapters 00-37)
- **Total Appendices**: 5 (A-E)
- **Swift Version**: 5.9+
- **iOS**: 17.0+
- **macOS**: 14.0+
- **Frameworks**: SwiftUI, Vapor, Combine

## 🎯 What's Included

Every code sample in this repository features:

✅ **Complete, Runnable Code** - Execute directly in Xcode or command line
✅ **Modern Swift Syntax** - Swift 5.9+ with latest features
✅ **PHP Comparisons** - Side-by-side examples showing PHP equivalents
✅ **Comprehensive Comments** - Clear documentation for all code
✅ **Real-World Examples** - Practical applications from PHP developer perspective
✅ **Production Ready** - Following Swift best practices and conventions

## 📚 Quick Start

### Run an iOS Example

```bash
# Navigate to code samples directory
cd /home/user/codewithphp/code-samples/swift-for-php-developers

# Open a chapter's Xcode project
open chapter-16/SwiftUIBasics/SwiftUIBasics.xcodeproj

# Build and run in Xcode (⌘R)
```

### Run a Command-Line Example

```bash
# Navigate to a chapter
cd chapter-02

# Run Swift file directly
swift syntax-comparisons.swift

# Or build with Swift Package Manager
swift build
swift run
```

### Run a Vapor Example

```bash
# Navigate to Vapor project
cd chapter-23/HelloVapor

# Run the server
swift run

# Visit http://localhost:8080
```

## 🗂️ Chapter Index

### Part 0: Getting Started

| Chapter | Topics | Files | Type |
|---------|--------|-------|------|
| [00](chapter-00/) | Quick Start Examples | 3 | Command-line |

### Part 1: Foundation (Chapters 01-05)

| Chapter | Topics | Files | Type |
|---------|--------|-------|------|
| [01](chapter-01/) | Environment Setup | 2 | Command-line |
| [02](chapter-02/) | Syntax Comparisons | 5 | Command-line |
| [03](chapter-03/) | Types and Variables | 4 | Command-line |
| [04](chapter-04/) | Optionals | 6 | Command-line |
| [05](chapter-05/) | Collections | 7 | Command-line |

### Part 2: OOP and Protocol-Oriented Swift (Chapters 06-11)

| Chapter | Topics | Files | Type |
|---------|--------|-------|------|
| [06](chapter-06/) | Classes vs Structs | 4 | Command-line |
| [07](chapter-07/) | Properties and Methods | 5 | Command-line |
| [08](chapter-08/) | Protocols | 6 | Command-line |
| [09](chapter-09/) | Enums | 4 | Command-line |
| [10](chapter-10/) | Generics | 5 | Command-line |
| [11](chapter-11/) | Error Handling | 4 | Command-line |

### Part 3: Memory and Advanced Features (Chapters 12-14)

| Chapter | Topics | Files | Type |
|---------|--------|-------|------|
| [12](chapter-12/) | ARC and Memory | 3 | Xcode project |
| [13](chapter-13/) | Closures | 5 | Command-line |
| [14](chapter-14/) | Extensions | 4 | Command-line |

### Part 4: iOS Development (Chapters 15-22)

| Chapter | Topics | Files | Type |
|---------|--------|-------|------|
| [15](chapter-15/) | iOS Introduction | 1 | Xcode project |
| [16](chapter-16/) | SwiftUI Basics | 1 | Xcode project |
| [17](chapter-17/) | State Management | 1 | Xcode project |
| [18](chapter-18/) | Navigation | 1 | Xcode project |
| [19](chapter-19/) | Networking | 1 | Xcode project (Weather App) |
| [20](chapter-20/) | Data Persistence | 1 | Xcode project |
| [21](chapter-21/) | Lists and Forms | 1 | Xcode project (Todo App) |
| [22](chapter-22/) | Apple Services | 1 | Xcode project |

### Part 5: Server-Side Swift (Chapters 23-27)

| Chapter | Topics | Files | Type |
|---------|--------|-------|------|
| [23](chapter-23/) | Vapor Introduction | 1 | Vapor project |
| [24](chapter-24/) | Routing & Controllers | 1 | Vapor project |
| [25](chapter-25/) | Fluent ORM | 1 | Vapor project |
| [26](chapter-26/) | Authentication | 1 | Vapor project |
| [27](chapter-27/) | WebSockets | 1 | Vapor project (Chat) |

### Part 6: Advanced Topics (Chapters 28-30)

| Chapter | Topics | Files | Type |
|---------|--------|-------|------|
| [28](chapter-28/) | Async/Await | 5 | Mixed |
| [29](chapter-29/) | Testing | 1 | Xcode project with tests |
| [30](chapter-30/) | Performance | 1 | Xcode project |

### Part 7: Deployment (Chapters 31-34)

| Chapter | Topics | Files | Type |
|---------|--------|-------|------|
| [31](chapter-31/) | App Store Submission | - | Documentation |
| [32](chapter-32/) | TestFlight | - | Documentation |
| [33](chapter-33/) | CI/CD | 2 | GitHub Actions config |
| [34](chapter-34/) | Server Deployment | 1 | Docker + Vapor |

### Part 8: Complete Applications (Chapters 35-37)

| Chapter | Topics | Files | Type |
|---------|--------|-------|------|
| [35](chapter-35/) | E-Commerce iOS App | 1 | Complete Xcode project |
| [36](chapter-36/) | Social Media API | 1 | Complete Vapor project |
| [37](chapter-37/) | Hybrid Swift + PHP | 2 | iOS + Laravel |

## 🚀 Requirements

### For iOS Development

- **macOS**: 11.0 or later
- **Xcode**: 15.0 or later
- **Swift**: 5.9 or later (included with Xcode)
- **iOS**: 17.0+ (for device deployment, simulator is fine for learning)

### For Server-Side Swift

- **macOS** or **Linux**
- **Swift**: 5.9 or later
- **Vapor**: 4.0+
- **Database**: PostgreSQL, MySQL, or SQLite

### Recommended

- **Mac** with Apple Silicon (M1/M2/M3) for best performance
- At least 8GB RAM (16GB recommended for Xcode)
- 50GB free disk space (Xcode + simulators)

## 📖 Code Quality

All code samples follow these standards:

### Modern Swift

```swift
// Explicit types when needed
let name: String = "John"

// Type inference when clear
let age = 30  // Inferred as Int

// Optionals for null safety
let user: User? = nil

// Strong typing
struct User {
    let id: Int
    let name: String
}
```

### Comprehensive Comments

```swift
/// Fetches user data from API
///
/// - Parameter id: User ID to fetch
/// - Returns: User object if found
/// - Throws: NetworkError if request fails
func fetchUser(id: Int) async throws -> User {
    // Implementation
}
```

### PHP Comparison Comments

```swift
// Swift version
let name = "John"
let greeting = "Hello, \(name)!"

// PHP equivalent:
// $name = "John";
// $greeting = "Hello, $name!";
```

## 🧪 Testing

Each chapter includes tests where appropriate:

```bash
# Run tests for a chapter
cd chapter-29/TestingExample
swift test

# Or in Xcode
⌘U (Command-U)
```

## 📈 Learning Paths

### Path 1: Quick Start (~15 hours)
Chapters: 00, 01, 02, 04, 08, 16, 19, 28

```bash
cd chapter-00 && swift run
cd ../chapter-01 && swift run
cd ../chapter-02 && swift run
# ... continue
```

### Path 2: iOS Development (~35 hours)
Chapters: 00-06, 10, 15-22, 28-31, 35

```bash
# Open iOS projects
open chapter-16/SwiftUIBasics.xcodeproj
open chapter-19/WeatherApp.xcodeproj
open chapter-35/ECommerceApp.xcodeproj
```

### Path 3: Server-Side Swift (~25 hours)
Chapters: 00-06, 10, 23-28, 34, 36

```bash
# Run Vapor examples
cd chapter-23/HelloVapor && swift run
cd ../chapter-24/RestAPI && swift run
cd ../chapter-36/SocialMediaAPI && swift run
```

## 🔗 Related Resources

- **Documentation**: [Swift for PHP Developers Series](https://github.com/dalehurley/codewithphp/tree/main/docs/series/swift-for-php-developers)
- **Main Repository**: [dalehurley/codewithphp](https://github.com/dalehurley/codewithphp)
- **PHP Algorithms Series**: [PHP Algorithms](../php-algorithms/)
- **Laravel Series**: [Build a CRM](../../code/rails-developers-love-laravel/)

## 📝 License

This code is part of the Swift for PHP Developers educational series. See the main repository for license information.

## 🤝 Contributing

Found an issue or have an improvement? Please open an issue or pull request in the [main repository](https://github.com/dalehurley/codewithphp).

## 📧 Support

For questions or feedback about these code samples, please refer to the main documentation or open an issue on GitHub.

---

**Happy Coding!** 🚀

*Master Swift with your PHP expertise as your foundation.*

---

**Last Updated:** 2025-11-15
