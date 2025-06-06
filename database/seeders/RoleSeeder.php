<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{

    public function run(): void
    {
        $adminRole = Role::create(['name' => 'Administrador']);
        $tecnicoRole = Role::create(['name' => 'Tecnico']);
        $clienteRole = Role::create(['name' => 'Cliente']);
    }
}
