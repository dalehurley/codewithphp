---
title: "15: Full-Stack - Inertia.js (React/Vue + Laravel)"
description: "Build modern full-stack applications combining Laravel backend with React/Vue frontend using Inertia.js. The best of both worlds for TypeScript developers."
series: "php-typescript-developers"
chapter: 15
order: 15
difficulty: "Advanced"
prerequisites:
  - "/series/php-typescript-developers/chapters/14-database-and-orms"
---

# Full-Stack: Inertia.js (React/Vue + Laravel)

## Overview

Inertia.js bridges the gap between Laravel's backend excellence and React/Vue's frontend power. If you're a TypeScript developer who wants to use Laravel, Inertia lets you build SPAs without building an API. It's like Next.js, but with Laravel as the backend.

## Learning Objectives

By the end of this chapter, you'll be able to:

- ✅ Set up Inertia.js with Laravel
- ✅ Build SPAs without building APIs
- ✅ Use React/Vue with Laravel seamlessly
- ✅ Handle routing and navigation
- ✅ Share data between frontend and backend
- ✅ Implement forms with validation
- ✅ Use TypeScript with Inertia

## What is Inertia.js?

**Problem:**
- **Traditional SPAs:** Build separate frontend (React) + backend API (Laravel)
- **SSR Frameworks:** Next.js/Nuxt great for Node.js, but what about Laravel?

**Solution: Inertia.js**
- Write Laravel controllers (no API endpoints)
- Use React/Vue components for UI
- No client-side routing needed
- Server-side routing with SPA feel
- TypeScript-first

### Architecture Comparison

**Next.js (with API Routes):**
```
React Frontend → API Routes → Database
```

**Inertia.js:**
```
React/Vue Frontend ← Inertia Protocol → Laravel Backend → Database
```

**Traditional SPA:**
```
React Frontend → REST API (Laravel) → Database
(Two separate apps)
```

**Inertia Benefits:**
- ✅ Single codebase
- ✅ No API to build/maintain
- ✅ Server-side routing
- ✅ Use Laravel auth, validation, etc.
- ✅ TypeScript support
- ✅ SSR available

## Installation

### Create Laravel Project with Inertia

```bash
# Create Laravel project
composer create-project laravel/laravel my-app
cd my-app

# Install Inertia server-side
composer require inertiajs/inertia-laravel

# Install frontend dependencies
npm install @inertiajs/react react react-dom
# Or for Vue:
# npm install @inertiajs/vue3 vue@next

# Install Vite plugin
npm install --save-dev @vitejs/plugin-react

# Set up Inertia middleware
php artisan inertia:middleware
```

**app/Http/Kernel.php:**
```php
'web' => [
    // ...
    \App\Http\Middleware\HandleInertiaRequests::class,
],
```

**resources/js/app.jsx:**
```javascript
import './bootstrap';
import { createRoot } from 'react-dom/client';
import { createInertiaApp } from '@inertiajs/react';

createInertiaApp({
  resolve: name => {
    const pages = import.meta.glob('./Pages/**/*.jsx', { eager: true });
    return pages[`./Pages/${name}.jsx`];
  },
  setup({ el, App, props }) {
    createRoot(el).render(<App {...props} />);
  },
});
```

**vite.config.js:**
```javascript
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';

export default defineConfig({
  plugins: [
    laravel(['resources/js/app.jsx']),
    react(),
  ],
});
```

## Basic Example

### Laravel Controller

```php
<?php
// app/Http/Controllers/UserController.php
namespace App\Http\Controllers;

use App\Models\User;
use Inertia\Inertia;

class UserController extends Controller {
    public function index() {
        return Inertia::render('Users/Index', [
            'users' => User::all()
        ]);
    }

    public function show(User $user) {
        return Inertia::render('Users/Show', [
            'user' => $user
        ]);
    }
}
```

### React Component

