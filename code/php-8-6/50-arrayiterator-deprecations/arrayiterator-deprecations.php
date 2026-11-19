<?php

// filename: arrayiterator-deprecations.php

declare(strict_types=1);

$inventory = new ArrayObject(['large' => 12, 'small' => 3, 'medium' => 7]);
$inventory->asort();
foreach ($inventory->getIterator() as $size => $count) {
    printf("%s %d\n", $size, $count);
}
echo json_encode($inventory->getArrayCopy(), JSON_THROW_ON_ERROR), PHP_EOL;
