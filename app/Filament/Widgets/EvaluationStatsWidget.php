<?php

namespace App\Filament\Widgets;

use App\Models\Evaluation;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class EvaluationStatsWidget extends BaseWidget
{
    protected ?string $heading = 'Resumen de Evaluaciones';
    protected static ?int $sort = 1;
    protected static ?string $pollingInterval = '60s';

    protected function getStats(): array
    {
        $evaluationCounts = Evaluation::select('rating', DB::raw('count(*) as total'))
            ->groupBy('rating')
            ->pluck('total', 'rating')
            ->toArray();

        $totalEvaluations = array_sum($evaluationCounts);

        $stats = [];

        for ($i = 1; $i <= 5; $i++) {
            $count = $evaluationCounts[$i] ?? 0;
            $percentage = $totalEvaluations > 0 ? round(($count / $totalEvaluations) * 100, 1) : 0;

            $label = match($i) {
                1 => '★ (Muy malo)',
                2 => '★★ (Malo)',
                3 => '★★★ (Regular)',
                4 => '★★★★ (Bueno)',
                5 => '★★★★★ (Excelente)',
            };

            $color = match($i) {
                1 => 'danger',
                2 => 'warning',
                3 => 'primary',
                4 => 'success',
                5 => 'success',
            };

            $stats[] = Stat::make("Calificación de servicios $label", "$count")
                ->description("$percentage% del total")
                ->descriptionIcon('heroicon-m-star')
                ->color($color)
                ->chart([
                    $percentage, 100 - $percentage
                ]);
        }

        return $stats;
    }
}
