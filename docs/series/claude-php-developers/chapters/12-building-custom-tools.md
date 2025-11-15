---
title: "12: Building Custom Tools in PHP"
description: "Build production-ready tool libraries for Claude. Master database queries, API integrations, file operations, and tool orchestration patterns for real-world applications."
series: "claude-php-developers"
chapter: 12
order: 12
difficulty: "Expert"
prerequisites:
  - "Completed Chapter 11"
  - "Understanding of PDO and databases"
  - "Experience with REST APIs"
---

![12: Building Custom Tools in PHP](/images/claude-php/chapter-12-hero-full.webp)

<div class="breadcrumbs">
  <a href="/">Home</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series">Series</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series/claude-php-developers">Claude for PHP Developers</a>
  <span class="breadcrumbs-separator">›</span>
  <span>Chapter 12</span>
</div>

# Chapter 12: Building Custom Tools in PHP

## Overview

Now that you understand tool use fundamentals, it's time to build production-ready tools for real-world applications. In this chapter, you'll create a comprehensive tool library that integrates with databases, external APIs, file systems, and more.

You'll learn architectural patterns for organizing tools, security best practices, performance optimization, and how to build tools that are maintainable, testable, and reusable across projects.

**What You'll Build**: A complete tool system for an e-commerce platform including database tools, payment processing, inventory management, and customer analytics.

## Prerequisites

Before starting, ensure you have:

- ✓ **Completed Chapter 11** (Tool Use Fundamentals)
- ✓ **PDO/Database experience** for SQL tools
- ✓ **REST API knowledge** for external integrations
- ✓ **Understanding of PHP interfaces** and OOP

**Estimated Time**: 60-75 minutes

## Architecture: Tool Registry Pattern

First, let's build a solid architecture for managing tools:

```php
<?php
# filename: src/Tools/ToolRegistry.php
declare(strict_types=1);

namespace App\Tools;

interface Tool
{
    /**
     * Get the tool definition for Claude API
     */
    public function getDefinition(): array;

    /**
     * Execute the tool with given input
     */
    public function execute(array $input): array;

    /**
     * Get the tool name
     */
    public function getName(): string;
}

class ToolRegistry
{
    /** @var array<string, Tool> */
    private array $tools = [];

    public function register(Tool $tool): self
    {
        $this->tools[$tool->getName()] = $tool;
        return $this;
    }

    public function execute(string $toolName, array $input): array
    {
        if (!isset($this->tools[$toolName])) {
            throw new \InvalidArgumentException("Tool not found: {$toolName}");
        }

        try {
            return $this->tools[$toolName]->execute($input);
        } catch (\Exception $e) {
            return [
                'error' => true,
                'message' => $e->getMessage(),
                'tool' => $toolName
            ];
        }
    }

    public function getDefinitions(): array
    {
        return array_map(
            fn(Tool $tool) => $tool->getDefinition(),
            array_values($this->tools)
        );
    }

    public function has(string $toolName): bool
    {
        return isset($this->tools[$toolName]);
    }

    public function all(): array
    {
        return $this->tools;
    }
}
```

## Database Tools

### Customer Database Tool

