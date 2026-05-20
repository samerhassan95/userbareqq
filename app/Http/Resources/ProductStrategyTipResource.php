<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductStrategyTipResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'text' => $this->text,
            'platforms' => $this->platforms ?? [],
            'platform_names' => collect($this->platforms ?? [])->map(fn($p) => ucfirst($p))->toArray(),
            'sort_order' => $this->sort_order ?? 0,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
