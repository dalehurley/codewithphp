<?php

declare(strict_types=1);

/**
 * Exercise 4: Create an Admin Layout
 * 
 * Practice using multiple layouts by creating a different layout for admin pages.
 * 
 * Requirements:
 * - Create src/Views/admin-layout.php with different color scheme and navigation
 * - Create an admin controller that uses the admin layout
 * - Demonstrate how to switch between public and admin layouts
 */

// ============================================================================
// src/Views/layouts/admin-layout.php - Admin Layout
// ============================================================================

function renderAdminLayout(string $title, string $content, array $user = []): string
{
    $userName = $user['name'] ?? 'Admin User';
    $userRole = $user['role'] ?? 'Administrator';

    ob_start();
?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo htmlspecialchars($title); ?> - Admin Panel</title>
        <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }

            body {
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
                background: #f5f7fa;
                display: flex;
                min-height: 100vh;
            }

            /* Sidebar */
            .sidebar {
                width: 260px;
                background: #2c3e50;
                color: white;
                padding: 0;
                position: fixed;
                height: 100vh;
                overflow-y: auto;
            }

            .sidebar-header {
                background: #1a252f;
                padding: 25px 20px;
                border-bottom: 1px solid #34495e;
            }

            .sidebar-header h2 {
                font-size: 1.4em;
                margin-bottom: 5px;
            }

            .sidebar-header p {
                color: #95a5a6;
                font-size: 0.85em;
            }

            .sidebar-nav {
                padding: 20px 0;
            }

            .nav-section {
                margin-bottom: 25px;
            }

            .nav-section-title {
                color: #95a5a6;
                font-size: 0.75em;
                text-transform: uppercase;
                letter-spacing: 1px;
                padding: 0 20px 10px;
                font-weight: 600;
            }

            .nav-link {
                display: block;
                padding: 12px 20px;
                color: #ecf0f1;
                text-decoration: none;
                transition: all 0.3s;
                border-left: 3px solid transparent;
            }

            .nav-link:hover {
                background: #34495e;
                border-left-color: #3498db;
            }

            .nav-link.active {
                background: #34495e;
                border-left-color: #3498db;
                color: #3498db;
            }

            .nav-link i {
                margin-right: 10px;
                width: 20px;
                display: inline-block;
            }

            /* Main Content */
            .main-content {
                margin-left: 260px;
                flex: 1;
                display: flex;
                flex-direction: column;
            }

            /* Top Bar */
            .topbar {
                background: white;
                padding: 15px 30px;
                border-bottom: 1px solid #e1e8ed;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }

            .page-title {
                font-size: 1.5em;
                color: #2c3e50;
            }

            .user-info {
                display: flex;
                align-items: center;
                gap: 15px;
            }

            .user-avatar {
                width: 40px;
                height: 40px;
                border-radius: 50%;
                background: #3498db;
                display: flex;
                align-items: center;
                justify-content: center;
                color: white;
                font-weight: bold;
            }

            .user-details {
                text-align: right;
            }

            .user-name {
                font-weight: 600;
                color: #2c3e50;
                font-size: 0.9em;
            }

            .user-role {
                font-size: 0.75em;
                color: #95a5a6;
            }

            /* Content Area */
            .content {
                padding: 30px;
                flex: 1;
            }

            .stats-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                gap: 20px;
                margin-bottom: 30px;
            }

            .stat-card {
                background: white;
                padding: 25px;
                border-radius: 8px;
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
                border-left: 4px solid #3498db;
            }

            .stat-card.success {
                border-left-color: #27ae60;
            }

            .stat-card.warning {
                border-left-color: #f39c12;
            }

            .stat-card.danger {
                border-left-color: #e74c3c;
            }

            .stat-value {
                font-size: 2em;
                font-weight: bold;
                color: #2c3e50;
                margin-bottom: 5px;
            }

            .stat-label {
                color: #7f8c8d;
                font-size: 0.9em;
            }

            .card {
                background: white;
                border-radius: 8px;
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
                padding: 25px;
            }

            .card-header {
                font-size: 1.2em;
                font-weight: 600;
                color: #2c3e50;
                margin-bottom: 20px;
                padding-bottom: 15px;
                border-bottom: 2px solid #ecf0f1;
            }
        </style>
    </head>

    <body>
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2>⚡ Admin Panel</h2>
                <p>Content Management System</p>
            </div>

            <nav class="sidebar-nav">
                <div class="nav-section">
                    <div class="nav-section-title">Main</div>
                    <a href="/admin" class="nav-link active">
                        <i>📊</i> Dashboard
                    </a>
                    <a href="/admin/analytics" class="nav-link">
                        <i>📈</i> Analytics
                    </a>
                </div>

                <div class="nav-section">
                    <div class="nav-section-title">Content</div>
                    <a href="/admin/posts" class="nav-link">
                        <i>📝</i> Posts
                    </a>
                    <a href="/admin/pages" class="nav-link">
                        <i>📄</i> Pages
                    </a>
                    <a href="/admin/media" class="nav-link">
                        <i>🖼️</i> Media
                    </a>
                </div>

                <div class="nav-section">
                    <div class="nav-section-title">Users</div>
                    <a href="/admin/users" class="nav-link">
                        <i>👥</i> All Users
                    </a>
                    <a href="/admin/roles" class="nav-link">
                        <i>🔐</i> Roles
                    </a>
                </div>

                <div class="nav-section">
                    <div class="nav-section-title">Settings</div>
                    <a href="/admin/settings" class="nav-link">
                        <i>⚙️</i> Settings
                    </a>
                    <a href="/" class="nav-link">
                        <i>🌐</i> View Site
                    </a>
                    <a href="/logout" class="nav-link">
                        <i>🚪</i> Logout
                    </a>
                </div>
            </nav>
        </aside>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Top Bar -->
            <header class="topbar">
                <h1 class="page-title"><?php echo htmlspecialchars($title); ?></h1>
                <div class="user-info">
                    <div class="user-details">
                        <div class="user-name"><?php echo htmlspecialchars($userName); ?></div>
                        <div class="user-role"><?php echo htmlspecialchars($userRole); ?></div>
                    </div>
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($userName, 0, 1)); ?>
                    </div>
                </div>
            </header>

            <!-- Content Area -->
            <main class="content">
                <?php echo $content; ?>
            </main>
        </div>
    </body>

    </html>
