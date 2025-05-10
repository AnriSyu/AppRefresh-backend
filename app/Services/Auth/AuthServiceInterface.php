<?php
namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Http\JsonResponse;

interface AuthServiceInterface
{
    public function login(array $credentials): JsonResponse;
    public function logout(User $user): JsonResponse;
    public function register(array $userData): JsonResponse;
}
