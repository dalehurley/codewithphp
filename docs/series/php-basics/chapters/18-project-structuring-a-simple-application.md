---
title: "18: Project: Structuring a Simple Application"
description: "Learn how to structure a modern PHP application using the MVC pattern, separating your concerns into Models, Views, and Controllers."
series: "php-basics"
chapter: 18
order: 18
difficulty: "Intermediate"
prerequisites:
  - "/series/php-basics/chapters/17-building-a-basic-http-router"
---

# Chapter 18: Project: Structuring a Simple Application

## Overview

We've built a router, which is the heart of a modern application's architecture. But right now, our route handlers are simple anonymous functions living in `index.php`. As our application grows, this will become incredibly messy.

To solve this, we'll introduce a foundational architectural pattern: **Model-View-Controller (MVC)**. MVC is a way of organizing your code that separates concerns into three distinct layers:

- **Model**: Represents your application's data and business logic. Think of this as the code that interacts with your database (e.g., a `User` model or a `Post` model).
- **View**: Represents the presentation layer—the HTML that gets sent to the user. It should be as "dumb" as possible, only responsible for displaying data it's given.
- **Controller**: Acts as the intermediary. It receives the request from the router, uses the Model to fetch or update data, and then passes that data to the appropriate View to be rendered.

In this chapter, we'll refactor our project to use this powerful and scalable structure.

## Prerequisites

Before starting this chapter, ensure you have:

- **Completed Chapter 17**: You should have a working router from the previous chapter
- **PHP 8.4** installed and running
- **Composer** installed and configured
- **A working `simple-blog` project** with the router from Chapter 17
- **Estimated Time**: ~25 minutes

## What You'll Build

By the end of this chapter, you'll have:

- A clean MVC directory structure (`Controllers/`, `Models/`, `Views/`)
- A `PageController` with methods for handling home and about pages
- An enhanced router that can dispatch to controller methods
- A simple view rendering system with data passing capabilities
- A **layout system** to eliminate duplicate HTML across views
- A helper function to load and render view templates
- Working routes that demonstrate the separation of concerns

## Objectives

- Understand the roles of Model, View, and Controller.
- Create a directory structure that reflects the MVC pattern.
- Refactor our router to call controller methods instead of closures.
- Create a simple templating system for rendering views and passing data to them.
- Implement a layout system to eliminate duplicate HTML across views.
- Use output buffering to compose views within layouts.

## Step 1: Creating the Application Structure (~4 min)

**Goal**: Set up the MVC directory structure and reorganize existing files.

Let's organize our `simple-blog` project to follow the MVC pattern.

1.  **Create New Directories**:

    Inside your `src/` directory, create the following new folders:

```bash
# From your project root
mkdir -p src/Controllers src/Models src/Views src/Routing
```

The `Views` directory is where we'll put our HTML templates. Since views are not PHP classes, they don't need to follow PSR-4, but it's good practice to keep them organized.

2.  **Move the Router File**:

    Move `src/Core/Router.php` to the new `src/Routing/Router.php` location:

```bash
# Move the Router
mv src/Core/Router.php src/Routing/Router.php
```

3.  **Update the Router Namespace**:

    Open `src/Routing/Router.php` and update the namespace at the top:

```php
<?php

declare(strict_types=1);

namespace App\Routing;  // Changed from App\Core

class Router
{
    // ... rest of the class remains the same
}
```

4.  **Update the Autoloader**:

```bash
# Rebuild Composer's autoloader
composer dump-autoload
```

**Expected Result**: Your `src/` directory should now look like this:

```
src/
├── Controllers/     (empty, ready for controllers)
├── Models/          (empty, ready for models)
├── Routing/
│   └── Router.php
└── Views/           (empty, ready for view templates)
```

**Validation**: Run this command to verify your structure:

```bash
ls -la src/
```

You should see all four directories listed.

## Step 2: Creating Controllers (~6 min)

**Goal**: Build a controller class and update the router to dispatch to controller methods.

A controller is a class that groups related request-handling logic. For example, a `PostController` might have methods for showing all posts (`index`), showing a single post (`show`), creating a new post (`create`), and so on.

### 2.1 Create a PageController

Let's create a controller for our static pages (Home, About).

**File: `src/Controllers/PageController.php`**

```php
<?php

declare(strict_types=1);

namespace App\Controllers;

class PageController
{
    public function home()
    {
        echo "This is the Home page from the controller.";
    }

    public function about()
    {
        echo "This is the About page from the controller.";
    }
}
```

