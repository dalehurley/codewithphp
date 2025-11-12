# Chapter 21: A Gentle Introduction to Symfony - Quick Start Guide

Symfony is a professional, enterprise-grade PHP framework known for its flexibility, performance, and reusable components.

## 🚀 What is Symfony?

Symfony is a **set of reusable PHP components** and a **web application framework** that provides:

- ✅ Highly flexible architecture
- ✅ Standalone reusable components
- ✅ Strong focus on best practices
- ✅ Enterprise-grade performance
- ✅ Extensive configuration options
- ✅ Powerful debugging tools
- ✅ Long-term support (LTS) versions
- ✅ Used by major platforms (Drupal, Laravel, phpBB)

## 📋 Prerequisites

- PHP 8.2+ (our blog uses 8.4!)
- Composer installed
- Symfony CLI (optional but recommended)
- Basic understanding from Chapters 1-19

## 🛠️ Installation

### Install Symfony CLI (Recommended)

```bash
# macOS
brew install symfony-cli/tap/symfony-cli

# Linux
wget https://get.symfony.com/cli/installer -O - | bash

# Windows
scoop install symfony-cli
```

### Create New Project

```bash
# Full web application
symfony new my-blog --webapp

# Microservice or API
symfony new my-api

# Using Composer
composer create-project symfony/skeleton my-blog
cd my-blog
composer require webapp  # Add full web stack
```

### Start Development Server

```bash
symfony server:start
# Or with PHP built-in server
php -S localhost:8000 -t public/
```

Visit: http://localhost:8000

## 📁 Symfony Directory Structure

```
my-blog/
├── bin/
│   └── console               # Symfony CLI commands
├── config/
│   ├── packages/             # Bundle configuration
│   ├── routes.yaml           # Route definitions
│   └── services.yaml         # Service configuration
├── public/
│   └── index.php             # Front controller
├── src/
│   ├── Controller/           # Controllers
│   ├── Entity/               # Doctrine entities
│   ├── Repository/           # Database repositories
│   ├── Form/                 # Form types
│   └── Kernel.php            # Application kernel
├── templates/                # Twig templates
├── var/
│   ├── cache/                # Application cache
│   └── log/                  # Application logs
├── vendor/                   # Composer dependencies
├── .env                      # Environment variables
└── composer.json             # Dependencies
```

## 🎯 Key Concepts

### 1. Symfony Console

Powerful command-line interface:

```bash
# List all commands
php bin/console list

# Create controller
php bin/console make:controller PostController

# Create entity (model)
php bin/console make:entity Post

# Database migrations
php bin/console make:migration
php bin/console doctrine:migrations:migrate

# Clear cache
php bin/console cache:clear

# Debug routes
php bin/console debug:router

# Debug container services
php bin/console debug:container
```

### 2. Routing

Define routes via annotations/attributes:

```php
// src/Controller/PostController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PostController extends AbstractController
{
    #[Route('/posts', name: 'post_list')]
    public function list(): Response
    {
        $posts = $this->getDoctrine()
            ->getRepository(Post::class)
            ->findAll();

        return $this->render('post/list.html.twig', [
            'posts' => $posts,
        ]);
    }

    #[Route('/posts/{id}', name: 'post_show', requirements: ['id' => '\d+'])]
    public function show(int $id): Response
    {
        $post = $this->getDoctrine()
            ->getRepository(Post::class)
            ->find($id);

        if (!$post) {
            throw $this->createNotFoundException('Post not found');
        }

        return $this->render('post/show.html.twig', [
            'post' => $post,
        ]);
    }
}
```

Or in YAML (`config/routes.yaml`):

```yaml
posts_list:
  path: /posts
  controller: App\Controller\PostController::list

post_show:
  path: /posts/{id}
  controller: App\Controller\PostController::show
  requirements:
    id: '\d+'
```

### 3. Doctrine ORM

Symfony's database abstraction:

