# Appendix D: Essential Crates for Web Development

A curated list of must-have Rust crates for PHP developers building web applications, with comparisons to PHP packages.

## Web Frameworks

### Actix Web
**Rust's fastest web framework**

```toml
[dependencies]
actix-web = "4.4"
```

**Features:**
- Extremely fast (500k+ req/sec)
- Actor-based architecture
- Middleware support
- WebSocket support
- Multipart forms

**PHP equivalent:** Laravel, Symfony

**Example:**
```rust
use actix_web::{web, App, HttpServer, HttpResponse};

#[actix_web::main]
async fn main() -> std::io::Result<()> {
    HttpServer::new(|| {
        App::new()
            .route("/", web::get().to(|| async { HttpResponse::Ok().body("Hello!") }))
    })
    .bind(("127.0.0.1", 8080))?
    .run()
    .await
}
```

**When to use:** High-performance APIs, real-time applications

---

### Axum
**Ergonomic web framework built on Tower**

```toml
[dependencies]
axum = "0.7"
tokio = { version = "1", features = ["full"] }
```

**Features:**
- Built on Tower middleware
- Type-safe extractors
- Excellent error handling
- Good documentation
- Growing ecosystem

**PHP equivalent:** Slim, Symfony

**Example:**
```rust
use axum::{Router, routing::get};

#[tokio::main]
async fn main() {
    let app = Router::new()
        .route("/", get(|| async { "Hello, World!" }));

    let listener = tokio::net::TcpListener::bind("127.0.0.1:8080")
        .await
        .unwrap();

    axum::serve(listener, app).await.unwrap();
}
```

**When to use:** Modern async applications, microservices

---

### Rocket
**Type-safe web framework with great DX**

```toml
[dependencies]
rocket = "0.5"
```

**Features:**
- Code generation with macros
- Type-safe routing
- Built-in templating
- Form handling
- Testing support

**PHP equivalent:** Laravel (similar developer experience)

**Example:**
```rust
#[macro_use] extern crate rocket;

#[get("/hello/<name>")]
fn hello(name: &str) -> String {
    format!("Hello, {}!", name)
}

#[launch]
fn rocket() -> _ {
    rocket::build().mount("/", routes![hello])
}
```

**When to use:** Rapid development, when DX is priority

## Database

### SQLx
**Async, compile-time verified SQL**

```toml
[dependencies]
sqlx = { version = "0.7", features = ["postgres", "runtime-tokio"] }
```

**Features:**
- Compile-time query verification
- Async/await support
- PostgreSQL, MySQL, SQLite
- Connection pooling
- Migrations

**PHP equivalent:** PDO, Doctrine DBAL

**Example:**
```rust
use sqlx::PgPool;

#[tokio::main]
async fn main() -> Result<(), sqlx::Error> {
    let pool = PgPool::connect("postgres://localhost/mydb").await?;

    let row: (i64,) = sqlx::query_as("SELECT $1")
        .bind(150_i64)
        .fetch_one(&pool)
        .await?;

    Ok(())
}
```

**When to use:** Type-safe queries, async database access

---

### Diesel
**Type-safe ORM**

```toml
[dependencies]
diesel = { version = "2.1", features = ["postgres"] }
```

**Features:**
- Compile-time query verification
- Powerful query builder
- Schema migrations
- Associations
- Connection pooling

**PHP equivalent:** Eloquent, Doctrine ORM

**Example:**
```rust
use diesel::prelude::*;

#[derive(Queryable)]
struct User {
    id: i32,
    name: String,
}

let users = users::table
    .filter(users::name.like("%John%"))
    .load::<User>(&mut conn)?;
```

**When to use:** Complex queries, ORM patterns

---

### SeaORM
**Async ORM**

```toml
[dependencies]
sea-orm = { version = "0.12", features = ["sqlx-postgres"] }
```

**Features:**
- Async-first
- Relationship handling
- Migrations
- Code generation
- Active Record pattern

**PHP equivalent:** Eloquent (async)

**Example:**
```rust
use sea_orm::*;

let users: Vec<user::Model> = User::find()
    .filter(user::Column::Name.contains("John"))
    .all(&db)
    .await?;
```

**When to use:** Async applications, Laravel-like patterns

## Serialization

### Serde
**Serialization/deserialization framework**

```toml
[dependencies]
serde = { version = "1.0", features = ["derive"] }
serde_json = "1.0"
```

**Features:**
- Zero-copy deserialization
- Type-safe
- Support for many formats
- Custom serializers
- Excellent performance

**PHP equivalent:** JMS Serializer, symfony/serializer

**Example:**
```rust
use serde::{Deserialize, Serialize};

#[derive(Serialize, Deserialize)]
struct User {
    name: String,
    age: u8,
}

let user = User { name: "Alice".to_string(), age: 30 };
let json = serde_json::to_string(&user)?;
let user: User = serde_json::from_str(&json)?;
```