```typescript
// resources/js/Pages/Users/Index.tsx
import { Link } from '@inertiajs/react';

interface User {
  id: number;
  name: string;
  email: string;
}

interface Props {
  users: User[];
}

export default function UsersIndex({ users }: Props) {
  return (
    <div>
      <h1>Users</h1>
      <ul>
        {users.map(user => (
          <li key={user.id}>
            <Link href={`/users/${user.id}`}>
              {user.name}
            </Link>
          </li>
        ))}
      </ul>
    </div>
  );
}
```

**No API needed!** Laravel passes data directly to React.

## Routing

### Laravel Routes

```php
<?php
// routes/web.php
use App\Http\Controllers\UserController;

Route::get('/users', [UserController::class, 'index']);
Route::get('/users/{user}', [UserController::class, 'show']);
```

**No API routes needed!** Inertia handles communication.

### Navigation (React)

```typescript
import { Link, router } from '@inertiajs/react';

// Link component (like Next.js Link)
<Link href="/users">Users</Link>

// Programmatic navigation
router.visit('/users');

// With method
router.post('/users', { name: 'Alice', email: 'alice@example.com' });
```

## Forms and Validation

### Traditional Next.js Form

```typescript
// Next.js approach
const handleSubmit = async (e) => {
  e.preventDefault();
  const response = await fetch('/api/users', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(formData),
  });
  const data = await response.json();
};
```

### Inertia Form (React)

```typescript
import { useForm } from '@inertiajs/react';

export default function CreateUser() {
  const { data, setData, post, processing, errors } = useForm({
    name: '',
    email: '',
  });

  const submit = (e: React.FormEvent) => {
    e.preventDefault();
    post('/users');
  };

  return (
    <form onSubmit={submit}>
      <input
        value={data.name}
        onChange={e => setData('name', e.target.value)}
      />
      {errors.name && <div>{errors.name}</div>}

      <input
        value={data.email}
        onChange={e => setData('email', e.target.value)}
      />
      {errors.email && <div>{errors.email}</div>}

      <button type="submit" disabled={processing}>
        Create User
      </button>
    </form>
  );
}
```

### Laravel Controller

```php
<?php
class UserController extends Controller {
    public function store(Request $request) {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
        ]);

        $user = User::create($validated);

        return redirect()->route('users.show', $user);
    }
}
```

**Validation errors** automatically sent back to React component!

## Shared Data

### Middleware (Share Data Globally)

```php
<?php
// app/Http/Middleware/HandleInertiaRequests.php
class HandleInertiaRequests extends Middleware {
    public function share(Request $request): array {
        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $request->user(),
            ],
            'flash' => [
                'success' => $request->session()->get('success'),
                'error' => $request->session()->get('error'),
            ],
        ]);
    }
}
```

### Access in React

```typescript
import { usePage } from '@inertiajs/react';

export default function Navigation() {
  const { auth } = usePage<{ auth: { user: User | null } }>().props;

  return (
    <nav>
      {auth.user ? (
        <span>Hello, {auth.user.name}</span>
      ) : (
        <a href="/login">Login</a>
      )}
    </nav>
  );
}
```

## TypeScript Support

### Generate TypeScript Types

**Install Ziggy (Laravel route helper):**
```bash
composer require tightenco/ziggy
```

**Generate types:**
```bash
php artisan ziggy:generate
```

**Use in TypeScript:**
```typescript
import { route } from 'ziggy-js';

// Type-safe routing!
<Link href={route('users.show', user.id)}>View User</Link>
```

### Shared Types

```typescript
// resources/js/types/index.d.ts
export interface User {
  id: number;
  name: string;
  email: string;
  created_at: string;
}

export interface Post {
  id: number;
  title: string;
  body: string;
  user: User;
}

export interface PageProps {
  auth: {
    user: User | null;
  };
  flash: {
    success?: string;
    error?: string;
  };
}
```

## Complete CRUD Example

### Laravel Controller

