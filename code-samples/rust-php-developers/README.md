# Rust for PHP Developers - Code Samples

Comprehensive code samples for the **[Rust for PHP Developers Series](https://github.com/dalehurley/codewithphp/tree/main/docs/series/rust-php-developers)**.

## Overview

This repository contains all code examples from the Rust for PHP Developers series. Every example includes both Rust and PHP implementations for easy comparison.

## Directory Structure

```
rust-php-developers/
├── chapter-00/          # Quick Start Guide
│   ├── hello-world/
│   ├── fibonacci/
│   ├── calculator/
│   └── benchmarks/
├── chapter-01/          # Why Rust for PHP Developers
│   ├── performance-comparisons/
│   ├── memory-safety/
│   └── decision-framework/
├── chapter-02/          # Variables and Types
│   ├── type-basics/
│   ├── immutability/
│   └── type-inference/
├── chapter-03/          # Ownership and Borrowing
│   ├── ownership-examples/
│   ├── borrowing/
│   └── lifetimes-intro/
├── chapter-04/          # Functions and Control Flow
│   ├── functions/
│   ├── control-flow/
│   └── pattern-matching/
├── chapter-05/          # Structs and Enums
│   ├── structs/
│   ├── enums/
│   └── option-result/
├── chapter-06/          # Error Handling
│   ├── result-types/
│   ├── option-types/
│   └── custom-errors/
├── chapter-07/          # Collections
│   ├── vectors/
│   ├── hashmaps/
│   └── strings/
├── chapter-08/          # Traits and Generics
│   ├── traits/
│   ├── generics/
│   └── trait-objects/
├── chapter-09/          # Lifetimes
│   ├── lifetime-basics/
│   ├── struct-lifetimes/
│   └── advanced-lifetimes/
├── chapter-10/          # Modules and Crates
│   ├── modules/
│   ├── crate-structure/
│   └── workspaces/
├── chapter-11/          # Iterators and Closures
│   ├── iterators/
│   ├── closures/
│   └── functional-patterns/
├── chapter-12/          # Smart Pointers
│   ├── box/
│   ├── rc-arc/
│   └── refcell-mutex/
├── chapter-13/          # Concurrency
│   ├── threads/
│   ├── channels/
│   └── shared-state/
├── chapter-14/          # Async Programming
│   ├── tokio-basics/
│   ├── async-await/
│   └── async-patterns/
├── chapter-15/          # Testing
│   ├── unit-tests/
│   ├── integration-tests/
│   └── benchmarks/
├── chapter-16/          # CLI Tools
│   ├── argument-parsing/
│   ├── file-processor/
│   └── grep-clone/
├── chapter-17/          # File I/O
│   ├── reading-files/
│   ├── writing-files/
│   └── directory-operations/
├── chapter-18/          # JSON and Serialization
│   ├── json-basics/
│   ├── custom-serialization/
│   └── other-formats/
├── chapter-19/          # Logging and Debugging
│   ├── logging/
│   ├── tracing/
│   └── debugging/
├── chapter-20/          # Performance Optimization
│   ├── profiling/
│   ├── benchmarking/
│   └── optimization-techniques/
├── chapter-21/          # HTTP Fundamentals
│   ├── http-client/
│   ├── reqwest-examples/
│   └── api-integration/
├── chapter-22/          # Actix Web
│   ├── basic-server/
│   ├── routing/
│   ├── middleware/
│   └── full-api/
├── chapter-23/          # Axum
│   ├── basic-server/
│   ├── extractors/
│   ├── middleware/
│   └── full-api/
├── chapter-24/          # Routing and Middleware
│   ├── advanced-routing/
│   ├── middleware-composition/
│   └── authentication/
├── chapter-25/          # Templates
│   ├── tera/
│   ├── askama/
│   └── server-side-rendering/
├── chapter-26/          # RESTful APIs
│   ├── crud-api/
│   ├── pagination/
│   └── versioning/
├── chapter-27/          # Validation
│   ├── validator/
│   ├── custom-validators/
│   └── error-responses/
├── chapter-28/          # GraphQL
│   ├── schema-definition/
│   ├── queries-mutations/
│   └── subscriptions/
├── chapter-29/          # WebSockets
│   ├── basic-websocket/
│   ├── chat-server/
│   └── broadcasting/
├── chapter-30/          # OpenAPI
│   ├── spec-generation/
│   ├── swagger-ui/
│   └── code-first/
├── chapter-31/          # SQLx
│   ├── basic-queries/
│   ├── transactions/
│   └── migrations/
├── chapter-32/          # Diesel
│   ├── schema/
│   ├── query-builder/
│   └── associations/
├── chapter-33/          # SeaORM
│   ├── entities/
│   ├── async-queries/
│   └── relations/
├── chapter-34/          # Redis
│   ├── basic-operations/
│   ├── caching/
│   └── pub-sub/
├── chapter-35/          # Migrations
│   ├── sqlx-migrations/
│   ├── diesel-migrations/
│   └── seeding/
├── chapter-36/          # Authentication
│   ├── jwt/
│   ├── sessions/
│   └── rbac/
├── chapter-37/          # Docker
│   ├── dockerfile-examples/
│   ├── docker-compose/
│   └── optimization/
├── chapter-38/          # Monitoring
│   ├── prometheus/
│   ├── tracing/
│   └── health-checks/
└── chapter-39/          # Production Deployment
    ├── aws-deployment/
    ├── kubernetes/
    └── ci-cd/
```

## Requirements

- **Rust**: 1.75+ (install via [rustup](https://rustup.rs/))
- **PHP**: 8.0+ (for comparison examples)
- **Cargo**: Included with Rust installation
- **Docker**: Optional, for containerization examples
- **PostgreSQL/MySQL**: Optional, for database examples

## Quick Start

### Run Any Example

```bash
# Navigate to a chapter
cd chapter-22/basic-server

# Build and run the Rust example
cargo run

# Run the PHP comparison example (if available)
php comparison.php
```

### Run All Tests

```bash
# Test all Rust examples
find . -name "Cargo.toml" -execdir cargo test \;

# Run benchmarks
find . -name "Cargo.toml" -execdir cargo bench \;
```

### Build All Examples

```bash
# Build all in release mode
find . -name "Cargo.toml" -execdir cargo build --release \;
```

## Example Structure

Each chapter directory contains:

```
chapter-XX/
├── example-name/
│   ├── Cargo.toml          # Rust dependencies
│   ├── src/
│   │   └── main.rs         # Rust implementation
│   ├── comparison.php      # PHP equivalent
│   ├── README.md           # Explanation
│   └── tests/              # Tests
├── another-example/
└── README.md               # Chapter overview
```

## Performance Comparison Examples

### Fibonacci (Chapter 00)

**PHP (8.3)**:
```bash
cd chapter-00/fibonacci
php fibonacci.php
# ~1,500ms for fibonacci(40)
```

**Rust (Release)**:
```bash
cd chapter-00/fibonacci
cargo run --release
# ~50ms for fibonacci(40) - 30x faster!
```

### HTTP Server (Chapter 22)

**PHP (Laravel + Swoole)**:
```bash
cd chapter-22/performance-comparison
php -S localhost:8000 laravel-server.php
# ~20,000 requests/second
```

**Rust (Actix Web)**:
```bash
cd chapter-22/performance-comparison
cargo run --release
# ~500,000 requests/second - 25x faster!
```

### Image Processing (Chapter 20)

**PHP (GD/Intervention)**:
```bash
cd chapter-20/image-processing
php process.php
# ~45 seconds for 1000 images
```

**Rust (image + rayon)**:
```bash
cd chapter-20/image-processing
cargo run --release
# ~2 seconds for 1000 images - 22x faster!
```

## Featured Examples

### CLI Tool (Chapter 16)

A production-ready grep clone in Rust:

```bash
cd chapter-16/grep-clone
cargo build --release

# Use it
./target/release/rgrep "pattern" file.txt
# 50-100x faster than equivalent PHP script
```

### Full REST API (Chapter 26)

Complete CRUD API with database:

```bash
cd chapter-26/crud-api

# Start PostgreSQL (Docker)
docker-compose up -d

# Run migrations
cargo run -- migrate

# Start API server
cargo run --release

# Test endpoints
curl http://localhost:8080/api/users
```

### WebSocket Chat Server (Chapter 29)

Real-time chat with broadcasting:

```bash
cd chapter-29/chat-server
cargo run --release

# Connect with multiple clients
# Open browser: http://localhost:8080
# Handles 100,000+ concurrent connections!
```

## Development Workflow

### Create New Example

```bash
# Create new Cargo project
cargo new --bin example-name
cd example-name

# Add to chapter directory structure
# Edit Cargo.toml, add dependencies
# Write src/main.rs
# Create comparison.php

# Test
cargo test

# Run
cargo run

# Format code
cargo fmt

# Lint
cargo clippy

# Build release
cargo build --release
```

### Common Dependencies

Most examples use these crates:

```toml
[dependencies]
# Web frameworks
actix-web = "4.4"
axum = "0.7"
tokio = { version = "1", features = ["full"] }

# Serialization
serde = { version = "1.0", features = ["derive"] }
serde_json = "1.0"

# Database
sqlx = { version = "0.7", features = ["postgres", "runtime-tokio"] }
diesel = { version = "2.1", features = ["postgres"] }

# Error handling
anyhow = "1.0"
thiserror = "1.0"

# CLI
clap = { version = "4.4", features = ["derive"] }

# Testing
criterion = "0.5"  # Benchmarking
mockall = "0.12"   # Mocking

# Logging
tracing = "0.1"
tracing-subscriber = "0.3"
```

## Comparison Notes

### PHP vs Rust - Typical Results

| Metric | PHP-FPM | Swoole | Rust |
|--------|---------|---------|------|
| **Requests/sec** | 5K | 20K | 500K |
| **Latency (p99)** | 50ms | 10ms | <1ms |
| **Memory/req** | 50KB | 10KB | 2KB |
| **Binary size** | N/A | N/A | 5-10MB |
| **Startup time** | Instant | ~100ms | Instant |
| **CPU usage** | High | Medium | Low |

### When Rust Shines

- ✅ High-traffic APIs (100k+ req/sec)
- ✅ CPU-intensive processing
- ✅ CLI tools (fast startup)
- ✅ WebSocket servers
- ✅ Data processing pipelines
- ✅ Long-running services

### When PHP Shines

- ✅ Rapid prototyping
- ✅ CMS (WordPress, Drupal)
- ✅ CRUD applications
- ✅ Frequent requirement changes
- ✅ Large existing ecosystem
- ✅ Shared hosting environments

## Testing

### Run Chapter Tests

```bash
# Test a specific chapter
cd chapter-08/traits
cargo test

# Test with output
cargo test -- --nocapture

# Test a specific test
cargo test test_name
```

### Benchmarking

```bash
# Run benchmarks
cd chapter-20/benchmarks
cargo bench

# Compare with PHP
php benchmark.php
```

## Common Issues

### Compilation Errors

**Issue**: Borrow checker errors
```
error[E0502]: cannot borrow `x` as mutable because it is also borrowed as immutable
```

**Solution**: Review Chapter 03 (Ownership and Borrowing). Most common for PHP developers.

### Dependency Issues

**Issue**: Dependency resolution fails
```bash
# Clear cache and retry
cargo clean
rm -rf ~/.cargo/registry
cargo build
```

### Performance Not as Expected

**Issue**: Rust not faster than PHP
```bash
# Ensure you're using release mode
cargo run --release  # NOT cargo run

# Check optimization level in Cargo.toml
[profile.release]
opt-level = 3
lto = true
codegen-units = 1
```

## Contributing

Found an issue or have an improvement?

1. Open an issue describing the problem
2. Submit a pull request with fixes
3. Include both Rust and PHP examples
4. Add tests for new examples

## Resources

- **Documentation**: [Rust for PHP Developers Series](https://github.com/dalehurley/codewithphp/tree/main/docs/series/rust-php-developers)
- **The Rust Book**: [doc.rust-lang.org/book](https://doc.rust-lang.org/book/)
- **Cargo Book**: [doc.rust-lang.org/cargo](https://doc.rust-lang.org/cargo/)
- **crates.io**: [crates.io](https://crates.io/)
- **PHP Manual**: [php.net](https://www.php.net/)

## License

This code is part of the Rust for PHP Developers educational series. See the main repository for license information.

---

**Happy Coding!** 🦀

*Last Updated: 2024*
