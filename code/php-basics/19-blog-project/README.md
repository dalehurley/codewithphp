# Chapter 19: Building a Simple Blog - Complete Project

A complete, working blog application demonstrating all concepts from the PHP Basics series.

## 🎯 What You'll Build

A fully functional blog with:

- ✅ User authentication (login/logout)
- ✅ Create, read, update, delete posts (CRUD)
- ✅ Markdown support for post content
- ✅ User sessions and authorization
- ✅ Clean MVC architecture
- ✅ RESTful routing
- ✅ SQLite database
- ✅ Form validation and security
- ✅ Professional code following PSR-12

## 📁 Project Structure

```
19-blog-project/
├── app/
│   ├── controllers/
│   │   ├── HomeController.php
│   │   ├── PostController.php
│   │   └── AuthController.php
│   ├── models/
│   │   ├── Model.php (Base)
│   │   ├── Post.php
│   │   └── User.php
│   ├── views/
│   │   ├── layouts/
│   │   │   └── main.php
│   │   ├── posts/
│   │   │   ├── index.php
│   │   │   ├── show.php
│   │   │   ├── create.php
│   │   │   └── edit.php
│   │   ├── auth/
│   │   │   └── login.php
│   │   └── home.php
│   └── Router.php
├── config/
│   └── config.php
├── database/
│   ├── schema.sql
│   └── seeds.sql
├── public/
│   ├── index.php (Front controller)
│   ├── .htaccess
│   └── css/
│       └── style.css
├── routes.php
└── README.md (this file)
```

## 🚀 Quick Start

### 1. Setup Database

```bash
# Create SQLite database
sqlite3 blog.db < database/schema.sql

# Optional: Add sample data
sqlite3 blog.db < database/seeds.sql
```

### 2. Configure Application

The default configuration should work out of the box. Check `config/config.php` if needed.

### 3. Start Development Server

```bash
# From the public/ directory
cd public
php -S localhost:8000

# Or from project root
php -S localhost:8000 -t public
```

### 4. Access the Blog

Open your browser and visit:

- **Home**: http://localhost:8000/
- **Posts**: http://localhost:8000/posts
- **Login**: http://localhost:8000/login

**Default Login:**

- Email: `admin@blog.com`
- Password: `password`

## 🎓 Concepts Demonstrated

### Architecture Patterns

- **MVC (Model-View-Controller)**: Separation of concerns
- **Front Controller**: Single entry point (public/index.php)
- **Repository Pattern**: Data access abstraction
- **Dependency Injection**: Controllers receive dependencies

### Security

- **Password Hashing**: `password_hash()` and `password_verify()`
- **SQL Injection Prevention**: PDO prepared statements
- **XSS Protection**: `htmlspecialchars()` in views
- **Session Security**: Session regeneration, secure cookies
- **Authorization**: Check user permissions before actions

### Database

- **PDO**: Modern database access
- **Migrations**: Schema version control (schema.sql)
- **Relationships**: Posts belong to users
- **CRUD Operations**: Create, Read, Update, Delete

### Routing

- **RESTful URLs**: `/posts`, `/posts/123`, `/posts/create`
- **HTTP Methods**: GET for display, POST for actions
- **Named Parameters**: `/posts/{id}` extraction
- **404 Handling**: Graceful error pages

### Views & Templates

- **Layout System**: Master layout with content injection
- **Partial Views**: Reusable components
- **Data Escaping**: Security in templates
- **Flash Messages**: One-time notifications

## 📋 Features

### Public Features

- **View all posts**: Browse published blog posts
- **Read post**: View individual post with full content
- **Markdown support**: Posts written in Markdown

### Authenticated Features

- **Login/Logout**: User authentication
- **Create post**: Write new blog posts
- **Edit post**: Update your posts
- **Delete post**: Remove posts
- **User profile**: View your information

## 🛣️ Routes

| Method | URL                  | Action           | Auth Required |
| ------ | -------------------- | ---------------- | ------------- |
| GET    | `/`                  | Home page        | No            |
| GET    | `/posts`             | List all posts   | No            |
| GET    | `/posts/{id}`        | Show single post | No            |
| GET    | `/posts/create`      | Show create form | Yes           |
| POST   | `/posts`             | Store new post   | Yes           |
| GET    | `/posts/{id}/edit`   | Show edit form   | Yes (owner)   |
| POST   | `/posts/{id}`        | Update post      | Yes (owner)   |
| POST   | `/posts/{id}/delete` | Delete post      | Yes (owner)   |
| GET    | `/login`             | Show login form  | No            |
| POST   | `/login`             | Process login    | No            |
| POST   | `/logout`            | Logout user      | Yes           |

## 💾 Database Schema

### Users Table

