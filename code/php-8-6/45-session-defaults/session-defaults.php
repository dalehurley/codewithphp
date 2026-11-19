<?php

// filename: session-defaults.php

declare(strict_types=1);

printf("Strict mode: %s\n", ini_get('session.use_strict_mode'));
$cookie = session_get_cookie_params();
printf("HttpOnly: %s\n", $cookie['httponly'] ? 'yes' : 'no');
printf("SameSite: %s\n", $cookie['samesite']);
