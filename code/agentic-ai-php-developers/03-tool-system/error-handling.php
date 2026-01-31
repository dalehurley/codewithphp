<?php

declare(strict_types=1);

/**
 * Error Handling Example
 * 
 * Demonstrates proper error handling with ToolResult:
 * - Success results
 * - Error results
 * - Exception handling
 * - Try-catch patterns
 * - Partial success reporting
 */

// Load from the claude-php-agent project in the workspace
require_once __DIR__ . '/../../../../claude-php-agent/vendor/autoload.php';

use ClaudeAgents\Tools\Tool;
use ClaudeAgents\Tools\ToolResult;

echo "=== Error Handling Example ===\n\n";

// Pattern 1: Simple Success/Error
$divideNumbers = Tool::create('divide')
    ->description('Divide two numbers')
    ->numberParam('dividend', 'Number to be divided')
    ->numberParam('divisor', 'Number to divide by')
    ->handler(function (array $input): ToolResult {
        $dividend = $input['dividend'];
        $divisor = $input['divisor'];
        
        // Check for division by zero
        if ($divisor == 0) {
            return ToolResult::error('Cannot divide by zero');
        }
        
        $result = $dividend / $divisor;
        
        return ToolResult::success([
            'dividend' => $dividend,
            'divisor' => $divisor,
            'result' => $result,
        ]);
    });

// Pattern 2: Try-Catch with Context
$fetchAPI = Tool::create('fetch_api')
    ->description('Fetch data from external API')
    ->stringParam('endpoint', 'API endpoint URL')
    ->handler(function (array $input): ToolResult {
        $endpoint = $input['endpoint'];
        
        try {
            // Simulate API call (would be real HTTP request in production)
            if (!filter_var($endpoint, FILTER_VALIDATE_URL)) {
                throw new \InvalidArgumentException("Invalid URL: {$endpoint}");
            }
            
            // Simulate different outcomes
            if (str_contains($endpoint, 'timeout')) {
                throw new \RuntimeException('Connection timeout after 30 seconds');
            }
            
            if (str_contains($endpoint, '404')) {
                throw new \RuntimeException('API returned 404 Not Found');
            }
            
            // Success case
            $data = [
                'status' => 200,
                'data' => ['users' => 42, 'active' => 38],
                'timestamp' => time(),
            ];
            
            return ToolResult::success($data);
            
        } catch (\InvalidArgumentException $e) {
            // Specific error with context
            return ToolResult::error(
                "Validation error: {$e->getMessage()} (endpoint: {$endpoint})"
            );
        } catch (\RuntimeException $e) {
            // Network/API errors
            return ToolResult::error(
                "API request failed: {$e->getMessage()}"
            );
        } catch (\Throwable $e) {
            // Catch-all for unexpected errors
            return ToolResult::fromException($e);
        }
    });

// Pattern 3: Partial Success Reporting
$processBatch = Tool::create('process_batch')
    ->description('Process multiple items (reports partial success)')
    ->arrayParam('items', 'Array of items to process')
    ->handler(function (array $input): ToolResult {
        $items = $input['items'];
        $processed = [];
        $errors = [];
        
        foreach ($items as $index => $item) {
            try {
                // Simulate processing
                if (empty($item)) {
                    throw new \InvalidArgumentException('Item cannot be empty');
                }
                
                if (strlen($item) < 3) {
                    throw new \InvalidArgumentException('Item too short (min 3 chars)');
                }
                
                // Success
                $processed[] = [
                    'item' => $item,
                    'processed_at' => date('c'),
                    'result' => strtoupper($item),
                ];
                
            } catch (\Throwable $e) {
                $errors[] = [
                    'item' => $item,
                    'index' => $index,
                    'error' => $e->getMessage(),
                ];
            }
        }
        
        // Return success even if some items failed
        return ToolResult::success([
            'total' => count($items),
            'processed' => count($processed),
            'failed' => count($errors),
            'results' => $processed,
            'errors' => $errors,
        ]);
    });

// Pattern 4: Validation with Multiple Checks
$transferFunds = Tool::create('transfer_funds')
    ->description('Transfer money between accounts (comprehensive validation)')
    ->stringParam('from_account', 'Source account ID')
    ->stringParam('to_account', 'Destination account ID')
    ->numberParam('amount', 'Amount to transfer', true, 0.01, 1000000)
    ->handler(function (array $input): ToolResult {
        $from = $input['from_account'];
        $to = $input['to_account'];
        $amount = $input['amount'];
        
        try {
            // Validation 1: Same account
            if ($from === $to) {
                return ToolResult::error('Cannot transfer to the same account');
            }
            
            // Validation 2: Account existence (simulated)
            $accounts = ['ACC001' => 1000.00, 'ACC002' => 500.00, 'ACC003' => 250.00];
            
            if (!isset($accounts[$from])) {
                return ToolResult::error("Source account not found: {$from}");
            }
            
            if (!isset($accounts[$to])) {
                return ToolResult::error("Destination account not found: {$to}");
            }
            
            // Validation 3: Sufficient balance
            if ($accounts[$from] < $amount) {
                return ToolResult::error(
                    "Insufficient balance: {$accounts[$from]} " .
                    "(need {$amount})"
                );
            }
            
            // Perform transfer (simulated)
            $accounts[$from] -= $amount;
            $accounts[$to] += $amount;
            
            return ToolResult::success([
                'transaction_id' => 'TXN_' . uniqid(),
                'from_account' => $from,
                'to_account' => $to,
                'amount' => $amount,
                'from_balance' => $accounts[$from],
                'to_balance' => $accounts[$to],
                'timestamp' => date('c'),
            ]);
            
        } catch (\Throwable $e) {
            return ToolResult::error(
                "Transfer failed: {$e->getMessage()}"
            );
        }
    });

