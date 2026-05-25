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
            $post = Post::with(['createdBy', 'updatedBy', 'client', 'feedbacks.client'])->findOrFail($id);

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

    /**
     * Add team members to post
     * POST /api/admin/posts/{id}/team
     */
    public function addTeamMembers(Request $request, $id)
    {
        try {
            $post = Post::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'team_members' => 'required|array',
                'team_members.*.member_id' => 'required|integer',
                'team_members.*.member_type' => 'required|in:designer,marketer',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.validation_error'),
                    'errors' => $validator->errors()
                ], 422);
            }

            $addedMembers = [];

            foreach ($request->team_members as $memberData) {
                // Convert member_type to full class name
                $memberType = $memberData['member_type'] === 'designer' 
                    ? \App\Models\Designer::class 
                    : \App\Models\Marketer::class;

                // Check if member exists
                $memberExists = $memberType::find($memberData['member_id']);
                
                if (!$memberExists) {
                    continue;
                }

                // Add team member (will skip if already exists due to unique constraint)
                try {
                    $teamMember = \App\Models\PostTeamMember::create([
                        'post_id' => $post->id,
                        'member_id' => $memberData['member_id'],
                        'member_type' => $memberType,
                        'role' => $memberData['member_type'],
                    ]);

                    $addedMembers[] = [
                        'id' => $teamMember->id,
                        'member_id' => $teamMember->member_id,
                        'member_type' => $memberData['member_type'],
                        'member_name' => $memberExists->name ?? $memberExists->username ?? 'N/A',
                    ];
                } catch (\Exception $e) {
                    // Skip if duplicate
                    continue;
                }
            }

            return response()->json([
                'success' => true,
                'message' => __('Team members added successfully'),
                'data' => [
                    'post_id' => $post->id,
                    'added_members' => $addedMembers,
                    'total_team_members' => $post->teamMembers()->count(),
                ]
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
     * Get team members for a post
     * GET /api/admin/posts/{id}/team
     */
    public function getTeamMembers($id)
    {
        try {
            $post = Post::findOrFail($id);

            $teamMembers = $post->teamMembers()->with('member')->get();

            $data = $teamMembers->map(function ($teamMember) {
                $member = $teamMember->member;
                
                return [
                    'id' => $teamMember->id,
                    'member_id' => $teamMember->member_id,
                    'member_type' => $teamMember->role,
                    'member_name' => $member->name ?? $member->username ?? 'N/A',
                    'member_email' => $member->email ?? null,
                    'member_photo' => isset($member->photo) && $member->photo ? asset($member->photo) : null,
                    'added_at' => $teamMember->created_at->format('Y-m-d H:i:s'),
                ];
            });

            return response()->json([
                'success' => true,
                'message' => __('Team members retrieved successfully'),
                'data' => [
                    'post_id' => $post->id,
                    'team_members' => $data,
                    'total_count' => $data->count(),
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
     * Remove team member from post
     * DELETE /api/admin/posts/{postId}/team/{teamMemberId}
     */
    public function removeTeamMember($postId, $teamMemberId)
    {
        try {
            $post = Post::findOrFail($postId);
            $teamMember = \App\Models\PostTeamMember::where('post_id', $postId)
                ->where('id', $teamMemberId)
                ->firstOrFail();

            $teamMember->delete();

            return response()->json([
                'success' => true,
                'message' => __('Team member removed successfully')
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
