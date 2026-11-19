<?php

// filename: mbregex-to-pcre.php

declare(strict_types=1);

$text = "Montréal; Αθήνα, Cafe\u{0301}";
$tokens = preg_split('/[;,]\s*/u', $text);
if ($tokens === false) {
    throw new RuntimeException(preg_last_error_msg());
}

foreach ($tokens as $token) {
    $result = preg_match('/^\p{L}[\p{L}\p{M}]*$/u', $token);
    if ($result === false) {
        throw new RuntimeException(preg_last_error_msg());
    }
    echo $token, $result === 1 ? " OK\n" : " invalid\n";
}
