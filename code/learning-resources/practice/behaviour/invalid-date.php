<?php

try {
    new DateTimeImmutable('this is not a date');
} catch (Exception $error) {
    echo get_class($error), PHP_EOL;
}
