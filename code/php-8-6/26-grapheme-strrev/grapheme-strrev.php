<?php

// filename: grapheme-strrev.php

declare(strict_types=1);

$label = "A:e\u{0301}:👍🏽";
echo $label, PHP_EOL;
echo grapheme_strrev($label), PHP_EOL;
