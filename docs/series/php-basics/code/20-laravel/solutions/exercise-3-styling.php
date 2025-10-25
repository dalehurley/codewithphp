<?php

/**
 * Exercise 3: Style Improvement
 * 
 * Use Tailwind CSS to make the posts page more visually appealing.
 * 
 * Requirements:
 * - Card-based layout for posts
 * - Hover effects
 * - Better typography
 * - Responsive design for mobile devices
 * - Use Tailwind CSS classes (included in Laravel 11 via Vite)
 */

// ============================================================================
// resources/views/posts/index.blade.php - Styled version
// ============================================================================

?>
@extends('layouts.app')

@section('title', 'Blog Posts')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-purple-50">
    <div class="container mx-auto px-4 py-12 max-w-6xl">
        {{-- Header Section --}}
        <header class="mb-12">
            <nav class="mb-6">
                <a href="{{ route('home') }}"
                    class="inline-flex items-center text-blue-600 hover:text-blue-800 transition-colors group">
                    <svg class="w-5 h-5 mr-2 group-hover:-translate-x-1 transition-transform"
                        fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                    </svg>
                    Back to Home
                </a>
            </nav>

            <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-5xl font-extrabold text-gray-900 mb-3 bg-clip-text text-transparent bg-gradient-to-r from-blue-600 to-purple-600">
                        Blog Posts
                    </h1>
                    <p class="text-gray-600 text-lg">
                        Discover stories, thinking, and expertise
                    </p>
                </div>

                <a href="{{ route('posts.create') }}"
                    class="mt-6 md:mt-0 inline-flex items-center px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-all hover:scale-105 shadow-lg hover:shadow-xl">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                    </svg>
                    New Post
                </a>
            </div>
        </header>

        @if($posts->isEmpty())
        {{-- Empty State --}}
        <div class="text-center py-20">
            <div class="inline-block p-8 bg-white rounded-full shadow-lg mb-6">
                <svg class="w-24 h-24 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>
            <h2 class="text-3xl font-bold text-gray-900 mb-4">No posts yet</h2>
            <p class="text-gray-600 mb-8 max-w-md mx-auto">
                Get started by creating your first blog post. Share your thoughts with the world!
            </p>
            <a href="{{ route('posts.create') }}"
                class="inline-flex items-center px-8 py-4 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-all hover:scale-105 shadow-lg">
                Create First Post
            </a>
        </div>
        @else
        {{-- Posts Grid --}}
        <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
            @foreach($posts as $post)
            <article class="group bg-white rounded-2xl shadow-md hover:shadow-2xl transition-all duration-300 overflow-hidden transform hover:-translate-y-2">
                {{-- Card Header with gradient --}}
                <div class="h-48 bg-gradient-to-br from-blue-500 via-purple-500 to-pink-500 relative overflow-hidden">
                    <div class="absolute inset-0 bg-black bg-opacity-20 group-hover:bg-opacity-0 transition-all"></div>
                    <div class="absolute bottom-4 left-4 right-4">
                        <span class="inline-block px-3 py-1 bg-white bg-opacity-90 rounded-full text-xs font-semibold text-gray-800">
                            {{ $post->created_at->format('M j, Y') }}
                        </span>
                    </div>
                </div>

                {{-- Card Body --}}
                <div class="p-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-3 line-clamp-2 group-hover:text-blue-600 transition-colors">
                        <a href="{{ route('posts.show', $post) }}" class="hover:underline">
                            {{ $post->title }}
                        </a>
                    </h2>

                    <p class="text-gray-600 mb-4 line-clamp-3 leading-relaxed">
                        {{ Str::limit($post->content, 120) }}
                    </p>

                    {{-- Read More Link --}}
                    <a href="{{ route('posts.show', $post) }}"
                        class="inline-flex items-center text-blue-600 hover:text-blue-800 font-semibold group-hover:gap-3 transition-all">
                        Read More
                        <svg class="w-5 h-5 ml-2 group-hover:translate-x-1 transition-transform"
                            fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </a>
                </div>

                {{-- Card Footer --}}
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-between">
                    <span class="text-sm text-gray-500">
                        {{ $post->updated_at->diffForHumans() }}
                    </span>
                    <div class="flex gap-2">
                        <a href="{{ route('posts.edit', $post) }}"
                            class="p-2 text-gray-400 hover:text-blue-600 transition-colors"
                            title="Edit post">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                            </svg>
                        </a>
                    </div>
                </div>
            </article>
            @endforeach
        </div>

        {{-- Load More Button (if applicable) --}}
        @if($posts->count() >= 9)
        <div class="mt-12 text-center">
            <button class="px-8 py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">
                Load More Posts
            </button>
        </div>
        @endif
        @endif
    </div>
