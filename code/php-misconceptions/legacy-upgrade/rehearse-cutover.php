<?php

declare(strict_types=1);

function expect(bool $passed, string $description): void
{
    if (!$passed) {
        throw new RuntimeException($description);
    }

    echo "PASS $description", PHP_EOL;
}

// The candidate accepts orders created before the status field existed.
$previousOrder = ['label' => 'paid', 'note' => ''];
$readStatus = static fn (array $order): string => $order['status'] ?? $order['label'];
$readLabel = static fn (array $order): string => $order['label'];
expect($readStatus($previousOrder) === 'paid', 'candidate reads a legacy order');

$candidateOrder = $previousOrder + ['status' => 'reconciled'];
$overwrittenOrder = ['label' => $readLabel($candidateOrder), 'note' => 'reviewed'];
expect(!isset($overwrittenOrder['status']), 'a whole-record legacy write loses status');

// A field-level update preserves data written by the candidate.
$updatedOrder = array_replace($candidateOrder, ['note' => 'reviewed']);
expect(
    $readLabel($updatedOrder) === 'paid'
        && $readStatus($updatedOrder) === 'reconciled'
        && $updatedOrder['note'] === 'reviewed',
    'reporting and candidate see the expected fields after a legacy update',
);

$oldReceipt = ['receipt_id' => 'R1'];
$newReceipt = ['receipt' => ['id' => 'R2']];
$previousWorker = static fn (array $payload): ?string => $payload['receipt_id'] ?? null;
$compatibleWorker = static fn (array $payload): ?string => $payload['receipt_id'] ?? $payload['receipt']['id'] ?? null;
expect($previousWorker($oldReceipt) === 'R1', 'previous worker accepts the original format');
expect($previousWorker($newReceipt) === null, 'previous worker rejects a new-format receipt');

// Model a delayed new-format job and its retry after reverting the producer.
$delivered = [];
foreach ([$oldReceipt, $newReceipt, $newReceipt] as $payload) {
    $id = $compatibleWorker($payload);
    if ($id !== null && !isset($delivered[$id])) {
        $delivered[$id] = true;
        echo "Delivered $id", PHP_EOL;
    }
}
expect(array_keys($delivered) === ['R1', 'R2'], 'compatible worker delivers each receipt once in this rehearsal');
