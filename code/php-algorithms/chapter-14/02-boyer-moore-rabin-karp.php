<?php

/**
 * Boyer-Moore and Rabin-Karp Search
 *
 * @package CodeWithPHP\Algorithms\Chapter14
 */

declare(strict_types=1);

function boyerMooreSearch(string $text, string $pattern): array
{
    $matches = [];
    $n = strlen($text);
    $m = strlen($pattern);

    if ($m === 0) return $matches;

    $badChar = [];
    for ($i = 0; $i < 256; $i++) {
        $badChar[chr($i)] = -1;
    }

    for ($i = 0; $i < $m; $i++) {
        $badChar[$pattern[$i]] = $i;
    }

    $s = 0;

    while ($s <= $n - $m) {
        $j = $m - 1;

        while ($j >= 0 && $pattern[$j] === $text[$s + $j]) {
            $j--;
        }

        if ($j < 0) {
            $matches[] = $s;
            $s += ($s + $m < $n) ? $m - ($badChar[$text[$s + $m]] ?? -1) : 1;
        } else {
            $s += max(1, $j - ($badChar[$text[$s + $j]] ?? -1));
        }
    }

    return $matches;
}

class RabinKarp
{
    private const PRIME = 101;
    private const BASE = 256;

    public function search(string $text, string $pattern): array
    {
        $matches = [];
        $n = strlen($text);
        $m = strlen($pattern);

        if ($m > $n) return $matches;

        $patternHash = $this->hash($pattern, $m);
        $textHash = $this->hash($text, $m);

        $h = 1;
        for ($i = 0; $i < $m - 1; $i++) {
            $h = ($h * self::BASE) % self::PRIME;
        }

        for ($i = 0; $i <= $n - $m; $i++) {
            if ($patternHash === $textHash) {
                if (substr($text, $i, $m) === $pattern) {
                    $matches[] = $i;
                }
            }

            if ($i < $n - $m) {
                $textHash = ($self::BASE * ($textHash - ord($text[$i]) * $h) + ord($text[$i + $m])) % self::PRIME;
                if ($textHash < 0) {
                    $textHash += self::PRIME;
                }
            }
        }

        return $matches;
    }

    private function hash(string $str, int $length): int
    {
        $hash = 0;
        for ($i = 0; $i < $length; $i++) {
            $hash = (self::BASE * $hash + ord($str[$i])) % self::PRIME;
        }
        return $hash;
    }
}

// DEMONSTRATIONS
echo "=== Boyer-Moore and Rabin-Karp ===\n\n";

$text = "AABAACAADAABAABA";
$pattern = "AABA";

echo "Boyer-Moore: ";
print_r(boyerMooreSearch($text, $pattern));

echo "Rabin-Karp: ";
$rk = new RabinKarp();
print_r($rk->search($text, $pattern));
