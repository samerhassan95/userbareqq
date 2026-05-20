<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductOrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_number' => 'ORD-' . str_pad($this->id, 6, '0', STR_PAD_LEFT),
            'product' => [
                'id' => $this->product_id,
                'name' => $this->product->name ?? 'N/A',
                'role' => $this->product_role,
                'role_label' => $this->product_role === 'one_time' ? 'One Time Purchase' : 'Strategy Subscription',
            ],
            'selected_feature' => $this->feature_id ? [
                'id' => $this->feature_id,
                'name' => $this->feature_name,
            ] : null,
            'duration' => $this->duration,
            'duration_label' => $this->duration ? ucfirst($this->duration) : null,
            'total_price' => (float) $this->total_price,
            'currency' => 'EGP',
            'status' => $this->status,
            'status_label' => ucfirst(str_replace('_', ' ', $this->status)),
            'payment' => [
                'invoice_id' => $this->invoice_id,
                'payment_status' => $this->invoice ? $this->invoice->status : 'pending',
            ],
            'subscription_id' => $this->subscription_id,
            'deliverable_url' => $this->deliverable_url,
            'has_deliverable' => !empty($this->deliverable_url),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
