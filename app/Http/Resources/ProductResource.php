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
            'category' => [
                'id' => $this->category->id ?? null,
                'name' => $this->category->name ?? null,
            ],
        ];

        // Add role-specific fields
        if ($this->product_role === 'strategy') {
            $data['monthly_price'] = (float) $this->monthly_price;
            $data['yearly_price'] = (float) $this->yearly_price;
            $data['strategy_tips'] = ProductStrategyTipResource::collection($this->strategyTips);
            $data['tips_count'] = $this->strategyTips->count();
        } else {
            // One-time product - use addons as features
            $features = $this->addons->map(function ($addon) {
                return [
                    'id' => $addon->id,
                    'name' => $addon->name,
                    'price' => (float) $addon->price,
                    'icon' => $addon->icon ? asset($addon->icon) : null,
                    'description' => $addon->description,
                    'feature_type' => $addon->feature_type ?? 'general',
                ];
            });
            
            $data['features'] = $features;
            $data['features_count'] = $features->count();
            $data['base_price'] = (float) $this->price;
            $data['total_with_all_features'] = (float) ($this->price + $features->sum('price'));
        }

        $data['attachments'] = $this->attachments->map(function ($attachment) {
            return [
                'id' => $attachment->id,
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

        $data['created_at'] = $this->created_at?->format('Y-m-d H:i:s');
        $data['updated_at'] = $this->updated_at?->format('Y-m-d H:i:s');

        return $data;
    }
}
