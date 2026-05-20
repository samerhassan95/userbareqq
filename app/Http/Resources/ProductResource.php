<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray($request)
    {
        $data = [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'price' => (float) $this->price,
            'note' => $this->note,
            'image' => $this->image ? asset($this->image) : null,
            'background_image' => $this->background_image ? asset('uploads/products/' . $this->background_image) : null,
            'type' => $this->type,
            'product_role' => $this->product_role ?? 'one_time',
            'category_name' => optional($this->category)->name,
        ];

        // Add role-specific fields
        if ($this->product_role === 'strategy') {
            $data['monthly_price'] = (float) ($this->monthly_price ?? 0);
            $data['yearly_price'] = (float) ($this->yearly_price ?? 0);
            
            if ($this->relationLoaded('strategyTips')) {
                $data['strategy_tips'] = ProductStrategyTipResource::collection($this->strategyTips);
            }
        } else {
            // One-time product - use addons as features
            if ($this->relationLoaded('addons')) {
                $data['features'] = $this->addons->map(function ($addon) {
                    return [
                        'id' => $addon->id,
                        'name' => $addon->name,
                        'price' => (float) $addon->price,
                        'icon' => $addon->icon ? asset($addon->icon) : null,
                        'description' => $addon->description,
                    ];
                });
            }
        }

        if ($this->relationLoaded('attachments')) {
            $data['attachments'] = $this->attachments->map(function ($attachment) {
                return [
                    'id' => $attachment->id,
                    'file_path' => asset($attachment->file_path),
                ];
            });
        }

        if ($this->relationLoaded('media')) {
            $data['media'] = $this->media->map(function ($media) {
                return [
                    'id' => $media->id,
                    'file_path' => asset($media->file_path),
                    'type' => $media->type,
                ];
            });
        }

        return $data;
    }
}
