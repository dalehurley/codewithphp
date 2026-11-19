<?php

// filename: zip-in-memory.php

declare(strict_types=1);

$archive = new ZipArchive();
if ($archive->openString(flags: ZipArchive::CREATE) !== true
    || !$archive->addFromString('notes.txt', "Pack light.\n")) {
    throw new RuntimeException('Could not build the ZIP archive');
}

$bytes = $archive->closeString();
if ($bytes === false) {
    throw new RuntimeException('Could not finalize the ZIP archive');
}

$reader = new ZipArchive();
if ($reader->openString($bytes, ZipArchive::RDONLY) !== true) {
    throw new RuntimeException('Could not reopen the ZIP archive');
}

try {
    $notes = $reader->getFromName('notes.txt');
    if ($notes === false) {
        throw new RuntimeException('Missing notes.txt');
    }
    echo $notes;
} finally {
    $reader->close();
}
