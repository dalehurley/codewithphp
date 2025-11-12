# Chapter 01: Installing Laravel and Your First Application

Code examples for Chapter 01 of the Laravel for Humans series.

## Files in This Directory

### Routes

- **`routes/web.php`** - All route examples from the chapter including:
  - Basic routes with closures
  - Routes with parameters
  - JSON responses
  - Controller-based routes
  - Named routes
  - Route groups
  - Route constraints

### Controllers

- **`app/Http/Controllers/WelcomeController.php`** - Example controller with methods for:
  - Displaying the welcome page
  - Greeting users by name
  - Returning API status
  - Showing application information

### Solutions

- **`solutions/exercise-1-personal-info.php`** - Solution for creating a personal info API route
- **`solutions/exercise-2-calculator-controller.php`** - Calculator controller with operation handling
- **`solutions/exercise-3-named-routes.php`** - Examples of naming routes and generating URLs

## How to Use These Examples

### Option 1: Copy into Fresh Laravel Install

1. Create a new Laravel project:
   ```bash
   composer create-project laravel/laravel taskflow
   cd taskflow
   ```

2. Copy the route examples into your `routes/web.php`

3. Copy the controller into `app/Http/Controllers/WelcomeController.php`

4. Start the dev server:
   ```bash
   php artisan serve
   ```

5. Visit the routes:
   - http://localhost:8000/
   - http://localhost:8000/hello
   - http://localhost:8000/greet/YourName
   - http://localhost:8000/api/status
   - http://localhost:8000/info

### Option 2: Study as Reference

Read through the files to understand:
- How routes are structured
- How controllers organize logic
- How to return different response types
- How to use route parameters
- Best practices for Laravel routing

## Testing the Routes

List all available routes:
```bash
php artisan route:list
```

Test routes in your browser:
- Simple routes: http://localhost:8000/hello
- Parameterized routes: http://localhost:8000/greet/Alice
- JSON responses: http://localhost:8000/api/status

## Common Commands Used

```bash
# Start development server
php artisan serve

# Generate a controller
php artisan make:controller WelcomeController

# List all routes
php artisan route:list

# Clear route cache
php artisan route:clear

# Generate application key
php artisan key:generate
```

## Next Steps

After working through these examples:

1. Try the exercises in the chapter
2. Experiment with different route patterns
3. Create your own controllers
4. Move on to Chapter 02 for advanced routing concepts

## Troubleshooting

**Routes not working?**
- Ensure the dev server is running (`php artisan serve`)
- Clear route cache: `php artisan route:clear`
- Check for typos in route definitions

**Controller not found?**
- Verify the namespace is correct
- Ensure the controller file exists in the right directory
- Run `composer dump-autoload`

**500 errors?**
- Check storage permissions: `chmod -R 775 storage bootstrap/cache`
- Ensure .env file exists (copy from .env.example)
- Check Laravel logs: `storage/logs/laravel.log`

## Resources

- [Laravel Routing Documentation](https://laravel.com/docs/11.x/routing)
- [Laravel Controllers Documentation](https://laravel.com/docs/11.x/controllers)
- [Laravel Responses Documentation](https://laravel.com/docs/11.x/responses)
