<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Resources\SliderResource;
use App\Repositories\SliderRepositoryInterface;
use Illuminate\Http\JsonResponse;

class SliderController extends Controller
{
    public function __construct(
        private SliderRepositoryInterface $sliderRepository
    ) {}

    public function index(): JsonResponse
    {
        $sliders = $this->sliderRepository->all();
        
        return response()->json([
            'status' => true,
            'message' => __('messages.sliders_retrieved_successfully'),
            'data' => SliderResource::collection($sliders),
        ]);
    }

    public function show($id): JsonResponse
    {
        $slider = $this->sliderRepository->findById($id);
        
        return response()->json([
            'status' => true,
            'message' => __('messages.slider_retrieved_successfully'),
            'data' => new SliderResource($slider),
        ]);
    }
}