// Tests
echo "=== Test 1: Successful Division ===\n";
$result1 = $divideNumbers->execute(['dividend' => 100, 'divisor' => 4]);
echo "Success: " . ($result1->isSuccess() ? 'Yes' : 'No') . "\n";
echo "Result: " . $result1->getContent() . "\n\n";

echo "=== Test 2: Division by Zero (Error) ===\n";
$result2 = $divideNumbers->execute(['dividend' => 100, 'divisor' => 0]);
echo "Success: " . ($result2->isSuccess() ? 'Yes' : 'No') . "\n";
echo "Error: " . ($result2->isError() ? 'Yes' : 'No') . "\n";
echo "Message: " . $result2->getContent() . "\n\n";

echo "=== Test 3: API Success ===\n";
$result3 = $fetchAPI->execute(['endpoint' => 'https://api.example.com/users']);
echo "Success: " . ($result3->isSuccess() ? 'Yes' : 'No') . "\n";
echo "Result: " . $result3->getContent() . "\n\n";

echo "=== Test 4: API Timeout (Caught Exception) ===\n";
$result4 = $fetchAPI->execute(['endpoint' => 'https://api.example.com/timeout']);
echo "Success: " . ($result4->isSuccess() ? 'Yes' : 'No') . "\n";
echo "Error: " . $result4->getContent() . "\n\n";

echo "=== Test 5: Invalid URL ===\n";
$result5 = $fetchAPI->execute(['endpoint' => 'not-a-url']);
echo "Success: " . ($result5->isSuccess() ? 'Yes' : 'No') . "\n";
echo "Error: " . $result5->getContent() . "\n\n";

echo "=== Test 6: Batch Processing (Partial Success) ===\n";
$result6 = $processBatch->execute([
    'items' => ['valid_item', 'another_valid', '', 'ab', 'good_item'],
]);
echo "Success: " . ($result6->isSuccess() ? 'Yes' : 'No') . "\n";
$data = json_decode($result6->getContent(), true);
echo "Total: {$data['total']}\n";
echo "Processed: {$data['processed']}\n";
echo "Failed: {$data['failed']}\n";
echo "\nProcessed Items:\n";
foreach ($data['results'] as $item) {
    echo "  ✓ {$item['item']} → {$item['result']}\n";
}
echo "\nFailed Items:\n";
foreach ($data['errors'] as $error) {
    echo "  ✗ Index {$error['index']}: {$error['error']}\n";
}
echo "\n";

echo "=== Test 7: Successful Transfer ===\n";
$result7 = $transferFunds->execute([
    'from_account' => 'ACC001',
    'to_account' => 'ACC002',
    'amount' => 100.00,
]);
echo "Success: " . ($result7->isSuccess() ? 'Yes' : 'No') . "\n";
$transfer = json_decode($result7->getContent(), true);
echo "Transaction ID: {$transfer['transaction_id']}\n";
echo "From: {$transfer['from_account']} (new balance: {$transfer['from_balance']})\n";
echo "To: {$transfer['to_account']} (new balance: {$transfer['to_balance']})\n\n";

echo "=== Test 8: Same Account Error ===\n";
$result8 = $transferFunds->execute([
    'from_account' => 'ACC001',
    'to_account' => 'ACC001',
    'amount' => 50.00,
]);
echo "Success: " . ($result8->isSuccess() ? 'Yes' : 'No') . "\n";
echo "Error: " . $result8->getContent() . "\n\n";

echo "=== Test 9: Insufficient Balance ===\n";
$result9 = $transferFunds->execute([
    'from_account' => 'ACC003',
    'to_account' => 'ACC001',
    'amount' => 1000.00,
]);
echo "Success: " . ($result9->isSuccess() ? 'Yes' : 'No') . "\n";
echo "Error: " . $result9->getContent() . "\n\n";

echo "=== Test 10: Account Not Found ===\n";
$result10 = $transferFunds->execute([
    'from_account' => 'ACC999',
    'to_account' => 'ACC001',
    'amount' => 50.00,
]);
echo "Success: " . ($result10->isSuccess() ? 'Yes' : 'No') . "\n";
echo "Error: " . $result10->getContent() . "\n\n";

echo "✅ Error handling example complete!\n";
echo "\nError Handling Patterns:\n";
echo "1. ToolResult::success() - For successful operations\n";
echo "2. ToolResult::error() - For expected failures (validation, business rules)\n";
echo "3. ToolResult::fromException() - For unexpected system errors\n";
echo "4. Try-catch blocks - Wrap risky operations\n";
echo "5. Partial success - Report both successes and failures in batch operations\n";
echo "6. Context in errors - Include relevant data (IDs, values) in error messages\n";
