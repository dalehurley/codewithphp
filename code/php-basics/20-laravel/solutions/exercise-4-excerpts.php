<?php

/**
 * Exercise 4: Add Post Excerpts
 * 
 * Modify the posts index to show only the first 100 characters of content
 * followed by "..." and a "Read more" link.
 * 
 * Requirements:
 * - Show first 100 characters of content
 * - Add "..." after truncated text
 * - Add "Read more" link
 * - Use PHP's substr() or Blade's Str::limit() helper
 */

// ============================================================================
// Method 1: Using Str::limit() helper (recommended)
// ============================================================================

?>
@extends('layouts.app')

@section('title', 'Blog Posts')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-4xl font-bold mb-8">Blog Posts</h1>

    <div class="grid gap-6">
        @foreach($posts as $post)
        <article class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-2xl font-semibold mb-2">
                <a href="{{ route('posts.show', $post) }}"
                    class="text-gray-900 hover:text-blue-600">
                    {{ $post->title }}
                </a>
            </h2>

            <p class="text-gray-600 text-sm mb-4">
                {{ $post->created_at->format('F j, Y') }}
            </p>

            {{-- Method 1: Using Str::limit() helper --}}
            <p class="text-gray-700 mb-4">
                {{ Str::limit($post->content, 100) }}
            </p>

            <a href="{{ route('posts.show', $post) }}"
                class="text-blue-600 hover:text-blue-800 font-medium inline-flex items-center">
                Read more
                <svg class="w-4 h-4 ml-1" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
            </a>
        </article>
        @endforeach
    </div>
</div>
@endsection
<?php

// ============================================================================
// Method 2: Using PHP substr() function
// ============================================================================

?>
<p class="text-gray-700 mb-4">
    @if(strlen($post->content) > 100)
    {{ substr($post->content, 0, 100) }}...
    @else
    {{ $post->content }}
    @endif
</p>
<?php

// ============================================================================
// Method 3: Using Str::words() for word-based truncation
// ============================================================================

?>
<p class="text-gray-700 mb-4">
    {{-- Truncate to 20 words instead of characters --}}
    {{ Str::words($post->content, 20, ' ...') }}
</p>
<?php

// ============================================================================
// Method 4: Creating a custom excerpt method in the Post model
// ============================================================================

// app/Models/Post.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Post extends Model
{
    /**
     * Get the post excerpt
     * 
     * @param int $length Maximum length in characters
     * @return string
     */
    public function excerpt(int $length = 100): string
    {
        return Str::limit($this->content, $length);
    }

    /**
     * Get the post excerpt by words
     * 
     * @param int $words Maximum number of words
     * @return string
     */
    public function excerptWords(int $words = 20): string
    {
        return Str::words($this->content, $words);
    }

    /**
     * Check if content is truncated
     * 
     * @param int $length Length to check against
     * @return bool
     */
    public function isTruncated(int $length = 100): bool
    {
        return strlen($this->content) > $length;
    }
}

// Usage in blade:
?>
<p class="text-gray-700 mb-4">
    {{ $post->excerpt() }}
</p>

@if($post->isTruncated())
<a href="{{ route('posts.show', $post) }}" class="text-blue-600">
    Read more
</a>
@endif
<?php

// ============================================================================
// Method 5: Advanced excerpt with HTML stripping
// ============================================================================

?>
<p class="text-gray-700 mb-4">
    {{-- Strip HTML tags before truncating --}}
    {{ Str::limit(strip_tags($post->content), 100) }}
</p>

{{-- Or with nl2br preserved: --}}
<p class="text-gray-700 mb-4">
    {!! Str::limit(nl2br(e($post->content)), 100) !!}
</p>
<?php

// ============================================================================
// Method 6: Using a Blade component for reusable excerpts
// ============================================================================

// resources/views/components/post-excerpt.blade.php
?>
@props(['content', 'limit' => 100, 'showReadMore' => true])

<div {{ $attributes }}>
    <p class="text-gray-700">
        {{ Str::limit($content, $limit) }}
    </p>

    @if($showReadMore && strlen($content) > $limit)
    <div class="mt-2">
        {{ $slot }}
    </div>
    @endif
</div>

{{-- Usage: --}}
<x-post-excerpt :content="$post->content" :limit="150">
    <a href="{{ route('posts.show', $post) }}" class="text-blue-600 hover:underline">
        Read full article →
    </a>
</x-post-excerpt>
<?php

// ============================================================================
// Complete example with all features
// ============================================================================

?>
@extends('layouts.app')