```php
<?php
# filename: src/Tools/Database/CustomerDatabaseTool.php
declare(strict_types=1);

namespace App\Tools\Database;

use App\Tools\Tool;
use PDO;

class CustomerDatabaseTool implements Tool
{
    public function __construct(
        private PDO $db
    ) {}

    public function getName(): string
    {
        return 'query_customers';
    }

    public function getDefinition(): array
    {
        return [
            'name' => $this->getName(),
            'description' => 'Searches the customer database by email, name, or customer ID. Returns customer details including order history, tier status, and contact information.',
            'input_schema' => [
                'type' => 'object',
                'properties' => [
                    'search_type' => [
                        'type' => 'string',
                        'enum' => ['email', 'name', 'customer_id'],
                        'description' => 'Type of search to perform'
                    ],
                    'search_value' => [
                        'type' => 'string',
                        'description' => 'The value to search for'
                    ],
                    'include_orders' => [
                        'type' => 'boolean',
                        'description' => 'Whether to include order history',
                        'default' => false
                    ]
                ],
                'required' => ['search_type', 'search_value']
            ]
        ];
    }

    public function execute(array $input): array
    {
        $searchType = $input['search_type'];
        $searchValue = $input['search_value'];
        $includeOrders = $input['include_orders'] ?? false;

        // Build query based on search type
        $query = "SELECT * FROM customers WHERE ";
        $params = [];

        switch ($searchType) {
            case 'email':
                $query .= "email = :value";
                break;
            case 'name':
                $query .= "CONCAT(first_name, ' ', last_name) LIKE :value";
                $searchValue = "%{$searchValue}%";
                break;
            case 'customer_id':
                $query .= "id = :value";
                break;
            default:
                throw new \InvalidArgumentException("Invalid search type");
        }

        $params[':value'] = $searchValue;

        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($customers)) {
            return [
                'found' => false,
                'message' => 'No customers found matching criteria'
            ];
        }

        // Include order history if requested
        if ($includeOrders) {
            foreach ($customers as &$customer) {
                $customer['orders'] = $this->getCustomerOrders($customer['id']);
            }
        }

        return [
            'found' => true,
            'count' => count($customers),
            'customers' => $customers
        ];
    }

    private function getCustomerOrders(int $customerId): array
    {
        $stmt = $this->db->prepare(
            "SELECT id, total, status, created_at
             FROM orders
             WHERE customer_id = :customer_id
             ORDER BY created_at DESC
             LIMIT 10"
        );
        $stmt->execute([':customer_id' => $customerId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
```

### Product Inventory Tool

```php
<?php
# filename: src/Tools/Database/InventoryTool.php
declare(strict_types=1);

namespace App\Tools\Database;

use App\Tools\Tool;
use PDO;

class InventoryTool implements Tool
{
    public function __construct(
        private PDO $db
    ) {}

    public function getName(): string
    {
        return 'check_inventory';
    }

    public function getDefinition(): array
    {
        return [
            'name' => $this->getName(),
            'description' => 'Checks product inventory levels, stock availability, and warehouse locations. Use this to answer questions about product availability.',
            'input_schema' => [
                'type' => 'object',
                'properties' => [
                    'product_id' => [
                        'type' => 'string',
                        'description' => 'Product SKU or ID'
                    ],
                    'warehouse' => [
                        'type' => 'string',
                        'description' => 'Specific warehouse code (optional)',
                    ]
                ],
                'required' => ['product_id']
            ]
        ];
    }

    public function execute(array $input): array
    {
        $productId = $input['product_id'];
        $warehouse = $input['warehouse'] ?? null;

        $query = "
            SELECT
                p.id, p.sku, p.name, p.price,
                i.warehouse_code, i.quantity, i.reserved,
                (i.quantity - i.reserved) as available,
                i.restock_date
            FROM products p
            JOIN inventory i ON p.id = i.product_id
            WHERE p.sku = :product_id OR p.id = :product_id
        ";

        $params = [':product_id' => $productId];

        if ($warehouse) {
            $query .= " AND i.warehouse_code = :warehouse";
            $params[':warehouse'] = $warehouse;
        }

        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        $inventory = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($inventory)) {
            return [
                'found' => false,
                'message' => 'Product not found'
            ];
        }

        $totalAvailable = array_sum(array_column($inventory, 'available'));

        return [
            'found' => true,
            'product' => [
                'id' => $inventory[0]['id'],
                'sku' => $inventory[0]['sku'],
                'name' => $inventory[0]['name'],
                'price' => $inventory[0]['price']
            ],
            'total_available' => $totalAvailable,
            'in_stock' => $totalAvailable > 0,
            'warehouses' => array_map(function($item) {
                return [
                    'warehouse' => $item['warehouse_code'],
                    'quantity' => $item['quantity'],
                    'reserved' => $item['reserved'],
                    'available' => $item['available'],
                    'restock_date' => $item['restock_date']
                ];
            }, $inventory)
        ];
    }
}
```

