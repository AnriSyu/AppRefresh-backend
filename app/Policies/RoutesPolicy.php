<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class RoutesPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('Administrador');
    }

    public function view(User $user, Model $model): bool
    {
        return $user->hasRole('Administrador');
    }

    public function create(User $user): bool
    {
        return $user->hasRole('Administrador');
    }

    public function update(User $user, Model $model): bool
    {
        return $user->hasRole('Administrador');
    }

    public function delete(User $user, Model $model): bool
    {
        return $user->hasRole('Administrador');
    }

    public function restore(User $user, Model $model): bool
    {
        return $user->hasRole('Administrador');
    }

    public function forceDelete(User $user, Model $model): bool
    {
        return $user->hasRole('Administrador');
    }
}
