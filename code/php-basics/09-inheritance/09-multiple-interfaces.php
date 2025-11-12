<?php

interface Shareable
{
    public function share(): string;
}

interface Searchable
{
    public function getSearchableContent(): string;
}

interface Cacheable
{
    public function getCacheKey(): string;
    public function getCacheDuration(): int; // in seconds
}

// BlogPost implements all three interfaces
class BlogPost implements Shareable, Searchable, Cacheable
{
    public function __construct(
        private string $title,
        private string $content,
        private int $id
    ) {}

    // From Shareable
    public function share(): string
    {
        return "Share: {$this->title}";
    }

    // From Searchable
    public function getSearchableContent(): string
    {
        return $this->title . ' ' . $this->content;
    }

    // From Cacheable
    public function getCacheKey(): string
    {
        return "blog_post_{$this->id}";
    }

    public function getCacheDuration(): int
    {
        return 3600; // Cache for 1 hour
    }
}

// Video only implements two of them
class Video implements Shareable, Cacheable
{
    public function __construct(
        private string $url,
        private int $id
    ) {}

    public function share(): string
    {
        return "Share video: {$this->url}";
    }

    public function getCacheKey(): string
    {
        return "video_{$this->id}";
    }

    public function getCacheDuration(): int
    {
        return 7200; // Cache for 2 hours
    }
}

// Functions can require specific interfaces
function shareItem(Shareable $item): void
{
    echo $item->share() . PHP_EOL;
}

function cacheItem(Cacheable $item): void
{
    echo "Caching with key: {$item->getCacheKey()} for {$item->getCacheDuration()}s" . PHP_EOL;
}

function searchContent(Searchable $item): void
{
    echo "Indexing: {$item->getSearchableContent()}" . PHP_EOL;
}

$post = new BlogPost("PHP Interfaces", "Learn about interfaces in PHP...", 1);
$video = new Video("https://example.com/video.mp4", 100);

// BlogPost can be used with all three functions
shareItem($post);
cacheItem($post);
searchContent($post);

echo PHP_EOL;

// Video can be used with two of them
shareItem($video);
cacheItem($video);
// searchContent($video); // This would cause an error - Video is not Searchable
