<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'title_ar',
        'description',
        'description_ar',
        'image',
        'status',
        'scheduled_date',
        'scheduled_time',
        'is_approved',
        'approved_at',
        'client_approved',
        'client_approved_at',
        'admin_approved',
        'admin_approved_at',
        'marketer_approved',
        'marketer_approved_at',
        'approved_by_client_id',
        'approved_by_admin_id',
        'approved_by_marketer_id',
        'client_id',
        'product_order_id',
        'strategy_work_id',
        'created_by_id',
        'created_by_type',
        'updated_by_id',
        'updated_by_type',
    ];

    protected $casts = [
        'is_approved' => 'boolean',
        'approved_at' => 'datetime',
        'client_approved' => 'boolean',
        'client_approved_at' => 'datetime',
        'admin_approved' => 'boolean',
        'admin_approved_at' => 'datetime',
        'marketer_approved' => 'boolean',
        'marketer_approved_at' => 'datetime',
        'scheduled_date' => 'date:Y-m-d',
    ];

    /**
     * Get the client who will approve the post
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Get the creator (Admin or Marketer)
     */
    public function createdBy()
    {
        return $this->morphTo('created_by');
    }

    /**
     * Get the last editor
     */
    public function updatedBy()
    {
        return $this->morphTo('updated_by');
    }

    /**
     * Get all feedbacks for this post
     */
    public function feedbacks()
    {
        return $this->hasMany(PostFeedback::class)->orderBy('created_at', 'desc');
    }

    /**
     * Check if post can be edited
     */
    public function canBeEdited()
    {
        // Post cannot be edited if ANY party has approved it
        return !$this->client_approved && !$this->admin_approved && !$this->marketer_approved;
    }

    /**
     * Check if feedbacks can be added
     */
    public function canReceiveFeedback()
    {
        // Post cannot receive feedback if ANY party has approved it
        return !$this->client_approved && !$this->admin_approved && !$this->marketer_approved;
    }

    /**
     * Get the product order this post belongs to
     */
    public function productOrder()
    {
        return $this->belongsTo(ProductOrder::class);
    }

    /**
     * Get the strategy work this post belongs to
     */
    public function strategyWork()
    {
        return $this->belongsTo(StrategyWork::class);
    }

    /**
     * Get the team members for this post
     */
    public function teamMembers()
    {
        return $this->hasMany(PostTeamMember::class);
    }

    /**
     * Approve post by client
     */
    public function approveByClient($clientId)
    {
        $this->update([
            'client_approved' => true,
            'client_approved_at' => now(),
            'approved_by_client_id' => $clientId,
            'is_approved' => true,
            'approved_at' => $this->approved_at ?? now(),
            'status' => 'in_review',
        ]);
        
        $this->refresh();
        $this->checkFullApproval();
    }

    /**
     * Approve post by admin
     */
    public function approveByAdmin($adminId)
    {
        $this->update([
            'admin_approved' => true,
            'admin_approved_at' => now(),
            'approved_by_admin_id' => $adminId,
            'is_approved' => true,
            'approved_at' => $this->approved_at ?? now(),
            'status' => 'in_review',
        ]);
        
        $this->refresh();
        $this->checkFullApproval();
    }

    /**
     * Approve post by marketer
     */
    public function approveByMarketer($marketerId)
    {
        $this->update([
            'marketer_approved' => true,
            'marketer_approved_at' => now(),
            'approved_by_marketer_id' => $marketerId,
            'is_approved' => true,
            'approved_at' => $this->approved_at ?? now(),
            'status' => 'in_review',
        ]);
        
        $this->refresh();
        $this->checkFullApproval();
    }

    /**
     * Check if all required approvals are done and mark as fully approved
     */
    protected function checkFullApproval()
    {
        if ($this->client_approved && $this->admin_approved && $this->marketer_approved) {
            $this->update([
                'status' => 'approved',
            ]);
        }
    }

    /**
     * Check if post is fully approved by all parties
     */
    public function isFullyApproved()
    {
        return $this->client_approved && $this->admin_approved && $this->marketer_approved;
    }

    /**
     * Get approval status summary
     */
    public function getApprovalStatus()
    {
        return [
            'client_approved' => $this->client_approved,
            'client_approved_at' => $this->client_approved_at,
            'admin_approved' => $this->admin_approved,
            'admin_approved_at' => $this->admin_approved_at,
            'marketer_approved' => $this->marketer_approved,
            'marketer_approved_at' => $this->marketer_approved_at,
            'is_fully_approved' => $this->isFullyApproved(),
        ];
    }
}
