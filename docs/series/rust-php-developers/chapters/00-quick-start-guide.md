---
title: "00: Quick Start Guide"
description: "Install Rust, set up your development environment, and run your first Rust program with immediate performance wins"
series: "rust-php-developers"
chapter: 0
order: 0
difficulty: "Beginner"
prerequisites:
  - "PHP 8.0+ installed"
  - "Basic command-line knowledge"
  - "Text editor or IDE"
---

![00: Quick Start Guide](/images/rust-php-developers/chapter-00-quick-start-hero-full.webp)

<div class="breadcrumbs">
  <a href="/">Home</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series">Series</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series/rust-php-developers">Rust for PHP Developers</a>
  <span class="breadcrumbs-separator">›</span>
  <span>Chapter 00</span>
</div>

# Chapter 00: Quick Start Guide

## Overview

Welcome to **Rust for PHP Developers**! This quick start guide gets you up and running with Rust in under 30 minutes. You'll install Rust, set up your development environment, write your first Rust program, and see immediate performance benefits compared to PHP.

By the end of this chapter, you'll have Rust installed, your editor configured, and you'll have run your first Rust programs. More importantly, you'll see concrete examples of where Rust shines and where PHP remains the better choice.

## Prerequisites

Before starting this chapter, you should have:

- **Operating System**: Linux, macOS, or Windows with WSL2
- **PHP 8.0+** installed for comparison examples
- **Terminal access** and basic command-line knowledge
- **Internet connection** for downloading Rust and dependencies
- **Text editor** (we'll configure it for Rust)

**Estimated Time**: ~30 minutes

## What You'll Build

By the end of this chapter, you will have:

- Rust toolchain installed (rustup, rustc, cargo)
- Development environment configured with rust-analyzer
- Your first "Hello World" program running
- Performance comparison between PHP and Rust
- Understanding of when to use each language

## Objectives

- Install Rust using rustup
- Understand the Rust toolchain (rustc, cargo, rustfmt, clippy)
- Set up VS Code or your preferred editor
- Write and run your first Rust program
- Compare PHP and Rust performance
- Learn basic Cargo commands

## Step 1: Installing Rust (~5 min)

### Goal

Install the Rust toolchain using rustup, the official Rust installer.

### Actions

1. **Install rustup** (Linux/macOS):

```bash
# Download and run the rustup installer
curl --proto '=https' --tlsv1.2 -sSf https://sh.rustup.rs | sh

# Follow the prompts - choose default installation (option 1)
```

2. **Install rustup** (Windows):

Download and run [rustup-init.exe](https://rustup.rs/) from the official website.

3. **Configure your shell**:

```bash
# Add Rust to your PATH (automatically done by installer)
# Reload your shell configuration
source $HOME/.cargo/env

# Or restart your terminal
```

4. **Verify installation**:

```bash
# Check Rust compiler version
rustc --version
# Expected output: rustc 1.75.0 (or newer)

# Check Cargo (Rust's package manager) version
cargo --version
# Expected output: cargo 1.75.0 (or newer)

# Check rustup version
rustup --version
# Expected output: rustup 1.26.0 (or newer)
```

### Expected Result

```
rustc 1.75.0 (82e1608df 2023-12-21)
cargo 1.75.0 (1d8b05cdd 2023-11-20)
rustup 1.26.0 (5af9b9484 2023-04-05)
```

### Why It Works

**rustup** is Rust's official toolchain manager (similar to nvm for Node.js or pyenv for Python). It manages:

- **rustc**: The Rust compiler
- **cargo**: Package manager and build tool (like Composer)
- **rustfmt**: Code formatter (like PHP CS Fixer)
- **clippy**: Linter for catching common mistakes
- **Multiple toolchains**: Switch between stable, beta, and nightly

Unlike PHP's single binary, Rust uses a toolchain approach that makes it easy to stay updated and manage different versions.

### Troubleshooting

- **Command not found after install** — Run `source $HOME/.cargo/env` or restart your terminal
- **Permission errors** — Don't use sudo; rustup installs to your home directory
- **Old version showing** — Run `rustup update` to get the latest stable version
- **Windows: linker errors** — Install Visual Studio C++ Build Tools or use WSL2

## Step 2: Setting Up Your Development Environment (~10 min)

### Goal

Configure your text editor or IDE for Rust development with autocomplete, error checking, and formatting.

### Actions

1. **Install VS Code** (recommended for beginners):

Download from [code.visualstudio.com](https://code.visualstudio.com/)

2. **Install rust-analyzer extension**:

```bash
# In VS Code, open Extensions (Ctrl+Shift+X or Cmd+Shift+X)
# Search for "rust-analyzer" and install it

# Or via command line
code --install-extension rust-lang.rust-analyzer
```

3. **Install additional helpful extensions**:

```bash
# Even Better TOML (for Cargo.toml files)
code --install-extension tamasfe.even-better-toml

# Error Lens (inline error messages)
code --install-extension usernamehw.errorlens

# crates (Cargo.toml dependency management)
code --install-extension serayuzgur.crates
```

4. **Configure VS Code settings** (optional but recommended):

Create `.vscode/settings.json` in your Rust projects:

```json
{
  "editor.formatOnSave": true,
  "rust-analyzer.checkOnSave.command": "clippy",
  "rust-analyzer.cargo.features": "all"
}
```

5. **Alternative editors**:

- **IntelliJ IDEA/CLion**: Install Rust plugin
- **Vim/Neovim**: Install rust.vim and coc-rust-analyzer
- **Emacs**: Install rust-mode and lsp-mode

### Expected Result

When you open a Rust file (.rs), you should see:
- Syntax highlighting
- Autocomplete suggestions
- Inline error messages
- Code actions (quick fixes)

### Why It Works

**rust-analyzer** is the official Rust Language Server Protocol (LSP) implementation. It provides:

- **Real-time error checking**: See compilation errors as you type
- **Autocomplete**: Like PHP's IntelliSense but type-aware
- **Go to definition**: Jump to function/type definitions
- **Refactoring tools**: Rename symbols, extract functions
- **Inline documentation**: Hover over functions to see docs

This makes Rust development feel as smooth as PHP with PHPStorm or VS Code with Intelephense.

### Troubleshooting

- **rust-analyzer not working** — Restart VS Code after installation
- **Slow performance** — Let rust-analyzer finish initial indexing (1-2 minutes)
- **Errors not showing** — Check that rust-analyzer is enabled in the status bar
- **Cargo.toml errors** — Ensure Even Better TOML extension is installed

## Step 3: Your First Rust Program (~5 min)

### Goal

Create and run your first Rust program using Cargo.

### Actions

1. **Create a new Rust project**:

```bash
# Navigate to your projects directory
cd ~/projects

# Create a new Rust binary project
cargo new hello-rust
cd hello-rust

# Project structure created:
# hello-rust/
# ├── Cargo.toml (like composer.json)
# ├── src/
# │   └── main.rs (your code)
# └── .gitignore
```

2. **Examine the generated code**:

```rust
// src/main.rs (auto-generated)
fn main() {
    println!("Hello, world!");
}
```

3. **Compare to PHP**:

```php
<?php
// hello.php
echo "Hello, world!\n";
```

4. **Run the Rust program**:

```bash
# Build and run in one command
cargo run

# Expected output:
#   Compiling hello-rust v0.1.0 (/path/to/hello-rust)
#    Finished dev [unoptimized + debuginfo] target(s) in 0.50s
#     Running `target/debug/hello-rust`
# Hello, world!
```

5. **Build for production**:

```bash
# Create optimized release build
cargo build --release

# Run the optimized binary
./target/release/hello-rust
```

### Expected Result

```
   Compiling hello-rust v0.1.0 (/home/user/projects/hello-rust)
    Finished dev [unoptimized + debuginfo] target(s) in 0.50s
     Running `target/debug/hello-rust`
Hello, world!
```

### Why It Works

**Key differences from PHP:**

| Concept | PHP | Rust |
|---------|-----|------|
| **Execution** | Interpreted (php script.php) | Compiled to binary |
| **Entry point** | Top-level code | fn main() function |
| **Output** | echo, print | println! macro |
| **Package manager** | composer | cargo |
| **Dependencies** | composer.json | Cargo.toml |

**Cargo** is Rust's build tool and package manager:
- `cargo new` — Create new project
- `cargo run` — Build and execute
- `cargo build` — Compile (debug mode)
- `cargo build --release` — Compile (optimized)
- `cargo test` — Run tests
- `cargo fmt` — Format code
- `cargo clippy` — Run linter

### Troubleshooting

- **Compilation errors** — Read the error messages carefully; Rust's compiler is very helpful
- **Long compile times** — First build is slow; subsequent builds are incremental
- **"cannot find binary" error** — Use `cargo run` instead of running the binary directly
- **Permission denied** — On Unix systems, binaries need execute permissions

## Step 4: PHP vs Rust Performance Comparison (~5 min)

### Goal

See concrete performance differences between PHP and Rust.

### Actions

1. **Create a CPU-intensive task in PHP**:

```php
<?php
// fibonacci.php
declare(strict_types=1);

function fibonacci(int $n): int {
    if ($n <= 1) return $n;
    return fibonacci($n - 1) + fibonacci($n - 2);
}

$start = microtime(true);
$result = fibonacci(40);
$duration = (microtime(true) - $start) * 1000;

echo "Fibonacci(40) = $result\n";
echo "Time: " . round($duration, 2) . "ms\n";
```

```bash
# Run PHP version
php fibonacci.php
# Expected: ~800-2000ms (depending on your machine)
```

2. **Create the same task in Rust**:

```rust
// src/main.rs
use std::time::Instant;

fn fibonacci(n: u32) -> u32 {
    if n <= 1 {
        return n;
    }
    fibonacci(n - 1) + fibonacci(n - 2)
}

fn main() {
    let start = Instant::now();
    let result = fibonacci(40);
    let duration = start.elapsed();

    println!("Fibonacci(40) = {}", result);
    println!("Time: {:?}", duration);
}
```

```bash
# Run Rust version (debug build)
cargo run
# Expected: ~400-800ms

# Run optimized Rust version
cargo run --release
# Expected: ~30-80ms (10-50x faster than PHP!)
```

3. **Compare results**:

| Implementation | Time (ms) | Relative Speed |
|----------------|-----------|----------------|
| PHP 8.3 | ~1,500 | 1x (baseline) |
| Rust (debug) | ~600 | ~2.5x faster |
| Rust (release) | ~50 | ~30x faster |

### Expected Result

**PHP output:**
```
Fibonacci(40) = 102334155
Time: 1523.45ms
```

**Rust output (release):**
```
Fibonacci(40) = 102334155
Time: 51.2ms
```

### Why It Works

**Performance differences:**

1. **Compilation**: Rust compiles to native machine code; PHP is interpreted
2. **Optimization**: Release builds enable aggressive optimizations
3. **Type system**: Rust knows exact types at compile time
4. **No runtime overhead**: No garbage collection pauses, no dynamic dispatch
5. **LLVM backend**: Rust uses the same optimizer as C/C++

**When does this matter?**
- ✅ CPU-intensive calculations
- ✅ Real-time processing
- ✅ High-throughput APIs
- ✅ Data processing pipelines
- ❌ CRUD web applications (PHP is fine!)
- ❌ Content management systems
- ❌ Admin dashboards

### Troubleshooting

- **Both versions too fast to measure** — Increase fibonacci(40) to fibonacci(42)
- **Rust slower than expected** — Make sure you're using `--release` flag
- **Stack overflow error** — This recursive approach is inefficient; we'll fix it later
- **Different result** — Check that types match (u32 vs i32)

## Step 5: Understanding Cargo.toml (~3 min)

### Goal

Learn the Rust equivalent of composer.json.

### Actions

1. **Examine Cargo.toml**:

```toml
# Cargo.toml (generated by cargo new)
[package]
name = "hello-rust"
version = "0.1.0"
edition = "2021"

[dependencies]
# Add external crates (packages) here
```

2. **Compare to composer.json**:

```json
// composer.json (PHP equivalent)
{
    "name": "vendor/hello-php",
    "version": "0.1.0",
    "require": {
        "php": "^8.0"
    }
}
```

3. **Add your first dependency**:

```toml
# Cargo.toml
[package]
name = "hello-rust"
version = "0.1.0"
edition = "2021"

[dependencies]
serde = "1.0"  # Like adding a Composer package
serde_json = "1.0"
```

4. **Install dependencies**:

```bash
# Cargo automatically downloads dependencies on next build
cargo build

# Dependencies are cached in ~/.cargo (like ~/.composer)
# Project lock file created: Cargo.lock (like composer.lock)
```

### Expected Result

Understanding the mapping:

| PHP (Composer) | Rust (Cargo) |
|----------------|--------------|
| composer.json | Cargo.toml |
| composer.lock | Cargo.lock |
| vendor/ | target/ |
| composer install | cargo build |
| composer require | Add to Cargo.toml + cargo build |
| packagist.org | crates.io |

### Why It Works

**Cargo.toml sections:**

- `[package]` — Project metadata (name, version, edition)
- `[dependencies]` — Runtime dependencies
- `[dev-dependencies]` — Development dependencies (like require-dev)
- `[build-dependencies]` — Build-time dependencies
- `[[bin]]` — Binary targets (multiple executables)

**Rust editions** (like PHP versions):
- `edition = "2021"` — Latest edition (Rust 1.56+)
- `edition = "2018"` — Previous edition
- `edition = "2015"` — Original edition

### Troubleshooting

- **Dependency not found** — Check spelling and version on [crates.io](https://crates.io)
- **Version conflicts** — Cargo.lock resolves versions automatically
- **Slow dependency downloads** — First time only; dependencies are cached
- **TOML syntax errors** — Install Even Better TOML extension for validation

## Step 6: Basic Cargo Commands (~2 min)

### Goal

Learn essential Cargo commands for daily development.

### Actions

1. **Essential commands**:

```bash
# Create a new project
cargo new my-project        # Binary (executable)
cargo new my-lib --lib      # Library

# Build and run
cargo build                 # Debug build
cargo build --release       # Optimized build
cargo run                   # Build + run (debug)
cargo run --release         # Build + run (optimized)

# Testing and quality
cargo test                  # Run tests
cargo fmt                   # Format code
cargo clippy                # Run linter

# Dependencies
cargo update                # Update dependencies
cargo tree                  # Show dependency tree

# Cleanup
cargo clean                 # Remove target/ directory

# Documentation
cargo doc --open            # Generate and open docs
```

2. **Compare to PHP tools**:

| Task | PHP | Rust |
|------|-----|------|
| **Create project** | composer init | cargo new |
| **Install deps** | composer install | cargo build |
| **Run tests** | phpunit | cargo test |
| **Format code** | php-cs-fixer | cargo fmt |
| **Lint** | phpstan/psalm | cargo clippy |
| **Update deps** | composer update | cargo update |

### Expected Result

You'll have a mental model of Cargo commands mapped to familiar PHP tools.

### Why It Works

**Cargo is opinionated** (like Laravel vs raw PHP):
- Standard project structure
- Built-in test runner
- Integrated formatter
- Consistent build process

This makes Rust projects more uniform than PHP projects, which can vary widely in structure.

### Troubleshooting

- **Cargo command not found** — Ensure `~/.cargo/bin` is in your PATH
- **Permission errors** — Never use sudo with Cargo
- **Slow builds** — Normal for first build; use `cargo check` for faster feedback
- **Too many compiler warnings** — Run `cargo clippy` to fix common issues

## Exercises

### Exercise 1: Create Your First CLI Tool

Build a simple command-line calculator that's faster than a PHP script.

```rust
// src/main.rs
use std::env;

fn main() {
    let args: Vec<String> = env::args().collect();

    if args.len() != 4 {
        eprintln!("Usage: {} <num1> <operator> <num2>", args[0]);
        std::process::exit(1);
    }

    let num1: f64 = args[1].parse().expect("Invalid number");
    let operator = &args[2];
    let num2: f64 = args[3].parse().expect("Invalid number");

    let result = match operator.as_str() {
        "+" => num1 + num2,
        "-" => num1 - num2,
        "*" => num1 * num2,
        "/" => num1 / num2,
        _ => {
            eprintln!("Unknown operator: {}", operator);
            std::process::exit(1);
        }
    };

    println!("{} {} {} = {}", num1, operator, num2, result);
}
```

**Run it:**
```bash
cargo run -- 10 + 5
# Output: 10 + 5 = 15

cargo run --release -- 100 / 7
# Output: 100 / 7 = 14.285714285714286
```

### Exercise 2: Benchmark String Concatenation

Compare PHP and Rust string operations.

**PHP version:**
```php
<?php
$start = microtime(true);
$result = '';
for ($i = 0; $i < 100000; $i++) {
    $result .= 'x';
}
echo "Time: " . round((microtime(true) - $start) * 1000, 2) . "ms\n";
```

**Rust version:**
```rust
use std::time::Instant;

fn main() {
    let start = Instant::now();
    let mut result = String::new();
    for _ in 0..100_000 {
        result.push('x');
    }
    println!("Time: {:?}", start.elapsed());
}
```

**Expected results:**
- PHP: ~100-300ms
- Rust (release): ~1-5ms (50-100x faster)

### Exercise 3: Reading Command-Line Arguments

Make a Rust program that greets users by name.

```rust
use std::env;

fn main() {
    let args: Vec<String> = env::args().collect();

    let name = if args.len() > 1 {
        &args[1]
    } else {
        "World"
    };

    println!("Hello, {}!", name);
}
```

```bash
cargo run -- Alice
# Output: Hello, Alice!

cargo run
# Output: Hello, World!
```

## Wrap-up

Congratulations! You've completed the quick start guide. Here's what you've accomplished:

- ✓ **Installed Rust** using rustup
- ✓ **Configured your editor** with rust-analyzer
- ✓ **Created your first Rust program** with Cargo
- ✓ **Compared PHP and Rust performance** (30-50x speedup!)
- ✓ **Learned Cargo commands** (Rust's Composer equivalent)
- ✓ **Built practical examples** (CLI calculator, benchmarks)

### Key Concepts Learned

- **Rust is compiled**: Creates fast native binaries
- **Cargo is powerful**: Like Composer + build tool + test runner
- **Performance matters**: 10-100x faster for CPU tasks
- **Development experience**: Modern tooling (rust-analyzer)
- **Use cases**: Right tool for the right job

### What's Next

In the next chapter, we'll explore **Why Rust for PHP Developers** in depth. You'll learn:

- When to choose Rust over PHP
- Real-world use cases and success stories
- Memory safety and the borrow checker
- Rust's type system vs PHP's type system
- Building a mental model for learning Rust

## Further Reading

- [The Rust Book](https://doc.rust-lang.org/book/) — Official Rust learning resource
- [Rust by Example](https://doc.rust-lang.org/rust-by-example/) — Learn with examples
- [Cargo Book](https://doc.rust-lang.org/cargo/) — Deep dive into Cargo
- [crates.io](https://crates.io/) — Rust package registry
- [Chapter 01: Why Rust for PHP Developers](/series/rust-php-developers/chapters/01-why-rust-for-php-developers) — Next chapter

## Code Samples

All code examples from this chapter are available in the GitHub repository:

**[View Chapter 00 Code Samples](https://github.com/dalehurley/codewithphp/tree/main/code-samples/rust-php-developers/chapter-00)**

Files included:
- `hello-world/` — Basic hello world example
- `fibonacci/` — Performance comparison
- `calculator/` — CLI calculator
- `benchmarks/` — String concatenation benchmarks
- `README.md` — Complete documentation

Clone and run:
```bash
git clone https://github.com/dalehurley/codewithphp.git
cd codewithphp/code-samples/rust-php-developers/chapter-00
cargo run
```

<ChapterCheckbox
  seriesId="rust-php-developers"
  chapterId="00"
  label="You've set up Rust and seen its performance benefits!"
/>

---

Ready to understand when and why to use Rust? Continue to [Chapter 01: Why Rust for PHP Developers](/series/rust-php-developers/chapters/01-why-rust-for-php-developers).
