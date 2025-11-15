# Rust for PHP Developers

A comprehensive guide to learning Rust programming from the perspective of an expert PHP developer.

## Overview

This series teaches Rust to experienced PHP developers by:
- Comparing Rust concepts to familiar PHP patterns
- Building real-world web applications
- Showing performance benchmarks
- Providing decision frameworks for choosing Rust vs PHP
- Covering deployment and production practices

## Series Status

**Current Status**: Outline and Foundation Complete

- ✅ Main index and learning paths
- ✅ Chapter 00: Quick Start Guide
- ✅ Chapter 01: Why Rust for PHP Developers
- ✅ Complete series outline (40 chapters)
- ✅ All 4 appendices
- 📝 Chapters 02-39: Outlined (to be developed)

## Structure

### Chapters (40 total)

- **Part 0**: Getting Started (Chapter 00)
- **Part 1**: Foundation (Chapters 01-05)
- **Part 2**: Core Language Features (Chapters 06-10)
- **Part 3**: Advanced Concepts (Chapters 11-15)
- **Part 4**: Systems Programming (Chapters 16-20)
- **Part 5**: Web Development Basics (Chapters 21-25)
- **Part 6**: APIs and Data (Chapters 26-30)
- **Part 7**: Database Integration (Chapters 31-35)
- **Part 8**: Production & Deployment (Chapters 36-39)

### Appendices (4 total)

- **Appendix A**: Rust vs PHP Quick Reference
- **Appendix B**: Cargo Commands Cheat Sheet
- **Appendix C**: Common Errors and Solutions
- **Appendix D**: Essential Crates for Web Development

## Quick Start

1. **Read the overview**: [index.md](./index.md)
2. **Install Rust**: [Chapter 00 - Quick Start Guide](./chapters/00-quick-start-guide.md)
3. **Understand the why**: [Chapter 01 - Why Rust for PHP Developers](./chapters/01-why-rust-for-php-developers.md)
4. **Follow a learning path**:
   - Quick Start (~8 hours)
   - Core Fundamentals (~25 hours)
   - Web Development Focus (~35 hours)
   - Complete Mastery (~60 hours)

## Learning Paths

### Quick Start Path (~8 hours)
Perfect for evaluating Rust quickly.

**Chapters**: 00, 01, 02, 03, 05, 08, 21, 24

### Core Fundamentals Path (~25 hours)
Solid foundation in Rust programming.

**Chapters**: 00-15

### Web Development Path (~35 hours)
Build production-ready web applications.

**Chapters**: 00-10, 16-30

### Complete Mastery Path (~60 hours)
From zero to deploying Rust applications.

**Chapters**: All chapters 00-39 + appendices

## Key Features

### 1. PHP Comparisons
Every concept includes side-by-side PHP comparisons:

```php
// PHP
$users = array_filter($users, fn($u) => $u->age >= 18);
```

```rust
// Rust
let adults: Vec<_> = users.iter().filter(|u| u.age >= 18).collect();
```

### 2. Performance Benchmarks
Real-world performance comparisons:

| Task | PHP | Rust | Speedup |
|------|-----|------|---------|
| Fibonacci(40) | 1,500ms | 50ms | 30x |
| HTTP Server | 5k req/s | 500k req/s | 100x |
| Image Processing | 45s | 2s | 22x |

### 3. Production-Ready Examples
All examples are:
- Complete and runnable
- Following best practices
- Production-ready code quality
- Fully documented

### 4. Decision Frameworks
Learn when to use Rust vs PHP:
- Performance requirements
- Team expertise
- Project constraints
- Cost analysis

## File Structure

```
rust-php-developers/
├── index.md                          # Main series landing page
├── README.md                         # This file
├── SERIES-OUTLINE.md                 # Complete outline
├── chapters/
│   ├── 00-quick-start-guide.md       # ✅ Complete
│   ├── 01-why-rust-for-php-developers.md  # ✅ Complete
│   ├── 02-variables-and-types.md     # 📝 Outlined
│   ├── 03-ownership-and-borrowing.md # 📝 Outlined
│   └── ... (04-39)                   # 📝 Outlined
└── appendices/
    ├── appendix-a-rust-php-reference.md       # ✅ Complete
    ├── appendix-b-cargo-cheat-sheet.md        # ✅ Complete
    ├── appendix-c-common-errors.md            # ✅ Complete
    └── appendix-d-essential-crates.md         # ✅ Complete
```

