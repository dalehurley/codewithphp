<?php

// filename: spl-object-and-extension-introspection.php

declare(strict_types=1);

$first = new stdClass();
$alias = $first;
$second = new stdClass();

echo 'Alias shares ID: ', spl_object_id($first) === spl_object_id($alias) ? 'yes' : 'no', PHP_EOL;
echo 'Second shares ID: ', spl_object_id($first) === spl_object_id($second) ? 'yes' : 'no', PHP_EOL;
$classes = new ReflectionExtension('SPL')->getClassNames();
echo 'ArrayObject is in SPL: ', in_array(ArrayObject::class, $classes, true) ? 'yes' : 'no', PHP_EOL;
