<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    use ApiResponse;

    public function index()
    {
        $users = User::latest()->get();
        return $this->successResponse('Users retrieved successfully.', UserResource::collection($users));
    }

    public function register(RegisterRequest $request)
    {
        $user = User::create([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'password' => $request->validated('password'),
            'role' => 'user',
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->successResponse('User registered successfully.', [
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => new UserResource($user),
        ], 201);
    }

    public function login(LoginRequest $request)
    {
        $user = User::where('email', $request->validated('email'))->first();

        if (! $user || ! Hash::check($request->validated('password'), $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->successResponse('Login successful.', [
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => new UserResource($user),
        ]);
    }

    public function createStaff(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                'unique:users,email',
                'regex:/^[\w\.-]+@mlgcl\.edu\.ph$/i'
            ],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'email.regex' => 'Email must be a valid @mlgcl.edu.ph address.',
        ]);

        $staff = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'role' => 'staff',
        ]);

        return $this->successResponse('Staff account created successfully.', new UserResource($staff), 201);
    }

    public function createUser(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                'unique:users,email',
                'regex:/^[\w\.-]+@mlgcl\.edu\.ph$/i'
            ],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'email.regex' => 'Email must be a valid @mlgcl.edu.ph address.',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'role' => 'user',
        ]);

        return $this->successResponse('User account created successfully.', new UserResource($user), 201);
    }

    public function me(Request $request)
    {
        return $this->successResponse(
            'Authenticated user retrieved successfully.',
            new UserResource($request->user())
        );
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return $this->successResponse('Logged out successfully.');
    }
}
