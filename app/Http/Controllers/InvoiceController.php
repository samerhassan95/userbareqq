<?php

namespace App\Http\Controllers;

use App\Http\Requests\InvoiceRequest;
use App\Http\Resources\InvoiceResource;
use App\Models\Admin;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Spatie\QueryBuilder\QueryBuilder;
use App\Services\FirebaseService;
use App\Services\OpayService;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;


class InvoiceController extends Controller
{
    private $firebaseService;
    private $opayService;

    public function __construct(FirebaseService $firebaseService, OpayService $opayService)
    {
        $this->firebaseService = $firebaseService;
        $this->opayService = $opayService;
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'product_id' => 'required|exists:products,id',
            'amount' => 'required|numeric|min:0',
            'due_date' => 'required|date|after:today',
            'payment_method' => 'nullable|in:bank_transfer,opay,cash',
        ]);

        $invoice = Invoice::create([
            'client_id' => $validated['client_id'],
            'product_id' => $validated['product_id'],
            'amount' => $validated['amount'],
            'due_date' => $validated['due_date'],
            'status' => 'unpaid',
            'payment_method' => $validated['payment_method'] ?? 'bank_transfer',
            'reference' => 'INV-' . time() . '-' . rand(100, 999),
        ]);

        // Load relationships
        $invoice->load(['client', 'product']);

        $this->sendInvoiceNotification($invoice);

        return response()->json([
            'status' => true,
            'message' => __('messages.invoice_created'),
            'data' => [
                'id' => $invoice->id,
                'invoice_number' => 'INV-' . str_pad($invoice->id, 6, '0', STR_PAD_LEFT),
                'client' => [
                    'id' => $invoice->client_id,
                    'name' => $invoice->client->name,
                    'email' => $invoice->client->email,
                ],
                'product' => [
                    'id' => $invoice->product_id,
                    'name' => $invoice->product->name,
                ],
                'amount' => (float) $invoice->amount,
                'amount_formatted' => number_format($invoice->amount, 2),
                'currency' => 'EGP',
                'status' => $invoice->status,
                'payment_method' => $invoice->payment_method,
                'due_date' => Carbon::parse($invoice->due_date)->format('d-m-Y'),
                'created_at' => $invoice->created_at->format('d-m-Y H:i'),
            ],
        ], 201);
    }

    /**
     * Update invoice (Admin only)
     * PUT /api/admin/invoices/{invoiceId}
     */
    public function update(Request $request, $invoiceId)
    {
        $invoice = Invoice::find($invoiceId);

        if (!$invoice) {
            return response()->json([
                'status' => false,
                'message' => 'Invoice not found.',
            ], 404);
        }

        $validated = $request->validate([
            'amount' => 'nullable|numeric|min:0',
            'status' => 'nullable|in:paid,unpaid,cancelled',
            'due_date' => 'nullable|date',
            'payment_method' => 'nullable|in:bank_transfer,opay,cash',
        ]);

        $invoice->update($validated);
        $invoice->load(['client', 'product']);

        return response()->json([
            'status' => true,
            'message' => __('messages.invoice_updated'),
            'data' => [
                'id' => $invoice->id,
                'invoice_number' => 'INV-' . str_pad($invoice->id, 6, '0', STR_PAD_LEFT),
                'client' => [
                    'id' => $invoice->client_id,
                    'name' => $invoice->client->name,
                    'email' => $invoice->client->email,
                ],
                'product' => [
                    'id' => $invoice->product_id,
                    'name' => $invoice->product->name,
                ],
                'amount' => (float) $invoice->amount,
                'amount_formatted' => number_format($invoice->amount, 2),
                'currency' => 'EGP',
                'status' => $invoice->status,
                'payment_method' => $invoice->payment_method,
                'due_date' => $invoice->due_date ? Carbon::parse($invoice->due_date)->format('d-m-Y') : null,
                'updated_at' => $invoice->updated_at->format('d-m-Y H:i'),
            ],
        ], 200);
    }

    /**
     * Delete invoice (Admin only)
     * DELETE /api/admin/invoices/{invoiceId}
     */
    public function destroy($invoiceId)
    {
        $invoice = Invoice::find($invoiceId);

        if (!$invoice) {
            return response()->json([
                'status' => false,
                'message' => 'Invoice not found.',
            ], 404);
        }

        $invoiceNumber = 'INV-' . str_pad($invoice->id, 6, '0', STR_PAD_LEFT);
        $invoice->delete();

        return response()->json([
            'status' => true,
            'message' => __('messages.invoice_deleted') . " {$invoiceNumber}",
        ], 200);
    }


    private function sendInvoiceNotification(Invoice $invoice)
    {
        $client = $invoice->client;

        if (!$client || !$client->device_token) {
            Log::warning('Client not found or has no device token for invoice notification.', [
                'invoice_id' => $invoice->id,
                'client_id' => $client ? $client->id : null
            ]);
            return;
        }

        $template = \App\Models\NotificationTemplate::where('type', 'invoice_created')->first();
        if (!$template) {
            Log::error('Notification template "invoice_created" not found.');
            return;
        }

        $title = $template->title;
        $message = str_replace(
            ['{invoice_id}', '{amount}', '{due_date}'],
            ['INV-' . $invoice->id, number_format($invoice->amount, 2), $invoice->due_date->format('d-m-Y')],
            $template->message
        );

        try {
            $dataPayload = [
                'invoice_id' => $invoice->id,
                'notification_type' => 'invoice_created',
            ];
            $this->firebaseService->sendNotification($client->device_token, $title, $message, $dataPayload);

            app(\App\Repositories\NotificationRepository::class)->createNotification($client, $title, $message, $client->device_token, 'invoice_created');

            Log::info('Invoice notification sent successfully.', ['client_id' => $client->id, 'invoice_id' => $invoice->id]);
        } catch (\Exception $e) {
            Log::error('Error sending invoice notification: ' . $e->getMessage());
        }
    }

    // public function getInvoicesForProject($projectId, Request $request)
    // {
    //     $project = Project::find($projectId);

    //     if (!$project) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'Project not found.',
    //             'data' => null
    //         ], 404);
    //     }

    //     // Apply the filters and only include allowed ones
    //     $invoicesQuery = QueryBuilder::for(Invoice::class)
    //         ->where('project_id', $projectId)
    //         ->with('milestone');

    //     // Apply status filter if it's provided in the query string
    //     $invoicesQuery->allowedFilters([
    //         'status', // status can be 'paid', 'unpaid', etc.
    //         'payment_method',
    //         'due_date',
    //     ]);

    //     // Get the invoices
    //     $invoices = $invoicesQuery->get();

    //     if ($invoices->isEmpty()) {
    //         return response()->json([
    //             'status' => true,
    //             'message' => 'No invoices found for this project.',
    //             'data' => []
    //         ], 200);
    //     }

    //     $client = Client::find($project->client_id);
    //     $creatorName = $client ? $client->name : 'Unknown';

    //     // Transform invoice data for response
    //     $invoiceData = $invoices->map(function ($invoice) use ($project, $creatorName) {
    //         return [
    //             'id'=>$invoice->id,
    //             'status' => $invoice->status,
    //             'payment_method' => $invoice->payment_method,
    //             'created_at' => $invoice->created_at->toDateTimeString(),
    //             'due_date' => $invoice->due_date,
    //             'amount' => $invoice->amount,
    //             'project_name' => $project->name,
    //             'client_name' => $creatorName,
    //         ];
    //     });

    //     return response()->json([
    //         'status' => true,
    //         'message' => 'Invoices retrieved successfully.',
    //         'data' => $invoiceData
    //     ], 200);
    // }

    /**
     * Get invoices for all clients (Admin view)
     * GET /api/admin/invoices
     */
    public function getAllInvoices(Request $request)
    {
        $invoicesQuery = Invoice::with(['product', 'client'])
            ->orderBy('created_at', 'desc');

        // Apply filters
        if ($request->filled('status')) {
            $invoicesQuery->where('status', $request->status);
        }

        if ($request->filled('client_id')) {
            $invoicesQuery->where('client_id', $request->client_id);
        }

        if ($request->filled('product_id')) {
            $invoicesQuery->where('product_id', $request->product_id);
        }

        $invoices = $invoicesQuery->get();
        $now = Carbon::now();

        // Summary
        $summary = [
            'all' => $invoices->count(),
            'paid' => $invoices->where('status', 'paid')->count(),
            'unpaid' => $invoices->where('status', 'unpaid')->count(),
            'overdue' => $invoices->filter(function ($invoice) use ($now) {
                return $invoice->status === 'unpaid' && 
                       $invoice->due_date && 
                       Carbon::parse($invoice->due_date)->lt($now);
            })->count(),
        ];

        // Transform data
        $invoiceData = $invoices->map(function ($invoice) use ($now) {
            $status = $invoice->status;
            if ($status === 'unpaid' && $invoice->due_date && Carbon::parse($invoice->due_date)->lt($now)) {
                $status = 'overdue';
            }

            return [
                'id' => $invoice->id,
                'invoice_number' => 'INV-' . str_pad($invoice->id, 6, '0', STR_PAD_LEFT),
                'client_name' => $invoice->client ? $invoice->client->name : 'N/A',
                'client_email' => $invoice->client ? $invoice->client->email : 'N/A',
                'product_name' => $invoice->product ? $invoice->product->name : 'N/A',
                'amount' => (float) $invoice->amount,
                'amount_formatted' => number_format($invoice->amount, 2),
                'currency' => 'EGP',
                'status' => $status,
                'status_label' => ucfirst($status),
                'payment_method' => $invoice->payment_method ?? 'N/A',
                'due_date' => $invoice->due_date ? Carbon::parse($invoice->due_date)->format('d-m-Y') : null,
                'created_at' => $invoice->created_at->format('d-m-Y H:i'),
                'has_payment_proof' => !empty($invoice->payment_proof),
            ];
        });

        return response()->json([
            'status' => true,
            'message' => __('messages.invoices_retrieved'),
            'data' => [
                'summary' => $summary,
                'invoices' => $invoiceData
            ]
        ], 200);
    }

    public function initiatePayment(Request $request, $invoiceId)
    {
        $invoice = Invoice::findOrFail($invoiceId);
        $gateway = $request->input('gateway', $invoice->gateway ?? 'opay');

        if ($gateway !== 'opay') {
            return response()->json([
                'status' => false,
                'message' => 'Unsupported payment gateway.',
            ], 422);
        }

        $paymentData = $this->opayService->initiatePayment($invoice);
        if (!$paymentData) {
            return response()->json([
                'status' => false,
                'message' => 'Payment gateway integration failed.',
            ], 400);
        }

        $invoice->update([
            'gateway' => $gateway,
            'order_no' => $paymentData['order_no'],
            'reference' => $invoice->reference ?? ('INV-' . $invoice->id . '-' . time()),
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Payment link generated successfully.',
            'data' => [
                'invoice_id' => $invoice->id,
                'gateway' => $gateway,
                'order_no' => $paymentData['order_no'],
                'payment_link' => $paymentData['payment_link'],
            ],
        ]);
    }


    public function getInvoiceStatusCounts()
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        // For clients, get their invoices
        $invoices = Invoice::where('client_id', $user->id)->get();

        $invoiceCounts = [
            'paid' => 0,
            'unpaid' => 0,
            'overdue' => 0,
            'total' => 0,
        ];

        foreach ($invoices as $invoice) {
            $invoiceCounts['total']++;

            if ($invoice->status === 'paid') {
                $invoiceCounts['paid']++;
            } elseif ($invoice->status === 'unpaid') {
                if ($invoice->due_date && Carbon::parse($invoice->due_date)->isPast()) {
                    $invoiceCounts['overdue']++;
                } else {
                    $invoiceCounts['unpaid']++;
                }
            }
        }

        return response()->json([
            'status' => true,
            'data' => $invoiceCounts,
        ]);
    }


    public function getInvoicesForClient(Request $request)
    {
        $client = auth()->user();

        // Fetch invoices for the client (now linked to products, not projects)
        $invoices = Invoice::where('client_id', $client->id)
            ->with(['product', 'client'])
            ->orderBy('created_at', 'desc')
            ->get();

        if ($invoices->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => __('messages.no_invoices_found'),
                'data' => []
            ], 404);
        }

        $formattedInvoices = $invoices->map(function ($invoice) {
            // Determine overdue status for unpaid invoices
            $status = $invoice->status;
            if ($status === 'unpaid' && $invoice->due_date && Carbon::parse($invoice->due_date)->isPast()) {
                $status = 'overdue';
            }

            return [
                'id' => $invoice->id,
                'invoice_number' => 'INV-' . str_pad($invoice->id, 6, '0', STR_PAD_LEFT),
                'product_name' => $invoice->product ? $invoice->product->name : 'N/A',
                'client_name' => auth()->user()->name,
                'created_at' => $invoice->created_at->format('d-m-Y'),
                'amount' => number_format($invoice->amount, 2),
                'currency' => 'EGP',
                'due_date' => $invoice->due_date ? Carbon::parse($invoice->due_date)->format('d-m-Y') : null,
                'status' => ucfirst($status),
                'payment_method' => $invoice->payment_method ?? 'N/A',
                'has_payment_proof' => !empty($invoice->payment_proof),
            ];
        });

        return response()->json([
            'status' => true,
            'message' => __('messages.invoices_retrieved'),
            'data' => $formattedInvoices
        ], 200);
    }


