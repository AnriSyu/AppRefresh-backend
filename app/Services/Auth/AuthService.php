<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Repositories\User\UserRepositoryInterface;
use App\Helpers\Traits\ResponseTrait;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\JsonResponse;
use Laravel\Sanctum\NewAccessToken;



class AuthService implements AuthServiceInterface
{
    use ResponseTrait;

    protected $userRepository;
    protected $tokenExpirationTime = 480;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function login(array $credentials): JsonResponse
    {
        try {
            $user = $this->userRepository->findByEmailWithSelect($credentials['email'], ['id', 'name', 'email', 'password']);

            if (!$user || !Hash::check($credentials['password'], $user->password)) {
                throw ValidationException::withMessages([
                    'email' => ['Las credenciales proporcionadas son incorrectas.'],
                ]);
            }

            $roles = $user->getRoleNames();

            $allowedRoles = ['Cliente', 'Tecnico'];
            $hasAllowedRole = $roles->intersect($allowedRoles)->isNotEmpty();

            if (!$hasAllowedRole) {
                throw ValidationException::withMessages([
                    'email' => ['Solo clientes y técnicos pueden acceder al sistema.'],
                ]);
            }

            $user->tokens()->delete();
            $token = $this->createToken($user, 'auth_token');
            unset($user->password);

            return $this->successJsonResponse([
                'token' => $token->plainTextToken,
                'user' => $user->name,
                'roles' => $roles,
                'expires_at' => now()->addMinutes($this->tokenExpirationTime)->toDateTimeString()
            ]);
        } catch (ValidationException $e) {
            return $this->errorJsonResponse($e->getMessage(), 422);
        } catch (\Exception $e) {
            return $this->errorJsonResponse($e->getMessage(), 500);
        }
    }

    public function logout(User $user): JsonResponse
    {
        try {
            $this->userRepository->deleteUserTokens($user);
            return $this->successJsonResponse(['message' => 'Sesión cerrada correctamente']);
        } catch (\Exception $e) {
            return $this->errorJsonResponse($e->getMessage(), 500);
        }
    }

    public function register(array $userData): JsonResponse
    {
        try {
            if (empty($userData['name']) || empty($userData['email']) || empty($userData['password'])) {
                return $this->errorJsonResponse('Todos los campos son obligatorios', 422);
            }

            $existingUser = $this->userRepository->findByEmail($userData['email']);
            if ($existingUser) {
                return $this->errorJsonResponse('El correo electrónico ya está registrado', 422);
            }

            $user = $this->userRepository->create([
                'name' => $userData['name'],
                'email' => $userData['email'],
                'password' => Hash::make($userData['password']),
            ]);

            $user->assignRole('Cliente');
            $token = $this->createToken($user, 'auth_token');

            return $this->successJsonResponse([
                'message' => 'Usuario registrado correctamente',
                'token' => $token->plainTextToken,
                'user' => $user->name,
                'roles' => $user->getRoleNames(),
                'expires_at' => now()->addMinutes($this->tokenExpirationTime)->toDateTimeString()
            ], 201);
        } catch (\Exception $e) {
            return $this->errorJsonResponse($e->getMessage(), 500);
        }
    }

    protected function createToken(User $user, string $name): NewAccessToken
    {
        return $user->createToken($name, ['*'], now()->addMinutes($this->tokenExpirationTime));
    }
}
