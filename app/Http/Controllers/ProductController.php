<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BaseController;
use App\Http\Requests\ProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\attachment;
use App\Models\Client;
use App\Models\NotificationTemplate;
use App\Models\Product;
use App\Models\Slider;
use App\Repositories\NotificationRepository;
use App\Repositories\ProductRepositoryInterface;
use App\Services\FirebaseService;
use App\Services\ImageService;
use Illuminate\Http\Request;

class ProductController extends BaseController
{
    private $repository;
    private $firebaseService;
    private $notificationRepository;

    public function __construct(ProductRepositoryInterface $repository, FirebaseService $firebaseService, NotificationRepository $notificationRepository)
    {
        parent::__construct($repository);
        $this->firebaseService = $firebaseService;
        $this->notificationRepository = $notificationRepository;
    }

    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'price' => 'nullable|numeric',
                'note' => 'nullable|string',
                'type' => 'required|in:subscription,one_time',
                'product_role' => 'required|in:one_time,strategy',
                'monthly_price' => 'required_if:product_role,strategy|nullable|numeric',
                'yearly_price' => 'required_if:product_role,strategy|nullable|numeric',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'attachments.*' => 'file|max:10240',
                'addons' => 'array',
                'addons.*' => 'exists:addons,id',
                'media.*' => 'file|max:10240', 
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $e->errors(),
            ], 422);
        }
    
        try {
            $productData = collect($validatedData)->except(['attachments', 'image', 'addons', 'media'])->toArray();
        
            if ($request->hasFile('image')) {
                $imagePath = ImageService::upload($request->file('image'), 'product_images');
                $productData['image'] = $imagePath;
            }
        
            $product = Product::create($productData);
        
            if ($request->hasFile('media')) {
                foreach ($request->file('media') as $file) {
                    $path = ImageService::upload($file, 'product_media');
                    $product->media()->create(['file_path' => $path, 'type' => 'image']); 
                }
            }
        
            // Handle addons (features for one_time products)
            if (!empty($validatedData['addons'])) {
                $product->addons()->attach($validatedData['addons']);
            }
        
            // Send notifications (skip if fails)
            try {
                $template = NotificationTemplate::where('type', 'new_product')->first();
            
                if ($template) {
                    $title = $template->title;
                    $message = str_replace('{product_name}', $product->name, $template->message);
                
                    $clients = Client::whereNotNull('device_token')->get();
                
                    if ($clients->isNotEmpty()) {
                        foreach ($clients as $client) {
                            $this->firebaseService->sendNotification($client->device_token, $title, $message, [
                                'notification_type' => $template->type
                            ]);
                            $this->notificationRepository->createNotification($client, $title, $message, $client->device_token, $template->type);
                        }
                    }
                }
            } catch (\Exception $e) {
                \Log::warning('Failed to send product notification: ' . $e->getMessage());
            }
        
            return response()->json([
                'status' => true,
                'message' => 'Product created successfully',
                'data' => new ProductResource($product->load(['addons', 'media', 'strategyTips', 'category'])),
            ], 201);
        } catch (\Exception $e) {
            \Log::error('Product creation failed: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Failed to create product',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    public function update(Request $request, $id)
    {
        $product = Product::find($id);
    
        if (!$product) {
            return response()->json(['message' => 'Product not found.'], 404);
        }
    
        $validatedData = $request->validate([
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'price' => 'nullable|numeric',
            'note' => 'nullable|string',
            'type' => 'nullable|in:subscription,one_time',
            'product_role' => 'nullable|in:one_time,strategy',
            'monthly_price' => 'nullable|numeric',
            'yearly_price' => 'nullable|numeric',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'attachments.*' => 'file|max:10240',
            'addons' => 'array',
            'addons.*' => 'exists:addons,id',
            'media.*' => 'file|max:10240', 
        ]);
    
        $productData = collect($validatedData)->except(['attachments', 'image', 'addons', 'media'])->toArray();
    
        if ($request->hasFile('image')) {
            if ($product->image) {
                ImageService::delete($product->image);
            }
            $imagePath = ImageService::upload($request->file('image'), 'product_images');
            $productData['image'] = $imagePath;
        }
    
        $product->update($productData);
    
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = ImageService::upload($file, 'attachments');
                $product->attachments()->create(['file_path' => $path]);
            }
        }
    
        if ($request->hasFile('media')) {
            foreach ($request->file('media') as $file) {
                $path = ImageService::upload($file, 'product_media');
                $product->media()->create(['file_path' => $path, 'type' => 'image']); 
            }
        }
    
        if (isset($validatedData['addons'])) {
            $product->addons()->sync($validatedData['addons']);
        }
    
        return response()->json(new ProductResource($product->load(['attachments', 'addons', 'media', 'strategyTips'])), 200);
    }


private function resolveImage($image, string $device = 'web'): ?string
{
    if (!$image) return null;

    if ($device === 'mobile') {
        $info = pathinfo($image);
        $image = $info['dirname'] . '/' . $info['filename'] . '-mobile.' . $info['extension'];
    }

    return asset($image);
}