@section('title', 'Blog Posts')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-4xl">
    <h1 class="text-4xl font-bold mb-8 text-gray-900">Latest Posts</h1>

    @forelse($posts as $post)
    <article class="bg-white rounded-lg shadow-md p-8 mb-6 hover:shadow-lg transition-shadow">
        {{-- Title --}}
        <h2 class="text-2xl font-bold mb-3">
            <a href="{{ route('posts.show', $post) }}"
                class="text-gray-900 hover:text-blue-600 transition-colors">
                {{ $post->title }}
            </a>
        </h2>

        {{-- Meta information --}}
        <div class="flex items-center text-sm text-gray-500 mb-4">
            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd" />
            </svg>
            {{ $post->created_at->format('F j, Y') }}

            <span class="mx-3">•</span>

            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
            </svg>
            {{ $post->updated_at->diffForHumans() }}

            @if(strlen($post->content) > 100)
            <span class="mx-3">•</span>
            <span>{{ ceil(str_word_count($post->content) / 200) }} min read</span>
            @endif
        </div>

        {{-- Excerpt --}}
        <p class="text-gray-700 leading-relaxed mb-4">
            {{ Str::limit($post->content, 150) }}
        </p>

        {{-- Read more link --}}
        <a href="{{ route('posts.show', $post) }}"
            class="inline-flex items-center text-blue-600 hover:text-blue-800 font-semibold group">
            Continue reading
            <svg class="w-4 h-4 ml-2 group-hover:translate-x-1 transition-transform"
                fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd" />
            </svg>
        </a>
    </article>
    @empty
    <div class="text-center py-12 bg-gray-50 rounded-lg">
        <p class="text-gray-600">No posts found.</p>
    </div>
    @endforelse
</div>
@endsection
<?php

// ============================================================================
// Demo Output
// ============================================================================

echo "=== Post Excerpts Implementation ===" . PHP_EOL . PHP_EOL;

echo "✓ Method 1: Str::limit() helper (recommended):" . PHP_EOL;
echo "  {{ Str::limit(\$post->content, 100) }}" . PHP_EOL;
echo "  - Automatic '...' appending" . PHP_EOL;
echo "  - Preserves whole words" . PHP_EOL;
echo "  - Clean and simple" . PHP_EOL . PHP_EOL;

echo "✓ Method 2: PHP substr():" . PHP_EOL;
echo "  {{ substr(\$post->content, 0, 100) }}..." . PHP_EOL;
echo "  - More control" . PHP_EOL;
echo "  - Need to add '...' manually" . PHP_EOL . PHP_EOL;

echo "✓ Method 3: Str::words() for word-based:" . PHP_EOL;
echo "  {{ Str::words(\$post->content, 20, ' ...') }}" . PHP_EOL;
echo "  - Truncates by word count" . PHP_EOL;
echo "  - Better for varying text" . PHP_EOL . PHP_EOL;

echo "✓ Method 4: Custom model method:" . PHP_EOL;
echo "  \$post->excerpt()" . PHP_EOL;
echo "  - Reusable across the app" . PHP_EOL;
echo "  - Can add custom logic" . PHP_EOL;
echo "  - Easy to test" . PHP_EOL . PHP_EOL;

echo "✓ Method 5: HTML stripping:" . PHP_EOL;
echo "  Str::limit(strip_tags(\$post->content), 100)" . PHP_EOL;
echo "  - Removes HTML tags" . PHP_EOL;
echo "  - Safer for excerpts" . PHP_EOL . PHP_EOL;

echo "✓ Method 6: Blade component:" . PHP_EOL;
echo "  <x-post-excerpt :content=\"\$post->content\" :limit=\"150\" />" . PHP_EOL;
echo "  - Highly reusable" . PHP_EOL;
echo "  - Consistent styling" . PHP_EOL;
echo "  - Easy to maintain" . PHP_EOL . PHP_EOL;

echo "✓ Enhanced features added:" . PHP_EOL;
echo "  - Reading time estimate" . PHP_EOL;
echo "  - Relative timestamps" . PHP_EOL;
echo "  - Animated read more arrow" . PHP_EOL;
echo "  - Meta information display" . PHP_EOL . PHP_EOL;

echo "Laravel string helpers used:" . PHP_EOL;
echo "  - Str::limit()" . PHP_EOL;
echo "  - Str::words()" . PHP_EOL;
echo "  - str_word_count()" . PHP_EOL;
echo "  - strlen()" . PHP_EOL;
echo "  - strip_tags()" . PHP_EOL;
