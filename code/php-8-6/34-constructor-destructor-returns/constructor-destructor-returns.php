<?php

// filename: constructor-destructor-returns.php

declare(strict_types=1);

final class Labels
{
    private function __construct(private array $labels)
    {
    }

    public static function fromCsv(string $csv): self
    {
        return new self(explode(',', $csv));
    }

    public function values(): Generator
    {
        foreach ($this->labels as $label) {
            yield trim($label);
        }
    }
}

foreach (Labels::fromCsv('tea, coffee')->values() as $label) {
    echo $label, PHP_EOL;
}