```php
// src/Entity/Post.php
namespace App\Entity;

use App\Repository\PostRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PostRepository::class)]
class Post
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(type: 'text')]
    private ?string $content = null;

    #[ORM\ManyToOne(inversedBy: 'posts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $author = null;

    // Getters and setters...
}

// Usage in controller
$entityManager = $this->getDoctrine()->getManager();

// Create
$post = new Post();
$post->setTitle('My Post');
$post->setContent('Content here');
$entityManager->persist($post);
$entityManager->flush();

// Read
$post = $entityManager->find(Post::class, $id);
$posts = $postRepository->findAll();
$posts = $postRepository->findBy(['published' => true]);

// Update
$post->setTitle('New Title');
$entityManager->flush();

// Delete
$entityManager->remove($post);
$entityManager->flush();
```

### 4. Twig Templates

Symfony's default templating engine:

```twig
{# templates/base.html.twig #}
<!DOCTYPE html>
<html>
<head>
    <title>{% block title %}My Blog{% endblock %}</title>
    {% block stylesheets %}{% endblock %}
</head>
<body>
    <nav>
        <a href="{{ path('post_list') }}">Posts</a>
        <a href="{{ path('about') }}">About</a>
    </nav>

    {% block body %}{% endblock %}

    <footer>
        <p>&copy; {{ "now"|date("Y") }} My Blog</p>
    </footer>

    {% block javascripts %}{% endblock %}
</body>
</html>

{# templates/post/list.html.twig #}
{% extends 'base.html.twig' %}

{% block title %}All Posts{% endblock %}

{% block body %}
    <h1>Blog Posts</h1>

    {% for post in posts %}
        <article>
            <h2>{{ post.title }}</h2>
            <p>{{ post.excerpt }}</p>
            <a href="{{ path('post_show', {id: post.id}) }}">
                Read More
            </a>
        </article>
    {% else %}
        <p>No posts found.</p>
    {% endfor %}
{% endblock %}
```

### 5. Dependency Injection

Symfony's core feature - automatic service wiring:

```php
namespace App\Controller;

use App\Service\MailerService;
use App\Repository\PostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PostController extends AbstractController
{
    public function __construct(
        private PostRepository $postRepository,
        private MailerService $mailer
    ) {}

    public function create(): Response
    {
        // Services automatically injected
        $posts = $this->postRepository->findAll();
        $this->mailer->send('New post created');

        return $this->render(...);
    }
}
```

### 6. Forms

Powerful form handling:

```php
// src/Form/PostType.php
namespace App\Form;

use App\Entity\Post;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class PostType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class)
            ->add('content', TextareaType::class)
            ->add('published', CheckboxType::class, [
                'required' => false
            ]);
    }
}

// In controller
$post = new Post();
$form = $this->createForm(PostType::class, $post);
$form->handleRequest($request);

if ($form->isSubmitted() && $form->isValid()) {
    $entityManager->persist($post);
    $entityManager->flush();

    return $this->redirectToRoute('post_show', ['id' => $post->getId()]);
}

return $this->render('post/new.html.twig', [
    'form' => $form->createView(),
]);
```

## 🔥 Symfony Features Overview

### Security Component

```yaml
# config/packages/security.yaml
security:
  password_hashers:
    Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface: "auto"

  providers:
    app_user_provider:
      entity:
        class: App\Entity\User
        property: email

  firewalls:
    main:
      lazy: true
      provider: app_user_provider
      form_login:
        login_path: login
        check_path: login
```

### Validation

```php
use Symfony\Component\Validator\Constraints as Assert;

class Post
{
    #[Assert\NotBlank]
    #[Assert\Length(min: 5, max: 255)]
    private ?string $title = null;

    #[Assert\NotBlank]
    #[Assert\Length(min: 10)]
    private ?string $content = null;
}
```

### Event System

```php
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PostSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            'post.created' => 'onPostCreated',
        ];
    }

    public function onPostCreated($event)
    {
        // Send notification
    }
}
```

### Cache

```php
use Symfony\Contracts\Cache\CacheInterface;

public function __construct(private CacheInterface $cache) {}

public function getPosts(): array
{
    return $this->cache->get('posts', function () {
        return $this->postRepository->findAll();
    });
}
```