```php
<?php
namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PostController extends Controller {
    public function index() {
        return Inertia::render('Posts/Index', [
            'posts' => Post::with('user')->latest()->get()
        ]);
    }

    public function create() {
        return Inertia::render('Posts/Create');
    }

    public function store(Request $request) {
        $validated = $request->validate([
            'title' => 'required|max:255',
            'body' => 'required',
        ]);

        $request->user()->posts()->create($validated);

        return redirect()->route('posts.index')
            ->with('success', 'Post created!');
    }

    public function edit(Post $post) {
        return Inertia::render('Posts/Edit', [
            'post' => $post
        ]);
    }

    public function update(Request $request, Post $post) {
        $validated = $request->validate([
            'title' => 'required|max:255',
            'body' => 'required',
        ]);

        $post->update($validated);

        return redirect()->route('posts.index')
            ->with('success', 'Post updated!');
    }

    public function destroy(Post $post) {
        $post->delete();

        return redirect()->route('posts.index')
            ->with('success', 'Post deleted!');
    }
}
```

### React Components

```typescript
// resources/js/Pages/Posts/Index.tsx
import { Link } from '@inertiajs/react';
import Layout from '@/Layouts/Layout';

interface Post {
  id: number;
  title: string;
  body: string;
  user: { name: string };
}

export default function PostsIndex({ posts }: { posts: Post[] }) {
  return (
    <Layout>
      <div className="flex justify-between items-center mb-6">
        <h1 className="text-2xl font-bold">Posts</h1>
        <Link
          href="/posts/create"
          className="bg-blue-500 text-white px-4 py-2 rounded"
        >
          Create Post
        </Link>
      </div>

      <div className="space-y-4">
        {posts.map(post => (
          <div key={post.id} className="border p-4 rounded">
            <h2 className="text-xl font-semibold">{post.title}</h2>
            <p className="text-gray-600">By {post.user.name}</p>
            <p className="mt-2">{post.body}</p>
            <div className="mt-4 space-x-2">
              <Link
                href={`/posts/${post.id}/edit`}
                className="text-blue-500"
              >
                Edit
              </Link>
              <Link
                href={`/posts/${post.id}`}
                method="delete"
                as="button"
                className="text-red-500"
              >
                Delete
              </Link>
            </div>
          </div>
        ))}
      </div>
    </Layout>
  );
}
```

```typescript
// resources/js/Pages/Posts/Create.tsx
import { useForm } from '@inertiajs/react';
import Layout from '@/Layouts/Layout';

export default function CreatePost() {
  const { data, setData, post, processing, errors } = useForm({
    title: '',
    body: '',
  });

  const submit = (e: React.FormEvent) => {
    e.preventDefault();
    post('/posts');
  };

  return (
    <Layout>
      <h1 className="text-2xl font-bold mb-6">Create Post</h1>

      <form onSubmit={submit} className="space-y-4">
        <div>
          <label className="block mb-1">Title</label>
          <input
            type="text"
            value={data.title}
            onChange={e => setData('title', e.target.value)}
            className="w-full border px-3 py-2 rounded"
          />
          {errors.title && (
            <div className="text-red-500 text-sm mt-1">{errors.title}</div>
          )}
        </div>

        <div>
          <label className="block mb-1">Body</label>
          <textarea
            value={data.body}
            onChange={e => setData('body', e.target.value)}
            className="w-full border px-3 py-2 rounded"
            rows={6}
          />
          {errors.body && (
            <div className="text-red-500 text-sm mt-1">{errors.body}</div>
          )}
        </div>

        <button
          type="submit"
          disabled={processing}
          className="bg-blue-500 text-white px-4 py-2 rounded"
        >
          Create Post
        </button>
      </form>
    </Layout>
  );
}
```

## Authentication

Laravel Breeze with Inertia provides complete auth:

```bash
composer require laravel/breeze --dev
php artisan breeze:install react
npm install && npm run dev
php artisan migrate
```

**Includes:**
- Login/Register pages (React)
- Password reset
- Email verification
- Profile management
- Middleware protection

```typescript
// Use in components
import { usePage } from '@inertiajs/react';

const { auth } = usePage().props;

if (auth.user) {
  // User is logged in
}
```

## Server-Side Rendering (SSR)

Enable SSR for better SEO and performance:

```bash
npm install @inertiajs/react @inertiajs/server
```

