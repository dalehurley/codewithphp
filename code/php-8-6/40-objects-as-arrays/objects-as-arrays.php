<?php

// filename: objects-as-arrays.php

declare(strict_types=1);

$request = (object) ['query' => '  live music  ', 'page' => 2];
$parameters = get_object_vars($request);
array_walk($parameters, static function (mixed &$value): void {
    if (is_string($value)) {
        $value = trim($value);
    }
});
echo http_build_query($parameters, encoding_type: PHP_QUERY_RFC3986), PHP_EOL;

$nested = ['filters' => get_object_vars((object) ['city' => ' Hobart '])];
array_walk_recursive($nested, static function (string &$value): void {
    $value = trim($value);
});
mb_convert_variables('UTF-8', 'UTF-8', $nested);
echo http_build_query($nested), PHP_EOL;

$options = get_object_vars((object) ['level' => 6]);
$compressor = deflate_init(ZLIB_ENCODING_RAW, $options);
if ($compressor === false) {
    throw new RuntimeException('Compression setup failed');
}
$compressed = deflate_add($compressor, 'ready', ZLIB_FINISH);
if ($compressed === false) {
    throw new RuntimeException('Compression failed');
}
$decompressor = inflate_init(ZLIB_ENCODING_RAW, []);
if ($decompressor === false) {
    throw new RuntimeException('Decompression setup failed');
}
echo inflate_add($decompressor, $compressed, ZLIB_FINISH), PHP_EOL;
