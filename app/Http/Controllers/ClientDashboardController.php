<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Meeting;
use App\Models\Subscription;
use App\Models\Slider;
use App\Models\Product;
use Illuminate\Http\Request;

class ClientDashboardController extends Controller
{
    public function dashboard(Request $request)
    {
        $client = auth()->user();

        // ✅ Get subscriptions and purchases
        $subscriptions = Subscription::with('product')
            ->where('client_id', $client->id)
            ->orderByDesc('updated_at')
            ->get();

        $invoices = Invoice::with('product')
            ->where('client_id', $client->id)
            ->orderByDesc('updated_at')
            ->get();

        $subscriptionCount = $subscriptions->count();
        $purchaseCount = $invoices->where('status', 'paid')->count();

        $subscriptionStatusSummary = [
            'active'   => $subscriptions->where('status', 'active')->count(),
            'expired'  => $subscriptions->where('status', 'expired')->count(),
            'canceled' => $subscriptions->where('status', 'canceled')->count(),
        ];

        $invoiceStatusSummary = [
            'paid'   => $invoices->where('status', 'paid')->count(),
            'unpaid' => $invoices->where('status', 'unpaid')->count(),
            'canceled' => $invoices->where('status', 'canceled')->count(),
        ];

        $meetings = Meeting::where('client_id', $client->id)
            ->orderByDesc('updated_at')
            ->take(5)
            ->get()
            ->map(function($meeting){
                return [
                    'id' => $meeting->id,
                    'meeting_name' => $meeting->meeting_name,
                    'date' => $meeting->date,
                    'start' => $meeting->start_time,
                    'end' => $meeting->end_time,
                ];
            });

        $sliders = Slider::with([
            'product' => function ($q) {
                $q->select('id', 'name', 'category_id', 'price', 'description')
                    ->with(['category:id,name']);
            }
        ])
        ->get()
        ->map(function ($slider) {
            $product = $slider->product;

            // Handle image array properly
            $firstImage = null;
            if (is_array($slider->image) && !empty($slider->image)) {
                $firstImage = url($slider->image[0]);
            } elseif (is_string($slider->image) && !empty($slider->image)) {
                $firstImage = url($slider->image);
            }

            return [
                'id' => $slider->id,
                'image' => $firstImage,
                'product' => $product ? [
                    'id' => $product->id,
                    'name' => $product->name,
                ] : null
            ];
        });

        return response()->json([
            'status' => true,
            'data' => [
                'subscriptions_summary' => [
                    'count' => $subscriptionCount,
                    'status' => $subscriptionStatusSummary
                ],
                'purchases_summary' => [
                    'count' => $purchaseCount,
                    'status' => $invoiceStatusSummary
                ],
                'meetings' => $meetings,
                'sliders' => $sliders
            ],
        ]);
    }
}
