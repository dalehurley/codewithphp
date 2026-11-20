<?php

declare(strict_types=1);

require __DIR__ . '/Order.php';

$order = new Order(12, OrderStatus::Pending);
echo $order->label, PHP_EOL;

$order->status = OrderStatus::Paid;
echo $order->label, PHP_EOL;