### 2.2 Update the Router to Support Controllers

We need to teach our router how to handle a `['ClassName', 'methodName']` syntax for handlers. Here's the complete updated `dispatch` method:

**File: `src/Routing/Router.php`**

```php
<?php

declare(strict_types=1);

namespace App\Routing;

class Router
{
    protected array $routes = [];

    public function get(string $uri, $handler): void
    {
        $this->addRoute('GET', $uri, $handler);
    }

    public function post(string $uri, $handler): void
    {
        $this->addRoute('POST', $uri, $handler);
    }

    protected function addRoute(string $method, string $uri, $handler): void
    {
        $this->routes[] = [
            'method' => $method,
            'uri' => $uri,
            'handler' => $handler,
        ];
    }

    public function dispatch(): void
    {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $method = $_SERVER['REQUEST_METHOD'];

        foreach ($this->routes as $route) {
            if ($route['uri'] === $uri && $route['method'] === $method) {
                $handler = $route['handler'];

                // Check if handler is an array [ControllerClass, 'method']
                if (is_array($handler)) {
                    $controller = new $handler[0]();
                    $controllerMethod = $handler[1];

                    return $controller->$controllerMethod();
                }

                // Handler is a closure/callable
                return call_user_func($handler);
            }
        }

        // No route found
        http_response_code(404);
        echo "404 Not Found";
    }
}
```

**Why it works**: We check if the handler is an array. If it is, we know it contains a class name and method name. We instantiate the controller and call the method dynamically.

### 2.3 Update index.php to Use the Controller

Now, let's refactor our routes to use the new controller.

**File: `public/index.php`**

```php
<?php

declare(strict_types=1);

require_once '../vendor/autoload.php';

use App\Controllers\PageController;
use App\Routing\Router;

$router = new Router();

// Use the [Controller::class, 'method'] syntax
$router->get('/', [PageController::class, 'home']);
$router->get('/about', [PageController::class, 'about']);

$router->dispatch();
```

Using `PageController::class` gives us the fully qualified class name as a string (`App\Controllers\PageController`), which is a clean and reliable way to reference classes.

**Expected Result**: When you visit `http://localhost:8000/`, you should see:

```
This is the Home page from the controller.
```

And when you visit `http://localhost:8000/about`, you should see:

```
This is the About page from the controller.
```

**Validation**: Test both routes:

```bash
# Start the dev server if not already running
php -S localhost:8000 -t public

# In another terminal, test the routes
curl http://localhost:8000/
curl http://localhost:8000/about
```

### Troubleshooting

**Problem**: "Class 'App\Controllers\PageController' not found"

**Solution**: Run `composer dump-autoload` to regenerate the autoloader.

**Problem**: "404 Not Found" on valid routes

**Solution**: Check that your `PageController.php` namespace is exactly `App\Controllers` and the class name matches the filename.

## Step 3: Creating a Simple View System (~7 min)

**Goal**: Separate presentation logic from controllers by creating a view rendering system.

Right now, our controllers are `echo`ing HTML directly. This is bad practice. The controller's job is to fetch data, not to render HTML. Let's create a simple system to handle views.

### 3.1 Create the View Files

Create two simple view files. These are just HTML with PHP tags where we want to insert data.

**File: `src/Views/home.php`**

```php
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
</head>
<body>
    <h1><?php echo htmlspecialchars($pageTitle); ?></h1>
    <p>Welcome to the homepage!</p>
    <nav>
        <a href="/">Home</a>
        <a href="/about">About</a>
    </nav>
</body>
</html>
```

**File: `src/Views/about.php`**

```php
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
</head>
<body>
    <h1><?php echo htmlspecialchars($pageTitle); ?></h1>
    <p>This is the about page. Learn more about our simple blog application.</p>
    <nav>
        <a href="/">Home</a>
        <a href="/about">About</a>
    </nav>
</body>
</html>
```

**Note**: We use `htmlspecialchars()` to prevent XSS attacks by escaping any HTML in our data.

### 3.2 Create a View Helper Function

We'll create a simple helper function that can render these views and pass data to them. Don't worry—we'll enhance this function in the next step to support layouts!

**File: `src/helpers.php`**