</div>

{{-- Custom CSS for line-clamp (Tailwind utility) --}}
@push('styles')
<style>
    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .line-clamp-3 {
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
</style>
@endpush
@endsection
<?php

// ============================================================================
// Alternative: Dark Mode Support
// ============================================================================

?>
{{-- Add to tailwind.config.js --}}
/*
module.exports = {
darkMode: 'class',
// ... rest of config
}
*/

{{-- Dark mode version --}}
<div class="min-h-screen bg-gray-900 dark">
    <div class="container mx-auto px-4 py-12">
        <article class="bg-gray-800 rounded-2xl shadow-xl hover:shadow-2xl transition-all">
            <div class="p-6">
                <h2 class="text-2xl font-bold text-white mb-3">
                    {{ $post->title }}
                </h2>
                <p class="text-gray-300 mb-4">
                    {{ Str::limit($post->content, 120) }}
                </p>
            </div>
        </article>
    </div>
</div>
<?php

// ============================================================================
// Responsive Breakpoints Reference
// ============================================================================

/*
Tailwind CSS breakpoints used:

sm:  640px  - Small devices (landscape phones)
md:  768px  - Medium devices (tablets)
lg:  1024px - Large devices (desktops)
xl:  1280px - Extra large devices
2xl: 1536px - 2X Extra large devices

Examples used in the solution:
- md:grid-cols-2    - 2 columns on tablets and up
- lg:grid-cols-3    - 3 columns on desktops
- md:flex-row       - Row layout on tablets and up
- hover:-translate-y-2 - Lift effect on hover
- transition-all    - Smooth transitions
*/

// ============================================================================
// Demo Output
// ============================================================================

echo "=== Styled Posts Page Implementation ===" . PHP_EOL . PHP_EOL;

echo "✓ Design improvements:" . PHP_EOL;
echo "  - Card-based layout with shadows" . PHP_EOL;
echo "  - Gradient backgrounds" . PHP_EOL;
echo "  - Hover effects (lift, scale, shadow)" . PHP_EOL;
echo "  - Smooth transitions" . PHP_EOL;
echo "  - Modern typography" . PHP_EOL . PHP_EOL;

echo "✓ Responsive design:" . PHP_EOL;
echo "  - Mobile: 1 column" . PHP_EOL;
echo "  - Tablet (md): 2 columns" . PHP_EOL;
echo "  - Desktop (lg): 3 columns" . PHP_EOL;
echo "  - Flexible header layout" . PHP_EOL . PHP_EOL;

echo "✓ Tailwind CSS features used:" . PHP_EOL;
echo "  - Gradient utilities (bg-gradient-to-br)" . PHP_EOL;
echo "  - Transform utilities (hover:-translate-y-2)" . PHP_EOL;
echo "  - Shadow utilities (shadow-md, hover:shadow-2xl)" . PHP_EOL;
echo "  - Transition utilities (transition-all)" . PHP_EOL;
echo "  - Grid system (grid, md:grid-cols-2)" . PHP_EOL;
echo "  - Flexbox utilities" . PHP_EOL;
echo "  - Color palette (blue, purple, pink)" . PHP_EOL;
echo "  - Typography utilities" . PHP_EOL . PHP_EOL;

echo "✓ UX enhancements:" . PHP_EOL;
echo "  - Empty state with helpful message" . PHP_EOL;
echo "  - Visual feedback on hover" . PHP_EOL;
echo "  - Clear call-to-action buttons" . PHP_EOL;
echo "  - Readable content with proper spacing" . PHP_EOL;
echo "  - Icons for better visual communication" . PHP_EOL . PHP_EOL;

echo "Bonus features:" . PHP_EOL;
echo "  - Dark mode support example" . PHP_EOL;
echo "  - Line-clamp for text truncation" . PHP_EOL;
echo "  - Animated arrow on read more" . PHP_EOL;
echo "  - Professional color scheme" . PHP_EOL;
