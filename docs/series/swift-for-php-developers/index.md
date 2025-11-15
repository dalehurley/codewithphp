---
title: Swift for PHP Developers
description: Master Swift development from basics to deployment—leverage your PHP expertise to build iOS, macOS, and server-side Swift applications.
series: swift-for-php-developers
order: 0
difficulty: Intermediate to Advanced
prerequisites:
  [
    "Expert-level PHP knowledge",
    "Understanding of object-oriented programming",
    "Familiarity with web frameworks (Laravel/Symfony)",
    "Basic understanding of mobile app concepts",
    "Comfortable with command line tools",
  ]
---

<div class="breadcrumbs">
  <a href="/">Home</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/#choose-your-learning-path">Series</a>
  <span class="breadcrumbs-separator">›</span>
  <span>Swift for PHP Developers</span>
</div>

# Swift for PHP Developers <span class="difficulty-badge difficulty-advanced">Intermediate to Advanced</span>

## Overview

Welcome to **Swift for PHP Developers** — a comprehensive, hands-on series that teaches you Swift development by leveraging your existing PHP expertise. Whether you're building native iOS and macOS applications, exploring server-side Swift, or diversifying your skill set, this series will transform you from a PHP expert into a proficient Swift developer.

Swift is Apple's modern, safe, and performant programming language designed for building applications across the entire Apple ecosystem—iOS, macOS, watchOS, and tvOS. But Swift isn't limited to Apple platforms; it's also a powerful choice for server-side development with frameworks like Vapor and has growing adoption in cross-platform scenarios.

As a PHP developer, you already understand web architecture, APIs, databases, and object-oriented programming. This series builds on that foundation, showing you how PHP concepts map to Swift, what's different, and how to think in Swift's protocol-oriented paradigm. You'll learn modern Swift development practices, from optionals and generics to async/await, SwiftUI, and beyond.

By the end of this series, you'll have built native mobile applications, created server-side Swift APIs, deployed apps to the App Store, and gained the confidence to tackle any Swift project. More importantly, you'll understand when to use Swift versus PHP, and how they can complement each other in your development toolkit.

## Who This Is For

This series is designed for:

- **Expert PHP developers** who want to expand into native mobile development
- **Full-stack developers** looking to add iOS/macOS app development to their skill set
- **Laravel/Symfony developers** interested in exploring server-side Swift
- **Web developers** wanting to understand native app development paradigms
- **Technical leads** evaluating Swift for their tech stack
- **Entrepreneurs** who want to build their own mobile applications

You should be comfortable with advanced PHP concepts, object-oriented programming, frameworks, and have a desire to learn a compiled, strongly-typed language with modern features.

## Prerequisites

**Software Requirements:**

