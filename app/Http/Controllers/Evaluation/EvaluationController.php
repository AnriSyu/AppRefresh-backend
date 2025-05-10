<?php

namespace App\Http\Controllers\Evaluation;

use App\Http\Controllers\Controller;
use App\Helpers\Traits\ResponseTrait;
use App\Services\Evaluation\EvaluationServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EvaluationController extends Controller
{
    use ResponseTrait;

    /**
     * @var EvaluationServiceInterface
     */
    protected $evaluationService;

    /**
     * EvaluationController constructor.
     *
     * @param EvaluationServiceInterface $evaluationService
     */
    public function __construct(EvaluationServiceInterface $evaluationService)
    {
        $this->evaluationService = $evaluationService;
    }

    /**
     * Display evaluations for a specific service.
     *
     * @param int $serviceId
     * @return JsonResponse
     */
    public function getServiceEvaluations(int $serviceId): JsonResponse
    {
        $ratingData = $this->evaluationService->getServiceRatingData($serviceId);

        return $this->successJsonResponse($ratingData);
    }

    public function createEvaluation(Request $request): JsonResponse
    {
        $data = $request->validate([
            'service_id' => 'required|integer|exists:services,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:255',
        ]);
        $evaluation = $this->evaluationService->createEvaluation($data);
        return $this->successJsonResponse([
            'data' => $evaluation,
            'message' => 'Evaluacion creada con exito'
        ]);

    }

}