public function getInvoiceDetails(Request $request, $invoiceId)
{
    $client = auth()->user();
    
    $invoice = Invoice::with(['product', 'client'])
        ->where('id', $invoiceId)
        ->where('client_id', $client->id)
        ->first();

    if (!$invoice) {
        return response()->json([
            'status' => false,
            'message' => __('messages.invoice_not_found'),
            'data' => []
        ], 404);
    }

    // Determine overdue status
    $status = $invoice->status;
    if ($status === 'unpaid' && $invoice->due_date && Carbon::parse($invoice->due_date)->isPast()) {
        $status = 'overdue';
    }

    $formattedInvoice = [
        'id' => $invoice->id,
        'invoice_number' => 'INV-' . str_pad($invoice->id, 6, '0', STR_PAD_LEFT),
        'status' => ucfirst($status),
        'status_label' => $status === 'overdue' ? 'Overdue' : ucfirst($invoice->status),
        'created_at' => $invoice->created_at->format('d-m-Y'),
        'due_date' => $invoice->due_date ? Carbon::parse($invoice->due_date)->format('d-m-Y') : null,
        'payment_method' => $invoice->payment_method ?? 'N/A',
        'amount' => (float) $invoice->amount,
        'amount_formatted' => number_format($invoice->amount, 2),
        'currency' => 'EGP',
        'product' => [
            'id' => $invoice->product_id,
            'name' => $invoice->product ? $invoice->product->name : 'N/A',
            'type' => $invoice->product ? $invoice->product->product_role : null,
        ],
        'client' => [
            'id' => $invoice->client_id,
            'name' => $invoice->client ? $invoice->client->name : 'N/A',
            'email' => $invoice->client ? $invoice->client->email : 'N/A',
        ],
        'payment_proof' => $invoice->payment_proof ? asset($invoice->payment_proof) : null,
        'has_payment_proof' => !empty($invoice->payment_proof),
    ];

    // ✅ If PDF requested
    if ($request->query('format') === 'pdf') {
        $pdf = Pdf::loadView('invoices.pdf', [
            'invoice' => $formattedInvoice
        ]);

        return $pdf->download('invoice-' . $invoice->id . '.pdf');
    }

    // ✅ Default JSON response
    return response()->json([
        'status' => true,
        'message' => __('messages.invoices_retrieved'),
        'data' => $formattedInvoice
    ], 200);
}


    public function getUserInvoices(Request $request)
{
    $user = auth()->user();

    $search = $request->product_name;

    // Get all invoices for this client
    $allInvoices = Invoice::where('client_id', $user->id)->get();

    $cards = [
        'all' => $allInvoices->count(),
        'paid' => $allInvoices->where('status', 'paid')->count(),
        'unpaid' => $allInvoices->where('status', 'unpaid')->count(),
        'overdue' => $allInvoices->filter(function ($inv) {
            return $inv->status === 'unpaid' && $inv->due_date && now()->gt($inv->due_date);
        })->count(),
    ];

    // Build query with filters
    $invoicesQuery = Invoice::with(['product', 'client'])
        ->where('client_id', $user->id);

    if ($search) {
        $invoicesQuery->whereHas('product', function($q) use ($search) {
            $q->where('name', 'like', '%' . $search . '%');
        });
    }

    $invoices = $invoicesQuery->orderBy('created_at', 'desc')->get();

    // Transform data
    $invoiceData = $invoices->map(function ($invoice) {
        $statusText = $invoice->status;
        if ($invoice->status === 'unpaid' && $invoice->due_date && now()->gt($invoice->due_date)) {
            $statusText = 'overdue';
        }

        return [
            'id' => $invoice->id,
            'invoice_number' => 'INV-' . str_pad($invoice->id, 6, '0', STR_PAD_LEFT),
            'amount' => (float) $invoice->amount,
            'amount_formatted' => number_format($invoice->amount, 2),
            'currency' => 'EGP',
            'product_name' => $invoice->product ? $invoice->product->name : 'N/A',
            'client_name' => $invoice->client ? $invoice->client->name : 'N/A',
            'status' => $statusText,
            'status_label' => ucfirst($statusText),
            'payment_method' => $invoice->payment_method ?? 'N/A',
            'due_date' => $invoice->due_date ? Carbon::parse($invoice->due_date)->format('d-m-Y') : null,
            'created_at' => $invoice->created_at->format('d-m-Y H:i'),
            'has_payment_proof' => !empty($invoice->payment_proof),
        ];
    });

    return response()->json([
        'status' => true,
        'data' => [
            'cards' => $cards,
            'invoices' => $invoiceData,
        ]
    ]);
}

    /**
     * Upload payment proof (offline payment)
     * POST /api/client/invoices/{invoiceId}/upload-payment-proof
     */
    public function uploadPaymentProof(Request $request, $invoiceId)
    {
        $request->validate([
            'payment_proof' => 'required|file|mimes:jpeg,png,jpg,pdf|max:5120', // 5MB max
        ]);

        $invoice = Invoice::find($invoiceId);

        if (!$invoice) {
            return response()->json([
                'status' => false,
                'message' => 'Invoice not found.',
            ], 404);
        }

        // Check if client owns this invoice
        $client = auth()->user();
        if ($invoice->client_id !== $client->id) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        // Upload payment proof
        $filePath = \App\Services\ImageService::upload($request->file('payment_proof'), 'payment_proofs');

        $invoice->update([
            'payment_proof' => $filePath,
            'payment_method' => 'bank_transfer',
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Payment proof uploaded successfully. Waiting for admin approval.',
            'data' => [
                'invoice_id' => $invoice->id,
                'payment_proof' => asset($filePath),
            ],
        ]);
    }
}