```php
<?php

declare(strict_types=1);

/**
 * Render a view with the given data.
 *
 * @param string $viewName The name of the view file (without .php extension)
 * @param array $data Associative array of data to pass to the view
 * @return void
 */
function view(string $viewName, array $data = []): void
{
    // Build the full path to the view file
    $viewPath = __DIR__ . "/Views/{$viewName}.php";

    // Check if the view file exists
    if (!file_exists($viewPath)) {
        throw new Exception("View not found: {$viewName}");
    }

    // Extract the data array into variables
    // EXTR_SKIP prevents overwriting existing variables (security measure)
    // e.g., ['pageTitle' => 'Home'] becomes $pageTitle = 'Home'
    extract($data, EXTR_SKIP);

    // Require the view file - it now has access to the extracted variables
    require $viewPath;
}
```

**Why it works**: The `extract()` function with `EXTR_SKIP` flag safely converts array keys into variable names, making them available inside the view file.

### 3.3 Load the Helper and Update the Controller

First, tell Composer to always load our `helpers.php` file. Open `composer.json` and add a `files` array to your `autoload` section:

```json
{
  "autoload": {
    "psr-4": {
      "App\\": "src/"
    },
    "files": ["src/helpers.php"]
  },
  "require": {}
}
```

Then, rebuild the autoloader:

```bash
composer dump-autoload
```

Now, update `PageController.php` to use our new `view()` function:

**File: `src/Controllers/PageController.php`**

```php
<?php

declare(strict_types=1);

namespace App\Controllers;

class PageController
{
    public function home(): void
    {
        view('home', [
            'pageTitle' => 'Home Page'
        ]);
    }

    public function about(): void
    {
        view('about', [
            'pageTitle' => 'About Us'
        ]);
    }
}
```

**Expected Result**: Visit `http://localhost:8000/` and you should see a properly formatted HTML page with:

- A page title of "Home Page" in the browser tab
- An `<h1>` heading saying "Home Page"
- A welcome message
- Navigation links to Home and About

**Validation**: Check the HTML source:

```bash
curl http://localhost:8000/
```

You should see complete HTML with `<!DOCTYPE html>`, proper head section, and the content from your view.

**Note**: You might notice we're duplicating HTML structure between `home.php` and `about.php`. This is intentional for now—we'll solve this problem elegantly in Step 4 with a layout system!

### Troubleshooting

**Problem**: "View not found: home"

**Solution**: Ensure `src/Views/home.php` exists and the path in `helpers.php` is correct. The `__DIR__` should point to the `src/` directory.

**Problem**: Variable `$pageTitle` is undefined in view

**Solution**: Make sure you're passing the data array as the second argument to `view()` and that Composer's autoloader has been rebuilt with `composer dump-autoload`.

**Problem**: "Failed to open stream" error

**Solution**: Check file permissions on the `src/Views/` directory. On Unix systems, run `chmod 755 src/Views`.

## Step 4: Adding a Layout System (~5 min)

**Goal**: Eliminate duplicate HTML structure across views by implementing a master layout template.

Right now, both `home.php` and `about.php` contain the full HTML structure (`<!DOCTYPE>`, `<head>`, `<body>`, navigation, etc.). As we add more pages, this duplication becomes a maintenance nightmare. What if we need to add a new navigation link or change the site's title format? We'd have to edit every single view file.

The solution is a **layout system** (also called a "master template"). This is a fundamental pattern used by all modern PHP frameworks.

### 4.1 Create the Layout File

Create a master layout that will wrap all our views.

**File: `src/Views/layout.php`**

```php
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle ?? 'Simple Blog'); ?></title>
    <style>
        body { font-family: sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
        nav { margin-bottom: 20px; padding: 10px; background: #f0f0f0; }
        nav a { margin-right: 15px; text-decoration: none; color: #333; }
        nav a:hover { color: #0066cc; }
        footer { margin-top: 40px; padding-top: 20px; border-top: 1px solid #ddd; color: #666; }
    </style>
</head>
<body>
    <nav>
        <a href="/">Home</a>
        <a href="/about">About</a>
    </nav>

    <main>
        <?php echo $content; ?>
    </main>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> Simple Blog. Built with PHP from scratch.</p>
    </footer>
</body>
</html>
```

**Note**: The `$content` variable will hold the rendered output from our individual view files.

### 4.2 Update the View Helper Function

Now we need to update our `view()` function to support layouts. We'll use PHP's **output buffering** to capture the view content and then inject it into the layout.

