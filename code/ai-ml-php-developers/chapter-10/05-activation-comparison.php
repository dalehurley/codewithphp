<?php
declare(strict_types=1);
require_once 'ActivationFunctions.php';
echo "Basic activation comparison - see 04-activation-functions.php for full version\n";
$z = 0.5;
echo "sigmoid($z) = " . ActivationFunctions::sigmoid($z) . "\n";
echo "tanh($z) = " . ActivationFunctions::tanh($z) . "\n";
echo "relu($z) = " . ActivationFunctions::relu($z) . "\n";
