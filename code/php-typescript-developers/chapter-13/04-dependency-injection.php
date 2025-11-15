<?php

/**
 * Laravel Dependency Injection Examples
 *
 * Laravel's service container automatically resolves dependencies.
 */

// ===== Basic Dependency Injection =====

namespace App\Services;

class UserService
{
    public function getAllUsers(): array
    {
        return ['Alice', 'Bob', 'Charlie'];
    }

    public function getUserById(int $id): ?string
    {
        $users = $this->getAllUsers();
        return $users[$id - 1] ?? null;
    }
}

// ===== Controller with DI =====

namespace App\Http\Controllers;

use App\Services\UserService;

class UserController extends Controller
{
    /**
     * Laravel automatically injects UserService!
     * No manual instantiation needed.
     */
    public function __construct(
        private UserService $userService
    ) {}

    public function index()
    {
        return response()->json([
            'users' => $this->userService->getAllUsers()
        ]);
    }

    public function show(int $id)
    {
        $user = $this->userService->getUserById($id);

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        return response()->json(['user' => $user]);
    }
}

// ===== Method Injection =====

class PostController extends Controller
{
    /**
     * Dependencies can be injected into methods too!
     */
    public function index(PostService $postService)
    {
        return $postService->getAllPosts();
    }

    /**
     * Mix route parameters with injected dependencies
     */
    public function show(int $id, PostService $postService)
    {
        return $postService->getPostById($id);
    }
}

// ===== Interface Binding =====

// Define interface
namespace App\Contracts;

interface UserRepositoryInterface
{
    public function all(): array;
    public function find(int $id): ?array;
}

// Implement interface
namespace App\Repositories;

use App\Contracts\UserRepositoryInterface;

class DatabaseUserRepository implements UserRepositoryInterface
{
    public function all(): array
    {
        // In real app: return User::all()->toArray();
        return [['id' => 1, 'name' => 'Alice']];
    }

    public function find(int $id): ?array
    {
        // In real app: return User::find($id)?->toArray();
        return ['id' => $id, 'name' => 'User ' . $id];
    }
}

// Bind in Service Provider (app/Providers/AppServiceProvider.php)
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Contracts\UserRepositoryInterface;
use App\Repositories\DatabaseUserRepository;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bind interface to implementation
        $this->app->bind(
            UserRepositoryInterface::class,
            DatabaseUserRepository::class
        );

        // Or as singleton (one instance shared across app)
        $this->app->singleton(
            UserRepositoryInterface::class,
            DatabaseUserRepository::class
        );
    }
}

// Use in controller
class UserController extends Controller
{
    /**
     * Laravel injects DatabaseUserRepository automatically
     * because it's bound to the interface!
     */
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {}

    public function index()
    {
        return $this->userRepository->all();
    }
}

// ===== Contextual Binding =====

namespace App\Providers;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Different implementations for different contexts
        $this->app->when(PhotoController::class)
            ->needs(StorageInterface::class)
            ->give(S3Storage::class);

        $this->app->when(VideoController::class)
            ->needs(StorageInterface::class)
            ->give(LocalStorage::class);
    }
}

// ===== Comparison with Nest.js =====

/**
 * Nest.js:
 * ```typescript
 * @Injectable()
 * export class UserService {
 *   findAll() {
 *     return ['Alice', 'Bob'];
 *   }
 * }
 *
 * @Controller('users')
 * export class UsersController {
 *   constructor(private userService: UserService) {}
 *
 *   @Get()
 *   findAll() {
 *     return this.userService.findAll();
 *   }
 * }
 *
 * // In module:
 * @Module({
 *   controllers: [UsersController],
 *   providers: [UserService],
 * })
 * export class UsersModule {}
 * ```
 *
 * Laravel:
 * - No @Injectable decorator needed - auto-wiring works out of the box
 * - No module registration needed - service container handles it
 * - Constructor injection works identically
 * - Interface binding gives more flexibility
 */

// ===== Comparison with Express.js =====

/**
 * Express.js (no DI):
 * ```javascript
 * const userService = require('./services/userService');
 *
 * app.get('/users', (req, res) => {
 *   const users = userService.getAllUsers();
 *   res.json(users);
 * });
 * ```
 *
 * Express.js (with manual DI):
 * ```javascript
 * class UserController {
 *   constructor(userService) {
 *     this.userService = userService;
 *   }
 *
 *   async getUsers(req, res) {
 *     const users = await this.userService.getAllUsers();
 *     res.json(users);
 *   }
 * }
 *
 * // Manual wiring
 * const userService = new UserService();
 * const userController = new UserController(userService);
 * ```
 *
 * Laravel advantages:
 * - Automatic dependency resolution
 * - No manual instantiation
 * - Type-hinted constructor parameters
 * - Interface-based programming
 * - Easy to test (swap implementations)
 */
