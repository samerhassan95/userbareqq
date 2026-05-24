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
                    'starts_at' => $this->subscription->starts_at ? $this->subscription->starts_at->format('Y-m-d') : null,
                    'expires_at' => $this->subscription->expires_at ? $this->subscription->expires_at->format('Y-m-d') : null,
                ];
            }

            $data = [
                'id' => $this->id,
                'order_number' => 'ORD-' . str_pad($this->id, 6, '0', STR_PAD_LEFT),
                'client' => [
                    'id' => $this->client_id,
                    'name' => $this->client ? $this->client->name : 'N/A',
                    'email' => $this->client ? $this->client->email : 'N/A',
                ],
                'product' => [
                    'id' => $this->product_id,
                    'name' => $this->product ? \App\Helpers\TranslationHelper::getTranslatedField($this->product, 'name') : 'N/A',
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

            // Add strategy-specific fields if product_role is 'strategy'
            if ($this->product_role === 'strategy') {
                $data['strategy_id'] = $this->subscription_id; // Use subscription_id as strategy_id
                $data['starts_at'] = $subscriptionData['starts_at'] ?? null;
                $data['ends_at'] = $subscriptionData['expires_at'] ?? null;
                
                // Add strategy tips from product
                if ($this->product && $this->product->strategyTips) {
                    $data['strategy_tips'] = $this->product->strategyTips->map(function ($tip) {
                        return [
                            'id' => $tip->id,
                            'text' => \App\Helpers\TranslationHelper::getTranslatedField($tip, 'text'),
                            'platforms' => $tip->platforms ?? [],
                        ];
                    })->toArray();
                } else {
                    $data['strategy_tips'] = [];
                }

                // Add team discussion
                $teamMembers = $this->teamMembers()->with('member')->get();
                $data['team_discussion'] = [
                    'discussion_id' => $this->id, // Use order ID as discussion ID for now
                    'team_count' => $teamMembers->count(),
                    'title' => 'Strategy Team Chat',
                    'team' => $teamMembers->map(function ($teamMember) {
                        $member = $teamMember->member;
                        if (!$member) return null;

                        return [
                            'id' => $member->id,
                            'name' => $member->name ?? $member->username ?? 'N/A',
                            'avatar' => isset($member->photo) && $member->photo ? asset($member->photo) : null,
                            'type' => class_basename($teamMember->member_type),
                            'role' => $teamMember->role ?? strtolower(class_basename($teamMember->member_type)),
                        ];
                    })->filter()->values()->toArray(),
                ];

                // Add works preview (first 3 upcoming works)
                $upcomingWorks = $this->strategyWorks()
                    ->where('scheduled_date', '>=', now()->format('Y-m-d'))
                    ->orderBy('scheduled_date')
                    ->orderBy('scheduled_time')
                    ->limit(3)
                    ->get();

                $data['works_preview'] = $upcomingWorks->map(function ($work) {
                    // Get posts for this work
                    $posts = $work->posts()->get();
                    
                    return [
                        'id' => $work->id,
                        'title' => \App\Helpers\TranslationHelper::getTranslatedField($work, 'title'),
                        'description' => \App\Helpers\TranslationHelper::getTranslatedField($work, 'description'),
                        'scheduled_date' => $work->scheduled_date->format('Y-m-d'),
                        'scheduled_time' => $work->scheduled_time,
                        'platforms' => $work->platforms ?? [],
                        'status' => $work->status,
                        'post_type' => $work->post_type,
                        'posts' => $posts->map(function ($post) {
                            return [
                                'id' => $post->id,
                                'title' => \App\Helpers\TranslationHelper::getTranslatedField($post, 'title'),
                                'image' => $post->image ? asset($post->image) : null,
                                'status' => $post->status,
                                'is_approved' => $post->is_approved,
                            ];
                        })->toArray(),
                        'posts_count' => $posts->count(),
                    ];
                })->toArray();

                // Add all posts for this order
                $allPosts = $this->posts()->orderBy('created_at', 'desc')->get();
                $data['posts'] = $allPosts->map(function ($post) {
                    return [
                        'id' => $post->id,
                        'title' => \App\Helpers\TranslationHelper::getTranslatedField($post, 'title'),
                        'description' => \App\Helpers\TranslationHelper::getTranslatedField($post, 'description'),
                        'image' => $post->image ? asset($post->image) : null,
                        'status' => $post->status,
                        'is_approved' => $post->is_approved,
                        'approved_at' => $post->approved_at ? $post->approved_at->format('Y-m-d H:i:s') : null,
                        'created_at' => $post->created_at ? $post->created_at->format('Y-m-d H:i:s') : null,
                    ];
                })->toArray();
                $data['posts_count'] = $allPosts->count();
            }

            return $data;
        } catch (\Exception $e) {
            \Log::error('ProductOrderResource error: ' . $e->getMessage());
            \Log::error('Order ID: ' . $this->id);
            throw $e;
        }
    }
}