**When to use:** JSON APIs, configuration files, all serialization

## HTTP Client

### reqwest
**Async HTTP client**

```toml
[dependencies]
reqwest = { version = "0.11", features = ["json"] }
```

**Features:**
- Async/sync support
- JSON support
- Form data
- Multipart forms
- Cookies
- Redirects

**PHP equivalent:** Guzzle

**Example:**
```rust
use reqwest;

#[tokio::main]
async fn main() -> Result<(), Box<dyn std::error::Error>> {
    let resp = reqwest::get("https://api.example.com/users")
        .await?
        .json::<Vec<User>>()
        .await?;

    Ok(())
}
```

**When to use:** API integration, HTTP requests

## Validation

### validator
**Struct validation**

```toml
[dependencies]
validator = { version = "0.16", features = ["derive"] }
```

**Features:**
- Derive macros
- Custom validators
- Email validation
- URL validation
- Length, range checks

**PHP equivalent:** Symfony Validator, Laravel Validation

**Example:**
```rust
use validator::{Validate, ValidationError};

#[derive(Validate)]
struct SignupForm {
    #[validate(email)]
    email: String,

    #[validate(length(min = 8))]
    password: String,

    #[validate(range(min = 18, max = 120))]
    age: u8,
}

let form = SignupForm { /* ... */ };
form.validate()?;
```

**When to use:** Request validation, form handling

---

### garde
**Modern validation library**

```toml
[dependencies]
garde = { version = "0.16", features = ["derive"] }
```

**Features:**
- Better error messages
- Custom validators
- Async validation
- Nested validation

**PHP equivalent:** Laravel Validation

**When to use:** Complex validation rules

## Authentication

### jsonwebtoken
**JWT implementation**

```toml
[dependencies]
jsonwebtoken = "9.2"
```

**Features:**
- Encode/decode JWTs
- Algorithm support
- Validation
- Claims

**PHP equivalent:** firebase/php-jwt

**Example:**
```rust
use jsonwebtoken::{encode, decode, Header, Validation, EncodingKey, DecodingKey};

#[derive(Serialize, Deserialize)]
struct Claims {
    sub: String,
    exp: usize,
}

let token = encode(&Header::default(), &claims, &EncodingKey::from_secret("secret".as_ref()))?;
let token_data = decode::<Claims>(&token, &DecodingKey::from_secret("secret".as_ref()), &Validation::default())?;
```

**When to use:** JWT authentication, API tokens

## Error Handling

### anyhow
**Ergonomic error handling**

```toml
[dependencies]
anyhow = "1.0"
```

**Features:**
- Simple error handling
- Error context
- Backtrace support
- ? operator support

**PHP equivalent:** No direct equivalent (exceptions)

**Example:**
```rust
use anyhow::{Result, Context};

fn read_config() -> Result<Config> {
    let content = std::fs::read_to_string("config.toml")
        .context("Failed to read config file")?;

    toml::from_str(&content)
        .context("Failed to parse config")
}
```

**When to use:** Application-level errors

---

### thiserror
**Custom error types**

```toml
[dependencies]
thiserror = "1.0"
```

**Features:**
- Derive Error trait
- Custom error messages
- Error source chains
- Display formatting

**PHP equivalent:** Custom exception classes

**Example:**
```rust
use thiserror::Error;

#[derive(Error, Debug)]
pub enum ApiError {
    #[error("User not found: {0}")]
    NotFound(u32),

    #[error("Database error")]
    Database(#[from] sqlx::Error),
}
```

**When to use:** Library-level errors, custom error types

## Logging & Tracing

### tracing
**Structured logging**

```toml
[dependencies]
tracing = "0.1"
tracing-subscriber = "0.3"
```

**Features:**
- Structured logging
- Async support
- Span tracking
- Multiple subscribers
- Performance

**PHP equivalent:** Monolog

**Example:**
```rust
use tracing::{info, warn, error};

#[tracing::instrument]
async fn process_user(user_id: u32) {
    info!("Processing user");

    // Automatically tracked span
}

fn main() {
    tracing_subscriber::fmt::init();

    info!("Application started");
}
```

**When to use:** Production logging, observability

---

### log
**Simple logging facade**

```toml
[dependencies]
log = "0.4"
env_logger = "0.11"
```

**PHP equivalent:** PSR-3 Logger Interface

**When to use:** Simple logging needs

## Configuration

### config
**Layered configuration**

```toml
[dependencies]
config = "0.13"
```

**Features:**
- Multiple formats (TOML, JSON, YAML, INI)
- Environment variables
- Layered configs
- Type-safe

**PHP equivalent:** symfony/config

---

### dotenvy
**Environment variables**

```toml
[dependencies]
dotenvy = "0.15"
```

**PHP equivalent:** vlucas/phpdotenv

**Example:**
```rust
use dotenvy::dotenv;

fn main() {
    dotenv().ok();

    let db_url = std::env::var("DATABASE_URL").expect("DATABASE_URL must be set");
}
```

