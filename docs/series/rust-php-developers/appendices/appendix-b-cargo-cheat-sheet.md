# Appendix B: Cargo Commands Cheat Sheet

Essential Cargo commands for Rust development, with PHP/Composer equivalents.

## Project Creation

### Create New Project

```bash
# Binary project (executable)
cargo new my-project
cargo new my-project --bin

# Library project
cargo new my-lib --lib

# In existing directory
cargo init
cargo init --lib
```

**PHP equivalent:**
```bash
composer init
```

## Building and Running

### Build Commands

```bash
# Build debug version (fast compile, slower runtime)
cargo build

# Build release version (slow compile, fast runtime)
cargo build --release

# Check code without building (faster)
cargo check

# Build documentation
cargo doc
cargo doc --open  # Build and open in browser
```

**PHP equivalent:**
```bash
# PHP is interpreted, no build step
php script.php
```

### Run Commands

```bash
# Build and run (debug)
cargo run

# Build and run (release)
cargo run --release

# Pass arguments
cargo run -- arg1 arg2

# Run specific binary (if multiple)
cargo run --bin my-binary

# Run example
cargo run --example example-name
```

**PHP equivalent:**
```bash
php script.php arg1 arg2
```

## Dependencies

### Managing Dependencies

```bash
# Add dependency (adds to Cargo.toml)
cargo add serde
cargo add serde --features derive

# Add development dependency
cargo add --dev criterion

# Update dependencies
cargo update

# Update specific dependency
cargo update serde

# Show dependency tree
cargo tree

# Show outdated dependencies
cargo outdated  # Requires cargo-outdated plugin
```

**PHP equivalent:**
```bash
composer require vendor/package
composer require --dev phpunit/phpunit
composer update
composer update vendor/package
composer show --tree
composer outdated
```

### Cargo.toml Example

```toml
[package]
name = "my-project"
version = "0.1.0"
edition = "2021"

[dependencies]
serde = { version = "1.0", features = ["derive"] }
tokio = { version = "1", features = ["full"] }

[dev-dependencies]
criterion = "0.5"

[profile.release]
opt-level = 3
lto = true
codegen-units = 1
```

## Testing

### Test Commands

```bash
# Run all tests
cargo test

# Run tests with output
cargo test -- --nocapture

# Run specific test
cargo test test_name

# Run tests matching pattern
cargo test pattern

# Run tests in specific file
cargo test --test integration_test

# Run doc tests only
cargo test --doc

# Run with threads (default: parallel)
cargo test -- --test-threads=1  # Sequential

# Show test output even for passing tests
cargo test -- --show-output
```

**PHP equivalent:**
```bash
phpunit
phpunit tests/Unit/UserTest.php
phpunit --filter testUserLogin
./vendor/bin/pest
```

### Benchmarking

```bash
# Run benchmarks (requires criterion or bencher)
cargo bench

# Benchmark specific test
cargo bench bench_name

# Benchmark with baseline
cargo bench --bench my_benchmark
```

**PHP equivalent:**
```bash
# PHPBench
phpbench run
```

## Code Quality

### Formatting

```bash
# Format code (rustfmt)
cargo fmt

# Check formatting without applying
cargo fmt -- --check

# Format specific file
cargo fmt -- src/main.rs
```

**PHP equivalent:**
```bash
vendor/bin/php-cs-fixer fix
vendor/bin/phpcbf
```

### Linting

```bash
# Run clippy (linter)
cargo clippy

# Clippy with all warnings as errors
cargo clippy -- -D warnings

# Clippy with pedantic lints
cargo clippy -- -W clippy::pedantic

# Fix clippy suggestions automatically
cargo clippy --fix
```

**PHP equivalent:**
```bash
vendor/bin/phpstan analyse
vendor/bin/psalm
vendor/bin/phpcs
```

### Audit

```bash
# Check dependencies for security vulnerabilities
cargo audit

# Fix security issues
cargo audit fix
```

**PHP equivalent:**
```bash
composer audit
```

## Cleaning

```bash
# Remove target directory (build artifacts)
cargo clean

# Clean and rebuild
cargo clean && cargo build

# Remove specific target
cargo clean --release
```

**PHP equivalent:**
```bash
rm -rf vendor/
composer install
```

## Publishing

### Crate Publishing

```bash
# Login to crates.io
cargo login

# Publish crate
cargo publish

# Publish with dry-run
cargo publish --dry-run

# Package without publishing
cargo package

# Verify package contents
cargo package --list
```

**PHP equivalent:**
```bash
# No equivalent for Composer (manual process)
# Submit to packagist.org via web interface
```

## Workspaces

### Workspace Commands

```bash
# Build all workspace members
cargo build --workspace

# Test all workspace members
cargo test --workspace

# Build specific workspace member
cargo build -p member-name

# Run in specific workspace member
cargo run -p member-name
```

**Cargo.toml (workspace root):**
```toml
[workspace]
members = [
    "crate1",
    "crate2",
    "shared",
]
```

**PHP equivalent:**
```json
{
    "require": {
        "local/package": "@dev"
    },
    "repositories": [
        {
            "type": "path",
            "url": "../local-package"
        }
    ]
}
```

