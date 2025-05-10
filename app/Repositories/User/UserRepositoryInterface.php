<?php
namespace App\Repositories\User;

use App\Models\User;

interface UserRepositoryInterface
{
    public function findByEmail(string $email): ?User;
    public function findByEmailWithSelect(string $email, array $columns): ?User;
    public function deleteUserTokens(User $user): void;
    public function create(array $data): User;
}
