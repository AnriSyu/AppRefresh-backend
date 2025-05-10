<?php

namespace App\Services\Evaluation;

use App\Models\Evaluation;
use App\Helpers\Traits\ResponseTrait;
use App\Repositories\Evaluation\EvaluationRepositoryInterface;
use App\Services\Evaluation\EvaluationServiceInterface;
use Illuminate\Database\Eloquent\Collection;

class EvaluationService implements EvaluationServiceInterface
{
    use ResponseTrait;

    /**
     * @var EvaluationRepositoryInterface
     */
    protected $evaluationRepository;

    /**
     * EvaluationService constructor.
     *
     * @param EvaluationRepositoryInterface $evaluationRepository
     */
    public function __construct(EvaluationRepositoryInterface $evaluationRepository)
    {
        $this->evaluationRepository = $evaluationRepository;
    }

    /**
     * Get evaluations for a service
     *
     * @param int $serviceId
     * @return Collection
     */
    public function getServiceEvaluations(int $serviceId): Collection
    {
        return $this->evaluationRepository->getEvaluationsByServiceId($serviceId);
    }

    /**
     * Create a new evaluation
     *
     * @param array $data
     * @return Evaluation
     */
    public function createEvaluation(array $data): Evaluation
    {
        return $this->evaluationRepository->createEvaluation($data);
    }

    /**
     * Get service evaluations with rating data
     *
     * @param int $serviceId
     * @return array
     */
    public function getServiceRatingData(int $serviceId): array
    {
        $evaluations = $this->getServiceEvaluations($serviceId);
        $averageRating = $this->evaluationRepository->getServiceAverageRating($serviceId);

        return [
            'service_id' => $serviceId,
            'average_rating' => $averageRating,
            'comments' => $evaluations->pluck('comment'),
            'total_evaluations' => $evaluations->count(),
        ];
    }   
}