## API Integration Tools

### Payment Processing Tool

```php
<?php
# filename: src/Tools/Payment/StripePaymentTool.php
declare(strict_types=1);

namespace App\Tools\Payment;

use App\Tools\Tool;

class StripePaymentTool implements Tool
{
    public function __construct(
        private string $stripeApiKey
    ) {}

    public function getName(): string
    {
        return 'process_payment';
    }

    public function getDefinition(): array
    {
        return [
            'name' => $this->getName(),
            'description' => 'Processes a payment through Stripe. ONLY use this after explicit customer confirmation. Returns payment status and transaction ID.',
            'input_schema' => [
                'type' => 'object',
                'properties' => [
                    'amount' => [
                        'type' => 'number',
                        'description' => 'Amount in USD (e.g., 99.99)',
                        'minimum' => 0.50
                    ],
                    'customer_id' => [
                        'type' => 'string',
                        'description' => 'Stripe customer ID'
                    ],
                    'description' => [
                        'type' => 'string',
                        'description' => 'Payment description'
                    ],
                    'payment_method' => [
                        'type' => 'string',
                        'description' => 'Payment method ID from Stripe'
                    ]
                ],
                'required' => ['amount', 'customer_id', 'description', 'payment_method']
            ]
        ];
    }

    public function execute(array $input): array
    {
        // In production, use Stripe SDK
        $amountCents = (int)($input['amount'] * 100);

        try {
            // Simulated Stripe API call
            // \Stripe\Stripe::setApiKey($this->stripeApiKey);
            // $charge = \Stripe\PaymentIntent::create([...]);

            $paymentIntent = $this->simulateStripePayment(
                $amountCents,
                $input['customer_id'],
                $input['description'],
                $input['payment_method']
            );

            return [
                'success' => true,
                'payment_intent_id' => $paymentIntent['id'],
                'amount' => $input['amount'],
                'status' => $paymentIntent['status'],
                'created_at' => date('Y-m-d H:i:s')
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'amount' => $input['amount']
            ];
        }
    }

    private function simulateStripePayment(
        int $amount,
        string $customerId,
        string $description,
        string $paymentMethod
    ): array {
        // Simulate successful payment
        return [
            'id' => 'pi_' . bin2hex(random_bytes(12)),
            'amount' => $amount,
            'currency' => 'usd',
            'customer' => $customerId,
            'description' => $description,
            'payment_method' => $paymentMethod,
            'status' => 'succeeded'
        ];
    }
}
```

### External API Tool

```php
<?php
# filename: src/Tools/External/ShippingRatesTool.php
declare(strict_types=1);

namespace App\Tools\External;

use App\Tools\Tool;

class ShippingRatesTool implements Tool
{
    public function __construct(
        private string $apiKey,
        private string $apiUrl = 'https://api.shippo.com/v1'
    ) {}

    public function getName(): string
    {
        return 'get_shipping_rates';
    }

    public function getDefinition(): array
    {
        return [
            'name' => $this->getName(),
            'description' => 'Gets shipping rates for a package from origin to destination. Returns available carriers, delivery times, and costs.',
            'input_schema' => [
                'type' => 'object',
                'properties' => [
                    'from_zip' => [
                        'type' => 'string',
                        'description' => 'Origin ZIP code',
                        'pattern' => '^\d{5}$'
                    ],
                    'to_zip' => [
                        'type' => 'string',
                        'description' => 'Destination ZIP code',
                        'pattern' => '^\d{5}$'
                    ],
                    'weight' => [
                        'type' => 'number',
                        'description' => 'Package weight in pounds',
                        'minimum' => 0.1
                    ],
                    'length' => ['type' => 'number', 'description' => 'Length in inches'],
                    'width' => ['type' => 'number', 'description' => 'Width in inches'],
                    'height' => ['type' => 'number', 'description' => 'Height in inches']
                ],
                'required' => ['from_zip', 'to_zip', 'weight', 'length', 'width', 'height']
            ]
        ];
    }

    public function execute(array $input): array
    {
        $ch = curl_init($this->apiUrl . '/shipments');

        $payload = [
            'address_from' => ['zip' => $input['from_zip']],
            'address_to' => ['zip' => $input['to_zip']],
            'parcels' => [[
                'length' => $input['length'],
                'width' => $input['width'],
                'height' => $input['height'],
                'distance_unit' => 'in',
                'weight' => $input['weight'],
                'mass_unit' => 'lb'
            ]]
        ];

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => [
                'Authorization: ShippoToken ' . $this->apiKey,
                'Content-Type: application/json'
            ]
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            return [
                'error' => true,
                'message' => 'Failed to get shipping rates'
            ];
        }

        $data = json_decode($response, true);

        // Format rates for Claude
        $rates = array_map(function($rate) {
            return [
                'carrier' => $rate['provider'],
                'service' => $rate['servicelevel']['name'],
                'cost' => $rate['amount'],
                'currency' => $rate['currency'],
                'delivery_days' => $rate['estimated_days']
            ];
        }, $data['rates'] ?? []);

        return [
            'success' => true,
            'rates' => $rates,
            'count' => count($rates)
        ];
    }
}
```

