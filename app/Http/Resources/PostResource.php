<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => \App\Helpers\TranslationHelper::getTranslatedField($this, 'title'),
            'description' => \App\Helpers\TranslationHelper::getTranslatedField($this, 'description'),
            'image' => $this->image ? asset($this->image) : null,
            'status' => $this->status,
            'is_approved' => $this->is_approved,
            'approved_at' => $this->approved_at ? $this->approved_at->format('Y-m-d H:i:s') : null,
            
            // Client info
            'client' => $this->client ? [
                'id' => $this->client->id,
                'name' => $this->client->name,
                'email' => $this->client->email,
            ] : null,
            
            // Product order info
            'product_order' => $this->productOrder ? [
                'id' => $this->productOrder->id,
                'order_number' => 'ORD-' . str_pad($this->productOrder->id, 6, '0', STR_PAD_LEFT),
                'product_name' => $this->productOrder->product ? \App\Helpers\TranslationHelper::getTranslatedField($this->productOrder->product, 'name') : null,
            ] : null,
            
            // Strategy work info
            'strategy_work' => $this->strategyWork ? [
                'id' => $this->strategyWork->id,
                'title' => \App\Helpers\TranslationHelper::getTranslatedField($this->strategyWork, 'title'),
                'scheduled_date' => $this->strategyWork->scheduled_date->format('Y-m-d'),
                'platforms' => $this->strategyWork->platforms ?? [],
            ] : null,
            
            // Creator info
            'created_by' => $this->createdBy ? [
                'id' => $this->createdBy->id,
                'name' => $this->createdBy->name ?? $this->createdBy->username ?? 'N/A',
                'type' => class_basename($this->created_by_type),
            ] : null,
            
            // Feedbacks
            'feedbacks' => $this->feedbacks->map(function ($feedback) {
                return [
                    'id' => $feedback->id,
                    'comment' => $feedback->comment,
                    'created_at' => $feedback->created_at->format('Y-m-d H:i:s'),
                    'created_by' => [
                        'id' => $feedback->createdBy->id ?? null,
                        'name' => $feedback->createdBy->name ?? 'N/A',
                        'type' => class_basename($feedback->created_by_type),
                    ],
                ];
            })->toArray(),
            'feedbacks_count' => $this->feedbacks->count(),
            
            'created_at' => $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null,
            'updated_at' => $this->updated_at ? $this->updated_at->format('Y-m-d H:i:s') : null,
        ];
    }
}
