---
title: "10: Debugging - Node Inspector vs Xdebug"
description: "Master PHP debugging with Xdebug using your Node.js debugging experience. Learn breakpoints, step debugging, profiling, and editor integration."
series: "php-typescript-developers"
chapter: 10
order: 10
difficulty: "Intermediate"
prerequisites:
  - "/series/php-typescript-developers/chapters/09-build-tools"
---

# Debugging: Node Inspector vs Xdebug

## Overview

If you're comfortable with Node.js debugging tools, you'll find PHP's Xdebug equally powerful. Both provide breakpoints, step-through debugging, variable inspection, and profiling. This chapter maps your debugging knowledge to the PHP ecosystem.

## Learning Objectives

By the end of this chapter, you'll be able to:

- ✅ Install and configure Xdebug
- ✅ Set breakpoints and step through code
- ✅ Inspect variables and call stacks
- ✅ Debug with VS Code and PHPStorm
- ✅ Use remote debugging
- ✅ Profile PHP applications
- ✅ Understand error handling and logging

## Debugging Tools Comparison

| Feature | Node.js | PHP |
|---------|---------|-----|
| **Built-in Debugger** | `node --inspect` | N/A (needs extension) |
| **Primary Tool** | Chrome DevTools | Xdebug |
| **IDE Integration** | VS Code Debugger | VS Code + Xdebug extension |
| **Breakpoints** | ✅ | ✅ |
| **Step Through** | ✅ | ✅ |
| **Variable Inspection** | ✅ | ✅ |
| **Remote Debugging** | ✅ | ✅ |
| **Profiling** | Chrome DevTools | Xdebug profiler |
| **Console Output** | `console.log()` | `var_dump()`, `error_log()` |

## Installation

### Node.js Debugger (Built-in)

```bash
# Built-in, just run with --inspect
node --inspect server.js
node --inspect-brk server.js  # Break on first line
```

### Xdebug Installation

**macOS (Homebrew):**
```bash
pecl install xdebug
```

**Linux (Ubuntu/Debian):**
```bash
sudo apt-get install php-xdebug
```

**Windows:**
Download from https://xdebug.org/download

**Verify Installation:**
```bash
php -v
# Should show: with Xdebug v3.x
```

### Xdebug Configuration

**php.ini or xdebug.ini:**
```ini
[xdebug]
zend_extension=xdebug.so

; Xdebug 3.x configuration
xdebug.mode=debug
xdebug.start_with_request=yes
xdebug.client_port=9003
xdebug.client_host=127.0.0.1

; Optional: Log for troubleshooting
xdebug.log=/tmp/xdebug.log
```

**Find your php.ini:**
```bash
php --ini
```

**Restart PHP-FPM/Apache after config changes:**
```bash
# macOS Homebrew
brew services restart php

# Linux
sudo systemctl restart php8.2-fpm
sudo systemctl restart apache2
```

## VS Code Setup

### Node.js Debugging

**.vscode/launch.json:**
```json
{
  "version": "0.2.0",
  "configurations": [
    {
      "type": "node",
      "request": "launch",
      "name": "Debug Node App",
      "program": "${workspaceFolder}/src/server.ts",
      "preLaunchTask": "tsc: build - tsconfig.json",
      "outFiles": ["${workspaceFolder}/dist/**/*.js"],
      "console": "integratedTerminal"
    }
  ]
}
```

**Usage:**
1. Set breakpoints (click left of line numbers)
2. Press F5 to start debugging
3. Code pauses at breakpoints

### PHP/Xdebug Debugging

**Install Extension:**
- PHP Debug by Xdebug

**.vscode/launch.json:**
```json
{
  "version": "0.2.0",
  "configurations": [
    {
      "name": "Listen for Xdebug",
      "type": "php",
      "request": "launch",
      "port": 9003,
      "pathMappings": {
        "/var/www/html": "${workspaceFolder}"
      }
    },
    {
      "name": "Launch Built-in Server",
      "type": "php",
      "request": "launch",
      "runtimeArgs": [
        "-S",
        "localhost:8000",
        "-t",
        "public"
      ],
      "port": 9003
    }
  ]
}
```

**Usage:**
1. Start debugging (F5) - VS Code listens for Xdebug
2. Set breakpoints
3. Load page in browser
4. Code pauses at breakpoints

## Basic Debugging

