<?php

namespace App\Filament\Widgets;

use App\Models\Service;
use App\Models\ServiceHistory;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class PendingServicesWidget extends BaseWidget
{
    protected static ?string $pollingInterval = '15s';

    protected function getStats(): array
    {
        $pendingCount = Service::whereHas('history', function ($query) {
            $query->where('status', 'P')
                ->whereIn('id', function ($subquery) {
                    $subquery->selectRaw('MAX(id)')
                        ->from('services_history')
                        ->groupBy('service_id');
                });
        })->count();

        return [
            Stat::make('Servicios pendientes por asignar', $pendingCount)
                ->description('Servicios que requieren un técnico')
                ->descriptionIcon('heroicon-o-exclamation-circle')
                ->color('warning')
                ->chart([7, 4, 5, 6, 5, 7, $pendingCount])
                ->url(route('filament.admin.resources.services.index', [
                    'tableFilters[status][value]' => 'P',
                ])),
        ];
    }
}
