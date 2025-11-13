---
title: "02: Setting Up Laravel 12 Project & Dev Environment"
description: "Install Laravel 12, configure Laravel Sail with Docker, and set up your complete development environment with MySQL, Redis, and Mailhog"
series: "build-crm-laravel-12"
chapter: 2
order: 2
difficulty: "Intermediate"
prerequisites:
  - "/series/build-crm-laravel-12/chapters/01-introduction-series-overview"
  - "PHP 8.4+ installed"
  - "Composer installed"
  - "Docker Desktop installed and running"
  - "Node.js 18+ and npm installed"
---

![Setting Up Laravel 12 Project & Dev Environment](/images/build-crm-laravel-12/chapter-02-setting-up-laravel-12-project-hero-full.webp)

# Chapter 02: Setting Up Laravel 12 Project & Dev Environment

## Overview

With a clear understanding of what you'll build, it's time to set up your Laravel 12 development environment. This chapter walks you through installing Laravel, configuring Laravel Sail (Docker-based development environment), and verifying that all services - PHP, MySQL, Redis, and Mailhog - are running correctly.

Laravel Sail provides a consistent, pre-configured Docker environment that eliminates the "it works on my machine" problem. Whether you're on macOS, Linux, or Windows (with WSL2), Sail gives you identical development containers with all the tools you need. No manual PHP installation, no database configuration headaches - just a single command to get started.

By the end of this chapter, you'll have a fully functioning Laravel 12 project running in Docker containers. You'll understand how to use Sail's CLI commands, access your application in the browser, and connect to the database. This foundation will support all future chapters as you build the CRM application.

This chapter is entirely hands-on. You'll type commands, see output, and verify that everything works before moving forward.

## Prerequisites

Before starting this chapter, you should have:

- Completed [Chapter 01](/series/build-crm-laravel-12/chapters/01-introduction-series-overview) or equivalent understanding
- **PHP 8.4+** installed locally (for initial Laravel installation only)
- **Composer** installed and available in your PATH
- **Docker Desktop** installed and running (verify with `docker --version`)
- **Node.js 18+** and **npm** installed (verify with `node --version` and `npm --version`)
- **Terminal access** with basic command-line familiarity
- **~2-3 GB of free disk space** for Docker images and containers

**Estimated Time**: ~30 minutes (including Docker image downloads)

### Platform-Specific Notes

#### **macOS & Linux Users**

- All commands below work exactly as shown
- Docker Desktop runs natively
- File paths use forward slashes: `/Users/yourname/projects/crm-app`

#### **Windows Users: WSL2 Setup Required**

This chapter works on Windows **only with WSL2** (Windows Subsystem for Linux 2). WSL2 provides a lightweight Linux environment where Docker runs properly.

**Before Starting:**