## Templates

### Tera
**Jinja2-like template engine**

```toml
[dependencies]
tera = "1.19"
```

**Features:**
- Jinja2 syntax
- Template inheritance
- Filters and functions
- Auto-escaping
- Fast

**PHP equivalent:** Twig

**Example:**
```rust
use tera::{Tera, Context};

let tera = Tera::new("templates/**/*").unwrap();
let mut context = Context::new();
context.insert("name", "Alice");

let html = tera.render("index.html", &context)?;
```

**When to use:** Server-side rendering

---

### Askama
**Compile-time templates**

```toml
[dependencies]
askama = "0.12"
```

**Features:**
- Compile-time checking
- Type-safe
- Jinja2-like syntax
- Fast

**PHP equivalent:** Blade (but compile-time)

**When to use:** Type-safe templates

## Testing

### mockall
**Mocking framework**

```toml
[dev-dependencies]
mockall = "0.12"
```

**PHP equivalent:** PHPUnit Mocks, Mockery

**Example:**
```rust
use mockall::mock;

mock! {
    Database {
        fn get_user(&self, id: u32) -> Option<User>;
    }
}

#[test]
fn test_get_user() {
    let mut mock = MockDatabase::new();
    mock.expect_get_user()
        .with(eq(1))
        .returning(|_| Some(User::default()));
}
```

---

### criterion
**Benchmarking**

```toml
[dev-dependencies]
criterion = "0.5"
```

**PHP equivalent:** PHPBench

**Example:**
```rust
use criterion::{black_box, criterion_group, criterion_main, Criterion};

fn fibonacci(n: u64) -> u64 {
    match n {
        0 => 1,
        1 => 1,
        n => fibonacci(n-1) + fibonacci(n-2),
    }
}

fn criterion_benchmark(c: &mut Criterion) {
    c.bench_function("fib 20", |b| b.iter(|| fibonacci(black_box(20))));
}

criterion_group!(benches, criterion_benchmark);
criterion_main!(benches);
```

## CLI Tools

### clap
**Command-line argument parsing**

```toml
[dependencies]
clap = { version = "4.4", features = ["derive"] }
```

**Features:**
- Derive API
- Subcommands
- Validation
- Auto-generated help
- Shell completions

**PHP equivalent:** Symfony Console

**Example:**
```rust
use clap::Parser;

#[derive(Parser)]
#[command(name = "myapp")]
struct Cli {
    #[arg(short, long)]
    name: String,

    #[arg(short, long, default_value = "0")]
    count: u32,
}

fn main() {
    let cli = Cli::parse();
    println!("Name: {}, Count: {}", cli.name, cli.count);
}
```

---

### indicatif
**Progress bars**

```toml
[dependencies]
indicatif = "0.17"
```

**PHP equivalent:** Symfony ProgressBar

---

### colored
**Terminal colors**

```toml
[dependencies]
colored = "2.1"
```

**PHP equivalent:** Symfony Console OutputFormatter

## Async Runtime

### Tokio
**Async runtime**

```toml
[dependencies]
tokio = { version = "1", features = ["full"] }
```

**Features:**
- Async runtime
- Task scheduler
- Timers
- I/O
- Channels
- Tracing integration

**PHP equivalent:** ReactPHP, Amphp

**Example:**
```rust
#[tokio::main]
async fn main() {
    tokio::spawn(async {
        println!("Hello from spawned task");
    }).await.unwrap();
}
```

**When to use:** All async applications

## Utilities

### chrono
**Date and time**

```toml
[dependencies]
chrono = "0.4"
```

**PHP equivalent:** Carbon, DateTime

---

### uuid
**UUID generation**

```toml
[dependencies]
uuid = { version = "1.6", features = ["v4"] }
```

**PHP equivalent:** ramsey/uuid

---

### regex
**Regular expressions**

```toml
[dependencies]
regex = "1.10"
```

**PHP equivalent:** preg_* functions

## Crate Selection Guide

| Use Case | Recommended Crate | Alternative |
|----------|------------------|-------------|
| **Web Framework** | Axum | Actix Web, Rocket |
| **Database (async)** | SQLx | SeaORM |
| **Database (sync)** | Diesel | - |
| **Serialization** | serde + serde_json | - |
| **HTTP Client** | reqwest | - |
| **Validation** | validator | garde |
| **Auth (JWT)** | jsonwebtoken | - |
| **Error Handling (app)** | anyhow | - |
| **Error Handling (lib)** | thiserror | - |
| **Logging** | tracing | log |
| **Templates** | Tera | Askama |
| **Testing (mocks)** | mockall | - |
| **CLI** | clap | - |
| **Async Runtime** | Tokio | async-std |

## Resources

- **crates.io**: Official package registry
- **lib.rs**: Alternative crate explorer
- **Awesome Rust**: Curated list of crates

---

Start with the essentials, add as needed. Most Rust projects use 5-10 core crates.
