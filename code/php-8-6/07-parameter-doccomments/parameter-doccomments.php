<?php

// filename: parameter-doccomments.php

declare(strict_types=1);

function importOrders(
    /** Local CSV path */
    string $path,
    /** Maximum rows in one batch */
    int $limit = 100,
    bool $verbose = false,
): array {
    return compact('path', 'limit', 'verbose');
}

foreach (new ReflectionFunction('importOrders')->getParameters() as $parameter) {
    printf("%s %s\n", $parameter->getName(), $parameter->getDocComment() ?: '(undocumented)');
}