**File: `src/helpers.php`**

```php
<?php

declare(strict_types=1);

/**
 * Render a view with the given data, wrapped in a layout.
 *
 * @param string $viewName The name of the view file (without .php extension)
 * @param array $data Associative array of data to pass to the view
 * @param string|null $layout The layout file to use (null for no layout)
 * @return void
 */
function view(string $viewName, array $data = [], ?string $layout = 'layout'): void
{
    // Build the full path to the view file
    $viewPath = __DIR__ . "/Views/{$viewName}.php";

    // Check if the view file exists
    if (!file_exists($viewPath)) {
        throw new Exception("View not found: {$viewName}");
    }

    // Extract the data array into variables
    // EXTR_SKIP prevents overwriting existing variables (security measure)
    extract($data, EXTR_SKIP);

    // Start output buffering to capture the view content
    ob_start();

    // Require the view file - its output will be captured
    require $viewPath;

    // Get the captured content and clean the buffer
    $content = ob_get_clean();

    // If a layout is specified, render the content within it
    if ($layout !== null) {
        $layoutPath = __DIR__ . "/Views/{$layout}.php";

        if (!file_exists($layoutPath)) {
            throw new Exception("Layout not found: {$layout}");
        }

        // Require the layout file, which has access to $content
        require $layoutPath;
    } else {
        // No layout, just echo the content
        echo $content;
    }
}
```

**Why it works**:

1. **Output buffering** (`ob_start()`) captures all output from the view file
2. We store that captured output in `$content`
3. We then render the layout, which has access to `$content`
4. The layout injects `$content` into the appropriate place in the HTML structure

### 4.3 Simplify the View Files

Now we can dramatically simplify our view files since they only need to contain their specific content—no more duplicate HTML!

**File: `src/Views/home.php`**

```php
<h1><?php echo htmlspecialchars($pageTitle); ?></h1>
<p>Welcome to the homepage! This is a simple blog application built from scratch using PHP and the MVC pattern.</p>
<p>Navigate through the site using the menu above, or check out our blog posts.</p>
```

**File: `src/Views/about.php`**

```php
<h1><?php echo htmlspecialchars($pageTitle); ?></h1>
<p>This is the about page. Learn more about our simple blog application.</p>
<h2>About This Project</h2>
<p>This blog was built as a learning project to demonstrate:</p>
<ul>
    <li>The Model-View-Controller (MVC) pattern</li>
    <li>Routing and controllers</li>
    <li>View rendering with layouts</li>
    <li>Separation of concerns</li>
</ul>
```

Notice how much cleaner these files are! They focus solely on their specific content.

### 4.4 Rebuild and Test

Since we modified `helpers.php`, rebuild the autoloader:

```bash
composer dump-autoload
```

**Expected Result**: Your pages should look better with consistent styling, navigation, and a footer—all without duplicating code!

**Validation**: View the source of any page:

```bash
curl http://localhost:8000/
```

You should see the complete HTML structure from `layout.php` with the page-specific content from `home.php` inserted in the middle.

### 4.5 Using Layouts Flexibly

The `view()` function now accepts an optional third parameter. You can:

**Use the default layout** (most common):

```php
view('home', ['pageTitle' => 'Home']);  // Uses layout.php
```

**Specify a different layout**:

```php
view('home', ['pageTitle' => 'Home'], 'admin-layout');  // Uses admin-layout.php
```

**Render without any layout**:

```php
view('home', ['pageTitle' => 'Home'], null);  // No layout, just the view
```

This flexibility is useful for AJAX responses, API endpoints, or special pages that need different layouts.

### Troubleshooting

**Problem**: "Cannot modify header information - headers already sent"

**Solution**: This happens when there's output before `ob_start()`. Check for whitespace or `echo` statements before the view is rendered. Make sure `declare(strict_types=1);` in `helpers.php` has no spaces before the `<?php` tag.

**Problem**: Layout shows but view content is missing

**Solution**: Check that `ob_get_clean()` is returning content. Add `var_dump($content);` before the layout is required to debug. The view file path might be wrong.

**Problem**: Undefined variable `$content` in layout

**Solution**: Ensure output buffering is working correctly and that `$content = ob_get_clean();` comes before `require $layoutPath;`.

## Exercises

### Exercise 1: Create a Base Controller (⭐⭐)

Right now, if we wanted to add functionality that all controllers share, we'd have to duplicate code. Let's create a base controller.

