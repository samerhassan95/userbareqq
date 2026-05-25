<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Designer;
use App\Models\Marketer;
use App\Models\ProductOrder;
use App\Models\ProductOrderTeamMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdminProductOrderTeamController extends Controller
{
    /**
     * Add team members to product order
     * POST /api/admin/product-orders/{orderId}/team
     */
    public function addTeamMembers(Request $request, $orderId)
    {
        try {
            $order = ProductOrder::findOrFail($orderId);

            // Verify it's a strategy order
            if ($order->product_role !== 'strategy') {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.only_strategy_orders_can_have_team')
                ], 422);
            }

            $validator = Validator::make($request->all(), [
                'team_members' => 'required|array|min:1',
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
            $errors = [];

            foreach ($request->team_members as $memberData) {
                // Verify member exists
                if ($memberData['member_type'] === 'designer') {
                    $member = Designer::find($memberData['member_id']);
                } else {
                    $member = Marketer::find($memberData['member_id']);
                }

                if (!$member) {
                    $errors[] = "Member ID {$memberData['member_id']} of type {$memberData['member_type']} not found";
                    continue;
                }

                // Check if already assigned
                $existing = ProductOrderTeamMember::where('product_order_id', $orderId)
                    ->where('member_id', $memberData['member_id'])
                    ->where('member_type', $memberData['member_type'])
                    ->first();

                if ($existing) {
                    $errors[] = "Member already assigned to this order";
                    continue;
                }

                // Add team member
                $teamMember = ProductOrderTeamMember::create([
                    'product_order_id' => $orderId,
                    'member_id' => $memberData['member_id'],
                    'member_type' => $memberData['member_type'],
                ]);

                $addedMembers[] = [
                    'id' => $teamMember->id,
                    'member_id' => $member->id,
                    'member_type' => $memberData['member_type'],
                    'username' => $member->username,
                    'email' => $member->email,
                ];
            }

            return response()->json([
                'success' => true,
                'message' => __('messages.team_members_added_successfully'),
                'data' => [
                    'added' => $addedMembers,
                    'errors' => $errors
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
     * Get team members for product order
     * GET /api/admin/product-orders/{orderId}/team
     */
    public function getTeamMembers($orderId)
    {
        try {
            $order = ProductOrder::findOrFail($orderId);

            $teamMembers = ProductOrderTeamMember::where('product_order_id', $orderId)->get();

            $members = $teamMembers->map(function ($tm) {
                if ($tm->member_type === 'designer') {
                    $member = Designer::find($tm->member_id);
                } else {
                    $member = Marketer::find($tm->member_id);
                }

                return [
                    'id' => $tm->id,
                    'member_id' => $tm->member_id,
                    'member_type' => $tm->member_type,
                    'username' => $member->username ?? 'N/A',
                    'email' => $member->email ?? 'N/A',
                    'phone' => $member->phone ?? 'N/A',
                    'created_at' => $tm->created_at->format('Y-m-d H:i:s'),
                ];
            });

            return response()->json([
                'success' => true,
                'message' => __('messages.team_members_retrieved_successfully'),
                'data' => $members
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
     * Remove team member from product order
     * DELETE /api/admin/product-orders/{orderId}/team/{teamMemberId}
     */
    public function removeTeamMember($orderId, $teamMemberId)
    {
        try {
            $teamMember = ProductOrderTeamMember::where('product_order_id', $orderId)
                ->where('id', $teamMemberId)
                ->firstOrFail();

            $teamMember->delete();

            return response()->json([
                'success' => true,
                'message' => __('messages.team_member_removed_successfully')
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
