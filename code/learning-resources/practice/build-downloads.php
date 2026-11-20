<?php

declare(strict_types=1);

$root = dirname(__DIR__, 3);
$downloads = $root . '/public/downloads/practice';

if (!class_exists(ZipArchive::class) || !in_array('sqlite', PDO::getAvailableDrivers(), true)) {
    fwrite(STDERR, "The zip and pdo_sqlite extensions are required.\n");
    exit(1);
}

function writeArchive(string $target, string $archiveFolder, string $source, array $extraFiles = []): void
{
    $temporary = tempnam(dirname($target), 'practice-');
    if ($temporary === false) {
        throw new RuntimeException('Could not create a temporary ZIP file.');
    }

    try {
        $archive = new ZipArchive();
        if ($archive->open($temporary, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException("Could not open $temporary.");
        }

        $files = $extraFiles;
        $directory = new RecursiveDirectoryIterator($source, FilesystemIterator::SKIP_DOTS);
        foreach (new RecursiveIteratorIterator($directory) as $file) {
            if ($file->isFile() && !str_starts_with($file->getFilename(), '.')) {
                $relative = substr($file->getPathname(), strlen($source) + 1);
                $files[str_replace(DIRECTORY_SEPARATOR, '/', $relative)] = $file->getPathname();
            }
        }

        ksort($files);
        foreach ($files as $relative => $file) {
            $entry = "$archiveFolder/$relative";
            $contents = file_get_contents($file);
            if ($contents === false || !$archive->addFromString($entry, $contents)) {
                throw new RuntimeException("Could not archive $file.");
            }
            // Keep the entry date stable when regenerating the downloads.
            if (!$archive->setMtimeName($entry, 978307200)) {
                throw new RuntimeException("Could not set the archive date for $entry.");
            }
        }

        if (!$archive->close() || !chmod($temporary, 0644) || !rename($temporary, $target)) {
            throw new RuntimeException("Could not publish $target.");
        }
    } finally {
        if (is_file($temporary)) {
            unlink($temporary);
        }
    }
}

function readLibrary(string $source): array
{
    $csv = fopen($source, 'rb');
    if ($csv === false) {
        throw new RuntimeException("Could not open $source.");
    }

    if (fgetcsv($csv, escape: '') !== ['id', 'title', 'category', 'pages', 'status', 'finished_on']) {
        throw new RuntimeException('Unexpected CSV columns.');
    }

    $books = [];
    while (($row = fgetcsv($csv, escape: '')) !== false) {
        if (count($row) !== 6) {
            throw new RuntimeException('Every CSV row must have six fields.');
        }
        [$id, $title, $category, $pages, $status, $finishedOn] = $row;
        $books[] = [
            'id' => (int) $id,
            'title' => $title,
            'category' => $category,
            'pages' => (int) $pages,
            'status' => $status,
            'finished_on' => $finishedOn === '' ? null : $finishedOn,
        ];
    }
    fclose($csv);

    return $books;
}

function writeLibraryDatabase(string $target, array $books): void
{
    $temporary = tempnam(dirname($target), 'library-');
    if ($temporary === false) {
        throw new RuntimeException('Could not create a temporary SQLite file.');
    }

    try {
        $database = new PDO("sqlite:$temporary");
        $database->exec('CREATE TABLE books (
            id INTEGER PRIMARY KEY,
            title TEXT NOT NULL,
            category TEXT NOT NULL,
            pages INTEGER NOT NULL CHECK (pages > 0),
            status TEXT NOT NULL CHECK (status IN (\'queued\', \'reading\', \'finished\')),
            finished_on TEXT CHECK ((status = \'finished\') = (finished_on IS NOT NULL))
        )');
        $insert = $database->prepare('INSERT INTO books (
            id, title, category, pages, status, finished_on
        ) VALUES (
            :id, :title, :category, :pages, :status, :finished_on
        )');

        $database->beginTransaction();
        foreach ($books as $book) {
            $insert->execute($book);
        }
        $database->commit();
        $insert = null;
        $database = null;

        if (!chmod($temporary, 0644) || !rename($temporary, $target)) {
            throw new RuntimeException("Could not publish $target.");
        }
    } finally {
        if (is_file($temporary)) {
            unlink($temporary);
        }
    }
}

$lesson = $root . '/code/php-basics';
$licence = ['LICENSE' => "$root/LICENSE"];
$exerciseSolutions = [
    'solutions/01-calculations.php' => "$lesson/01-first-script/solutions/exercise-2-calculations.php",
    'solutions/03-fizzbuzz.php' => "$lesson/03-control/solutions/exercise-3-fizzbuzz.php",
    'solutions/04-temperature.php' => "$lesson/04-functions/solutions/exercise-1-temperature-converter.php",
    'solutions/06-student-grades.php' => "$lesson/06-arrays/solutions/exercise-2-student-grades.php",
];

$books = readLibrary("$downloads/library-books.csv");
$json = json_encode($books, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);
if (file_put_contents("$downloads/library-books.json", $json . "\n") === false) {
    throw new RuntimeException('Could not publish the JSON dataset.');
}
writeLibraryDatabase("$downloads/library-books.sqlite", $books);
writeArchive("$downloads/php-basics-exercises.zip", 'php-basics-exercises', __DIR__ . '/exercises', $exerciseSolutions + $licence);
writeArchive("$downloads/php-debugging-challenges.zip", 'php-debugging-challenges', __DIR__ . '/debugging', $licence);
writeArchive("$downloads/php-behaviour-examples.zip", 'php-behaviour-examples', __DIR__ . '/behaviour', $licence);
