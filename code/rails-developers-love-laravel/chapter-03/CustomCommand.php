<?php
/**
 * Chapter 03: Custom Artisan Command Example
 * 
 * This file shows how to create a custom Artisan command,
 * equivalent to a Rails Rake task.
 * 
 * Usage:
 *   php artisan users:send-digest
 *   php artisan users:send-digest --queue
 */

namespace App\Console\Commands;

use App\Models\User;
use App\Mail\WeeklyDigest;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendDigest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:send-digest
                          {--queue : Queue the emails instead of sending immediately}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send weekly digest emails to active users';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting to send digest emails...');

        $users = User::whereNotNull('email_verified_at')->get();

        $bar = $this->output->createProgressBar($users->count());
        $bar->start();

        foreach ($users as $user) {
            if ($this->option('queue')) {
                Mail::to($user)->queue(new WeeklyDigest($user));
            } else {
                Mail::to($user)->send(new WeeklyDigest($user));
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        $this->info('✓ Digest emails sent successfully!');

        return Command::SUCCESS;
    }
}

// ============ COMMAND WITH USER INTERACTION ============

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateUser extends Command
{
    protected $signature = 'user:create';
    protected $description = 'Create a new user interactively';

    public function handle(): int
    {
        $this->info('Creating a new user...');
        $this->newLine();

        // Ask for input
        $name = $this->ask('What is the user name?');

        // Ask with validation
        do {
            $email = $this->ask('What is the user email?');

            if (User::where('email', $email)->exists()) {
                $this->error("Email '$email' already exists!");
            }
        } while (User::where('email', $email)->exists());

        // Secret input (password input)
        $password = $this->secret('What is the password?');

        // Confirmation
        if (!$this->confirm('Create user ' . $name . '?')) {
            $this->info('Cancelled.');
            return Command::FAILURE;
        }

        // Create user
        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
        ]);

        $this->info('User created successfully!');
        $this->newLine();

        // Display as table
        $this->table(
            ['ID', 'Name', 'Email'],
            [[$user->id, $user->name, $user->email]]
        );

        return Command::SUCCESS;
    }
}

// ============ COMMAND WITH CHOICE SELECTION ============

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SetupEnvironment extends Command
{
    protected $signature = 'app:setup';
    protected $description = 'Setup application environment';

    public function handle(): int
    {
        $environment = $this->choice(
            'Select environment',
            ['development', 'staging', 'production'],
            'development'
        );

        $this->info("Setting up $environment environment...");

        switch ($environment) {
            case 'development':
                $this->setupDevelopment();
                break;
            case 'staging':
                $this->setupStaging();
                break;
            case 'production':
                $this->setupProduction();
                break;
        }

        $this->info('Environment setup complete!');

        return Command::SUCCESS;
    }

    private function setupDevelopment(): void
    {
        $this->line('- Installing development dependencies');
        $this->line('- Setting up local database');
        $this->line('- Seeding sample data');
    }

    private function setupStaging(): void
    {
        $this->line('- Configuring staging server');
        $this->line('- Setting up SSL certificate');
        $this->line('- Configuring environment variables');
    }

    private function setupProduction(): void
    {
        if (!$this->confirm('Are you sure you want to setup production?')) {
            $this->error('Production setup cancelled!');
            return;
        }

        $this->line('- Hardening security');
        $this->line('- Configuring backups');
        $this->line('- Setting up monitoring');
    }
}







