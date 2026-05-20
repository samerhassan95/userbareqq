<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductOrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'order_id' => $this->id,
            'product_id' => $this->product_id,
            'product_name' => $this->product->name ?? null,
            'product_role' => $this->product_role,
            'feature_id' => $this->feature_id,
            'feature_name' => $this->feature_name,
            'duration' => $this->duration,
            'total_price' => $this->total_price,
            'status' => $this->status,
            'invoice_id' => $this->invoice_id,
            'subscription_id' => $this->subscription_id,
            'deliverable_url' => $this->deliverable_url,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