## 📦 Symfony Components (Standalone)

Symfony components can be used independently:

```bash
# HTTP Foundation
composer require symfony/http-foundation

# Routing
composer require symfony/routing

# Console
composer require symfony/console

# Mailer
composer require symfony/mailer

# Validator
composer require symfony/validator
```

Example using HttpFoundation:

```php
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$request = Request::createFromGlobals();
$name = $request->query->get('name', 'Guest');

$response = new Response(
    "Hello, $name!",
    Response::HTTP_OK,
    ['content-type' => 'text/html']
);

$response->send();
```

## 🎓 Learning Path

### Beginner

1. **Symfony Documentation**: Start with "Quick Tour"
2. **Symfony Casts**: Excellent video tutorials
3. **The Fast Track**: Free official book
4. **Build a CRUD app**: Posts with Doctrine

### Intermediate

1. **Forms & Validation**: Complex form handling
2. **Security**: Authentication & authorization
3. **Events**: Event-driven architecture
4. **Testing**: PHPUnit with Symfony

### Advanced

1. **Custom bundles**: Create reusable bundles
2. **API Platform**: Build REST/GraphQL APIs
3. **Messenger**: Async/message queues
4. **Workflow**: State machines

## 🆚 Symfony vs Laravel

| Feature            | Symfony                            | Laravel                    |
| ------------------ | ---------------------------------- | -------------------------- |
| **Philosophy**     | Components first, framework second | Full-stack framework       |
| **Flexibility**    | Extremely flexible                 | Opinionated                |
| **Learning Curve** | Steeper                            | Gentler                    |
| **Configuration**  | YAML/XML/PHP                       | PHP arrays                 |
| **ORM**            | Doctrine (powerful, complex)       | Eloquent (simple, elegant) |
| **Templating**     | Twig                               | Blade                      |
| **Best For**       | Enterprise, complex apps           | Rapid development, MVPs    |
| **LTS Support**    | 4 years                            | No official LTS            |
| **Bundle System**  | Extensive                          | Package ecosystem          |

## 💡 When to Use Symfony

**Use Symfony when:**

- ✅ Building enterprise applications
- ✅ Need maximum flexibility
- ✅ Complex business logic
- ✅ Long-term maintenance
- ✅ Need standalone components
- ✅ API-first architecture

**Choose Laravel when:**

- ✅ Rapid prototyping
- ✅ Prefer convention over configuration
- ✅ Smaller to medium projects
- ✅ Want elegant syntax

## 🚀 Quick Start: Blog in Symfony

```bash
# Create project
symfony new my-blog --webapp
cd my-blog

# Create Post entity
php bin/console make:entity Post

# Create migration
php bin/console make:migration
php bin/console doctrine:migrations:migrate

# Create controller
php bin/console make:controller PostController

# Install maker bundle (development)
composer require --dev symfony/maker-bundle

# Create CRUD
php bin/console make:crud Post

# Start server
symfony server:start
```

## 📚 Resources

- **Official Site**: https://symfony.com
- **Documentation**: https://symfony.com/doc
- **Symfony Casts**: https://symfonycasts.com
- **The Fast Track**: https://symfony.com/doc/current/the-fast-track
- **Symfony Blog**: https://symfony.com/blog
- **Stack Overflow**: Large Symfony community

## 🎯 Next Steps

1. **Install Symfony**: Try it locally
2. **Read "Quick Tour"**: In documentation
3. **Follow a tutorial**: SymfonyCasts or official guides
4. **Explore components**: Use them standalone
5. **Join Community**: Symfony Slack, forums

## 💎 Symfony Philosophy

**"Symfony is more than a framework. It's a community, a spirit, and a set of best practices."**

Key principles:

- Decoupled components
- Follow standards (PSR)
- Test-driven development
- Performance optimization
- Backward compatibility

## Related Chapter

[Chapter 21: A Gentle Introduction to Symfony](../../chapters/21-a-gentle-introduction-to-symfony.md)

---

**Remember**: Symfony and Laravel both build on PHP fundamentals. Choose based on your project needs, not hype. Both are excellent frameworks!
