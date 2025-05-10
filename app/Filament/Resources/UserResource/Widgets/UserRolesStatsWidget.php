<?php

namespace App\Filament\Resources\UserResource\Widgets;

use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class UserRolesStatsWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        $roleNames = DB::table('roles')->pluck('name');

        $rolesToShow = [
            'tecnico' => [
                'alternativeNames' => ['Tecnico', 'técnico', 'tecnicos', 'técnicos'],
                'label' => 'Técnicos',
                'description' => 'Usuarios con rol de técnico',
                'icon' => 'heroicon-o-user-group',
                'color' => 'success',
            ],
            'cliente' => [
                'alternativeNames' => ['Cliente', 'clientes', 'Clientes'],
                'label' => 'Clientes',
                'description' => 'Usuarios con rol de cliente',
                'icon' => 'heroicon-o-user-group',
                'color' => 'success',
            ],
        ];

        $stats = [];

        foreach ($rolesToShow as $roleBase => $config) {
            $exactRoleName = $roleBase;

            if (!$roleNames->contains($roleBase)) {
                foreach ($config['alternativeNames'] as $alt) {
                    if ($roleNames->contains($alt)) {
                        $exactRoleName = $alt;
                        break;
                    }
                }
            }

            $count = $roleNames->contains($exactRoleName) ? User::role($exactRoleName)->count() : 0;

            $stats[] = Stat::make($config['label'], $count)
                ->description($config['description'])
                ->descriptionIcon($config['icon'])
                ->color($config['color']);
        }

        return $stats;
    }
}