**resources/js/ssr.jsx:**
```javascript
import { createServer } from '@inertiajs/server';
import { createInertiaApp } from '@inertiajs/react';
import ReactDOMServer from 'react-dom/server';

createServer(page =>
  createInertiaApp({
    page,
    render: ReactDOMServer.renderToString,
    resolve: name => {
      const pages = import.meta.glob('./Pages/**/*.jsx', { eager: true });
      return pages[`./Pages/${name}.jsx`];
    },
    setup: ({ App, props }) => <App {...props} />,
  }),
);
```

## Comparison with Other Stacks

| Feature | Next.js | Remix | Inertia + Laravel |
|---------|---------|-------|-------------------|
| **Backend** | Node.js | Node.js | PHP/Laravel |
| **Frontend** | React | React | React/Vue |
| **Routing** | File-based | File-based | Laravel routes |
| **Data Fetching** | `getServerSideProps` | `loader` | Controller return |
| **Forms** | Manual | `<Form>` + action | `useForm` hook |
| **Auth** | NextAuth | Custom | Laravel Breeze |
| **TypeScript** | ✅ | ✅ | ✅ |
| **SSR** | ✅ | ✅ | ✅ (optional) |

## Key Takeaways

1. **Inertia bridges Laravel + React/Vue** seamlessly - no API layer needed
2. **No API needed** - controllers return props directly to React components
3. **TypeScript support** is first-class with type generation from Laravel models
4. **Forms and validation** are incredibly simple - submit to Laravel routes directly
5. **Feels like SPA** but server-side rendered - best of both architectures
6. **Best of both worlds** - Laravel backend power + React/Vue frontend DX
7. **Perfect for TypeScript devs** who want Laravel without building separate API
8. **Server-side routing** with client-side navigation - no route duplication
9. **Ziggy** provides Laravel routes in JavaScript - type-safe route helpers
10. **Shared data** automatically available to all pages (auth user, flash messages, etc.)
11. **Partial reloads** only fetch changed data - efficient like GraphQL without complexity
12. **SSR support** available via Inertia SSR - true server-side rendering for SEO

## When to Use Inertia

### ✅ Use Inertia When:
- Building full-stack applications
- Want Laravel backend + React/Vue frontend
- Don't need separate mobile app (use API instead)
- Prefer monolithic architecture
- Want rapid development

### ❌ Don't Use Inertia When:
- Need separate API for mobile apps
- Building microservices
- Frontend and backend teams are separate
- Need GraphQL
- Want static site generation

## Next Steps

**Congratulations!** 🎉 You've completed the entire "PHP for TypeScript Developers" series!

You now have the skills to:
- ✅ Build REST APIs with Laravel
- ✅ Use Eloquent ORM effectively
- ✅ Create full-stack applications with Inertia
- ✅ Apply TypeScript knowledge to PHP
- ✅ Use modern PHP tooling

### Continue Learning

- **Laravel Documentation:** https://laravel.com/docs
- **Inertia.js Docs:** https://inertiajs.com
- **Laracasts:** https://laracasts.com (video tutorials)
- **Build real projects!** The best way to learn

## Series Complete! 🚀

You've mastered:

**Phase 1: Foundations**
- Type systems, syntax, functions, OOP, error handling

**Phase 2: Ecosystem**
- Package management, testing, quality tools, build process, debugging

**Phase 3: Advanced & Frameworks**
- Async patterns, REST APIs, Laravel, Eloquent ORM, full-stack development

**You're now a full-stack PHP developer with TypeScript superpowers!**

## Resources

- [Inertia.js Documentation](https://inertiajs.com/)
- [Laravel Breeze](https://laravel.com/docs/starter-kits#breeze)
- [Ziggy (Route Helper)](https://github.com/tighten/ziggy)
- [Laravel Jetstream](https://jetstream.laravel.com/) - Advanced starter kit
- [Inertia.js Pingcrm Demo](https://github.com/inertiajs/pingcrm) - Full example app

---

**Thank you for completing this series!** 🎉

**Questions or feedback?** Open an issue on [GitHub](https://github.com/dalehurley/codewithphp/issues)
