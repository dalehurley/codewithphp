<?php

declare(strict_types=1);

// Fictional assumptions shared by both plans.
$trafficGb = 100;
$hourlyRateCents = 8000;

$managedFixedCents = 4000 + 2000;
$managedIncludedGb = 100;
$managedOverageCentsPerGb = 10;
$managedHours = 1;
$managedTrafficCents = max(0, $trafficGb - $managedIncludedGb) * $managedOverageCentsPerGb;
$managedTotalCents = $managedFixedCents + $managedTrafficCents + $managedHours * $hourlyRateCents;

$selfManagedFixedCents = 3000 + 1500 + 1000;
$selfManagedIncludedGb = 125;
$selfManagedOverageCentsPerGb = 20;
$selfManagedHours = 2;
$selfManagedTrafficCents = max(0, $trafficGb - $selfManagedIncludedGb) * $selfManagedOverageCentsPerGb;
$selfManagedTotalCents = $selfManagedFixedCents + $selfManagedTrafficCents + $selfManagedHours * $hourlyRateCents;

printf("Managed: A\$%.2F per month\n", $managedTotalCents / 100);
printf("Self-managed: A\$%.2F per month\n", $selfManagedTotalCents / 100);