## Code Samples

All code examples are in:
`/code-samples/rust-php-developers/`

Each chapter includes:
- Runnable Rust examples
- PHP comparisons
- Benchmarks
- Tests

## Topics Covered

### Foundation
- Installation and setup
- Variables and types
- Ownership and borrowing
- Functions and control flow
- Structs and enums

### Core Language
- Error handling (Result/Option)
- Collections (Vec, HashMap, String)
- Traits and generics
- Lifetimes
- Modules and crates

### Advanced
- Iterators and closures
- Smart pointers (Box, Rc, Arc)
- Concurrency (threads, channels)
- Async programming (Tokio)
- Testing and benchmarking

### Systems Programming
- CLI tools
- File I/O
- JSON serialization
- Logging and debugging
- Performance optimization

### Web Development
- HTTP fundamentals
- Actix Web framework
- Axum framework
- Routing and middleware
- Template engines

### APIs
- RESTful API design
- Request validation
- GraphQL
- WebSockets
- OpenAPI documentation

### Databases
- SQLx (async queries)
- Diesel ORM
- SeaORM (async ORM)
- Redis caching
- Migrations and seeding

### Production
- Authentication/Authorization
- Docker deployment
- Monitoring and observability
- Production deployment
- CI/CD pipelines

## Prerequisites

- Expert-level PHP knowledge (PHP 8.0+)
- Web development experience
- Understanding of HTTP, APIs, databases
- Basic command-line proficiency
- No prior Rust knowledge required

## Development Roadmap

### Phase 1: Foundation ✅
- [x] Series outline
- [x] Chapter 00: Quick Start
- [x] Chapter 01: Why Rust
- [x] All appendices
- [x] Code samples structure

### Phase 2: Core Chapters (In Progress)
- [ ] Chapters 02-05 (Foundation)
- [ ] Chapters 06-10 (Core Language)
- [ ] Chapters 11-15 (Advanced Concepts)

### Phase 3: Web Development
- [ ] Chapters 16-20 (Systems Programming)
- [ ] Chapters 21-25 (Web Basics)
- [ ] Chapters 26-30 (APIs)

### Phase 4: Database & Production
- [ ] Chapters 31-35 (Databases)
- [ ] Chapters 36-39 (Production)

### Phase 5: Polish & Launch
- [ ] Review all content
- [ ] Add more code examples
- [ ] Create video walkthroughs
- [ ] Community feedback

## Contributing

This series is under active development. Feedback and contributions welcome!

### How to Contribute

1. **Report Issues**: Found an error? [Open an issue](https://github.com/dalehurley/codewithphp/issues)
2. **Suggest Improvements**: Have ideas? Start a discussion
3. **Submit Examples**: Share your Rust vs PHP comparisons
4. **Review Content**: Provide feedback on chapters

## Resources

### Official Rust Resources
- [The Rust Book](https://doc.rust-lang.org/book/)
- [Rust by Example](https://doc.rust-lang.org/rust-by-example/)
- [Cargo Book](https://doc.rust-lang.org/cargo/)
- [crates.io](https://crates.io/) - Package registry

### Rust for Web Development
- [Are We Web Yet?](https://www.arewewebyet.org/)
- [Actix Web](https://actix.rs/)
- [Axum](https://github.com/tokio-rs/axum)
- [Diesel ORM](https://diesel.rs/)

### Community
- [Rust Users Forum](https://users.rust-lang.org/)
- [Rust Reddit](https://www.reddit.com/r/rust/)
- [Rust Discord](https://discord.gg/rust-lang)

## Related Series

- **[PHP Algorithms](/series/php-algorithms/)** - Master algorithms in PHP
- **[Build a CRM with Laravel 12](/series/build-crm-laravel-12/)** - PHP web patterns
- **[PHP Basics](/series/php-basics/)** - PHP fundamentals

## License

See the main repository for license information.

## Author

Part of the [Code with PHP](https://github.com/dalehurley/codewithphp) learning series.

---

**Ready to start?** Head to [Chapter 00: Quick Start Guide](./chapters/00-quick-start-guide.md)!