- **macOS** (required for iOS/macOS development)
- **Xcode 15+** (Apple's IDE, free from Mac App Store)
- **Swift 5.9+** (included with Xcode)
- **Command Line Tools** for Xcode
- **Homebrew** (package manager for macOS)
- **Optional**: iPhone/iPad for device testing (simulator works fine)
- **Optional**: Apple Developer Account ($99/year for App Store distribution)

**Time Commitment:**

- **Estimated total**: 60–80 hours to complete all chapters
- **Per chapter**: 45 minutes to 2 hours
- **Quick Start path**: 15 hours
- **iOS Development path**: 35 hours
- **Server-Side Swift path**: 25 hours
- **Complete mastery path**: 60+ hours

**Skill Assumptions:**

- Expert-level PHP (you write production PHP code confidently)
- Strong OOP understanding (classes, interfaces, inheritance, polymorphism)
- Experience with at least one PHP framework (Laravel, Symfony, etc.)
- Understanding of REST APIs and web architecture
- Familiarity with Git and version control
- No prior Swift or mobile development experience required

## What You'll Build

<ProgressTracker seriesId="swift-for-php-developers" :totalChapters="38" title="Your Progress" />

By working through this series, you will:

1. **Master Swift fundamentals** through a PHP lens:
   - Type system (strong typing vs PHP's dynamic types)
   - Optionals (Swift's approach to null safety)
   - Protocols and protocol-oriented programming
   - Generics and type constraints
   - Swift's memory management vs PHP's garbage collection
   - Functional programming features

2. **Build native iOS and macOS applications**:
   - Todo app with SwiftUI
   - Weather app with API integration
   - Social media client with authentication
   - E-commerce app with payments
   - Real-time chat application
   - Camera and photo processing app

3. **Create server-side Swift applications**:
   - RESTful API with Vapor framework
   - GraphQL server
   - WebSocket real-time service
   - Background job processing
   - Microservices architecture
   - Integration with existing PHP backends

4. **Deploy and distribute applications**:
   - App Store submission process
   - TestFlight beta testing
   - CI/CD pipelines with GitHub Actions
   - Server deployment (Docker, cloud platforms)
   - App analytics and monitoring

Every project is production-ready, following Swift best practices, and includes comprehensive explanations comparing Swift and PHP approaches.

## Learning Objectives

By the end of this series, you will be able to:

- **Read and write idiomatic Swift code** with confidence
- **Build native iOS and macOS applications** from scratch
- **Create server-side Swift APIs** using Vapor
- **Understand Swift's type system** and leverage it for safer code
- **Master async/await** for concurrent programming
- **Design apps with SwiftUI** using declarative UI patterns
- **Integrate with Apple services** (CloudKit, Push Notifications, In-App Purchases)
- **Deploy apps to the App Store** following Apple's guidelines
- **Make informed decisions** about when to use Swift vs PHP
- **Bridge Swift and PHP** in full-stack applications

## How This Series Works

This series follows a **progressive, comparative approach**: you'll learn Swift by understanding how PHP concepts translate, what's fundamentally different, and why Swift makes certain design decisions.

Each chapter includes:

- **PHP to Swift mapping** showing equivalent concepts and patterns
- **Side-by-side code comparisons** highlighting differences
- **Hands-on projects** demonstrating real-world applications
- **Best practices** for Swift development
- **Common pitfalls** for PHP developers learning Swift
- **Performance considerations** and optimization techniques
- **Further reading** for deeper exploration

We'll start with environment setup and Swift basics, progress through language features and iOS development, explore server-side Swift, and finish with deployment, advanced topics, and complete applications.

::: tip
Think of Swift as "PHP with a different philosophy." Where PHP is flexible and forgiving, Swift is strict and safe. Where PHP relies on runtime checks, Swift catches errors at compile time. This series helps you embrace that mindset shift.
:::

## Quick Start

Want to see Swift in action right now? Here's a 2-minute comparison:

```php
<?php
// PHP: Dynamic typing, null coalescing
function getUser(?int $id): ?array {
    if ($id === null) {
        return null;
    }
    // Fetch from database
    return ['id' => $id, 'name' => 'John'];
}

$user = getUser(1);
$name = $user['name'] ?? 'Guest'; // Runtime null check
echo "Hello, $name\n";
```

```swift
// Swift: Static typing, optionals, compile-time safety
struct User {
    let id: Int
    let name: String
}

func getUser(id: Int?) -> User? {
    guard let id = id else {
        return nil
    }
    // Fetch from database
    return User(id: id, name: "John")
}

let user = getUser(id: 1)
let name = user?.name ?? "Guest" // Compile-time checked
print("Hello, \(name)")
```

**Key Differences:**
- Swift uses structs (value types) where PHP uses arrays
- Optionals (`?`) are built into the type system
- Swift catches type errors at compile time
- SwiftUI uses declarative syntax (like Blade but reactive)

**What's Next?**
Head to [Chapter 00: Quick Start Guide](/series/swift-for-php-developers/chapters/00-quick-start-guide/) for a 5-minute overview, or start comprehensive learning with [Chapter 01: Setting Up Your Swift Development Environment](/series/swift-for-php-developers/chapters/01-setting-up-environment/).

---

## Learning Paths & Chapters

Choose your learning path based on your goals, or explore all chapters below.

::: tip Recommended Learning Paths
- **Quick Start** (~15 hours): Chapters 00-05, 10, 15, 20, 28
- **iOS Development Focus** (~35 hours): Chapters 00-06, 10-22, 28-30, 35
- **Server-Side Swift Focus** (~25 hours): Chapters 00-06, 10, 23-27, 31-33, 36
- **Complete Mastery** (~60 hours): All chapters 00-37 + all appendices
:::

### Part 0: Getting Started (Chapter 00)

Get oriented quickly with Swift's ecosystem and how it compares to PHP.

#### [00 — Quick Start Guide](/series/swift-for-php-developers/chapters/00-quick-start-guide)
**NEW!** Start here if you have 5 minutes. See PHP vs Swift comparisons, common scenarios, and discover which topics to learn based on your goals. Includes a decision matrix for choosing Swift vs PHP for different project types.

---

### Part 1: Foundation (Chapters 01–05)

Build essential Swift knowledge leveraging your PHP background.

#### [01 — Setting Up Your Swift Development Environment](/series/swift-for-php-developers/chapters/01-setting-up-environment)
Install Xcode, configure command line tools, understand Swift Package Manager (SPM), and compare to PHP's Composer. Set up your first Swift project and learn the Xcode IDE. Understand the differences between interpreted PHP and compiled Swift.

#### [02 — Swift Syntax for PHP Developers](/series/swift-for-php-developers/chapters/02-swift-syntax-for-php-developers)
Learn Swift syntax by mapping it to PHP: variables (let vs var vs PHP's $), control flow (if/switch/loops), functions, and basic types. Understand Swift's explicit typing vs PHP's dynamic typing, and why Swift enforces compile-time type safety.

#### [03 — Types, Constants, and Variables](/series/swift-for-php-developers/chapters/03-types-constants-variables)
Master Swift's type system: value types vs reference types, type inference, type annotations, constants (let) vs variables (var). Compare to PHP's loose typing and understand the benefits of Swift's approach for preventing bugs.

#### [04 — Optionals: Swift's Approach to Null Safety](/series/swift-for-php-developers/chapters/04-optionals-null-safety)
Understand optionals (`String?`) as Swift's replacement for null. Learn optional binding, optional chaining, nil coalescing, and guard statements. Compare to PHP's null coalescing operator and nullable types. This is one of the biggest mindset shifts from PHP.

#### [05 — Collections: Arrays, Dictionaries, and Sets](/series/swift-for-php-developers/chapters/05-collections-arrays-dictionaries-sets)
Explore Swift's strongly-typed collections vs PHP's flexible arrays. Learn Array, Dictionary, Set operations, generics in collections, and when to use each type. Understand value semantics (copy-on-write) vs PHP's reference counting.

---

### Part 2: Object-Oriented and Protocol-Oriented Swift (Chapters 06–11)

Transition from PHP's class-based OOP to Swift's protocol-oriented programming.

#### [06 — Classes and Structs: Reference vs Value Types](/series/swift-for-php-developers/chapters/06-classes-structs-value-reference-types)
Understand the fundamental difference between classes (reference types) and structs (value types). Learn when to use each, memory management implications, and compare to PHP's object model (everything is a reference).

#### [07 — Properties, Methods, and Initializers](/series/swift-for-php-developers/chapters/07-properties-methods-initializers)
Master stored properties, computed properties, property observers (willSet/didSet), methods, and initialization. Compare to PHP's constructors, getters/setters, and magic methods. Understand Swift's strict initialization requirements.

#### [08 — Protocols: Swift's Answer to Interfaces](/series/swift-for-php-developers/chapters/08-protocols-interfaces)
Learn protocol-oriented programming—Swift's modern alternative to classical inheritance. Understand protocols vs PHP interfaces, protocol extensions, default implementations, and protocol composition. This is Swift's superpower.

#### [09 — Enums and Pattern Matching](/series/swift-for-php-developers/chapters/09-enums-pattern-matching)
Explore Swift's powerful enums (far beyond PHP's backed enums): associated values, raw values, pattern matching with switch, and exhaustive checking. Build type-safe state machines and result types.

#### [10 — Generics and Type Constraints](/series/swift-for-php-developers/chapters/10-generics-type-constraints)
Master generics for writing reusable, type-safe code. Understand generic functions, generic types, type constraints, and associated types in protocols. Compare to PHP's limited generics support and understand compile-time vs runtime type safety.

#### [11 — Error Handling: Do-Try-Catch](/series/swift-for-php-developers/chapters/11-error-handling-do-try-catch)
Learn Swift's error handling with do-try-catch, throwing functions, and error propagation. Compare to PHP's try-catch and understand Result types as an alternative approach. Build robust error handling for production apps.

---

### Part 3: Memory Management and Advanced Language Features (Chapters 12–14)

Understand Swift's memory model and advanced language features.

#### [12 — Automatic Reference Counting (ARC) and Memory Management](/series/swift-for-php-developers/chapters/12-arc-memory-management)
Learn how Swift manages memory with ARC (Automatic Reference Counting) vs PHP's garbage collection. Understand strong references, weak references, unowned references, and avoid retain cycles. Debug memory leaks with Instruments.

#### [13 — Closures and Functional Programming](/series/swift-for-php-developers/chapters/13-closures-functional-programming)
Master closures (Swift's anonymous functions) and functional patterns: map, filter, reduce, compactMap, flatMap. Compare to PHP's closures and arrow functions. Understand capture lists and escaping vs non-escaping closures.

#### [14 — Extensions and Protocol Extensions](/series/swift-for-php-developers/chapters/14-extensions-protocol-extensions)
Extend existing types without inheritance using extensions. Add protocol conformance, computed properties, and methods to any type. Compare to PHP's traits and understand Swift's more powerful extension system.

---

### Part 4: iOS Development Fundamentals (Chapters 15–22)

Build native iOS applications with UIKit and SwiftUI.

#### [15 — Introduction to iOS Development](/series/swift-for-php-developers/chapters/15-intro-ios-development)
Understand iOS app architecture, app lifecycle, and development concepts. Learn about ViewControllers, Views, the responder chain, and MVC pattern. Compare to PHP web request/response cycle and understand the event-driven nature of mobile apps.

#### [16 — SwiftUI Basics: Declarative UI for PHP Developers](/series/swift-for-php-developers/chapters/16-swiftui-basics)
Learn SwiftUI's declarative syntax (similar to Blade but reactive). Build UIs with Text, Image, VStack, HStack, List, and modifiers. Compare to PHP templating and understand how state drives UI updates automatically.

#### [17 — State Management in SwiftUI](/series/swift-for-php-developers/chapters/17-state-management-swiftui)
Master @State, @Binding, @ObservedObject, @StateObject, @EnvironmentObject for managing app state. Compare to PHP session management and frontend state (React/Vue). Build reactive UIs that update automatically.

#### [18 — Navigation and Routing in SwiftUI](/series/swift-for-php-developers/chapters/18-navigation-routing-swiftui)
Implement navigation with NavigationStack, sheets, alerts, and programmatic navigation. Compare to Laravel/Symfony routing and understand iOS navigation patterns (push, present, dismiss).

#### [19 — Networking: Fetching Data from APIs](/series/swift-for-php-developers/chapters/19-networking-fetching-apis)
Make HTTP requests using URLSession, decode JSON with Codable, handle async operations, and integrate with REST APIs. Compare to PHP's Guzzle/HTTP clients and understand iOS async patterns. Build a weather app consuming external APIs.

#### [20 — Data Persistence: UserDefaults, Core Data, and SwiftData](/series/swift-for-php-developers/chapters/20-data-persistence)
Store data locally using UserDefaults (simple key-value), Core Data (relational database), and SwiftData (modern ORM). Compare to PHP sessions, databases, and Eloquent ORM. Build offline-capable apps.

#### [21 — Working with Lists and Forms](/series/swift-for-php-developers/chapters/21-lists-forms)
Build dynamic lists with List and ForEach, create forms with input fields, pickers, toggles, and validation. Compare to PHP form handling and Laravel validation. Implement create/edit/delete functionality.

#### [22 — Integrating with Apple Services](/series/swift-for-php-developers/chapters/22-apple-services-integration)
Add CloudKit sync, Push Notifications, Sign in with Apple, and In-App Purchases. Understand Apple ecosystem integration and how it differs from web-based authentication and payments.

---

### Part 5: Server-Side Swift (Chapters 23–27)

Build backend services with Swift using Vapor framework.

#### [23 — Introduction to Server-Side Swift and Vapor](/series/swift-for-php-developers/chapters/23-server-side-swift-vapor-intro)
Understand why Swift on the server, compare Vapor to Laravel/Symfony, and build your first Vapor app. Explore routing, controllers, middleware, and project structure. Leverage your PHP web framework knowledge.

#### [24 — Routing, Controllers, and Request Handling](/series/swift-for-php-developers/chapters/24-vapor-routing-controllers)
Build RESTful APIs with Vapor routing, controllers, request validation, and response formatting. Compare to Laravel routes and controllers. Implement CRUD operations with JSON responses.

#### [25 — Databases with Fluent ORM](/series/swift-for-php-developers/chapters/25-fluent-orm-databases)
Use Fluent (Vapor's ORM) to interact with PostgreSQL, MySQL, or SQLite. Define models, run migrations, query databases, and manage relationships. Compare to Eloquent ORM and understand async database operations.

#### [26 — Authentication and Authorization](/series/swift-for-php-developers/chapters/26-authentication-authorization-vapor)
Implement JWT authentication, session management, password hashing, and role-based access control. Compare to Laravel authentication and Passport. Build secure API endpoints.

#### [27 — WebSockets and Real-Time Communication](/series/swift-for-php-developers/chapters/27-websockets-realtime-vapor)
Build real-time features with WebSockets, broadcasting, and event streams. Compare to Laravel Echo and Pusher. Create a chat application with real-time messaging.

---

### Part 6: Advanced Swift Topics (Chapters 28–30)

Master concurrency, testing, and performance optimization.

#### [28 — Async/Await and Concurrency](/series/swift-for-php-developers/chapters/28-async-await-concurrency)
Master Swift's modern concurrency model with async/await, Tasks, Task Groups, and actors. Compare to PHP's async approaches (ReactPHP, Swoole) and understand structured concurrency. Prevent data races with compiler-enforced safety.

#### [29 — Testing: Unit Tests, UI Tests, and TDD](/series/swift-for-php-developers/chapters/29-testing-unit-ui-tdd)
Write tests with XCTest, implement test-driven development, mock dependencies, and run UI tests. Compare to PHPUnit and understand iOS testing best practices. Achieve high test coverage for production apps.

#### [30 — Performance Optimization and Profiling](/series/swift-for-php-developers/chapters/30-performance-optimization-profiling)
Profile apps with Instruments, optimize rendering, reduce memory usage, and improve launch times. Understand iOS performance characteristics vs web performance. Use Time Profiler, Allocations, and Leaks instruments.

---

### Part 7: Deployment and Distribution (Chapters 31–34)

Ship your apps to the App Store and deploy server-side Swift.

#### [31 — App Store Submission Process](/series/swift-for-php-developers/chapters/31-app-store-submission)
Prepare apps for release: code signing, certificates, provisioning profiles, App Store Connect, screenshots, metadata, and submission. Navigate Apple's review process and guidelines. Understand app versioning and updates.

#### [32 — TestFlight and Beta Testing](/series/swift-for-php-developers/chapters/32-testflight-beta-testing)
Distribute beta builds with TestFlight, manage testers, collect feedback, and iterate. Compare to web app staging environments and understand mobile beta testing workflows.

#### [33 — CI/CD for iOS Apps](/series/swift-for-php-developers/chapters/33-cicd-ios-apps)
Automate builds, tests, and deployments with GitHub Actions, Fastlane, and Xcode Cloud. Compare to PHP CI/CD pipelines and implement automated App Store releases. Build robust deployment pipelines.

#### [34 — Deploying Server-Side Swift Applications](/series/swift-for-php-developers/chapters/34-deploying-server-side-swift)
Deploy Vapor apps to production: Docker containerization, cloud platforms (AWS, DigitalOcean, Heroku), monitoring, and scaling. Compare to PHP deployment (Laravel Forge, Vapor) and understand Swift deployment best practices.

---

### Part 8: Practical Applications and Case Studies (Chapters 35–37)

Build complete, production-ready applications from start to finish.

#### [35 — Building a Complete iOS App: E-Commerce Application](/series/swift-for-php-developers/chapters/35-complete-ios-ecommerce-app)
Build a full e-commerce app with product listings, shopping cart, checkout, payment integration (Stripe), order history, and user profiles. Integrate with a PHP Laravel backend API. Production-ready architecture and best practices.

#### [36 — Building a Server-Side Swift API: Social Media Backend](/series/swift-for-php-developers/chapters/36-server-side-api-social-media)
Create a complete social media API with Vapor: user authentication, posts, likes, comments, followers, image uploads, feed generation, and real-time notifications. Compare architecture to Laravel API and understand trade-offs.

#### [37 — Hybrid Stack: Integrating Swift Apps with PHP Backends](/series/swift-for-php-developers/chapters/37-hybrid-swift-php-integration)
Build full-stack applications combining Swift iOS apps with PHP (Laravel) backends. Handle authentication, API design, shared models, webhooks, and real-time sync. Leverage the strengths of both ecosystems.

---

## Appendices

Quick reference materials to support your learning journey.

- **[Appendix A: PHP to Swift Quick Reference](/series/swift-for-php-developers/appendices/a-php-swift-quick-reference/)** — Side-by-side syntax comparison, common patterns, and translation guide
- **[Appendix B: Swift Standard Library Reference](/series/swift-for-php-developers/appendices/b-swift-standard-library/)** — Essential types, protocols, and functions with PHP equivalents
- **[Appendix C: Xcode Tips and Shortcuts](/series/swift-for-php-developers/appendices/c-xcode-tips-shortcuts/)** — Productivity tips, keyboard shortcuts, and debugging techniques
- **[Appendix D: Common Errors and Solutions](/series/swift-for-php-developers/appendices/d-common-errors-solutions/)** — Troubleshooting guide for PHP developers learning Swift
- **[Appendix E: Further Resources](/series/swift-for-php-developers/appendices/e-further-resources/)** — Books, courses, documentation, and community resources

---

## Frequently Asked Questions

**Do I need a Mac to learn Swift?**
Yes, for iOS/macOS development you need a Mac with Xcode. However, you can learn server-side Swift on Linux. For the complete experience (iOS apps), a Mac is required.

**Can I use Swift without an Apple Developer account?**
Yes! You can develop and test apps in the simulator without an account. You only need the $99/year account to deploy to physical devices and publish to the App Store.

**How does Swift compare to PHP for web development?**
Swift (with Vapor) is faster and uses less memory than PHP, but has a smaller ecosystem and fewer hosting options. PHP excels for web applications, while Swift shines for native mobile apps and performance-critical APIs.

**Will this series teach me SwiftUI or UIKit?**
Primarily SwiftUI, as it's Apple's modern, recommended approach. We cover UIKit basics to help you understand existing code and when UIKit is still necessary.

**Can I build Android apps with Swift?**
Not officially. Swift is primarily for Apple platforms and server-side development. For Android, consider Kotlin. For cross-platform, consider React Native or Flutter (but this series focuses on native Swift).

**How long does it take to become productive in Swift coming from PHP?**
With daily practice, you can build simple iOS apps in 2-3 weeks. Becoming proficient in both language and platform takes 2-3 months. Mastery requires 6-12 months of regular development.

**Should I learn Swift if I already know JavaScript/TypeScript?**
Yes! Swift offers unique features (value types, protocol-oriented programming, memory safety) and is the best choice for native Apple platform development. The concepts are transferable to other languages.

**Is Swift difficult for PHP developers?**
The syntax is different, but many concepts transfer. The biggest challenges are: strong typing (vs PHP's dynamic typing), memory management (ARC vs garbage collection), and iOS platform concepts. This series addresses these transitions explicitly.

## Getting Help

**Stuck on something?** Here's where to get help:

- **Check the appendices first**:
  - [Appendix A: PHP to Swift Quick Reference](/series/swift-for-php-developers/appendices/a-php-swift-quick-reference/)
  - [Appendix D: Common Errors and Solutions](/series/swift-for-php-developers/appendices/d-common-errors-solutions/)
- **Apple Documentation**: [Swift.org](https://swift.org/) and [Apple Developer Docs](https://developer.apple.com/documentation/)
- **GitHub Discussions**: [Ask questions and share progress](https://github.com/dalehurley/codewithphp/discussions)
- **Report issues**: [Open an issue](https://github.com/dalehurley/codewithphp/issues) for unclear explanations

## Related Resources

Want to dive deeper? These resources complement the series:

### Swift Resources

- **[Swift.org](https://swift.org/)**: Official Swift language website
- **[Apple Developer](https://developer.apple.com/)**: Official documentation and guides
- **[Hacking with Swift](https://www.hackingwithswift.com/)**: Excellent tutorials and courses
- **[Swift by Sundell](https://www.swiftbysundell.com/)**: Advanced Swift articles and podcasts
- **[Ray Wenderlich](https://www.raywenderlich.com/)**: Comprehensive iOS tutorials

### Vapor Resources

- **[Vapor Docs](https://docs.vapor.codes/)**: Official Vapor documentation
- **[Vapor Discord](https://discord.gg/vapor)**: Active community support

### Books

- **"Swift Programming: The Big Nerd Ranch Guide"** — Comprehensive Swift fundamentals
- **"iOS Programming Fundamentals with Swift"** by Matt Neuburg — Deep dive into iOS
- **"Server-Side Swift with Vapor"** by Tim Condon — Vapor framework guide

### Related Code with PHP Series

- **[PHP Basics](/series/php-basics/)** — Strengthen PHP foundations
- **[PHP Algorithms](/series/php-algorithms/)** — Algorithm knowledge transfers to Swift
- **[Laravel Series](/series/build-crm-laravel-12/)** — Build PHP backends for Swift apps

---

::: tip Ready to Start?
Head to [Chapter 00: Quick Start Guide](/series/swift-for-php-developers/chapters/00-quick-start-guide) for a 5-minute overview, or begin comprehensive learning with [Chapter 01: Setting Up Your Swift Development Environment](/series/swift-for-php-developers/chapters/01-setting-up-environment)!
:::

---

## Continue Your Learning

Explore other aspects of modern development:

**→ [PHP Algorithms](/series/php-algorithms/)** — Master algorithms in PHP, apply to Swift
**→ [Build a CRM with Laravel 12](/series/build-crm-laravel-12/)** — Build PHP backends for your Swift apps
**→ [PHP Basics](/series/php-basics/)** — Strengthen your PHP foundation

<style>
:root {
  --swift-orange: #f05138;
  --swift-orange-dark: #d63b23;
  --php-purple: #777bb4;
  --php-purple-dark: #5d5d9d;
  --neutral-gray: #64748b;
  --bg-light: #f8fafc;
}

/* Chapter card enhancements */
div[style*="display: flex"][style*="align-items: flex-start"] {
  transition: all 0.3s ease;
  padding: 1rem;
  border-radius: 8px;
  border-left: 4px solid var(--swift-orange);
}

div[style*="display: flex"][style*="align-items: flex-start"]:hover {
  background: var(--bg-light);
  transform: translateX(4px);
  box-shadow: 0 2px 12px rgba(240, 81, 56, 0.15);
  border-left-color: var(--swift-orange-dark);
}
</style>
