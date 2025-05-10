<?php

namespace App\Repositories\Evaluation;

use App\Models\Evaluation;
use Illuminate\Database\Eloquent\Collection;

interface EvaluationRepositoryInterface
{
    /**
     * Get evaluations by service ID
     *
     * @param int $serviceId
     * @return Collection
     */
    public function getEvaluationsByServiceId(int $serviceId): Collection;

    /**
     * Create new evaluation
     *
     * @param array $data
     * @return Evaluation
     */
    public function createEvaluation(array $data): Evaluation;

    /**
     * Get service average rating
     *
     * @param int $serviceId
     * @return float
     */
    public function getServiceAverageRating(int $serviceId): float;
}
