<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;

class AdminClientController extends Controller
{
    /**
     * Get all clients
     */
    public function index(Request $request)
    {
        $query = Client::query()->with(['subscriptions', 'invoices']);

        // Search by name, email, or phone
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Filter by role
        if ($request->has('role')) {
            $query->where('role', $request->role);
        }

        // Pagination
        $perPage = $request->get('per_page', 15);
        $clients = $query->latest()->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => __('messages.clients_retrieved_successfully'),
            'data' => $clients
        ]);
    }

    /**
     * Get single client details
     */
    public function show($id)
    {
        $client = Client::with(['subscriptions', 'invoices', 'socialCredentials'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => __('messages.client_retrieved_successfully'),
            'data' => $client
        ]);
    }
}