## File Operations Tools

### File Reader Tool

```php
<?php
# filename: src/Tools/File/FileReaderTool.php
declare(strict_types=1);

namespace App\Tools\File;

use App\Tools\Tool;

class FileReaderTool implements Tool
{
    public function __construct(
        private string $allowedPath,
        private int $maxFileSize = 1048576 // 1MB
    ) {}

    public function getName(): string
    {
        return 'read_file';
    }

    public function getDefinition(): array
    {
        return [
            'name' => $this->getName(),
            'description' => 'Reads the contents of a file. Supports text files, CSV, JSON, and log files. Returns file contents and metadata.',
            'input_schema' => [
                'type' => 'object',
                'properties' => [
                    'file_path' => [
                        'type' => 'string',
                        'description' => 'Path to the file to read'
                    ],
                    'format' => [
                        'type' => 'string',
                        'enum' => ['text', 'json', 'csv'],
                        'description' => 'Expected file format',
                        'default' => 'text'
                    ]
                ],
                'required' => ['file_path']
            ]
        ];
    }

    public function execute(array $input): array
    {
        $filePath = $input['file_path'];
        $format = $input['format'] ?? 'text';

        // Security: Ensure file is within allowed path
        $realPath = realpath($filePath);
        $allowedRealPath = realpath($this->allowedPath);

        if (!$realPath || !str_starts_with($realPath, $allowedRealPath)) {
            return [
                'error' => true,
                'message' => 'Access denied: File outside allowed directory'
            ];
        }

        if (!file_exists($realPath)) {
            return [
                'error' => true,
                'message' => 'File not found'
            ];
        }

        $fileSize = filesize($realPath);
        if ($fileSize > $this->maxFileSize) {
            return [
                'error' => true,
                'message' => "File too large (max {$this->maxFileSize} bytes)"
            ];
        }

        $contents = file_get_contents($realPath);

        $result = [
            'success' => true,
            'file_path' => $filePath,
            'size' => $fileSize,
            'modified' => date('Y-m-d H:i:s', filemtime($realPath))
        ];

        switch ($format) {
            case 'json':
                $decoded = json_decode($contents, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    return [
                        'error' => true,
                        'message' => 'Invalid JSON: ' . json_last_error_msg()
                    ];
                }
                $result['data'] = $decoded;
                break;

            case 'csv':
                $result['data'] = array_map('str_getcsv', explode("\n", $contents));
                break;

            default:
                $result['contents'] = $contents;
                $result['line_count'] = substr_count($contents, "\n") + 1;
        }

        return $result;
    }
}
```

### Log Analyzer Tool

