<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\PostFeedback;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ClientPostController extends Controller
{
    /**
     * Get all posts for this client
     */
    public function index(Request $request)
    {
        try {
            $clientId = auth()->id();
            
            $query = Post::with(['createdBy', 'updatedBy', 'client', 'feedbacks.client'])
                ->where('client_id', $clientId);

            // Filter by approval status
            if ($request->filled('is_approved')) {
                $query->where('is_approved', $request->is_approved === 'true' || $request->is_approved === true);
            }

            // Filter by status
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            // Search
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('title_ar', 'like', "%{$search}%");
                });
            }

            // Pagination
            if ($request->get('pagination') === 'false' || $request->get('pagination') === false) {
                $posts = $query->latest()->get();
            } else {
                $perPage = $request->get('per_page', 15);
                $posts = $query->latest()->paginate($perPage);
            }

            // Add full image URL
            $posts->transform(function ($post) {
                if ($post->image) {
                    $post->image = asset('posts/' . $post->image);
                }
                return $post;
            });

            return response()->json([
                'success' => true,
                'message' => __('messages.posts_retrieved_successfully'),
                'data' => $posts
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('messages.error_occurred'),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get single post
     */
    public function show($id)
    {
        try {
            $clientId = auth()->id();
            
            $post = Post::with(['createdBy', 'updatedBy', 'client', 'feedbacks.client'])
                ->where('client_id', $clientId)
                ->findOrFail($id);

            // Add full image URL
            if ($post->image) {
                $post->image = asset('posts/' . $post->image);
            }

            return response()->json([
                'success' => true,
                'message' => __('messages.post_retrieved_successfully'),
                'data' => $post
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('messages.error_occurred'),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add feedback to a post
     */
    public function addFeedback(Request $request, $id)
    {
        try {
            $clientId = auth()->id();
            
            $post = Post::where('client_id', $clientId)->findOrFail($id);

            // Check if post can receive feedback
            if (!$post->canReceiveFeedback()) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.post_already_approved_no_feedback')
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'comment' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.validation_error'),
                    'errors' => $validator->errors()
                ], 422);
            }

            $feedback = PostFeedback::create([
                'post_id' => $post->id,
                'client_id' => $clientId,
                'comment' => $request->comment,
            ]);

            return response()->json([
                'success' => true,
                'message' => __('messages.feedback_added_successfully'),
                'data' => $feedback->load('client')
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('messages.error_occurred'),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Approve a post by client
     */
    public function approve($id)
    {
        try {
            $clientId = auth()->id();
            
            $post = Post::where('client_id', $clientId)->findOrFail($id);

            // Check if already approved by client
            if ($post->client_approved) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.post_already_approved_by_you')
                ], 400);
            }

            $post->approveByClient($clientId);

            // Add full image URL
            $postData = $post->load(['createdBy', 'updatedBy', 'client']);
            if ($postData->image) {
                $postData->image = asset('posts/' . $postData->image);
            }

            return response()->json([
                'success' => true,
                'message' => __('messages.post_approved_by_client_successfully'),
                'data' => [
                    'post' => $postData,
                    'approval_status' => $post->getApprovalStatus()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('messages.error_occurred'),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all feedbacks for a post
     */
    public function getFeedbacks($id)
    {
        try {
            $clientId = auth()->id();
            
            $post = Post::where('client_id', $clientId)->findOrFail($id);
            
            $feedbacks = $post->feedbacks()->with('client')->get();

            return response()->json([
                'success' => true,
                'message' => __('messages.feedbacks_retrieved_successfully'),
                'data' => $feedbacks
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('messages.error_occurred'),
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
