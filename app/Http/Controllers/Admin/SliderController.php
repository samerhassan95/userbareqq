<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSliderRequest;
use App\Http\Requests\Admin\UpdateSliderRequest;
use App\Http\Resources\SliderResource;
use App\Repositories\SliderRepositoryInterface;
use App\Services\ImageService;
use Illuminate\Http\JsonResponse;

class SliderController extends Controller
{
    public function __construct(
        private SliderRepositoryInterface $sliderRepository,
        private ImageService $imageService
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

    public function store(StoreSliderRequest $request): JsonResponse
    {
        $data = $request->validated();
        
        if ($request->hasFile('image')) {
            $data['image'] = $this->imageService->uploadImage($request->file('image'), 'sliders');
        }
        
        $slider = $this->sliderRepository->create($data);
        
        return response()->json([
            'status' => true,
            'message' => __('messages.slider_created_successfully'),
            'data' => new SliderResource($slider),
        ], 201);
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

    public function update(UpdateSliderRequest $request, $id): JsonResponse
    {
        $data = $request->validated();
        
        $slider = $this->sliderRepository->findById($id);
        
        if ($request->hasFile('image')) {
            // Delete old image
            if ($slider->image) {
                $this->imageService->deleteImage($slider->image);
            }
            $data['image'] = $this->imageService->uploadImage($request->file('image'), 'sliders');
        }
        
        $slider = $this->sliderRepository->update($id, $data);
        
        return response()->json([
            'status' => true,
            'message' => __('messages.slider_updated_successfully'),
            'data' => new SliderResource($slider),
        ]);
    }

    public function destroy($id): JsonResponse
    {
        $slider = $this->sliderRepository->findById($id);
        
        // Delete image
        if ($slider->image) {
            $this->imageService->deleteImage($slider->image);
        }
        
        $this->sliderRepository->delete($id);
        
        return response()->json([
            'status' => true,
            'message' => __('messages.slider_deleted_successfully'),
        ]);
    }
}
