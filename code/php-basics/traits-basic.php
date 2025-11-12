<?php

// Define the trait
trait Timestampable
{
    private DateTime $createdAt;

    // Traits can have methods, but initialization is best done
    // through a method that the class calls explicitly
    protected function initializeTimestamp(): void
    {
        $this->createdAt = new DateTime();
    }

    public function getCreatedAt(): string
    {
        return $this->createdAt->format('Y-m-d H:i:s');
    }
}

// Create classes that use the trait
class BlogPost
{
    // "Copies" the properties and methods from Timestampable into this class
    use Timestampable;

    public string $title;

    public function __construct(string $title)
    {
        $this->title = $title;
        $this->initializeTimestamp(); // Call the trait's initialization method
    }
}

class Comment
{
    use Timestampable;

    public string $text;

    public function __construct(string $text)
    {
        $this->text = $text;
        $this->initializeTimestamp(); // Call the trait's initialization method
    }
}

$post = new BlogPost("My First Post");
echo "Post created at: " . $post->getCreatedAt() . PHP_EOL;

sleep(1); // Wait for 1 second

$comment = new Comment("This is a great post!");
echo "Comment created at: " . $comment->getCreatedAt() . PHP_EOL;
