<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    /**
     * Get single post for any role
     */
    public function show($id)
    {
        try {
            $query = Post::with(['createdBy', 'updatedBy', 'client', 'feedbacks.user', 'teamMembers.member']);
            
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
}
