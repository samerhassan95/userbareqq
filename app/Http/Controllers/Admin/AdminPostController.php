<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class AdminPostController extends Controller
{
    /**
     * Get all posts
     */
    public function index(Request $request)
    {
        try {
            $query = Post::with(['createdBy', 'updatedBy', 'client', 'feedbacks']);

            // Filter by status
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            // Filter by approval status
            if ($request->filled('is_approved')) {
                $query->where('is_approved', $request->is_approved === 'true' || $request->is_approved === true);
            }

            // Filter by client
            if ($request->filled('client_id')) {
                $query->where('client_id', $request->client_id);
            }

            // Search
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('title_ar', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%")
                      ->orWhere('description_ar', 'like', "%{$search}%");
                });
            }

            // Pagination
            if ($request->get('pagination') === 'false' || $request->get('pagination') === false) {
                $posts = $query->latest()->get();
            } else {
                $perPage = $request->get('per_page', 15);
                $posts = $query->latest()->paginate($perPage);
            }

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
            $post = Post::with(['createdBy', 'updatedBy', 'client', 'feedbacks.client'])->findOrFail($id);

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
     * Create new post (Admin only)
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'title_ar' => 'nullable|string|max:255',
                'description' => 'required|string',
                'description_ar' => 'nullable|string',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'client_id' => 'required|exists:clients,id',
                'product_order_id' => 'nullable|exists:product_orders,id',
                'strategy_work_id' => 'nullable|exists:strategy_works,id',
                'status' => 'nullable|in:pending,approved,rejected',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.validation_error'),
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $validator->validated();

            // Handle image upload
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = uniqid() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('posts'), $imageName);
                $data['image'] = $imageName;
            }

            // Set creator
            $data['created_by_id'] = auth()->id();
            $data['created_by_type'] = 'App\Models\Admin';
            $data['updated_by_id'] = auth()->id();
            $data['updated_by_type'] = 'App\Models\Admin';
            $data['is_approved'] = false;

            $post = Post::create($data);

            return response()->json([
                'success' => true,
                'message' => __('messages.post_created_successfully'),
                'data' => $post->load(['createdBy', 'updatedBy', 'client'])
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
     * Update post (Admin can edit)
     */
    public function update(Request $request, $id)
    {
        try {
            $post = Post::findOrFail($id);

            // Check if post can be edited
            if (!$post->canBeEdited()) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.post_already_approved')
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'title' => 'sometimes|required|string|max:255',
                'title_ar' => 'nullable|string|max:255',
                'description' => 'sometimes|required|string',
                'description_ar' => 'nullable|string',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'client_id' => 'sometimes|required|exists:clients,id',
                'product_order_id' => 'nullable|exists:product_orders,id',
                'strategy_work_id' => 'nullable|exists:strategy_works,id',
                'status' => 'nullable|in:pending,approved,rejected',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.validation_error'),
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $validator->validated();

            // Handle image upload
            if ($request->hasFile('image')) {
                // Delete old image
                if ($post->image && file_exists(public_path('posts/' . $post->image))) {
                    unlink(public_path('posts/' . $post->image));
                }

                $image = $request->file('image');
                $imageName = uniqid() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('posts'), $imageName);
                $data['image'] = $imageName;
            }

            // Update editor info
            $data['updated_by_id'] = auth()->id();
            $data['updated_by_type'] = 'App\Models\Admin';

            $post->update($data);

            return response()->json([
                'success' => true,
                'message' => __('messages.post_updated_successfully'),
                'data' => $post->load(['createdBy', 'updatedBy', 'client', 'feedbacks'])
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
     * Delete post (Admin only)
     */
    public function destroy($id)
    {
        try {
            $post = Post::findOrFail($id);

            // Delete image
            if ($post->image && file_exists(public_path('posts/' . $post->image))) {
                unlink(public_path('posts/' . $post->image));
            }

            $post->delete();

            return response()->json([
                'success' => true,
                'message' => __('messages.post_deleted_successfully')
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
