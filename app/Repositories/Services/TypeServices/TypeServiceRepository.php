<?php
namespace App\Repositories\Services\TypeServices;

use App\Models\TypeService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class TypeServiceRepository implements TypeServiceRepositoryInterface
{
    public function getAllTypeServices(): Collection
    {
        return Cache::remember('all_type_services', now()->addHours(24), function () {
            return TypeService::all();
        });
    }
}
