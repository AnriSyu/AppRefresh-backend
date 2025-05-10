<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\TypeService;

class TypesServices extends Seeder
{

    public function run(): void
    {
        TypeService::create([
            'name' => 'Servicio Técnico',
        ]);

        TypeService::create([
            'name' => 'Mantenimiento Preventivo',
        ]);

        TypeService::create([
            'name' => 'Mantenimiento Correctivo',
        ]);

        TypeService::create([
            'name' => 'Instalación',
        ]);
    }
}
