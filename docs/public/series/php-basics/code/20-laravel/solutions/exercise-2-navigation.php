<?php

/**
 * Exercise 2: Add a Navigation Link
 * 
 * Update posts index to add a "Back to Welcome" link, and update the welcome
 * page to add a "View Posts" link.
 * 
 * Requirements:
 * - Add navigation link to posts/index.blade.php going to /
 * - Add navigation link to welcome.blade.php going to /posts
 * - Use route() helper for proper URL generation
 * - Style links appropriately
 */

// ============================================================================
// resources/views/posts/index.blade.php - Add "Back to Welcome" link
// ============================================================================

?>
@extends('layouts.app')

@section('title', 'All Posts')

@section('content')
<div class="container mx-auto px-4 py-8">
    {{-- Navigation breadcrumb --}}
    <nav class="mb-6">
        <a href="{{ route('home') }}"
            class="text-blue-600 hover:text-blue-800 flex items-center">
            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z" />
            </svg>
            Back to Welcome
        </a>
    </nav>

    <h1 class="text-4xl font-bold mb-8 text-gray-900">Blog Posts</h1>

    @if($posts->isEmpty())
    <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-3 rounded">
        <p>No posts yet. <a href="{{ route('posts.create') }}" class="underline">Create the first post</a>!</p>
    </div>
    @else
    <div class="grid gap-6">
        @foreach($posts as $post)
        <article class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition">
            <h2 class="text-2xl font-semibold mb-2">
                <a href="{{ route('posts.show', $post) }}"
                    class="text-gray-900 hover:text-blue-600">
                    {{ $post->title }}
                </a>
            </h2>
            <p class="text-gray-600 text-sm mb-4">
                {{ $post->created_at->format('F j, Y') }}
            </p>
            <p class="text-gray-700">
                {{ Str::limit($post->content, 200) }}
            </p>
            <a href="{{ route('posts.show', $post) }}"
                class="text-blue-600 hover:text-blue-800 font-medium mt-3 inline-block">
                Read more →
            </a>
        </article>
        @endforeach
    </div>
    @endif
</div>
@endsection
<?php

// ============================================================================
// resources/views/welcome.blade.php - Add "View Posts" link
// ============================================================================

?>
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laravel</title>
    @vite('resources/css/app.css')
</head>

<body class="antialiased">
    <div class="min-h-screen bg-gray-100 flex items-center justify-center">
        <div class="max-w-2xl mx-auto text-center px-4">
            {{-- Logo/Header --}}
            <div class="mb-8">
                <h1 class="text-6xl font-bold text-gray-900 mb-4">
                    Welcome to Laravel
                </h1>
                <p class="text-xl text-gray-600">
                    A modern PHP framework for building web applications
                </p>
            </div>

            {{-- Quick Links --}}
            <div class="bg-white rounded-lg shadow-lg p-8">
                <h2 class="text-2xl font-semibold mb-6 text-gray-900">
                    Quick Links
                </h2>

                <div class="grid gap-4 md:grid-cols-2">
                    {{-- View Posts Link --}}
                    <a href="{{ route('posts.index') }}"
                        class="block p-6 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                        <div class="flex items-center justify-center mb-3">
                            <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9 4.804A7.968 7.968 0 005.5 4c-1.255 0-2.443.29-3.5.804v10A7.969 7.969 0 015.5 14c1.669 0 3.218.51 4.5 1.385A7.962 7.962 0 0114.5 14c1.255 0 2.443.29 3.5.804v-10A7.968 7.968 0 0014.5 4c-1.255 0-2.443.29-3.5.804V12a1 1 0 11-2 0V4.804z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold mb-2">View Posts</h3>
                        <p class="text-blue-100">
                            Browse all blog posts
                        </p>
                    </a>

                    {{-- Documentation Link --}}
                    <a href="https://laravel.com/docs"
                        target="_blank"
                        class="block p-6 bg-gray-800 text-white rounded-lg hover:bg-gray-900 transition">
                        <div class="flex items-center justify-center mb-3">
                            <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9 4.804A7.968 7.968 0 005.5 4c-1.255 0-2.443.29-3.5.804v10A7.969 7.969 0 015.5 14c1.669 0 3.218.51 4.5 1.385A7.962 7.962 0 0114.5 14c1.255 0 2.443.29 3.5.804v-10A7.968 7.968 0 0014.5 4c-1.255 0-2.443.29-3.5.804V12a1 1 0 11-2 0V4.804z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold mb-2">Documentation</h3>
                        <p class="text-gray-300">
                            Read the Laravel docs
                        </p>
                    </a>

                    {{-- Create Post Link --}}
                    <a href="{{ route('posts.create') }}"
                        class="block p-6 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                        <div class="flex items-center justify-center mb-3">
                            <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold mb-2">Create Post</h3>
                        <p class="text-green-100">
                            Write a new blog post
                        </p>
                    </a>

                    {{-- Laracasts Link --}}
                    <a href="https://laracasts.com"
                        target="_blank"
                        class="block p-6 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition">
                        <div class="flex items-center justify-center mb-3">
                            <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M2 6a2 2 0 012-2h6a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V6zM14.553 7.106A1 1 0 0014 8v4a1 1 0 00.553.894l2 1A1 1 0 0018 13V7a1 1 0 00-1.447-.894l-2 1z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold mb-2">Laracasts</h3>
                        <p class="text-purple-100">
                            Watch video tutorials
                        </p>
                    </a>
                </div>
            </div>

            {{-- Footer --}}
            <div class="mt-8 text-gray-600 text-sm">
                Laravel v{{ Illuminate\Foundation\Application::VERSION }}
                (PHP v{{ PHP_VERSION }})
            </div>
        </div>
    </div>
