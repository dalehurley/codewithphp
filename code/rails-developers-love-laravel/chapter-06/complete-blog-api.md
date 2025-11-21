# Complete Blog API Example

This example shows how to build a complete REST API with Laravel for a blog application.

## API Endpoints

### Authentication
- `POST /api/register` - Register new user
- `POST /api/login` - Login user
- `POST /api/logout` - Logout (authenticated)
- `GET /api/user` - Get current user (authenticated)

### Posts
- `GET /api/posts` - List posts (with filtering and pagination)
- `POST /api/posts` - Create post (authenticated)
- `GET /api/posts/{post}` - Get single post
- `PUT /api/posts/{post}` - Update post (authenticated, authorized)
- `DELETE /api/posts/{post}` - Delete post (authenticated, authorized)
- `POST /api/posts/{post}/publish` - Publish post (authenticated, authorized)

### Comments
- `GET /api/posts/{post}/comments` - List comments for post
- `POST /api/posts/{post}/comments` - Create comment (authenticated)
- `PUT /api/comments/{comment}` - Update comment (authenticated, authorized)
- `DELETE /api/comments/{comment}` - Delete comment (authenticated, authorized)

### Tags
- `GET /api/tags` - List all tags
- `POST /api/tags` - Create tag (admin only)
- `DELETE /api/tags/{tag}` - Delete tag (admin only)

## Usage Examples

### Register

```bash
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'
```

Response:
```json
{
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "created_at": "2025-01-01T12:00:00Z",
    "updated_at": "2025-01-01T12:00:00Z"
  },
  "token": "1|abc123xyz789..."
}
```

### Login

```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "john@example.com",
    "password": "password123"
  }'
```

### Create Post

```bash
curl -X POST http://localhost:8000/api/posts \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Getting Started with Laravel",
    "body": "Laravel is a beautiful framework...",
    "excerpt": "Learn the basics of Laravel",
    "tag_ids": [1, 2, 3]
  }'
```

Response:
```json
{
  "id": 1,
  "title": "Getting Started with Laravel",
  "body": "Laravel is a beautiful framework...",
  "excerpt": "Learn the basics of Laravel",
  "published": false,
  "published_at": null,
  "created_at": "2025-01-01T12:00:00Z",
  "updated_at": "2025-01-01T12:00:00Z",
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com"
  },
  "tags": [
    { "id": 1, "name": "laravel" },
    { "id": 2, "name": "php" }
  ],
  "comments": [],
  "comments_count": 0
}
```

### List Posts with Filtering

```bash
# All posts
curl http://localhost:8000/api/posts

# Published posts only
curl "http://localhost:8000/api/posts?published=true"

# Search
curl "http://localhost:8000/api/posts?search=laravel"

# Sort by title ascending
curl "http://localhost:8000/api/posts?sort_by=title&sort_order=asc"

# Pagination
curl "http://localhost:8000/api/posts?page=2&per_page=10"

# Combine filters
curl "http://localhost:8000/api/posts?published=true&search=laravel&sort_by=created_at&sort_order=desc&page=1&per_page=20"
```

Response:
```json
{
  "data": [
    {
      "id": 1,
      "title": "Getting Started with Laravel",
      "excerpt": "Learn the basics of Laravel",
      "published": true,
      "published_at": "2025-01-01T12:00:00Z",
      "created_at": "2025-01-01T12:00:00Z",
      "user": {
        "id": 1,
        "name": "John Doe"
      },
      "comments_count": 5
    }
  ],
  "links": {
    "first": "http://localhost:8000/api/posts?page=1",
    "last": "http://localhost:8000/api/posts?page=3",
    "prev": null,
    "next": "http://localhost:8000/api/posts?page=2"
  },
  "meta": {
    "current_page": 1,
    "from": 1,
    "to": 15,
    "total": 42,
    "per_page": 15,
    "last_page": 3
  }
}
```

### Create Comment

```bash
curl -X POST http://localhost:8000/api/posts/1/comments \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "body": "Great article! Very helpful."
  }'
```

### Update Post

```bash
curl -X PUT http://localhost:8000/api/posts/1 \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Updated Title",
    "body": "Updated content..."
  }'
```

