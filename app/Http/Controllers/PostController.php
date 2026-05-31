<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Traits\SendsNotifications;
use Illuminate\Http\Request;

class PostController extends Controller
{
    use SendsNotifications;
    /**
     * Get single post for any role
     */
    public function show($id)
    {
        try {
            $query = Post::with(['createdBy', 'updatedBy', 'client', 'feedbacks.user', 'teamMembers.member', 'strategyWork']);
            
            // If user is client, enforce client_id check
            if (auth()->guard('client')->check()) {
                $query->where('client_id', auth()->guard('client')->id());
            }

            $post = $query->findOrFail($id);

            // Add full image URL
            if ($post->image) {
                $post->image = asset('posts/' . $post->image);
            }

            return response()->json([
                'success' => true,
                'message' => __('messages.post_retrieved_successfully'),
                'data' => $post
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => __('messages.post_not_found')
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('messages.error_occurred'),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Approve post for any allowed role
     */
    public function approve($id)
    {
        try {
            $post = Post::findOrFail($id);
            $userId = auth()->id();

            if (auth()->guard('client')->check()) {
                if ($post->client_id !== $userId) {
                    return response()->json([
                        'success' => false,
                        'message' => __('messages.unauthorized')
                    ], 403);
                }
                if ($post->client_approved) {
                    return response()->json([
                        'success' => false,
                        'message' => __('messages.post_already_approved_by_you') ?? 'Post already approved by you'
                    ], 400);
                }
                $post->approveByClient($userId);
                $message = __('messages.post_approved_by_client_successfully');
            } elseif (auth()->guard('admin')->check()) {
                if ($post->admin_approved) {
                    return response()->json([
                        'success' => false,
                        'message' => __('messages.post_already_approved_by_admin') ?? 'Post already approved by admin'
                    ], 400);
                }
                $post->approveByAdmin($userId);
                $message = __('messages.post_approved_by_admin_successfully');
            } elseif (auth()->guard('marketer')->check()) {
                if ($post->marketer_approved) {
                    return response()->json([
                        'success' => false,
                        'message' => __('messages.post_already_approved_by_marketer') ?? 'Post already approved by marketer'
                    ], 400);
                }
                $post->approveByMarketer($userId);
                $message = __('messages.post_approved_by_marketer_successfully');
            } else {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.unauthorized') ?? 'Unauthorized to approve post'
                ], 403);
            }

            // Add full image URL
            $postData = $post->load(['createdBy', 'updatedBy', 'client']);
            if ($postData->image) {
                $postData->image = asset('posts/' . $postData->image);
            }

            // Send notifications
            try {
                $currentUser = $this->getCurrentUser();
                $post->load(['teamMembers.member']);
                
                // Notify team members (designers/marketers) - but not the one who approved
                foreach ($post->teamMembers as $teamMember) {
                    $member = $teamMember->member;
                    if ($member && $member->id !== $currentUser->id) {
                        $this->sendNotification(
                            $member,
                            'Post Approved! 🎉',
                            "Post \"{$post->title}\" has been approved",
                            'post_approved',
                            [
                                'post_id' => $post->id,
                                'title' => $post->title
                            ]
                        );
                    }
                }
                
                // Notify client (if not the one approving)
                if ($post->client && (!auth()->guard('client')->check() || $post->client->id !== $currentUser->id)) {
                    $this->sendNotification(
                        $post->client,
                        'Post Approved',
                        "Post \"{$post->title}\" has been approved",
                        'post_approved',
                        [
                            'post_id' => $post->id,
                            'title' => $post->title,
                            'scheduled_at' => $post->scheduled_at
                        ]
                    );
                }
                
                // Notify admin
                if (!auth()->guard('admin')->check()) {
                    $this->notifyAdmins(
                        'Post Approved',
                        "Post \"{$post->title}\" approved by {$currentUser->name}",
                        'post_approved',
                        [
                            'post_id' => $post->id,
                            'title' => $post->title,
                            'approved_by' => $currentUser->name
                        ]
                    );
                }
            } catch (\Exception $e) {
                \Log::error('Failed to send post approval notifications: ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => [
                    'post' => $postData,
                    'approval_status' => $post->getApprovalStatus()
                ]
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => __('messages.post_not_found')
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('messages.error_occurred'),
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