```php
<?php
# filename: src/Tools/File/LogAnalyzerTool.php
declare(strict_types=1);

namespace App\Tools\File;

use App\Tools\Tool;

class LogAnalyzerTool implements Tool
{
    public function __construct(
        private string $logDirectory
    ) {}

    public function getName(): string
    {
        return 'analyze_logs';
    }

    public function getDefinition(): array
    {
        return [
            'name' => $this->getName(),
            'description' => 'Analyzes application log files for errors, warnings, and patterns. Returns summary statistics and recent critical entries.',
            'input_schema' => [
                'type' => 'object',
                'properties' => [
                    'log_file' => [
                        'type' => 'string',
                        'description' => 'Log file name (e.g., "app.log", "error.log")'
                    ],
                    'level' => [
                        'type' => 'string',
                        'enum' => ['ERROR', 'WARNING', 'INFO', 'DEBUG'],
                        'description' => 'Minimum log level to analyze'
                    ],
                    'last_n_lines' => [
                        'type' => 'integer',
                        'description' => 'Analyze only the last N lines',
                        'default' => 1000
                    ]
                ],
                'required' => ['log_file']
            ]
        ];
    }

    public function execute(array $input): array
    {
        $logFile = $this->logDirectory . '/' . basename($input['log_file']);
        $level = $input['level'] ?? 'ERROR';
        $lastNLines = $input['last_n_lines'] ?? 1000;

        if (!file_exists($logFile)) {
            return [
                'error' => true,
                'message' => 'Log file not found'
            ];
        }

        // Read last N lines
        $lines = $this->readLastLines($logFile, $lastNLines);

        // Parse log entries
        $entries = array_map(function($line) {
            if (preg_match('/\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] \w+\.(\w+): (.+)/', $line, $matches)) {
                return [
                    'timestamp' => $matches[1],
                    'level' => $matches[2],
                    'message' => $matches[3]
                ];
            }
            return null;
        }, $lines);

        $entries = array_filter($entries);

        // Filter by level
        $levelPriority = ['DEBUG' => 0, 'INFO' => 1, 'WARNING' => 2, 'ERROR' => 3];
        $minPriority = $levelPriority[$level] ?? 0;

        $filtered = array_filter($entries, function($entry) use ($levelPriority, $minPriority) {
            return ($levelPriority[$entry['level']] ?? 0) >= $minPriority;
        });

        // Calculate statistics
        $stats = [
            'total_entries' => count($entries),
            'filtered_entries' => count($filtered),
            'by_level' => []
        ];

        foreach ($entries as $entry) {
            $level = $entry['level'];
            $stats['by_level'][$level] = ($stats['by_level'][$level] ?? 0) + 1;
        }

        return [
            'success' => true,
            'log_file' => basename($logFile),
            'statistics' => $stats,
            'recent_entries' => array_slice($filtered, -10)
        ];
    }

    private function readLastLines(string $file, int $lines): array
    {
        $handle = fopen($file, 'r');
        $linecounter = $lines;
        $pos = -2;
        $beginning = false;
        $text = [];

        while ($linecounter > 0) {
            $t = " ";
            while ($t != "\n") {
                if (fseek($handle, $pos, SEEK_END) == -1) {
                    $beginning = true;
                    break;
                }
                $t = fgetc($handle);
                $pos--;
            }
            $linecounter--;
            if ($beginning) {
                rewind($handle);
            }
            $text[$lines - $linecounter - 1] = fgets($handle);
            if ($beginning) break;
        }

        fclose($handle);
        return array_reverse($text);
    }
}
```

## Complete Integration Example

Putting it all together:

```php
<?php
# filename: examples/01-complete-tool-system.php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Anthropic\Anthropic;
use App\Tools\ToolRegistry;
use App\Tools\Database\CustomerDatabaseTool;
use App\Tools\Database\InventoryTool;
use App\Tools\Payment\StripePaymentTool;
use App\Tools\External\ShippingRatesTool;
use App\Tools\File\FileReaderTool;
use App\Tools\File\LogAnalyzerTool;

// Initialize database
$db = new PDO('sqlite:' . __DIR__ . '/ecommerce.db');

// Create tool registry
$registry = new ToolRegistry();

// Register all tools
$registry
    ->register(new CustomerDatabaseTool($db))
    ->register(new InventoryTool($db))
    ->register(new StripePaymentTool(getenv('STRIPE_SECRET_KEY')))
    ->register(new ShippingRatesTool(getenv('SHIPPO_API_KEY')))
    ->register(new FileReaderTool(__DIR__ . '/data'))
    ->register(new LogAnalyzerTool(__DIR__ . '/logs'));

// Initialize Claude
$client = Anthropic::factory()
    ->withApiKey(getenv('ANTHROPIC_API_KEY'))
    ->make();

// Agent function
function runAgent(string $userMessage, ToolRegistry $registry): string
{
    global $client;

    $messages = [[
        'role' => 'user',
        'content' => $userMessage
    ]];

    $systemPrompt = <<<SYSTEM
You are an AI assistant for an e-commerce platform with access to various tools.

Available capabilities:
- Query customer database
- Check product inventory
- Process payments (requires confirmation)
- Get shipping rates
- Read files
- Analyze logs

Always be helpful, accurate, and secure. Never process payments without explicit confirmation.
SYSTEM;

    $response = $client->messages()->create([
        'model' => 'claude-sonnet-4-20250514',
        'max_tokens' => 4096,
        'system' => $systemPrompt,
        'tools' => $registry->getDefinitions(),
        'messages' => $messages
    ]);

    $iterations = 0;
    while ($response->stopReason === 'tool_use' && $iterations < 15) {
        $iterations++;

        $messages[] = [
            'role' => 'assistant',
            'content' => $response->content
        ];

        $toolResults = [];
        foreach ($response->content as $block) {
            if ($block->type === 'tool_use') {
                echo "[{$iterations}] Using tool: {$block->name}\n";

                $result = $registry->execute($block->name, (array)$block->input);

                $toolResults[] = [
                    'type' => 'tool_result',
                    'tool_use_id' => $block->id,
                    'content' => json_encode($result)
                ];
            }
        }

        $messages[] = [
            'role' => 'user',
            'content' => $toolResults
        ];

        $response = $client->messages()->create([
            'model' => 'claude-sonnet-4-20250514',
            'max_tokens' => 4096,
            'system' => $systemPrompt,
            'tools' => $registry->getDefinitions(),
            'messages' => $messages
        ]);
    }

    $finalText = '';
    foreach ($response->content as $block) {
        if ($block->type === 'text') {
            $finalText .= $block->text;
        }
    }

    return $finalText;
}

// Example usage
echo "=== Customer Support Query ===\n";
echo runAgent(
    "Look up customer john@example.com and check if we have product SKU-123 in stock. " .
    "If yes, get shipping rates from 94103 to 10001 for a 2lb package (12x8x6 inches).",
    $registry
);
```

## Testing Tools

```php
<?php
# filename: tests/Tools/InventoryToolTest.php
declare(strict_types=1);

namespace Tests\Tools;

use PHPUnit\Framework\TestCase;
use App\Tools\Database\InventoryTool;
use PDO;

class InventoryToolTest extends TestCase
{
    private PDO $db;
    private InventoryTool $tool;

    protected function setUp(): void
    {
        $this->db = new PDO('sqlite::memory:');
        $this->createTestDatabase();
        $this->tool = new InventoryTool($this->db);
    }

    public function testGetDefinitionReturnsValidSchema(): void
    {
        $definition = $this->tool->getDefinition();

        $this->assertArrayHasKey('name', $definition);
        $this->assertArrayHasKey('description', $definition);
        $this->assertArrayHasKey('input_schema', $definition);
        $this->assertEquals('check_inventory', $definition['name']);
    }

    public function testExecuteReturnsProductInventory(): void
    {
        $result = $this->tool->execute(['product_id' => 'SKU-123']);

        $this->assertTrue($result['found']);
        $this->assertArrayHasKey('total_available', $result);
        $this->assertArrayHasKey('warehouses', $result);
    }

    public function testExecuteWithInvalidProductReturnsNotFound(): void
    {
        $result = $this->tool->execute(['product_id' => 'INVALID']);

        $this->assertFalse($result['found']);
    }

    private function createTestDatabase(): void
    {
        $this->db->exec("
            CREATE TABLE products (
                id INTEGER PRIMARY KEY,
                sku TEXT,
                name TEXT,
                price REAL
            )
        ");

        $this->db->exec("
            CREATE TABLE inventory (
                id INTEGER PRIMARY KEY,
                product_id INTEGER,
                warehouse_code TEXT,
                quantity INTEGER,
                reserved INTEGER,
                restock_date TEXT
            )
        ");

        // Insert test data
        $this->db->exec("
            INSERT INTO products VALUES (1, 'SKU-123', 'Test Product', 99.99)
        ");

        $this->db->exec("
            INSERT INTO inventory VALUES (1, 1, 'WH-01', 100, 10, NULL)
        ");
    }
}
```

