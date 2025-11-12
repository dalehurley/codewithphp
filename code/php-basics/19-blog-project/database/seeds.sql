-- Sample Data for Blog
-- Password for all users: "password" (hashed with PASSWORD_DEFAULT)

-- Insert sample users
INSERT INTO users (name, email, password, bio) VALUES
(
    'Admin User',
    'admin@blog.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'Blog administrator and main author'
),
(
    'Jane Doe',
    'jane@example.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'Tech enthusiast and occasional blogger'
),
(
    'John Smith',
    'john@example.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'Developer and writer'
);

-- Insert sample posts
INSERT INTO posts (user_id, title, slug, content, excerpt, published, published_at) VALUES
(
    1,
    'Welcome to Our Blog',
    'welcome-to-our-blog',
    'Welcome to our new blog! We''re excited to share our thoughts and experiences with you.

## What We''ll Cover

This blog will feature articles about:

- Web development
- PHP programming
- Best practices
- Tutorials and guides

## Get Involved

Feel free to leave comments and engage with our content. We''d love to hear from you!',
    'Welcome to our new blog! We''re excited to share our thoughts and experiences.',
    1,
    datetime('now')
),
(
    1,
    'Getting Started with PHP',
    'getting-started-with-php',
    '# Getting Started with PHP

PHP is a powerful server-side scripting language that''s perfect for web development.

## Why PHP?

- Easy to learn
- Vast ecosystem
- Great documentation
- Active community

## Your First Script

```php
<?php
echo "Hello, World!";
```

That''s all you need to get started!',
    'Learn the basics of PHP programming in this beginner-friendly guide.',
    1,
    datetime('now', '-1 day')
),
(
    2,
    'Understanding MVC Architecture',
    'understanding-mvc-architecture',
    '# MVC Architecture Explained

The Model-View-Controller (MVC) pattern separates your application into three main components:

## Model

Handles data and business logic. Interacts with the database.

## View

Presents data to the user. Templates and HTML.

## Controller

Coordinates between Model and View. Handles user requests.

This separation makes your code more maintainable and testable.',
    'A clear explanation of the MVC architectural pattern.',
    1,
    datetime('now', '-2 days')
),
(
    3,
    'Database Best Practices',
    'database-best-practices',
    '# Database Best Practices

When working with databases, follow these key principles:

## Use Prepared Statements

Always use prepared statements to prevent SQL injection:

```php
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
```

## Index Your Queries

Add indexes to frequently queried columns for better performance.

## Normalize Your Schema

Follow database normalization principles to reduce redundancy.',
    'Essential tips for working with databases securely and efficiently.',
    1,
    datetime('now', '-3 days')
),
(
    1,
    'Draft: Upcoming Features',
    'draft-upcoming-features',
    'This is a draft post about upcoming features we''re planning.

It''s not yet published, so only admins can see it in the dashboard.',
    'A preview of exciting new features coming soon.',
    0,
    NULL
);