<?php
    return ob_get_clean();
}

// ============================================================================
// src/Views/layouts/public-layout.php - Public Layout (for comparison)
// ============================================================================

function renderPublicLayout(string $title, string $content): string
{
    ob_start();
?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo htmlspecialchars($title); ?></title>
        <style>
            body {
                font-family: Arial, sans-serif;
                max-width: 1200px;
                margin: 0 auto;
                padding: 20px;
                background: #f5f5f5;
            }

            header {
                background: #3498db;
                color: white;
                padding: 20px;
                margin-bottom: 30px;
            }

            nav a {
                color: white;
                margin-right: 15px;
                text-decoration: none;
            }
        </style>
    </head>

    <body>
        <header>
            <h1><?php echo htmlspecialchars($title); ?></h1>
            <nav>
                <a href="/">Home</a>
                <a href="/about">About</a>
                <a href="/contact">Contact</a>
                <a href="/admin">Admin</a>
            </nav>
        </header>
        <main>
            <?php echo $content; ?>
        </main>
    </body>

    </html>
<?php
    return ob_get_clean();
}

// ============================================================================
// Controllers using different layouts
// ============================================================================

namespace App\Controllers;

class AdminController
{
    public function dashboard(): void
    {
        $content = '
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-value">1,234</div>
                    <div class="stat-label">Total Posts</div>
                </div>
                <div class="stat-card success">
                    <div class="stat-value">567</div>
                    <div class="stat-label">Published</div>
                </div>
                <div class="stat-card warning">
                    <div class="stat-value">89</div>
                    <div class="stat-label">Pending</div>
                </div>
                <div class="stat-card danger">
                    <div class="stat-value">12</div>
                    <div class="stat-label">Flagged</div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">Recent Activity</div>
                <p>Welcome to the admin dashboard! This layout uses a dark sidebar theme optimized for admin tasks.</p>
            </div>
        ';

        echo renderAdminLayout(
            'Dashboard',
            $content,
            ['name' => 'John Doe', 'role' => 'Super Administrator']
        );
    }
}

// ============================================================================
// Demo
// ============================================================================

echo "=== Multiple Layouts Demo ===" . PHP_EOL . PHP_EOL;

echo "✓ Created admin-layout.php with:" . PHP_EOL;
echo "  - Dark sidebar navigation (#2c3e50)" . PHP_EOL;
echo "  - Top bar with user info" . PHP_EOL;
echo "  - Fixed sidebar layout" . PHP_EOL;
echo "  - Organized navigation sections" . PHP_EOL;
echo "  - Statistics dashboard components" . PHP_EOL . PHP_EOL;

echo "✓ Created public-layout.php with:" . PHP_EOL;
echo "  - Light, simple design" . PHP_EOL;
echo "  - Horizontal navigation" . PHP_EOL;
echo "  - Centered content layout" . PHP_EOL . PHP_EOL;

echo "✓ Key differences:" . PHP_EOL;
echo "  Admin: Dark sidebar, advanced navigation, user info" . PHP_EOL;
echo "  Public: Simple header, basic navigation, clean design" . PHP_EOL . PHP_EOL;

echo "✓ Usage in controllers:" . PHP_EOL;
echo "  Admin pages: renderAdminLayout(\$title, \$content, \$user)" . PHP_EOL;
echo "  Public pages: renderPublicLayout(\$title, \$content)" . PHP_EOL . PHP_EOL;

echo "Benefits of multiple layouts:" . PHP_EOL;
echo "  1. Distinct visual separation of areas" . PHP_EOL;
echo "  2. Optimized UX for different user types" . PHP_EOL;
echo "  3. Better organization and navigation" . PHP_EOL;
echo "  4. Professional appearance" . PHP_EOL;
echo "  5. Easy to maintain and update" . PHP_EOL;
