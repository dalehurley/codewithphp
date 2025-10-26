<?php

/**
 * Exercise 1: Show a Single Post
 * 
 * Create a route /posts/{id} that displays a single post.
 * 
 * Requirements:
 * - Add route in routes/web.php with parameter
 * - Create show method in PostController
 * - Create show.blade.php view
 * - Use Post::findOrFail($id) to retrieve post (returns 404 if not found)
 */

// ============================================================================
// routes/web.php - Add route with parameter
// ============================================================================

/*
Route::get('/posts/{id}', [PostController::class, 'show'])->name('posts.show');

// Or using Route::resource for RESTful routes:
Route::resource('posts', PostController::class)->only(['index', 'show']);
*/

// ============================================================================
// app/Http/Controllers/PostController.php - Add show method
// ============================================================================

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    /**
     * Display all posts
     */
    public function index()
    {
        $posts = Post::all();
        return view('posts.index', compact('posts'));
    }

    /**
     * Display a single post
     * 
     * @param int $id Post ID
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        // findOrFail automatically returns 404 if post not found
        $post = Post::findOrFail($id);

        return view('posts.show', compact('post'));
    }

    /**
     * Alternative: Using route model binding (even better!)
     * Laravel automatically finds the post by ID
     */
    public function showWithBinding(Post $post)
    {
        // Laravel automatically injects the Post model
        // If not found, returns 404 automatically
        return view('posts.show', compact('post'));
    }
}

// ============================================================================
// resources/views/posts/show.blade.php - Create view
// ============================================================================

?>
@extends('layouts.app')

@section('title', $post->title)

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-3xl mx-auto">
        {{-- Back link --}}
        <a href="{{ route('posts.index') }}" class="text-blue-600 hover:text-blue-800 mb-4 inline-block">
            ← Back to all posts
        </a>

        {{-- Post header --}}
        <article class="bg-white rounded-lg shadow-md p-8">
            <header class="mb-6 pb-6 border-b border-gray-200">
                <h1 class="text-4xl font-bold text-gray-900 mb-3">
                    {{ $post->title }}
                </h1>

                <div class="flex items-center text-gray-600 text-sm">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd" />
                    </svg>
                    Posted on {{ $post->created_at->format('F j, Y') }}

                    @if($post->created_at != $post->updated_at)
                    <span class="mx-2">•</span>
                    <span>Updated {{ $post->updated_at->diffForHumans() }}</span>
                    @endif
                </div>
            </header>

            {{-- Post content --}}
            <div class="prose prose-lg max-w-none">
                {!! nl2br(e($post->content)) !!}
            </div>

            {{-- Post footer with actions --}}
            <footer class="mt-8 pt-6 border-t border-gray-200">
                <div class="flex gap-3">
                    <a href="{{ route('posts.edit', $post) }}"
                        class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                        Edit Post
                    </a>

                    <form action="{{ route('posts.destroy', $post) }}"
                        method="POST"
                        onsubmit="return confirm('Are you sure you want to delete this post?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">
                            Delete Post
                        </button>
                    </form>
                </div>
            </footer>
        </article>

        {{-- Related posts section (bonus) --}}
        @if($relatedPosts = \App\Models\Post::where('id', '!=', $post->id)->take(3)->get())
        <div class="mt-12">
            <h2 class="text-2xl font-bold mb-6">More Posts</h2>
            <div class="grid gap-6 md:grid-cols-3">
                @foreach($relatedPosts as $related)
                <a href="{{ route('posts.show', $related) }}"
                    class="block bg-white rounded-lg shadow p-5 hover:shadow-lg transition">
                    <h3 class="font-semibold text-gray-900 mb-2">
                        {{ Str::limit($related->title, 50) }}
                    </h3>
                    <p class="text-gray-600 text-sm">
                        {{ Str::limit($related->content, 100) }}
                    </p>
                </a>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
<?php

// ============================================================================
// Alternative: Using Route Model Binding in routes/web.php
// ============================================================================

/*
// Laravel will automatically find the Post by ID
Route::get('/posts/{post}', [PostController::class, 'show'])->name('posts.show');

// In controller, change method signature:
public function show(Post $post)
{
    // $post is already loaded!
    return view('posts.show', compact('post'));
}
*/

// ============================================================================
// Testing the route
// ============================================================================

/*
// Visit these URLs:
http://your-app.test/posts/1      // Show post with ID 1
http://your-app.test/posts/999    // Should show 404 page
http://your-app.test/posts/abc    // Should show 404 page (invalid ID)
*/

// ============================================================================
// Demo Output
// ============================================================================

echo "=== Show Single Post Implementation ===" . PHP_EOL . PHP_EOL;

echo "✓ Added route with parameter:" . PHP_EOL;
echo "  Route::get('/posts/{id}', [PostController::class, 'show']);" . PHP_EOL . PHP_EOL;

echo "✓ Created PostController::show() method:" . PHP_EOL;
echo "  - Uses Post::findOrFail(\$id)" . PHP_EOL;
echo "  - Automatically returns 404 if not found" . PHP_EOL;
echo "  - Returns view with post data" . PHP_EOL . PHP_EOL;

echo "✓ Created show.blade.php view with:" . PHP_EOL;
echo "  - Post title in large heading" . PHP_EOL;
echo "  - Created/updated timestamps" . PHP_EOL;
echo "  - Full post content" . PHP_EOL;
echo "  - Back to list link" . PHP_EOL;
echo "  - Edit and Delete buttons" . PHP_EOL;
echo "  - Tailwind CSS styling" . PHP_EOL;
echo "  - Related posts section (bonus)" . PHP_EOL . PHP_EOL;

echo "✓ Bonus: Route Model Binding:" . PHP_EOL;
echo "  - Change route to /posts/{post}" . PHP_EOL;
echo "  - Change method signature to show(Post \$post)" . PHP_EOL;
echo "  - Laravel automatically injects the model" . PHP_EOL;
echo "  - Even cleaner and more Laravel-like!" . PHP_EOL . PHP_EOL;

echo "Laravel features used:" . PHP_EOL;
echo "  - Route parameters" . PHP_EOL;
echo "  - findOrFail() for automatic 404s" . PHP_EOL;
echo "  - Blade templating" . PHP_EOL;
echo "  - route() helper" . PHP_EOL;
echo "  - Carbon date formatting" . PHP_EOL;
echo "  - diffForHumans() for relative times" . PHP_EOL;
echo "  - Str::limit() helper" . PHP_EOL;