```sql
CREATE TABLE users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    email TEXT UNIQUE NOT NULL,
    password TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
```

### Posts Table

```sql
CREATE TABLE posts (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    title TEXT NOT NULL,
    slug TEXT UNIQUE NOT NULL,
    content TEXT NOT NULL,
    published BOOLEAN DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);
```

## 🎨 Styling

Basic CSS is included in `public/css/style.css`. The design is:

- **Responsive**: Works on mobile and desktop
- **Modern**: Clean, professional appearance
- **Accessible**: WCAG compliant
- **Minimal**: Easy to customize

## 🔧 Customization

### Add New Feature

1. **Create Model** (if needed): `app/models/Comment.php`
2. **Create Controller**: `app/controllers/CommentController.php`
3. **Add Routes**: In `routes.php`
4. **Create Views**: `app/views/comments/*.php`
5. **Update Database**: Add table to `schema.sql`

### Change Database

Edit `config/config.php`:

```php
'database' => [
    'driver' => 'mysql',
    'host' => 'localhost',
    'database' => 'blog',
    'username' => 'root',
    'password' => '',
]
```

### Add Middleware

In `routes.php`:

```php
// Check authentication before route
if (!isset($_SESSION['user_id'])) {
    header('Location: /login');
    exit;
}

// Define protected routes below
```

## 🐛 Troubleshooting

### Database Connection Error

**Problem**: Can't connect to database

**Solution**:

- Check `blog.db` file exists
- Verify permissions: `chmod 666 blog.db`
- Check config in `config/config.php`

### 404 on All Routes

**Problem**: Only index.php works

**Solution**:

- Ensure `.htaccess` is present in `public/`
- Enable `mod_rewrite` in Apache
- Or use PHP built-in server: `php -S localhost:8000`

### Session Not Persisting

**Problem**: Logged out immediately

**Solution**:

- Check `session_start()` is called
- Verify cookies are enabled in browser
- Check session save path is writable

### Styles Not Loading

**Problem**: No CSS styling

**Solution**:

- Ensure serving from `public/` directory
- Check browser console for 404 errors
- Verify `public/css/style.css` exists

## 📚 Learning Path

### Beginner

1. **Understand structure**: Explore the file organization
2. **Follow request flow**: From URL to response
3. **Read the code**: Start with `public/index.php`
4. **Modify views**: Change templates to see results
5. **Add validation**: Enhance form validation

### Intermediate

1. **Add features**: Comments, categories, tags
2. **Improve security**: CSRF tokens, rate limiting
3. **Add API**: JSON endpoints for posts
4. **Testing**: Write PHPUnit tests
5. **Deploy**: Host on shared hosting or VPS

### Advanced

1. **Caching**: Add Redis/Memcached
2. **Search**: Full-text search for posts
3. **Media**: File uploads for images
4. **Email**: Notifications and newsletters
5. **Queue**: Background job processing

## 🎯 Exercises

### Exercise 1: Add Categories

Create a categories system:

- Add `categories` table
- Associate posts with categories
- Filter posts by category
- Category management CRUD

### Exercise 2: Add Comments

Allow users to comment on posts:

- Create comments table
- Display comments on post page
- Add/delete comments
- Comment moderation

### Exercise 3: User Profiles

Enhance user functionality:

- Public profile pages
- Edit profile (name, bio, avatar)
- View user's posts
- Password change feature

### Exercise 4: Rich Text Editor

Replace textarea with rich editor:

- Integrate TinyMCE or CKEditor
- Support images and formatting
- Preview before publish
- Sanitize HTML input

## 📖 Related Chapters

This project uses concepts from:

- **Chapter 05**: Forms and user input
- **Chapter 08-09**: OOP and inheritance
- **Chapter 11**: Error handling
- **Chapter 14**: Database with PDO
- **Chapter 15**: Sessions and authentication
- **Chapter 16**: PSR coding standards
- **Chapter 17**: HTTP routing
- **Chapter 18**: MVC structure

## 🚀 Next Steps

After completing this project:

1. **Chapter 20**: Learn Laravel framework
2. **Chapter 21**: Explore Symfony
3. **Deploy your blog**: Make it live!
4. **Portfolio project**: Showcase your work
5. **Keep building**: Add more features

## 📝 Notes

- This is an educational project, not production-ready
- Add CSRF protection for production use
- Implement proper error logging
- Add comprehensive validation
- Consider using a PHP framework for real projects

## 🤝 Contributing

Found a bug or want to improve the blog?

1. Review the code
2. Make your changes
3. Test thoroughly
4. Document what you changed
5. Share your improvements!

---

**Congratulations!** You've built a complete web application from scratch using only PHP fundamentals. You now understand how web frameworks work under the hood!