### Publish Post

```bash
curl -X POST http://localhost:8000/api/posts/1/publish \
  -H "Authorization: Bearer YOUR_TOKEN"
```

Response:
```json
{
  "id": 1,
  "title": "Getting Started with Laravel",
  "published": true,
  "published_at": "2025-01-01T14:30:00Z",
  ...
}
```

### Delete Post

```bash
curl -X DELETE http://localhost:8000/api/posts/1 \
  -H "Authorization: Bearer YOUR_TOKEN"
```

Response: 204 No Content

## Error Responses

### 401 Unauthorized (No Token)

```bash
curl http://localhost:8000/api/posts
```

Response:
```json
{
  "message": "Unauthenticated."
}
```

### 403 Forbidden (Not Authorized)

```bash
curl -X DELETE http://localhost:8000/api/posts/2 \
  -H "Authorization: Bearer YOUR_TOKEN"
```

If you don't own the post, you get:
```json
{
  "message": "This action is unauthorized."
}
```

### 422 Unprocessable Entity (Validation Error)

```bash
curl -X POST http://localhost:8000/api/posts \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{}'
```

Response:
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "title": ["The title field is required."],
    "body": ["The body field is required."]
  }
}
```

## Database Schema

### posts table
- id
- user_id (foreign key)
- title
- body
- excerpt
- published (boolean)
- published_at (timestamp, nullable)
- timestamps

### comments table
- id
- post_id (foreign key)
- user_id (foreign key)
- body
- timestamps

### tags table
- id
- name (unique)
- timestamps

### post_tag table (pivot)
- post_id
- tag_id

## Testing

### Run API Tests

```bash
php artisan test tests/Feature/Api/

# Or with Pest
./vendor/bin/pest tests/Feature/Api/

# With coverage
./vendor/bin/pest --coverage tests/Feature/Api/
```

### Test a Specific Endpoint

```bash
./vendor/bin/pest tests/Feature/Api/PostTest.php --filter=test_can_create_post
```

## Performance Optimization

### Eager Load Relationships

```php
// In PostController
$posts = Post::with(['user', 'tags', 'comments'])
    ->paginate(15);
```

### Cache Popular Posts

```php
// In PostController
$posts = Cache::remember('posts.published', 3600, function () {
    return Post::where('published', true)
        ->with(['user', 'tags'])
        ->latest()
        ->paginate(15);
});
```

### Use Select to Limit Columns

```php
// Get only necessary columns
$posts = Post::select('id', 'title', 'excerpt', 'published_at')
    ->with('user:id,name')
    ->paginate(15);
```

## Security Best Practices

1. **Always validate input** using Form Requests
2. **Use authorization policies** to check access
3. **Hash sensitive data** - passwords automatically hashed
4. **Use rate limiting** to prevent abuse
5. **Validate file uploads** (type, size, dimensions)
6. **Use HTTPS only** in production
7. **Set proper CORS headers**
8. **Implement proper authentication** with tokens
9. **Log suspicious activities**
10. **Keep dependencies updated**

## Common Issues

### 401 Unauthorized
- Token is missing or expired
- Include `Authorization: Bearer YOUR_TOKEN` header
- Check token is valid: `User::findToken('token')`

### 403 Forbidden
- You don't own the resource
- Check authorization policy
- Verify `authorize()` passes in controller

### 422 Unprocessable Entity
- Validation failed
- Check error message for which field failed
- Verify data types match expectations

### 404 Not Found
- Resource doesn't exist
- Check ID is correct
- Verify model binding is working

## Production Deployment

### Before going live:
- [ ] Set `APP_ENV=production`
- [ ] Set `APP_DEBUG=false`
- [ ] Run migrations: `php artisan migrate --force`
- [ ] Cache configuration: `php artisan config:cache`
- [ ] Cache routes: `php artisan route:cache`
- [ ] Seed data if needed: `php artisan db:seed`
- [ ] Set up CORS properly
- [ ] Configure rate limiting
- [ ] Set up monitoring/logging
- [ ] Test all endpoints thoroughly
- [ ] Set up backups
- [ ] Configure HTTPS/SSL







