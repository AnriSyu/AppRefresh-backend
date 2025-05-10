<?php

namespace App\Repositories\User;

use App\Models\User;
use Illuminate\Support\Facades\Cache;

class UserRepository implements UserRepositoryInterface
{
    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    public function findByEmailWithSelect(string $email, array $columns): ?User
    {
        $cacheKey = 'user_email_' . md5($email);

        return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($email, $columns) {
            return User::select($columns)->where('email', $email)->first();
        });
    }

    public function deleteUserTokens(User $user): void
    {
        Cache::forget('user_email_' . md5($user->email));
        $user->tokens()->delete();
    }

    public function create(array $data): User
    {
        return User::create($data);
    }
}
