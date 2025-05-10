<?php

namespace App\Repositories\Services\TypeServices;

use Illuminate\Database\Eloquent\Collection;

interface TypeServiceRepositoryInterface
{
    public function getAllTypeServices(): Collection;
}
