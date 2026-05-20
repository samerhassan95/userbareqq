<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray($request)
    {
        $addons = $this->addons->map(function ($addon) {
            return [
                'id' => $addon->id,
                'name' => $addon->name,
                'price' => $addon->price,
                'icon' => $addon->icon ? asset($addon->icon) : null,
                'description' => $addon->description,
                'feature_type' => $addon->feature_type ?? 'general',
            ];
        });

        $totalPrice = $this->price + $addons->sum('price');

        $data = [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'note' => $this->note,
            'image' => $this->image ? asset($this->image) : null,
            'background_image' => $this->background_image ? asset('uploads/products/' . $this->background_image) : null,
            'type' => $this->type,
            'product_role' => $this->product_role ?? 'one_time',
            'category_name' => $this->category->name ?? null,
        ];

        // Add role-specific fields
        if ($this->product_role === 'strategy') {
            $data['monthly_price'] = $this->monthly_price;
            $data['yearly_price'] = $this->yearly_price;
            $data['strategy_tips'] = ProductStrategyTipResource::collection($this->strategyTips);
        } else {
            // One-time product
            $data['features'] = $addons;
            $data['addons'] = $addons; // Keep for backward compatibility
            $data['total_price'] = $totalPrice;
        }

        $data['attachments'] = $this->attachments->map(function ($attachment) {
            return [
                'file_path' => asset($attachment->file_path),
            ];
        });

        $data['media'] = $this->media->map(function ($media) {
            return [
                'id' => $media->id,
                'file_path' => asset($media->file_path),
                'type' => $media->type,
            ];
        });

        return $data;
    }
}
