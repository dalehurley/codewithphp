<?php

// filename: property-access-reflection.php

declare(strict_types=1);

final class Article
{
    public string $title = 'Feature tour';
    public private(set) string $slug = 'feature-tour';
    public readonly int $id;

    public function assignId(int $id): void
    {
        $this->id = $id;
    }
}

$article = new Article();
foreach (['title', 'slug', 'id'] as $name) {
    $property = new ReflectionProperty(Article::class, $name);
    printf("%s read=%s write=%s\n", $name,
        $property->isReadable(null, $article) ? 'yes' : 'no',
        $property->isWritable(null, $article) ? 'yes' : 'no');
}

$article->assignId(7);
$id = new ReflectionProperty(Article::class, 'id');
echo 'Initialized id readable: ', $id->isReadable(null, $article) ? 'yes' : 'no', PHP_EOL;