### Node.js Example

```typescript
// server.ts
import express from 'express';

const app = express();

app.get('/user/:id', (req, res) => {
  const userId = parseInt(req.params.id);
  debugger; // Breakpoint (or set in IDE)

  const user = getUser(userId);
  res.json(user);
});

function getUser(id: number) {
  return { id, name: 'Alice' };
}

app.listen(3000);
```

**Debug:**
1. Run: `node --inspect server.ts`
2. Open Chrome DevTools (chrome://inspect)
3. Set breakpoints
4. Make request to trigger breakpoint

### PHP Example

```php
<?php
// index.php
$userId = $_GET['id'] ?? 1;
$userId = (int) $userId; // Breakpoint here

$user = getUser($userId);
header('Content-Type: application/json');
echo json_encode($user);

function getUser(int $id): array {
    return ['id' => $id, 'name' => 'Alice']; // Breakpoint here
}
```

**Debug:**
1. Start debugging in VS Code (F5)
2. Set breakpoints (click left of line numbers)
3. Visit: http://localhost:8000?id=1
4. Code pauses at breakpoints

## Debugging Features

### Breakpoints

**Both Node.js and PHP:**
- **Line Breakpoints:** Click left of line number
- **Conditional Breakpoints:** Right-click breakpoint → Edit Breakpoint
- **Logpoints:** Log without stopping execution

**VS Code:**
```typescript
// Conditional breakpoint
userId === 1  // Only breaks if userId is 1

// Logpoint
User ID: {userId}  // Logs without stopping
```

### Step Through Code

| Action | Shortcut | Description |
|--------|----------|-------------|
| **Continue** | F5 | Continue to next breakpoint |
| **Step Over** | F10 | Execute current line, don't enter functions |
| **Step Into** | F11 | Enter function call |
| **Step Out** | Shift+F11 | Exit current function |
| **Restart** | Ctrl+Shift+F5 | Restart debugging session |

**Identical in both Node.js and PHP debugging!**

### Variable Inspection

**Watch Variables:**
- **Variables Panel:** See all local variables
- **Watch Panel:** Add specific variables to watch
- **Hover:** Hover over variables to see values
- **Debug Console:** Evaluate expressions

**Node.js Debug Console:**
```javascript
> userId
1
> typeof userId
'number'
> user
{ id: 1, name: 'Alice' }
```

**PHP Debug Console:**
```php
> $userId
1
> gettype($userId)
"integer"
> $user
array(2) { ["id"]=> int(1) ["name"]=> string(5) "Alice" }
```

### Call Stack

Both debuggers show the call stack:

```
getUser (index.php:12)
← processRequest (index.php:5)
← main (index.php:2)
```

Click any frame to inspect variables at that point.

## Remote Debugging

### Node.js Remote Debugging

```bash
# On remote server
node --inspect=0.0.0.0:9229 server.js
```

**VS Code launch.json:**
```json
{
  "type": "node",
  "request": "attach",
  "name": "Attach to Remote",
  "address": "remote-server.com",
  "port": 9229,
  "localRoot": "${workspaceFolder}",
  "remoteRoot": "/var/www/app"
}
```

### PHP/Xdebug Remote Debugging

**On remote server (php.ini):**
```ini
xdebug.mode=debug
xdebug.start_with_request=yes
xdebug.client_host=your-local-ip  # Your machine's IP
xdebug.client_port=9003
```

**VS Code launch.json:**
```json
{
  "name": "Listen for Xdebug (Remote)",
  "type": "php",
  "request": "launch",
  "port": 9003,
  "pathMappings": {
    "/var/www/html": "${workspaceFolder}"
  }
}
```

**SSH Tunnel (if behind firewall):**
```bash
ssh -R 9003:localhost:9003 user@remote-server
```

## PHPStorm Debugging

PHPStorm has excellent built-in Xdebug support (no extension needed):

### Configuration

1. **Settings → PHP → Debug**
   - Xdebug port: 9003
   - Check "Can accept external connections"

2. **Run → Edit Configurations**
   - Add "PHP Web Page"
   - Set start URL: http://localhost:8000

3. **Enable Debugging**
   - Click "Start Listening for PHP Debug Connections" (phone icon)
   - Set breakpoints
   - Load page in browser

### Zero-Configuration Debugging

PHPStorm can detect Xdebug automatically:

1. Install browser extension: Xdebug Helper
2. Click extension icon → Debug
3. PHPStorm automatically catches debug session
4. Set breakpoints and debug!

## Logging vs Debugging

### Node.js Logging

```typescript
console.log('User ID:', userId);
console.error('Error occurred:', error);
console.table(users);
console.time('query');
// ... operation
console.timeEnd('query');
```

### PHP Logging

```php
<?php
// var_dump (detailed output)
var_dump($userId);
// int(1)

// print_r (readable output)
print_r($user);
// Array ( [id] => 1 [name] => Alice )

// error_log (to file)
error_log("User ID: {$userId}");

// Symfony VarDumper (better formatting)
dump($user);  // Colorful output
dd($user);    // Dump and die
```

**Install Symfony VarDumper:**
```bash
composer require symfony/var-dumper
```

```php
<?php
require 'vendor/autoload.php';

$user = ['id' => 1, 'name' => 'Alice'];
dump($user);  // Pretty output, continues execution
dd($user);    // Pretty output, stops execution
```

## Error Handling

### Node.js Error Display

```javascript
// Unhandled errors show stack trace
throw new Error('Something went wrong');

process.on('uncaughtException', (err) => {
  console.error('Uncaught Exception:', err);
});
```

### PHP Error Display

```php
<?php
// Display errors (development only!)
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

// Throw error
throw new Exception('Something went wrong');

// Custom error handler
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    echo "Error: {$errstr} in {$errfile}:{$errline}\n";
});

// Custom exception handler
set_exception_handler(function($exception) {
    echo "Uncaught Exception: " . $exception->getMessage() . "\n";
});
```

**Production:** Never display errors! Log them instead:

```php
<?php
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', '/var/log/php_errors.log');
```

## Performance Profiling

### Node.js Profiling

**Chrome DevTools:**
```bash
node --inspect server.js
```

1. Open chrome://inspect
2. Click "Profiler" tab
3. Start profiling
4. Perform operations
5. Stop profiling → Analyze flame graph

### Xdebug Profiling

**Enable in php.ini:**
```ini
xdebug.mode=profile
xdebug.output_dir=/tmp/xdebug_profiles
xdebug.start_with_request=trigger
```

**Trigger profiling:**
```bash
# Via URL parameter
curl "http://localhost:8000?XDEBUG_PROFILE=1"

# Via cookie
curl -b "XDEBUG_PROFILE=1" http://localhost:8000
```

**Analyze with tools:**
- **QCacheGrind** (Windows/Linux)
- **KCacheGrind** (Linux/macOS via Homebrew)
- **Webgrind** (Web-based)

### Blackfire (Alternative Profiler)

Blackfire is a production-grade profiler (like commercial Chrome DevTools):

```bash
# Install Blackfire extension
brew install blackfire

# Profile via CLI
blackfire run php script.php

# Or trigger via browser extension
```

Shows:
- Execution time breakdown
- Memory usage
- SQL queries
- Function call graphs

## Common Debugging Scenarios

### Scenario 1: Debug API Request

**Node.js:**
```typescript
app.post('/api/users', async (req, res) => {
  debugger; // Set breakpoint
  const { name, email } = req.body;

  const user = await createUser(name, email);
  res.json(user);
});
```

**PHP:**
```php
<?php
// index.php
$data = json_decode(file_get_contents('php://input'), true);
// Set breakpoint here

$name = $data['name'] ?? '';
$email = $data['email'] ?? '';

$user = createUser($name, $email);
header('Content-Type: application/json');
echo json_encode($user);
```

### Scenario 2: Debug Database Query

**Node.js (TypeORM):**
```typescript
const user = await userRepository.findOne({
  where: { id: userId }
});
// Set breakpoint to inspect user
```

**PHP (Eloquent):**
```php
<?php
$user = User::find($userId);
// Set breakpoint to inspect $user
```

### Scenario 3: Debug Async Code

**Node.js:**
```typescript
async function fetchData() {
  const response = await fetch('https://api.example.com/data');
  debugger; // Inspect response
  return response.json();
}
```

**PHP (using Guzzle):**
```php
<?php
use GuzzleHttp\Client;

$client = new Client();
$response = $client->get('https://api.example.com/data');
// Set breakpoint here
$data = json_decode($response->getBody(), true);
```

## Xdebug Troubleshooting

### Xdebug Not Connecting

**Check:**
```bash
# 1. Verify Xdebug is loaded
php -v
# Should show: with Xdebug v3.x

# 2. Check configuration
php --ini

# 3. Check Xdebug settings
php -i | grep xdebug
```

**Common issues:**
- Wrong port (9003 in Xdebug 3, was 9000 in Xdebug 2)
- Firewall blocking connection
- Path mappings incorrect (Docker/Remote)

### Docker Debugging

**docker-compose.yml:**
```yaml
services:
  php:
    image: php:8.2-fpm
    environment:
      XDEBUG_MODE: debug
      XDEBUG_CONFIG: client_host=host.docker.internal
    volumes:
      - ./:/var/www/html
```

**VS Code launch.json:**
```json
{
  "pathMappings": {
    "/var/www/html": "${workspaceFolder}"
  }
}
```

## Best Practices

### 1. Development vs Production

**Development:**
```php
<?php
ini_set('display_errors', '1');
error_reporting(E_ALL);
xdebug.mode=debug
```

**Production:**
```php
<?php
ini_set('display_errors', '0');
ini_set('log_errors', '1');
xdebug.mode=off  # Disable Xdebug!
```

### 2. Use Logging for Production

```php
<?php
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

$log = new Logger('app');
$log->pushHandler(new StreamHandler('/var/log/app.log', Logger::DEBUG));

$log->info('User logged in', ['user_id' => $userId]);
$log->error('Database connection failed', ['error' => $error]);
```

### 3. Conditional Debugging

```php
<?php
if (isset($_GET['debug'])) {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
}
```

## Key Takeaways

1. **Xdebug = Node.js debugger** - Same features, different setup
2. **Breakpoints work identically** - Click line numbers, set conditions
3. **Step-through commands are the same** - F10, F11, etc.
4. **VS Code supports both** - Consistent debugging experience
5. **Remote debugging available** - SSH tunnels work well
6. **Profiling included** - Xdebug profiles performance
7. **Always disable in production** - Xdebug adds overhead

## Comparison Table

| Feature | Node.js | PHP/Xdebug |
|---------|---------|------------|
| **Setup** | Built-in | Requires extension |
| **Breakpoints** | ✅ | ✅ |
| **Step Through** | ✅ | ✅ |
| **Variable Inspection** | ✅ | ✅ |
| **Call Stack** | ✅ | ✅ |
| **Watch Expressions** | ✅ | ✅ |
| **Remote Debugging** | ✅ | ✅ |
| **Profiling** | Chrome DevTools | Xdebug profiler |
| **IDE Support** | VS Code, WebStorm | VS Code, PHPStorm |
| **Performance Impact** | Minimal | Moderate (disable in prod) |

## Quick Reference

| Task | Node.js | PHP/Xdebug |
|------|---------|------------|
| **Start debugger** | `node --inspect` | Enable in php.ini |
| **Set breakpoint** | Click line number | Click line number |
| **Step over** | F10 | F10 |
| **Step into** | F11 | F11 |
| **Continue** | F5 | F5 |
| **Inspect variable** | Hover or Console | Hover or Console |
| **Remote debug** | `--inspect=0.0.0.0` | Set `client_host` |
| **Log output** | `console.log()` | `error_log()`, `dump()` |

## Next Steps

Congratulations! You've completed **Phase 2: Ecosystem**. You now understand PHP's tooling landscape: package management, testing, code quality, build tools, and debugging.

**Next Chapter:** [11: Async in PHP: Promises vs Fibers](/series/php-typescript-developers/chapters/11-async-in-php)

**Phase 3 Preview:** Advanced topics (Async patterns, APIs, Laravel framework, databases, full-stack development)

## Resources

- [Xdebug Documentation](https://xdebug.org/docs/)
- [VS Code PHP Debug Extension](https://marketplace.visualstudio.com/items?itemName=xdebug.php-debug)
- [PHPStorm Debugging Guide](https://www.jetbrains.com/help/phpstorm/debugging.html)
- [Symfony VarDumper](https://symfony.com/doc/current/components/var_dumper.html)
- [Blackfire Profiler](https://www.blackfire.io/)
- [Monolog](https://github.com/Seldaek/monolog)

---

**Questions or feedback?** Open an issue on [GitHub](https://github.com/dalehurley/codewithphp/issues)