1. **Install WSL2**: Follow [Microsoft's WSL2 installation guide](https://docs.microsoft.com/en-us/windows/wsl/install)
   - Enables: `wsl --install` (Windows 11) or manual install on Windows 10
   - Requires: Windows 10 Build 19041+ or Windows 11
2. **Configure Docker Desktop for WSL2**:
   - Open Docker Desktop → Settings
   - Resources → WSL Integration
   - Enable WSL2-based engine
   - Enable integration with your Linux distribution (Ubuntu recommended)
3. **Use WSL2 Terminal**:
   - Open: Windows Terminal → Select Ubuntu profile (or your Linux distro)
   - **NOT**: PowerShell, CMD, or Git Bash
   - All commands in this chapter run in WSL2 terminal
4. **File System Notes**:
   - Store projects in WSL2 filesystem: `/home/username/projects/crm-app`
   - **Avoid**: Windows paths like `C:\Users\...` (much slower with Docker)
   - Access from Windows File Explorer: `\\wsl$\Ubuntu\home\username\projects`

**File Path Differences:**

| Context            | macOS/Linux                        | Windows (WSL2)                    |
| ------------------ | ---------------------------------- | --------------------------------- |
| Project location   | `/Users/yourname/projects/crm-app` | `/home/yourname/projects/crm-app` |
| MySQL from WSL2    | `localhost:3306`                   | `localhost:3306`                  |
| MySQL from Windows | N/A                                | Use WSL IP (advanced)             |
| Commands           | `php artisan serve`                | `php artisan serve` (identical)   |

**Verification**: After WSL2 setup, run this in your WSL2 terminal:

```bash
docker --version
# Expected: Docker version 20.x.x or higher
```

If you see "docker: command not found", Docker Desktop isn't connected to WSL2. Check Settings → Resources → WSL Integration.

## What You'll Build

By the end of this chapter, you will have:

- A new Laravel 12 project named `crm-app` created via Laravel's installation script
- **Laravel Sail** configured and running with Docker containers for:
  - PHP 8.4
  - MySQL 8.0
  - Redis (for caching and queues)
  - Mailhog (for email testing)
- Access to the Laravel welcome page at `http://localhost`
- Working database connection verified via Artisan tinker
- Understanding of Sail CLI commands for starting, stopping, and interacting with containers
- Foundation for all future CRM development

## Quick Start

Want to see where this chapter takes you? Here's the end state:

```bash
# Check that all services are running
./vendor/bin/sail ps

# Expected: 4 containers running (laravel.test, mysql, redis, mailhog)
# Access your app at http://localhost
# View Mailhog at http://localhost:8025
```

By the end of this chapter, you'll have all these services running and verified.

## Objectives

- Install Laravel 12 using the official installer or `laravel new` command
- Configure Laravel Sail with MySQL, Redis, and Mailhog services
- Start Sail containers and verify all services are running
- Access the Laravel welcome page in your browser
- Use Artisan commands via Sail to interact with the application
- Connect to the MySQL database and run a test query
- Configure environment variables in `.env` for database and services
- Understand Sail's directory structure and how Docker Compose is configured

## Step 1: Install Laravel via Installer (~5 min)

### Goal

Create a new Laravel 12 project using the official installer.

### Actions

1. **Install the Laravel installer** (if you don't have it already):

```bash
# Install Laravel installer globally via Composer
composer global require laravel/installer
```

2. **Create a new Laravel 12 project**:

```bash
# Navigate to your projects directory first
cd ~/Developer  # or C:\projects on Windows

# Create the new Laravel project
laravel new crm-app
```

This command:

- Creates a new directory named `crm-app`
- Downloads Laravel 12 and all dependencies via Composer
- Generates a `.env` file with default configuration
- Creates a secure `APP_KEY`

3. **Navigate into your project**:

```bash
# Move into the new project directory
cd crm-app

# Verify Laravel is installed
php artisan --version
```

### Expected Result

```
Laravel Framework 12.x.x
```

### Why It Works

The Laravel installer is a convenient way to scaffold new projects without needing to manually clone repositories or manage dependencies. It runs `composer create-project` under the hood, pulling the latest Laravel framework and generating essential configuration files like `.env` and the application key.

**Key files created**:

- `.env` - Configuration file with database credentials, app keys, and service settings (will be updated for Docker)
- `composer.json` & `composer.lock` - PHP dependency management
- `artisan` - Your command-line interface to the application
- `public/` - Webroot serving your application
- `app/` - Your application code

The `.env` file will be updated in the next steps to work with Docker containers.

### Troubleshooting

- **Error: "laravel command not found"** - The Laravel installer wasn't installed globally. Run `composer global require laravel/installer` and ensure Composer's global bin directory (`~/.composer/vendor/bin` on Mac/Linux, or `%APPDATA%\Composer\vendor\bin` on Windows) is in your PATH.
- **Error: "Composer command not found"** - Composer isn't installed or not in your PATH. Install Composer from [getcomposer.org](https://getcomposer.org) and follow their installation instructions.
- **Permission denied when running laravel command** - On Linux/Mac, run `chmod +x ~/.composer/vendor/bin/laravel` to make the installer executable.

## Step 2: Configure Laravel Sail (~10 min)

### Goal

Install and configure Laravel Sail with MySQL, Redis, and Mailhog services.

### Actions

1. **Add Laravel Sail to your project**:

```bash
# Install Sail as a development dependency
composer require laravel/sail --dev
```

2. **Run the Sail installation wizard**:

```bash
# Publish Sail's Docker configuration and customize services
php artisan sail:install
```

3. **Select services** when prompted:

When you run the command above, you'll see an interactive prompt:

```
Which services would you like to install?

  [ ] mysql
  [ ] pgsql
  [ ] mariadb
  [ ] redis
  [ ] memcached
  [ ] meilisearch
  [ ] minio
  [ ] mailpit
  [ ] mailhog
  [ ] soketi
  [ ] selenium
```

Use arrow keys to navigate and spacebar to select. Choose these services:

- ✓ `mysql` (our relational database)
- ✓ `redis` (for caching and job queues)
- ✓ `mailhog` (for testing email functionality)

Leave all others unchecked. Press Enter to confirm.

### Expected Result

```
Application ready! Build something amazing.
```

The command creates a `docker-compose.yml` file in your project root with configurations for all selected services. Your `.env` file is also automatically updated with the correct connection settings for Docker's internal network.

### Why It Works

Laravel Sail simplifies Docker setup by providing a pre-configured `docker-compose.yml` file and shell script (`vendor/bin/sail`) that handles running commands inside containers. Instead of manually orchestrating Docker containers, Sail abstracts away complexity and provides an interface that feels like running commands locally. The services communicate via Docker's internal network, with hostnames matching service names (e.g., `mysql`, `redis`).

::: info
**What is Docker Compose?** Docker Compose is a tool for defining and running multi-container Docker applications. Instead of running each container individually, you define all services in a `docker-compose.yml` file and orchestrate them together. Sail automatically generates this file with PHP, MySQL, Redis, and Mailhog pre-configured.
:::

::: info
**Service Names as Hostnames**: Inside Docker containers, services don't communicate via `localhost` or `127.0.0.1`. Instead, Docker's DNS server resolves service names (mysql, redis, mailhog) to container IP addresses. This is why your `.env` file uses `DB_HOST=mysql` instead of `DB_HOST=127.0.0.1`.
:::

### Troubleshooting

- **Error: "docker: command not found"** - Docker Desktop isn't installed. Download it from [docker.com](https://www.docker.com/products/docker-desktop) and ensure it's running before proceeding.
- **Error: "Could not open requirements.txt"** - This occurs if Sail can't find your project files. Ensure you're in the `crm-app` directory when running the install command.
- **Interactive prompt doesn't appear** - This sometimes happens in certain terminal environments. You can manually create `docker-compose.yml` by copying from [Laravel's documentation](https://laravel.com/docs/12.x/sail#installation).

## Step 3: Start Sail and Verify Services (~5 min)

### Goal

Build and launch all Docker containers, then verify they're running correctly.

### Actions

1. **Start Sail containers in detached mode**:

```bash
# Build Docker images (if needed) and start containers in background
./vendor/bin/sail up -d
```

This command:

- Downloads Docker base images (only on first run)
- Builds images specific to your project
- Starts all containers in the background (`-d` flag)

::: info
**First-run timing**: The first time you run this command may take 5-15 minutes as Docker downloads images for PHP 8.4, MySQL 8.0, Redis, and Mailhog. This is a one-time process. Subsequent starts will be nearly instant.
:::

2. **Verify all services are running**:

```bash
# List all running containers
./vendor/bin/sail ps
```

3. **Wait for database to be ready**:

```bash
# Wait a few seconds for MySQL to fully initialize
# You can then run this to verify the connection
./vendor/bin/sail artisan tinker
```

### Expected Result

The `sail ps` command should show output like:

```
NAME                     COMMAND                 SERVICE      STATUS        PORTS
crm-app-laravel.test-1   "start-container"       laravel.test  Up 2 minutes  0.0.0.0:80->80/tcp
crm-app-mysql-1          "docker-entrypoint..." mysql         Up 2 minutes  3306/tcp, 33060/tcp
crm-app-redis-1          "docker-entrypoint..." redis         Up 2 minutes  6379/tcp
crm-app-mailhog-1        "MailHog"               mailhog       Up 2 minutes  0.0.0.0:1025->1025/tcp, 0.0.0.0:8025->8025/tcp
```

All services should show **"Up"** status. When you run `tinker`, you'll see:

```
Psy Shell v0.11.x — PHP 8.4.x
```

### Why It Works

The `up -d` command uses Docker Compose to orchestrate starting containers based on your `docker-compose.yml` configuration. The `-d` flag detaches the process from your terminal, allowing containers to run in the background. Each service (Laravel/PHP, MySQL, Redis, Mailhog) gets its own container with isolated resources and networking. Docker's internal DNS allows containers to communicate using service names as hostnames.

::: info
**Container Isolation**: Each container is an isolated environment with its own filesystem, processes, and network stack. Your PHP container can't directly access files on your machine - it only sees files mounted in `/var/www/html`. This isolation ensures consistency and prevents conflicts between projects.
:::

### Troubleshooting

- **Error: "docker daemon is not running"** - Start Docker Desktop (macOS/Windows) or ensure the Docker daemon is running on Linux.
- **Error: "Permission denied while trying to connect to Docker daemon"** - On Linux, add your user to the `docker` group: `sudo usermod -aG docker $USER` and log out/in.
- **Command hangs after "Pulling from library/..."** - This is normal on first run. Docker is downloading large images. Be patient; it can take several minutes.
- **MySQL container exits immediately** - This sometimes happens if it can't write to disk. Ensure you have enough free disk space (2-3 GB minimum).

## Step 4: Access the Application (~2 min)

### Goal

Verify that your Laravel application is accessible in the browser and all services are responding.

### Actions

1. **Open your browser and navigate to your application**:

Visit **`http://localhost`** in your web browser.

2. **Access other services**:

Open these additional URLs in separate tabs to verify all services:

- **Mailhog (Email Testing UI)**: `http://localhost:8025`
- **Database**: `localhost` port `3306` (MySQL client connection)
- **Redis**: `localhost` port `6379` (Redis client connection)

### Expected Result

- **http://localhost** shows the Laravel 12 welcome page with the Laravel logo and "Welcome to Laravel" heading.
- **http://localhost:8025** shows the Mailhog web interface (an empty inbox at this point).
- Database and Redis don't have web interfaces, but you can verify them by attempting to connect with a client tool if desired.

### Why It Works

Sail's `docker-compose.yml` includes port mappings that forward container ports to your local machine:

- Container port 80 (Laravel web server) → Local port 80
- Container port 1025 (Mailhog SMTP) → Local port 1025
- Container port 8025 (Mailhog web UI) → Local port 8025

The hostnames `mysql`, `redis`, and `mailhog` resolve to `127.0.0.1` when accessed from your local machine (since Sail includes a DNS entry or uses Docker's internal networking).

### Troubleshooting

- **Error: "Connection refused" at http://localhost** - The Laravel container might not be fully started yet. Wait 10-15 seconds and refresh the page. If it persists, run `./vendor/bin/sail ps` to check status.
- **Error: "This site can't be reached"** - Ensure port 80 isn't in use by another application. Run `lsof -i :80` (macOS/Linux) to see what's using it.
- **Mailhog not responding** - This is less critical for initial setup. Verify with `./vendor/bin/sail ps` that the `mailhog` container is running.

## Step 5: Configure Environment Variables (~3 min)

### Goal

Verify that your `.env` file is configured to communicate with Docker containers using service names instead of localhost.

### Actions

1. **Open your `.env` file** in your code editor (located in the `crm-app` root directory):

```bash
# Using VS Code (or your preferred editor)
code .env
```

2. **Verify database configuration**:

Locate these lines and ensure they match. The key difference from standard configuration is that `DB_HOST=mysql` (the service name), not `127.0.0.1`:

```ini
# Database Connection
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=crm_app
DB_USERNAME=sail
DB_PASSWORD=password
```

3. **Verify caching, queue, and session configuration**:

Look for these lines and confirm they point to the `redis` service:

```ini
# Caching & Queues
BROADCAST_DRIVER=log
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379
```

4. **Verify mail configuration**:

Confirm these lines point to the `mailhog` service:

```ini
# Mail (for Mailhog)
MAIL_MAILER=smtp
MAIL_HOST=mailhog
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"
```

### Expected Result

All `_HOST` and service-related variables are correctly pointing to service names (`mysql`, `redis`, `mailhog`) rather than `127.0.0.1` or `localhost`. The `sail:install` command should have already set these correctly.

### Why It Works

When running inside Docker containers, each service is accessible via its service name on Docker's internal network. `localhost` or `127.0.0.1` would only refer to the PHP container itself, not the other services.

**Network isolation**: Your PHP container is isolated from your machine and other containers. It can't reach services on your machine via `127.0.0.1`. Instead:

- From PHP container → Use service name: `mysql` (not `localhost:3306`)
- From your machine → Use port mapping: `localhost:3306` (which Docker routes to the container)

Laravel needs these environment variables to know how to connect to MySQL, Redis, and Mailhog from inside the PHP container. The configuration was automatically updated when you ran `sail:install`.

**Pro Tip**: This is why you'll always see `DB_HOST=mysql` in Laravel Sail projects - it's the only way for code inside the container to reach the MySQL service.

### Troubleshooting

- **Variables still point to 127.0.0.1** - Update them to use Docker service names. After editing, save the file and restart containers.

```ini
# Wrong (won't work in Docker containers)
DB_HOST=127.0.0.1
REDIS_HOST=localhost
MAIL_HOST=localhost

# Correct (Docker service names)
DB_HOST=mysql
REDIS_HOST=redis
MAIL_HOST=mailhog
```

Then restart: `./vendor/bin/sail restart`

- **Changes aren't taking effect** - Docker caches the `.env` file. Restart containers: `./vendor/bin/sail restart`
- **Database connection failures in next step** - Ensure `DB_HOST=mysql`, `DB_PORT=3306`, `DB_USERNAME=sail`, `DB_PASSWORD=password` are all correct.

## Step 6: Test Database Connection (~5 min)

### Goal

Verify that Laravel can successfully communicate with the MySQL database container.

### Actions

1. **Run Laravel's database migrations**:

Migrations are pre-built SQL scripts that create your database tables. Running them proves your database connection works:

```bash
# Execute migrations inside the PHP container via Sail
./vendor/bin/sail artisan migrate
```

2. **Observe the output**:

The command will create Laravel's default tables (users, password resets, jobs, etc.).

### Expected Result

```
INFO  Preparing database.

Creating migration table ....................................... 10ms DONE

INFO  Running migrations.

2014_10_12_000000_create_users_table ............................ 25ms DONE

2014_10_12_100000_create_password_reset_tokens_table ............ 18ms DONE

2019_08_19_000000_create_failed_jobs_table ...................... 15ms DONE

2019_12_14_000001_create_personal_access_tokens_table ........... 22ms DONE
```

All migrations complete with no errors.

### Why It Works

The `migrate` command:

1. **Connects to MySQL** using the `DB_*` variables from your `.env` file (reads `DB_HOST=mysql`, `DB_USER=sail`, etc.)
2. **Creates migrations table** to track which migrations have run (prevents duplicate execution)
3. **Executes migrations sequentially** - runs each migration file in timestamp order
4. **Records success** - logs each successful migration so it won't run again

**What migrations do**: Migrations are version-controlled SQL scripts that define your database structure. Instead of manually creating tables in MySQL, migrations provide a repeatable, version-controlled way to define your schema. This is essential for team projects and deployments.

If the connection failed, you'd see an error like "SQLSTATE[HY000]: General error: Unable to connect to MySQL server." The successful output confirms your `crm_app` database exists and Laravel can read/write to it through the Docker network.

**Database initialized**: After this step, your database contains:

- `migrations` table - tracks which migrations have run
- `users` table - for user authentication
- `password_reset_tokens` table - for password reset functionality
- `failed_jobs` table - for tracking failed queued jobs
- `personal_access_tokens` table - for API authentication

You now have a complete, production-ready database structure!

### Troubleshooting

- **Error: "SQLSTATE[HY000]"** - The MySQL container isn't responding. Run `./vendor/bin/sail ps` and check if the `mysql` container is running. If not, wait 10-15 seconds for it to fully initialize, then try again.
- **Error: "Access denied for user 'sail'"** - Your `.env` file has incorrect credentials. Verify `DB_USERNAME=sail` and `DB_PASSWORD=password` in Step 5.
- **Error: "Unknown database 'crm_app'"** - The database doesn't exist yet, but that's OK. Laravel will create it on first migration. If this persists, restart containers: `./vendor/bin/sail restart`
- **Command hangs indefinitely** - Press `Ctrl+C` to cancel. This usually means the container is unresponsive. Try `./vendor/bin/sail restart` and try again.

## Before You Continue: Verification Checklist

Before moving to the next chapter, verify that your environment is properly set up:

```bash
# 1. Check all containers are running
./vendor/bin/sail ps

# Should show: laravel.test, mysql, redis, mailhog all "Up"

# 2. Verify Laravel is accessible
curl http://localhost

# Should return: HTML content starting with <!DOCTYPE html>

# 3. Test database connection
./vendor/bin/sail artisan tinker

# Inside tinker, run:
>>> DB::connection()->getPdo()
# Should return: PDOConnection object

>>> exit
```

✅ **All three tests pass?** You're ready for Chapter 03!

::: warning
**If any test fails**:

1. Check container status: `./vendor/bin/sail ps`
2. View logs: `./vendor/bin/sail logs laravel.test`
3. Restart containers: `./vendor/bin/sail restart`
4. Wait 10-15 seconds and try again
   :::

## Troubleshooting Common Issues

If you encounter problems during setup, this section covers the most common errors and their solutions.

### Issue 1: "docker: command not found"

**Symptom**: `docker: command not found` when running `docker --version`

**Causes**:

- Docker Desktop not installed
- Docker not in system PATH
- (Windows) WSL2 not properly configured with Docker Desktop

**Solutions**:

1. **macOS/Linux**: Install Docker Desktop from [docker.com/products/docker-desktop](https://www.docker.com/products/docker-desktop)
2. **Windows**:
   - Install Docker Desktop
   - Go to Settings → Resources → WSL Integration
   - Toggle "Enable WSL 2 based engine"
   - Toggle integration with your Linux distribution
   - Restart Docker Desktop
3. **Verify**: Run `docker --version` again
4. **If still failing**: Restart your terminal/WSL2 session

### Issue 2: "Permission denied" errors

**Symptom**: `permission denied while trying to connect to Docker daemon`

**Cause**: Your user doesn't have permissions to access Docker socket

**Solution** (Linux only; macOS/Windows handles this automatically):

```bash
# Add your user to docker group
sudo usermod -aG docker $USER

# Apply new group membership (choose one):
# Option 1: Log out and back in (easiest)
# Option 2: Run this command
newgrp docker

# Verify
docker ps  # Should work without sudo
```

### Issue 3: "Port already in use"

**Symptom**: `Error response from daemon: driver failed programming external connectivity on endpoint: Bind for 0.0.0.0:80 failed: port is already allocated`

**Causes**:

- Another application using port 80 (Apache, Nginx, IIS)
- Another Laravel Sail instance running
- Previous containers didn't stop cleanly

**Solutions**:

1. **Check what's using port 80**:

```bash
# macOS/Linux
lsof -i :80

# Windows (PowerShell as Admin)
netstat -ano | findstr :80
```

2. **Stop the application or change Sail port**:

   - Option A: Stop the conflicting app
   - Option B: Edit `docker-compose.yml` and change port mapping:

   ```yaml
   services:
     laravel.test:
       ports:
         - "8080:80" # Use 8080 instead of 80
   ```

   Then access at `http://localhost:8080`

3. **Stop existing Sail containers**:

```bash
./vendor/bin/sail down
```

### Issue 4: "MySQL container won't start"

**Symptom**: MySQL container shows "Exited" status; `./vendor/bin/sail ps` shows it's not running

**Causes**:

- Insufficient disk space
- MySQL port 3306 already in use
- Volume mount permission issues

**Solutions**:

1. **Check disk space**:

```bash
# macOS/Linux
df -h  # Look for ~2-3GB free

# Windows (PowerShell)
Get-Volume
```

2. **Check if 3306 is in use**:

```bash
# macOS/Linux
lsof -i :3306

# Windows (PowerShell as Admin)
netstat -ano | findstr :3306
```

3. **View MySQL logs**:

```bash
./vendor/bin/sail logs mysql
# Look for errors; if you see permission issues:
```

4. **Restart with fresh MySQL container**:

```bash
./vendor/bin/sail down
./vendor/bin/sail up -d
```

### Issue 5: "Composer dependency conflicts"

**Symptom**: Error about incompatible packages during `laravel new crm-app`

**Cause**: Your global Composer cache has stale data

**Solution**:

```bash
# Clear Composer cache
composer clear-cache

# Try Laravel installer again
laravel new crm-app
```

### Issue 6: "Node.js version mismatch"

**Symptom**: `Node v14.0.0 requires a minimum of v18.0.0`

**Cause**: Node.js too old

**Solution**:

1. Check your version: `node --version`
2. If older than 18.0.0, upgrade from [nodejs.org](https://nodejs.org)
3. Verify: `node --version` (should show v18.0.0 or higher)
4. Retry: `npm install` inside your project

### Issue 7: "Sail command not found" after `composer require laravel/sail`

**Symptom**: `sail: command not found` or `./vendor/bin/sail` doesn't exist

**Causes**:

- Composer dependencies didn't install properly
- You're not in the project directory

**Solutions**:

1. **Verify you're in the correct directory**:

```bash
pwd  # Should end with /crm-app
ls -la vendor/bin/sail  # Should exist
```

2. **Reinstall dependencies**:

```bash
composer install
php artisan sail:install
```

3. **Verify Composer completed**:

```bash
composer diagnose  # Should show "OK"
```

### Issue 8: "Database connection refused"

**Symptom**: `SQLSTATE[HY000]: General error: Unable to connect to MySQL server at 'mysql:3306'`

**Causes**:

- MySQL container didn't start
- Incorrect `.env` database credentials
- Container networking issue

**Solutions**:

1. **Check MySQL is running**:

```bash
./vendor/bin/sail ps
# mysql row should show "Up X minutes"
```

2. **Verify `.env` database settings**:

```bash
grep DB_ .env
# Should show:
# DB_HOST=mysql
# DB_DATABASE=crm_app
# DB_USERNAME=sail
# DB_PASSWORD=password
```

3. **Wait for MySQL to initialize** (first startup takes 30 seconds):

```bash
./vendor/bin/sail logs mysql
# Wait for "Ready for connections"
```

4. **Test connection manually**:

```bash
./vendor/bin/sail mysql
# If it connects, press Ctrl+C to exit
```

### Issue 9: "File permissions denied" when running commands

**Symptom**: `[Errno 13] Permission denied: '/app/bootstrap/cache'`

**Cause**: Container user can't write to bootstrap/cache directory

**Solution** (usually happens on Linux):

```bash
# Fix permissions on your machine
sudo chmod -R 777 bootstrap/storage

# Or use Sail to fix (inside Docker):
./vendor/bin/sail artisan storage:link
```

### Issue 10: "Artisan command hangs"

**Symptom**: Commands like `sail artisan migrate` hang indefinitely

**Cause**: Container unresponsive or MySQL isn't ready

**Solution**:

1. Press `Ctrl+C` to cancel
2. Check container health: `./vendor/bin/sail ps`
3. If "Unhealthy", restart: `./vendor/bin/sail restart`
4. Wait 15 seconds and try again
5. If persistent, check logs: `./vendor/bin/sail logs`

### Getting Help: Enabling Debug Logging

If you're stuck, enable verbose Docker logging:

```bash
# Show full Docker Compose logs in real-time
./vendor/bin/sail logs -f

# In another terminal, run your command
./vendor/bin/sail artisan migrate

# Watch the logs in the first terminal to see what's happening
```

### Common Quick Fixes (Try These First!)

Before deep troubleshooting:

```bash
# 1. Restart containers
./vendor/bin/sail restart

# 2. Clear all caches
./vendor/bin/sail artisan config:clear
./vendor/bin/sail artisan cache:clear
./vendor/bin/sail artisan route:clear

# 3. Nuclear option (wipes everything, starts fresh)
./vendor/bin/sail down
./vendor/bin/sail up -d
./vendor/bin/sail artisan migrate
```

### Still Stuck?

If these troubleshooting steps don't help:

1. **Check Laravel Sail docs**: [Laravel Sail troubleshooting](https://laravel.com/docs/12.x/sail#troubleshooting)
2. **Check Docker Desktop issues**: [Docker Desktop documentation](https://docs.docker.com/)
3. **Google the error**: Copy the exact error message into Google - similar issues often have solutions
4. **Ask for help**: Share your `./vendor/bin/sail ps` output and the full error message

## Exercises

### Exercise 1: Explore Sail Commands (~10 min)

**Goal**: Learn essential Sail CLI commands and understand how to manage containers

Try these commands and observe the output:

```bash
# Check which containers are running
./vendor/bin/sail ps

# Check Laravel/Sail version
./vendor/bin/sail artisan --version

# List all available Sail commands
./vendor/bin/sail

# Enter the PHP container shell (type 'exit' to leave)
./vendor/bin/sail shell
```

**Expected Output** (for `sail ps`):

```
NAME                     COMMAND                 SERVICE      STATUS        PORTS
crm-app-laravel.test-1   "start-container"       laravel.test  Up 2 minutes  0.0.0.0:80->80/tcp
crm-app-mysql-1          "docker-entrypoint..." mysql         Up 2 minutes  3306/tcp, 33060/tcp
crm-app-redis-1          "docker-entrypoint..." redis         Up 2 minutes  6379/tcp
crm-app-mailhog-1        "MailHog"               mailhog       Up 2 minutes  0.0.0.0:1025->1025/tcp, 0.0.0.0:8025->8025/tcp
```

**Validation**: You should be able to:

- See all 4 containers with "Up" status
- Run `sail artisan --version` and see Laravel Framework version
- Enter the Sail shell with `sail shell` and type commands inside the container
- Stop containers with `./vendor/bin/sail down` (don't do this yet - we still need them!)

**Pro Tip**: Bookmark the output of `./vendor/bin/sail` - these are all the commands available to manage your development environment throughout the series.

### Exercise 2: Create a Bash Alias (~5 min)

**Goal**: Simplify Sail commands with a shell alias

**Note**: This exercise applies to macOS, Linux, and Windows with WSL2. Windows PowerShell users can skip this exercise - you'll use `./vendor/bin/sail` or the full command path.

1. Open your shell config file:

```bash
# For zsh (macOS default)
code ~/.zshrc

# For bash (Linux default)
code ~/.bashrc

# For bash on Windows WSL2
code ~/.bashrc
```

2. Add this alias at the bottom:

```bash
alias sail='sh $([ -f sail ] && echo sail || echo ./vendor/bin/sail)'
```

3. Reload your shell:

```bash
# For zsh
source ~/.zshrc

# For bash
source ~/.bashrc
```

**Validation**: Test the alias by running both commands:

```bash
# Old way (still works)
./vendor/bin/sail artisan --version

# New way (shorter)
sail artisan --version
```

**Expected Output** (identical for both):

```
Laravel Framework 12.x.x
```

Both commands should produce identical output. From now on, you can use `sail` instead of `./vendor/bin/sail` throughout the series.

### Exercise 3: Inspect Docker Containers (~5 min)

**Goal**: Understand what's running under the hood

1. List all running containers on your system:

```bash
# Shows all Docker containers
docker ps

# Shows container details including names, status, port mappings
docker ps --format "table {{.Names}}\t{{.Status}}\t{{.Ports}}"
```

2. Inspect a specific container:

```bash
# View detailed information about the MySQL container
docker inspect crm-app-mysql-1

# View just the network settings
docker inspect crm-app-mysql-1 | grep -A 10 '"Networks"'
```

**Expected Output** (from `docker ps`):

```
CONTAINER ID   IMAGE                    COMMAND                 CREATED        STATUS        PORTS                              NAMES
abc123def456   sail-8.4/app             "start-container"       2 minutes ago  Up 2 minutes  0.0.0.0:80->80/tcp                crm-app-laravel.test-1
def456ghi789   mysql:8.0                "docker-entrypoint..." 2 minutes ago  Up 2 minutes  3306/tcp, 33060/tcp                crm-app-mysql-1
ghi789jkl012   redis:7-alpine           "redis-server"         2 minutes ago  Up 2 minutes  6379/tcp                          crm-app-redis-1
jkl012mno345   mailhog/mailhog:latest   "MailHog"              2 minutes ago  Up 2 minutes  0.0.0.0:1025->1025/tcp, 8025/tcp crm-app-mailhog-1
```

**Validation**: You can:

- Identify all four services running
- See the image names (sail-8.4/app, mysql:8.0, redis:7-alpine, mailhog/mailhog)
- Understand the port mappings (e.g., 0.0.0.0:80->80/tcp means localhost:80 forwards to container port 80)
- Recognize that internal services use Docker hostnames (mysql, redis) instead of localhost

## Wrap-up

Congratulations! You've successfully set up a complete, production-grade development environment for your CRM application.

You've accomplished:

✓ **Installed Laravel 12** with a single command, creating a fully-configured project structure  
✓ **Configured Laravel Sail** with MySQL, Redis, and Mailhog services in Docker  
✓ **Launched Docker containers** and verified all services are running  
✓ **Verified the application** is accessible at `http://localhost` with the Laravel welcome page  
✓ **Configured environment variables** to enable Docker container communication  
✓ **Tested the database connection** by running migrations and creating tables  
✓ **Learned essential Sail commands** for development workflows

You now have:

- A stable Docker-based development environment that mirrors production
- MySQL database ready for CRM data
- Redis for fast caching and job queues
- Mailhog for testing email functionality without sending real emails
- A foundation that will support all future CRM features

**Remember**: From here forward, always use `./vendor/bin/sail` (or `sail` if you created an alias) to run commands. This ensures they execute inside your Docker containers where all services are available.

**What's Next:**

In **Chapter 03: Laravel 12 Fundamentals & Project Structure**, you'll explore what's actually inside your running Laravel application. You'll examine the directory structure you created in this chapter, understand how Laravel's MVC (Model-View-Controller) architecture works, and create your first routes and controllers. Most importantly, you'll see how requests flow through your running Docker environment to produce responses.

Keep your Sail containers running (`sail up -d` if you stopped them). In Chapter 03, every command and route test depends on Docker being ready.

<ChapterCheckbox 
  seriesId="build-crm-laravel-12"
  chapterId="02"
  label="Environment setup complete - Laravel 12 is running in Docker!"
/>

## Further Reading

- [Laravel Installation](https://laravel.com/docs/12.x/installation) - Official installation guide
- [Laravel Sail Documentation](https://laravel.com/docs/12.x/sail) - Complete Sail reference
- [Docker Documentation](https://docs.docker.com/) - Understanding Docker basics
- [Laravel Configuration](https://laravel.com/docs/12.x/configuration) - Environment variables and configuration
- [Chapter 02 Code Reference Materials](/code/build-crm-laravel-12/chapter-02-setup/) - Docker configuration examples, environment template, and verification scripts
- [Docker Networking Guide](https://docs.docker.com/engine/network/) - Deep dive into Docker networking and container communication
