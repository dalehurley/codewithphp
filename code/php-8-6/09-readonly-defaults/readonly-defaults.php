<?php

// filename: readonly-defaults.php

declare(strict_types=1);

interface ExportFormat
{
    public string $extension { get; }
}

final readonly class CsvFormat implements ExportFormat
{
    public string $extension = 'csv';

    public function filename(string $base): string
    {
        return $base . '.' . $this->extension;
    }
}

$format = new CsvFormat();
echo $format->filename('orders'), PHP_EOL;

try {
    $format->extension = 'tsv';
} catch (Error) {
    echo "Readonly default already initialized\n";
}
