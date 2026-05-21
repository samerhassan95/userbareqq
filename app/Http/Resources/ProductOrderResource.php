<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductOrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        try {
            // Build subscription data safely
            $subscriptionData = null;
            if ($this->subscription_id && $this->subscription) {
                $subscriptionData = [
                    'id' => $this->subscription_id,
                    'status' => $this->subscription->status ?? 'pending',
                    'starts_at' => $this->subscription->starts_at ? $this->subscription->starts_at->format('Y-m-d H:i:s') : null,
                    'expires_at' => $this->subscription->expires_at ? $this->subscription->expires_at->format('Y-m-d H:i:s') : null,
                ];
            }

            return [
                'id' => $this->id,
                'order_number' => 'ORD-' . str_pad($this->id, 6, '0', STR_PAD_LEFT),
                'client' => [
                    'id' => $this->client_id,
                    'name' => $this->client ? $this->client->name : 'N/A',
                    'email' => $this->client ? $this->client->email : 'N/A',
                ],
                'product' => [
                    'id' => $this->product_id,
                    'name' => $this->product ? $this->product->name : 'N/A',
                    'image' => ($this->product && $this->product->image) ? asset($this->product->image) : null,
                    'role' => $this->product_role,
                    'role_label' => $this->product_role === 'one_time' ? 'One Time Purchase' : 'Strategy Subscription',
                ],
                'duration' => $this->duration,
                'duration_label' => $this->duration ? ucfirst($this->duration) : null,
                'total_price' => (float) $this->total_price,
                'currency' => 'EGP',
                'status' => $this->status,
                'status_label' => ucfirst(str_replace('_', ' ', $this->status)),
                'payment' => [
                    'invoice_id' => $this->invoice_id,
                    'payment_status' => $this->invoice ? $this->invoice->status : 'pending',
                    'payment_proof' => ($this->invoice && isset($this->invoice->payment_proof)) ? asset($this->invoice->payment_proof) : null,
                ],
                'subscription' => $subscriptionData,
                'deliverable_url' => $this->deliverable_url ? asset($this->deliverable_url) : null,
                'has_deliverable' => !empty($this->deliverable_url),
                'admin_notes' => $this->admin_notes,
                'created_at' => $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null,
                'updated_at' => $this->updated_at ? $this->updated_at->format('Y-m-d H:i:s') : null,
            ];
        } catch (\Exception $e) {
            \Log::error('ProductOrderResource error: ' . $e->getMessage());
            \Log::error('Order ID: ' . $this->id);
            throw $e;
        }
    }
}