## Advanced Features

### Feature Flags

```bash
# Build with specific features
cargo build --features feature1,feature2

# Build with all features
cargo build --all-features

# Build with no default features
cargo build --no-default-features

# Check which features are enabled
cargo tree -e features
```

**Cargo.toml:**
```toml
[features]
default = ["std"]
std = []
async = ["tokio"]

[dependencies]
tokio = { version = "1", optional = true }
```

## Helpful Plugins

### Install Cargo Plugins

```bash
# Install cargo-watch (auto-rebuild on file changes)
cargo install cargo-watch

# Install cargo-edit (cargo add/rm commands)
cargo install cargo-edit

# Install cargo-outdated
cargo install cargo-outdated

# Install cargo-audit
cargo install cargo-audit

# Install cargo-expand (expand macros)
cargo install cargo-expand

# Install cargo-flamegraph (profiling)
cargo install cargo-flamegraph
```

### Using Plugins

```bash
# Auto-rebuild on changes
cargo watch -x run
cargo watch -x test

# Expand macros
cargo expand

# Generate flamegraph
cargo flamegraph

# Remove dependency
cargo rm serde
```

## Common Workflows

### Development Workflow

```bash
# 1. Create project
cargo new my-app
cd my-app

# 2. Add dependencies
cargo add actix-web
cargo add serde --features derive

# 3. Develop with auto-reload
cargo watch -x run

# 4. Run tests
cargo test

# 5. Format and lint
cargo fmt
cargo clippy

# 6. Build release
cargo build --release
```

### CI/CD Workflow

```bash
# Check formatting
cargo fmt -- --check

# Lint with warnings as errors
cargo clippy -- -D warnings

# Run tests
cargo test --all-features

# Build release
cargo build --release

# Audit dependencies
cargo audit
```

## Environment Variables

```bash
# Increase verbosity
cargo build -vv

# Offline mode (use cached dependencies)
cargo build --offline

# Set target directory
CARGO_TARGET_DIR=./build cargo build

# Force colored output
CARGO_TERM_COLOR=always cargo build

# Set number of parallel jobs
cargo build -j 4
```

## Target Management

### Cross-Compilation

```bash
# List installed targets
rustup target list

# Add target
rustup target add x86_64-unknown-linux-musl

# Build for target
cargo build --target x86_64-unknown-linux-musl

# Build for multiple targets
cargo build --target x86_64-pc-windows-gnu
```

## Debugging

```bash
# Build with debug info
cargo build

# Run with debugger (gdb)
rust-gdb target/debug/my-app

# Run with debugger (lldb)
rust-lldb target/debug/my-app

# Run with backtrace
RUST_BACKTRACE=1 cargo run
RUST_BACKTRACE=full cargo run
```

## Performance

### Release Optimizations

```toml
# Cargo.toml
[profile.release]
opt-level = 3        # Maximum optimization
lto = true           # Link-time optimization
codegen-units = 1    # Better optimization, slower compile
strip = true         # Strip symbols
panic = 'abort'      # Smaller binary
```

### Build Profiles

```bash
# Build with dev profile (default)
cargo build

# Build with release profile
cargo build --release

# Custom profile
[profile.production]
inherits = "release"
lto = true
codegen-units = 1
```

## Useful Cargo Flags

```bash
# Verbose output
cargo build -v
cargo build -vv

# Quiet output
cargo build -q

# Jobs (parallel compilation)
cargo build -j 4

# Keep going on error
cargo build --keep-going

# Timing information
cargo build --timings

# Explain compiler error
cargo --explain E0502
```

## Quick Reference Table

| Task | Cargo Command | PHP Equivalent |
|------|---------------|----------------|
| **Create project** | `cargo new app` | `composer init` |
| **Add dependency** | `cargo add serde` | `composer require vendor/pkg` |
| **Install deps** | `cargo build` | `composer install` |
| **Update deps** | `cargo update` | `composer update` |
| **Run code** | `cargo run` | `php script.php` |
| **Run tests** | `cargo test` | `phpunit` |
| **Format code** | `cargo fmt` | `php-cs-fixer fix` |
| **Lint code** | `cargo clippy` | `phpstan analyse` |
| **Build production** | `cargo build --release` | N/A |
| **Clean build** | `cargo clean` | `rm -rf vendor/` |
| **Security audit** | `cargo audit` | `composer audit` |
| **Publish** | `cargo publish` | Submit to packagist.org |

## Pro Tips

1. **Use `cargo check` during development** — Much faster than `cargo build`
2. **Enable auto-formatting** — Add `rustfmt` to your editor
3. **Run `clippy` often** — Catches common mistakes
4. **Use `cargo watch` for development** — Auto-rebuild on save
5. **Profile before optimizing** — Use `cargo flamegraph`
6. **Cache dependencies in CI** — Speed up builds significantly
7. **Use workspaces for monorepos** — Share dependencies
8. **Enable release optimizations** — But profile to ensure they help

---

For more details, see:
- [Cargo Book](https://doc.rust-lang.org/cargo/)
- [Cargo Reference](https://doc.rust-lang.org/cargo/reference/)