**Tasks**:

1. Create an abstract `Controller` class in `src/Core/Controller.php`
2. Add a `protected function view()` method that wraps the global `view()` helper
3. Make your `PageController` extend this new `Controller` class
4. Update `PageController` methods to use `$this->view()` instead of the global function

**Hints**:

```php
// src/Core/Controller.php
abstract class Controller
{
    protected function view(string $viewName, array $data = []): void
    {
        view($viewName, $data);
    }
}
```

This object-oriented approach makes it easier to add shared functionality like authentication checks or logging.

### Exercise 2: Create a Post Model and Controller (⭐⭐⭐)

Build out the Model layer of MVC by creating a `Post` model and displaying posts.

**Tasks**:

1. Create `src/Models/Post.php` with a `public static function all()` method
2. Have `all()` return hardcoded blog post data (array of arrays with `title`, `content`, `date` keys)
3. Create `src/Controllers/PostController.php` with an `index()` method
4. In `index()`, fetch posts using `Post::all()` and pass to a view
5. Create `src/Views/posts/index.php` to display all posts (use a loop)
6. Register a `/posts` route in `public/index.php`

**Expected Output**: Visiting `http://localhost:8000/posts` should display a list of blog posts with titles, content, and dates.

**Hint for the view**:

```php
// src/Views/posts/index.php
foreach ($posts as $post): ?>
    <article>
        <h2><?php echo htmlspecialchars($post['title']); ?></h2>
        <p><?php echo htmlspecialchars($post['content']); ?></p>
    </article>
<?php endforeach;
```

### Exercise 3: Add a 404 Page (⭐)

Improve the user experience when visiting invalid routes.

**Tasks**:

1. Create `src/Views/404.php` with a friendly "Page not found" message
2. Update the router's `dispatch()` method to call `view('404')` instead of `echo "404 Not Found"`
3. Test by visiting `http://localhost:8000/nonexistent-page`

### Exercise 4: Create an Admin Layout (⭐⭐)

Practice using multiple layouts by creating a different layout for administrative pages.

**Tasks**:

1. Create `src/Views/admin-layout.php` with a different color scheme and navigation
2. Add a link to a "Dashboard" (`/admin`) in the admin navigation
3. Create `src/Controllers/AdminController.php` with a `dashboard()` method
4. Have the controller render a view using the admin layout: `view('admin/dashboard', [...], 'admin-layout')`
5. Create `src/Views/admin/dashboard.php` with some dashboard content
6. Add the `/admin` route to `index.php`

**Expected Behavior**: The admin pages should look visually different (different colors, different nav) from the public pages while still using the same view rendering system.

**Hint**: Your admin layout could have:

```php
<style>
    body { background: #2c3e50; color: white; }
    nav { background: #34495e; }
    /* ... more admin-specific styling ... */
</style>
```

## Wrap-up

Congratulations! This was a massive step forward in building a real application. You've successfully:

- ✅ Implemented the MVC pattern
- ✅ Separated concerns into Models, Views, and Controllers
- ✅ Created a reusable view rendering system with layouts
- ✅ Learned about output buffering for template composition
- ✅ Built a scalable directory structure
- ✅ Enhanced your router to work with controllers
- ✅ Eliminated code duplication with master templates

You now have a clean, organized, and scalable structure that mirrors the foundation of almost every modern PHP framework like Laravel (Blade layouts), Symfony (Twig layouts), and CodeIgniter (template inheritance).

**What's Next**: In the final project chapter, we'll bring everything together by connecting our `Post` model to a real database, building out full CRUD (Create, Read, Update, Delete) functionality, and completing our blog application.

## Further Reading

- [MVC Pattern Explained](https://www.php.net/manual/en/tutorial.php) - PHP manual on basic architecture
- [PSR-4 Autoloading Standard](https://www.php-fig.org/psr/psr-4/) - Understanding PHP autoloading conventions
- [Composer Autoloading](https://getcomposer.org/doc/01-basic-usage.md#autoloading) - Official Composer documentation
- [Output Buffering in PHP](https://www.php.net/manual/en/book.outcontrol.php) - Official documentation on output control functions
- [Security Best Practices](https://www.php.net/manual/en/security.php) - PHP security guidelines including XSS prevention
- [Template Inheritance Patterns](https://en.wikipedia.org/wiki/Template_method_pattern) - Understanding layout and template patterns
