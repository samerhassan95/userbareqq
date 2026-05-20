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
            'client' => [
                'id' => $this->client_id,
                'name' => optional($this->client)->name,
                'email' => optional($this->client)->email,
            ],
            'product' => [
                'id' => $this->product_id,
                'name' => optional($this->product)->name,
                'image' => $this->product && $this->product->image ? asset($this->product->image) : null,
                'role' => $this->product_role,
                'role_label' => $this->product_role === 'one_time' ? 'One Time Purchase' : 'Strategy Subscription',
            ],
            'selected_feature' => $this->feature_id ? [
                'id' => $this->feature_id,
                'name' => $this->feature_name ?? optional($this->feature)->name,
                'price' => optional($this->feature)->price ?? 0,
            ] : null,
            'duration' => $this->duration,
            'duration_label' => $this->duration ? ucfirst($this->duration) : null,
            'total_price' => (float) $this->total_price,
            'currency' => 'EGP',
            'status' => $this->status,
            'status_label' => ucfirst(str_replace('_', ' ', $this->status)),
            'payment' => [
                'invoice_id' => $this->invoice_id,
                'payment_status' => optional($this->invoice)->status ?? 'pending',
                'payment_proof' => $this->invoice && $this->invoice->payment_proof ? asset($this->invoice->payment_proof) : null,
            ],
            'subscription' => $this->subscription_id ? [
                'id' => $this->subscription_id,
                'status' => optional($this->subscription)->status,
                'starts_at' => $this->subscription ? optional($this->subscription->starts_at)->format('Y-m-d H:i:s') : null,
                'expires_at' => $this->subscription ? optional($this->subscription->expires_at)->format('Y-m-d H:i:s') : null,
            ] : null,
            'deliverable_url' => $this->deliverable_url ? asset($this->deliverable_url) : null,
            'has_deliverable' => !empty($this->deliverable_url),
            'admin_notes' => $this->admin_notes,
            'created_at' => optional($this->created_at)->format('Y-m-d H:i:s'),
            'updated_at' => optional($this->updated_at)->format('Y-m-d H:i:s'),
        ];
    }
}