## Security Best Practices

```php
<?php
# filename: src/Tools/Security/SecureToolWrapper.php
declare(strict_types=1);

namespace App\Tools\Security;

use App\Tools\Tool;
use Psr\Log\LoggerInterface;

class SecureToolWrapper implements Tool
{
    public function __construct(
        private Tool $tool,
        private LoggerInterface $logger,
        private array $allowedRoles = []
    ) {}

    public function getName(): string
    {
        return $this->tool->getName();
    }

    public function getDefinition(): array
    {
        return $this->tool->getDefinition();
    }

    public function execute(array $input): array
    {
        // Log tool usage
        $this->logger->info("Tool executed: {$this->getName()}", [
            'input' => $input,
            'timestamp' => time()
        ]);

        // Validate input
        if (!$this->validateInput($input)) {
            return [
                'error' => true,
                'message' => 'Invalid input parameters'
            ];
        }

        // Execute with timeout
        $result = $this->executeWithTimeout(
            fn() => $this->tool->execute($input),
            30 // 30 second timeout
        );

        // Log result
        $this->logger->info("Tool completed: {$this->getName()}", [
            'success' => !isset($result['error'])
        ]);

        return $result;
    }

    private function validateInput(array $input): bool
    {
        $definition = $this->tool->getDefinition();
        $schema = $definition['input_schema'];

        // Check required fields
        foreach ($schema['required'] ?? [] as $field) {
            if (!isset($input[$field])) {
                return false;
            }
        }

        return true;
    }

    private function executeWithTimeout(callable $callback, int $timeout): array
    {
        // In production, implement proper timeout handling
        // This is a simplified example
        try {
            return $callback();
        } catch (\Exception $e) {
            $this->logger->error("Tool error: {$this->getName()}", [
                'error' => $e->getMessage()
            ]);

            return [
                'error' => true,
                'message' => 'Tool execution failed'
            ];
        }
    }
}
```

## Key Takeaways

- ✓ Use a tool registry pattern for organized, maintainable tool libraries
- ✓ Implement the Tool interface for consistent tool structure
- ✓ Database tools enable Claude to query and analyze data
- ✓ API integration tools extend Claude's capabilities to external services
- ✓ File operation tools must implement strict security measures
- ✓ Always validate inputs and handle errors gracefully
- ✓ Log tool usage for debugging and security auditing
- ✓ Write unit tests for all custom tools
- ✓ Implement timeouts to prevent hung operations
- ✓ Use descriptive schemas to help Claude understand tool capabilities

<ChapterCheckbox
  seriesId="claude-php-developers"
  chapterId="12"
  label="You've built a production-ready tool library!"
/>

---

Continue to [Chapter 13: Vision - Working with Images](/series/claude-php-developers/chapters/13-vision-images) to add visual understanding capabilities.

## 💻 Code Samples

All code examples from this chapter are available in the GitHub repository:

**[View Chapter 12 Code Samples](https://github.com/dalehurley/codewithphp/tree/main/code/claude-php/chapter-12)**

Clone and run locally:
```bash
git clone https://github.com/dalehurley/codewithphp.git
cd codewithphp/code/claude-php/chapter-12
composer install
export ANTHROPIC_API_KEY="sk-ant-your-key-here"
php examples/01-complete-tool-system.php
```
