<?php

declare(strict_types=1);

use DI\ContainerBuilder;
use App\Services\UserService;

$containerBuilder = new ContainerBuilder();
$containerBuilder->addDefinitions([
    UserService::class => \DI\create(UserService::class),
]);

return $containerBuilder->build();



