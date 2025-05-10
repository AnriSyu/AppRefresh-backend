<?php

namespace App\Repositories\Evaluation;

use App\Models\Evaluation;
use App\Repositories\Evaluation\EvaluationRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class EvaluationRepository implements EvaluationRepositoryInterface
{
    /**
     * @var Evaluation
     */
    protected $evaluation;

    /**
     * EvaluationRepository constructor.
     *
     * @param Evaluation $evaluation
     */
    public function __construct(Evaluation $evaluation)
    {
        $this->evaluation = $evaluation;
    }

    /**
     * Get evaluations by service ID
     *
     * @param int $serviceId
     * @return Collection
     */
    public function getEvaluationsByServiceId(int $serviceId): Collection
    {
        return $this->evaluation->where('service_id', $serviceId)->get();
    }

    /**
     * Create new evaluation
     *
     * @param array $data
     * @return Evaluation
     */
    public function createEvaluation(array $data): Evaluation
    {
        return $this->evaluation->create($data);
    }

    /**
     * Get service average rating
     *
     * @param int $serviceId
     * @return float
     */
    public function getServiceAverageRating(int $serviceId): float
    {
        return $this->evaluation
            ->where('service_id', $serviceId)
            ->avg('rating') ?? 0;
    }
}
