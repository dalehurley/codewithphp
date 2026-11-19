<?php

declare(strict_types=1);

final class LegacyOrder
{
}

$order = new LegacyOrder();
$order->label = 'paid';
echo $order->label, PHP_EOL;
