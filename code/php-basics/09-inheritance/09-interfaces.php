<?php

// An interface defines a contract of methods.
interface Shareable
{
    public function share(): string;
}

class BlogPost implements Shareable
{
    private string $title;
    private string $content;

    public function __construct(string $title, string $content)
    {
        $this->title = $title;
        $this->content = $content;
    }

    // Must implement the share() method from Shareable
    public function share(): string
    {
        return "Sharing blog post: {$this->title}";
    }
}

class Image implements Shareable
{
    private string $url;
    private string $altText;

    public function __construct(string $url, string $altText)
    {
        $this->url = $url;
        $this->altText = $altText;
    }

    // Must implement the share() method from Shareable
    public function share(): string
    {
        return "Sharing image: {$this->url} ({$this->altText})";
    }
}

// This function accepts ANY object that implements Shareable
function processShareable(Shareable $item): void
{
    echo $item->share() . PHP_EOL;
}

$post = new BlogPost("My Awesome Trip", "I went to the mountains...");
$image = new Image("/images/trip.jpg", "Mountain landscape");

processShareable($post);
processShareable($image);

// Both BlogPost and Image are completely different classes,
// but they can both be used in processShareable() because
// they share the Shareable interface contract.
