<?php

namespace App\Services\Evaluation;

use App\Models\Evaluation;
use Illuminate\Database\Eloquent\Collection;

interface EvaluationServiceInterface
{
    /**
     * Get evaluations for a service
     *
     * @param int $serviceId
     * @return Collection
     */
    public function getServiceEvaluations(int $serviceId): Collection;

    /**
     * Create a new evaluation
     *
     * @param array $data
     * @return Evaluation
     */
    public function createEvaluation(array $data): Evaluation;

    /**
     * Get service evaluations with rating data
     *
     * @param int $serviceId
     * @return array
     */
    public function getServiceRatingData(int $serviceId): array;
}
