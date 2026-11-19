<?php

// filename: class-string-guards.php

declare(strict_types=1);

class Handler
{
}

final class CsvHandler extends Handler
{
}

$handler = new CsvHandler();
$name = CsvHandler::class;
echo 'Instance: ', $handler instanceof Handler ? 'yes' : 'no', PHP_EOL;
echo 'Class is a handler: ', is_a($name, Handler::class, true) ? 'yes' : 'no', PHP_EOL;
echo 'Class extends handler: ', is_subclass_of($name, Handler::class, true) ? 'yes' : 'no', PHP_EOL;
