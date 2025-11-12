{{-- filename: resources/views/blog/post-list.blade.php --}}
{{-- Laravel Blade Template Example --}}
<!DOCTYPE html>
<html>
<head>
    <title>Posts</title>
</head>
<body>
    <h1>Blog Posts</h1>
    @foreach($posts as $post)
        <article>
            <h2>{{ $post->title }}</h2>
            <p>{{ Str::limit($post->content, 30) }}</p>
            <p>Published: {{ $post->published_at->format('F j, Y') }}</p>
        </article>
    @endforeach
</body>
</html>

