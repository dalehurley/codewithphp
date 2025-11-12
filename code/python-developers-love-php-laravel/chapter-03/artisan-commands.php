<?php

/**
 * Laravel Artisan Command Examples
 * 
 * This file demonstrates custom Artisan commands in Laravel.
 * Compare to Django's management commands (django-manage.py).
 * 
 * To create a custom command:
 * php artisan make:command CommandName
 */

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

/**
 * Example: Custom Artisan command to list users
 * 
 * Django equivalent: management/commands/list_users.py
 */
class ListUsers extends Command
{
    /**
     * Command signature (name and arguments)
     * 
     * Syntax: command:name {argument} {--option=default}
     */
    protected $signature = 'users:list {--limit=10 : Number of users to display}';

    /**
     * Command description (shown in php artisan list)
     */
    protected $description = 'Display a list of users in a table format';

    /**
     * Execute the command
     * 
     * @return int Command exit code (0 = success, 1 = failure)
     */
    public function handle(): int
    {
        $limit = (int) $this->option('limit');
        
        // Fetch users from database
        $users = User::limit($limit)->get();
        
        if ($users->isEmpty()) {
            $this->warn('No users found.');
            return Command::SUCCESS;
        }
        
        // Prepare data for table display
        $tableData = $users->map(function ($user) {
            return [
                'ID' => $user->id,
                'Name' => $user->name,
                'Email' => $user->email,
                'Created At' => $user->created_at->format('Y-m-d H:i:s'),
            ];
        })->toArray();
        
        // Display table
        $this->table(
            ['ID', 'Name', 'Email', 'Created At'],
            $tableData
        );
        
        $this->info("Displayed {$users->count()} user(s).");
        
        return Command::SUCCESS;
    }
}

/**
 * Example: Command with progress bar
 */
class ProcessUsers extends Command
{
    protected $signature = 'users:process';
    protected $description = 'Process users with progress bar';

    public function handle(): int
    {
        $users = User::all();
        $bar = $this->output->createProgressBar($users->count());
        $bar->start();
        
        foreach ($users as $user) {
            // Process user...
            usleep(100000); // Simulate work
            $bar->advance();
        }
        
        $bar->finish();
        $this->newLine();
        $this->info('All users processed!');
        
        return Command::SUCCESS;
    }
}

/**
 * Example: Command with questions/interactive input
 */
class CreateUser extends Command
{
    protected $signature = 'users:create';
    protected $description = 'Create a new user interactively';

    public function handle(): int
    {
        $name = $this->ask('What is the user\'s name?');
        $email = $this->ask('What is the user\'s email?');
        
        if (!$this->confirm('Do you want to create this user?')) {
            $this->info('User creation cancelled.');
            return Command::SUCCESS;
        }
        
        try {
            $user = User::create([
                'name' => $name,
                'email' => $email,
            ]);
            
            $this->info("User created successfully! ID: {$user->id}");
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Failed to create user: {$e->getMessage()}");
            return Command::FAILURE;
        }
    }
}

/**
 * Example: Code Generation Commands
 * 
 * Laravel's make:* commands generate boilerplate code automatically.
 * Django doesn't have equivalent generators - you create files manually.
 * 
 * Common make:* commands:
 * 
 * php artisan make:model Post -m -f
 *   - Creates Post model
 *   - -m flag: creates migration
 *   - -f flag: creates factory
 * 
 * php artisan make:controller PostController --resource
 *   - Creates controller with CRUD methods:
 *     index(), create(), store(), show(), edit(), update(), destroy()
 * 
 * php artisan make:request StorePostRequest
 *   - Creates form request class for validation
 * 
 * php artisan make:middleware CheckAge
 *   - Creates middleware class
 * 
 * php artisan make:resource PostResource
 *   - Creates API resource for transforming model data
 * 
 * php artisan make:model Post -a
 *   - Creates model with migration, factory, seeder, and controller
 */

/**
 * Example: Generated Model (from make:model Post -m -f)
 */
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'content',
        'user_id',
    ];
}

/**
 * Example: Generated Resource Controller (from make:controller PostController --resource)
 */
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function index()
    {
        // Display a listing of the resource
    }

    public function create()
    {
        // Show the form for creating a new resource
    }

    public function store(Request $request)
    {
        // Store a newly created resource in storage
    }

    public function show(Post $post)
    {
        // Display the specified resource
    }

    public function edit(Post $post)
    {
        // Show the form for editing the specified resource
    }

    public function update(Request $request, Post $post)
    {
        // Update the specified resource in storage
    }

    public function destroy(Post $post)
    {
        // Remove the specified resource from storage
    }
}

/**
 * Example: Generated Form Request (from make:request StorePostRequest)
 */
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ];
    }
}

/**
 * Example: Generated Middleware (from make:middleware CheckAge)
 */
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckAge
{
    public function handle(Request $request, Closure $next): mixed
    {
        if ($request->age < 18) {
            return redirect('home');
        }

        return $next($request);
    }
}

