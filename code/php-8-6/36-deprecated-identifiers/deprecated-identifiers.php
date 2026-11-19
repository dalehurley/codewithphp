<?php

// filename: deprecated-identifiers.php

declare(strict_types=1);

const DEFAULT_LANGUAGE = 'en';

final class MessageRules
{
    public const MESSAGE_NAMESPACE = 'billing';
}

function isStatus(string $state): bool
{
    return in_array($state, ['pending', 'complete'], true);
}

printf("%s.%s state=%s\n", MessageRules::MESSAGE_NAMESPACE, DEFAULT_LANGUAGE,
    isStatus('complete') ? 'valid' : 'invalid');
