<?php

/**
 * Laravel Controller Examples
 *
 * Demonstrates various controller patterns in Laravel.
 */

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;

/**
 * Basic API Controller
 *
 * RESTful controller for User resource.
 * Compare to Express.js controller or Nest.js service.
 */
class UserController extends Controller
{
    /**
     * Display a listing of users.
     *
     * GET /api/users
     */
    public function index()
    {
        return User::all();

        // With pagination:
        // return User::paginate(15);

        // With filters:
        // return User::where('is_active', true)->get();
    }

    /**
     * Store a newly created user.
     *
     * POST /api/users
     */
    public function store(StoreUserRequest $request)
    {
        // Request is automatically validated via StoreUserRequest
        $validated = $request->validated();

        $user = User::create($validated);

        return response()->json($user, 201);
    }

    /**
     * Display the specified user.
     *
     * GET /api/users/{id}
     */
    public function show(User $user)
    {
        // Route model binding - Laravel automatically finds user by ID
        return $user;

        // With relationships:
        // return $user->load('posts', 'comments');
    }

    /**
     * Update the specified user.
     *
     * PUT/PATCH /api/users/{id}
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        $validated = $request->validated();

        $user->update($validated);

        return $user;
    }

    /**
     * Remove the specified user.
     *
     * DELETE /api/users/{id}
     */
    public function destroy(User $user)
    {
        $user->delete();

        return response()->noContent();
    }
}

/**
 * Controller with Dependency Injection
 *
 * Demonstrates Laravel's service container and DI.
 */
class UserServiceController extends Controller
{
    /**
     * Constructor injection
     *
     * Laravel automatically resolves dependencies!
     */
    public function __construct(
        private UserService $userService,
        private LoggerInterface $logger
    ) {}

    public function index()
    {
        $this->logger->info('Fetching all users');

        return $this->userService->getAllUsers();
    }

    public function store(StoreUserRequest $request)
    {
        $user = $this->userService->createUser($request->validated());

        $this->logger->info('User created', ['id' => $user->id]);

        return response()->json($user, 201);
    }
}

/**
 * API Resource Controller with Transformers
 *
 * Use API Resources to transform data before returning.
 */
class UserResourceController extends Controller
{
    public function index()
    {
        $users = User::with('posts')->paginate(15);

        return UserResource::collection($users);
    }

    public function show(User $user)
    {
        return new UserResource($user->load('posts', 'comments'));
    }

    public function store(StoreUserRequest $request)
    {
        $user = User::create($request->validated());

        return new UserResource($user);
    }
}

/**
 * Comparison with Express.js
 *
 * Express.js:
 * ```javascript
 * class UserController {
 *   async index(req, res) {
 *     const users = await User.findAll();
 *     res.json(users);
 *   }
 *
 *   async show(req, res) {
 *     const user = await User.findByPk(req.params.id);
 *     if (!user) {
 *       return res.status(404).json({ error: 'Not found' });
 *     }
 *     res.json(user);
 *   }
 *
 *   async store(req, res) {
 *     const user = await User.create(req.body);
 *     res.status(201).json(user);
 *   }
 * }
 * ```
 *
 * Laravel differences:
 * - Automatic model binding (no manual findByPk needed)
 * - Built-in validation (Form Requests)
 * - Automatic dependency injection
 * - Cleaner syntax
 * - No async/await needed (PHP is synchronous)
 */

/**
 * Comparison with Nest.js
 *
 * Nest.js:
 * ```typescript
 * @Controller('users')
 * export class UsersController {
 *   constructor(private usersService: UsersService) {}
 *
 *   @Get()
 *   findAll() {
 *     return this.usersService.findAll();
 *   }
 *
 *   @Get(':id')
 *   findOne(@Param('id') id: string) {
 *     return this.usersService.findOne(+id);
 *   }
 *
 *   @Post()
 *   create(@Body() createUserDto: CreateUserDto) {
 *     return this.usersService.create(createUserDto);
 *   }
 * }
 * ```
 *
 * Laravel equivalent:
 * - Routes defined in routes/api.php instead of decorators
 * - Dependency injection works the same way
 * - Form Requests instead of DTOs
 * - Similar structure and patterns
 */