</body>

</html>
<?php

// ============================================================================
// Alternative: Creating a navigation component
// ============================================================================

?>
{{-- resources/views/components/nav-link.blade.php --}}
@props(['href', 'active' => false])

<a href="{{ $href }}"
    {{ $attributes->merge(['class' => $active 
       ? 'text-blue-600 font-semibold' 
       : 'text-gray-600 hover:text-blue-600']) }}>
    {{ $slot }}
</a>

{{-- Usage in any view: --}}
<nav class="flex gap-4">
    <x-nav-link href="{{ route('home') }}" :active="request()->routeIs('home')">
        Home
    </x-nav-link>
    <x-nav-link href="{{ route('posts.index') }}" :active="request()->routeIs('posts.*')">
        Posts
    </x-nav-link>
    <x-nav-link href="{{ route('about') }}">
        About
    </x-nav-link>
</nav>
<?php

// ============================================================================
// Demo Output
// ============================================================================

echo "=== Navigation Links Implementation ===" . PHP_EOL . PHP_EOL;

echo "✓ Updated posts/index.blade.php:" . PHP_EOL;
echo "  - Added 'Back to Welcome' link at top" . PHP_EOL;
echo "  - Uses route('home') helper" . PHP_EOL;
echo "  - Includes home icon" . PHP_EOL;
echo "  - Breadcrumb-style navigation" . PHP_EOL . PHP_EOL;

echo "✓ Updated welcome.blade.php:" . PHP_EOL;
echo "  - Added 'View Posts' link card" . PHP_EOL;
echo "  - Uses route('posts.index') helper" . PHP_EOL;
echo "  - Grid layout with multiple quick links" . PHP_EOL;
echo "  - Icons for each section" . PHP_EOL;
echo "  - Additional links to docs, create, Laracasts" . PHP_EOL . PHP_EOL;

echo "✓ Best practices used:" . PHP_EOL;
echo "  - route() helper instead of hardcoded URLs" . PHP_EOL;
echo "  - Named routes for flexibility" . PHP_EOL;
echo "  - Consistent styling with Tailwind CSS" . PHP_EOL;
echo "  - Hover effects for better UX" . PHP_EOL;
echo "  - SVG icons for visual appeal" . PHP_EOL . PHP_EOL;

echo "✓ Bonus: Navigation component created:" . PHP_EOL;
echo "  - Reusable nav-link component" . PHP_EOL;
echo "  - Active state handling" . PHP_EOL;
echo "  - Can be used throughout the app" . PHP_EOL . PHP_EOL;

echo "Laravel features used:" . PHP_EOL;
echo "  - route() helper" . PHP_EOL;
echo "  - Named routes" . PHP_EOL;
echo "  - Blade components" . PHP_EOL;
echo "  - request()->routeIs() for active state" . PHP_EOL;
echo "  - Tailwind CSS (via Vite)" . PHP_EOL;