public function show($id)
{
    try {
        $device = request()->input('device', 'web');

        $product = Product::with(['addons', 'strategyTips', 'category'])->find($id);

        if (!$product) {
            return response()->json([
                'status' => false,
                'message' => 'Product not found.',
            ], 404);
        }

        $data = [
            'id'               => $product->id,
            'name'             => $product->name,
            'description'      => $this->localizedText($product->description),
            'note'             => $product->note,
            'image'            => $this->resolveImage($product->image, $device),
            'background_image' => $product->background_image,
            'type'             => $product->type,
            'product_role'     => $product->product_role ?? 'one_time',
            'category_name'    => $product->category->name ?? null,
            'created_at'       => $product->created_at,
            'updated_at'       => $product->updated_at,
        ];

        // Add role-specific fields
        if ($product->product_role === 'strategy') {
            $data['monthly_price'] = (float) $product->monthly_price;
            $data['yearly_price'] = (float) $product->yearly_price;
            $data['strategy_tips'] = $product->strategyTips->map(function ($tip) {
                return [
                    'id'        => $tip->id,
                    'text'      => $tip->text,
                    'platforms' => $tip->platforms ?? [],
                ];
            });
        } else {
            // One-time product
            $data['price'] = (float) $product->price;
            $data['features'] = $product->addons->map(function ($addon) {
                return [
                    'id'           => $addon->id,
                    'name'         => $addon->name,
                    'price'        => (float) $addon->price,
                    'description'  => $this->localizedText($addon->description),
                    'feature_type' => 'general',
                ];
            });
        }

        return response()->json([
            'status' => true,
            'data'   => $data,
        ], 200);
    } catch (\Exception $e) {
        \Log::error('Product show failed: ' . $e->getMessage());
        return response()->json([
            'status' => false,
            'message' => 'Failed to retrieve product',
            'error' => $e->getMessage(),
        ], 500);
    }
}

public function deleteMedia($productId, $mediaId)
{
    $product = Product::find($productId);

    if (!$product) {
        return response()->json(['message' => 'Product not found.'], 404);
    }

    $media = $product->media()->find($mediaId);

    if (!$media) {
        return response()->json(['message' => 'Media not found.'], 404);
    }

    ImageService::delete($media->file_path);
    $media->delete();

    return response()->json(['message' => 'Media deleted successfully.'], 200);
}

public function ourProducts(Request $request)
{
    $search = $request->search;
    $device = $request->input('device', 'web');
    $productRole = $request->input('product_role'); // Filter by product_role

    $products = Product::with(['category:id,name', 'addons', 'strategyTips'])
        ->when($search, function ($q) use ($search) {
            $q->where('name', 'LIKE', "%{$search}%");
        })
        ->when($productRole, function ($q) use ($productRole) {
            $q->where('product_role', $productRole);
        })
        ->select('id', 'name', 'category_id', 'price', 'description', 'background_image', 'type', 'image', 'product_role', 'monthly_price', 'yearly_price')
        ->get()
        ->map(function ($item) use ($device) {
            $data = [
                'id'               => $item->id,
                'name'             => $item->name,
                'price'            => $item->price,
                'description'      => $this->localizedText($item->description),
                'image'            => $this->resolveImage($item->image, $device),
                'category_name'    => $item->category->name ?? null,
                'background_image' => $item->background_image,
                'type'             => $item->type,
                'product_role'     => $item->product_role ?? 'one_time',
            ];

            // Add role-specific fields
            if ($item->product_role === 'strategy') {
                $data['monthly_price'] = $item->monthly_price;
                $data['yearly_price'] = $item->yearly_price;
            }

            return $data;
        });

    $sliders = Slider::with([
            'product' => function ($q) {
                $q->select('id', 'name', 'category_id', 'price', 'description')
                  ->with(['category:id,name']);
            }
        ])
        ->get()
        ->map(function ($slider) {
            $product = $slider->product;

            $firstImage = null;
            if (is_array($slider->image) && !empty($slider->image)) {
                $firstImage = url($slider->image[0]);
            } elseif (is_string($slider->image) && !empty($slider->image)) {
                $firstImage = url($slider->image);
            }

            return [
                'id'      => $slider->id,
                'image'   => $firstImage,
                'product' => $product ? [
                    'id'   => $product->id,
                    'name' => $product->name,
                ] : null,
            ];
        });

    return response()->json([
        'status'   => true,
        'products' => $products,
        'sliders'  => $sliders,
    ]);
}
private function localizedText(?string $text): ?string
{
    if (!$text) {
        return null;
    }

    $lang = request()->query('lang')
        ?? request()->header('Accept-Language', 'en');

    if (!str_contains($text, '|')) {
        return $text;
    }

    [$ar, $en] = array_map('trim', explode('|', $text, 2));

    return strtolower($lang) === 'ar' ? $ar : $en;
}


}
