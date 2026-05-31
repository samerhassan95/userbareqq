<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\PostFeedback;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PostFeedbackController extends Controller
{
    /**
     * Add feedback to a post (Admin, Client, Marketer, Designer)
     */
    public function addFeedback(Request $request, $id)
    {
        try {
            // Get the authenticated user and their type
            $user = auth()->user();
            $userType = null;
            $userId = null;

            // Determine user type based on which guard is authenticated
            if (auth()->guard('admin')->check()) {
                $userType = 'App\Models\Admin';
                $userId = auth()->guard('admin')->id();
            } elseif (auth()->guard('client')->check()) {
                $userType = 'App\Models\Client';
                $userId = auth()->guard('client')->id();
            } elseif (auth()->guard('marketer')->check()) {
                $userType = 'App\Models\Marketer';
                $userId = auth()->guard('marketer')->id();
            } elseif (auth()->guard('designer')->check()) {
                $userType = 'App\Models\Designer';
                $userId = auth()->guard('designer')->id();
            } else {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.unauthorized')
                ], 401);
            }

            $post = Post::findOrFail($id);

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
                'user_id' => $userId,
                'user_type' => $userType,
                'comment' => $request->comment,
            ]);

            return response()->json([
                'success' => true,
                'message' => __('messages.feedback_added_successfully'),
                'data' => $this->formatFeedback($feedback)
            ], 201);
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
     * Get all feedbacks for a post (Admin, Client, Marketer, Designer)
     */
    public function getFeedbacks($id)
    {
        try {
            $post = Post::findOrFail($id);

            $feedbacks = $post->feedbacks()
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($feedback) {
                    return $this->formatFeedback($feedback);
                });

            return response()->json([
                'success' => true,
                'message' => __('messages.feedbacks_retrieved_successfully'),
                'data' => $feedbacks
            ], 200);
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
     * Format feedback response with user details
     */
    private function formatFeedback($feedback)
    {
        $userData = [];

        if ($feedback->user_type === 'App\Models\Admin' && $feedback->user) {
            $userData = [
                'id' => $feedback->user->id,
                'name' => $feedback->user->name,
                'email' => $feedback->user->email,
                'role' => 'Admin',
            ];
        } elseif ($feedback->user_type === 'App\Models\Client' && $feedback->user) {
            $userData = [
                'id' => $feedback->user->id,
                'name' => $feedback->user->name,
                'email' => $feedback->user->email,
                'role' => 'Client',
            ];
        } elseif ($feedback->user_type === 'App\Models\Marketer' && $feedback->user) {
            $userData = [
                'id' => $feedback->user->id,
                'name' => $feedback->user->name,
                'email' => $feedback->user->email,
                'role' => 'Marketer',
            ];
        } elseif ($feedback->user_type === 'App\Models\Designer' && $feedback->user) {
            $userData = [
                'id' => $feedback->user->id,
                'name' => $feedback->user->name,
                'email' => $feedback->user->email,
                'role' => 'Designer',
            ];
        }

        return [
            'id' => $feedback->id,
            'comment' => $feedback->comment,
            'user' => $userData,
            'created_at' => $feedback->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
