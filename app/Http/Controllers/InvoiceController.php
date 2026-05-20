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


class InvoiceController extends BaseController
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
            'gateway' => 'nullable|in:opay',
        ]);

        $invoice = Invoice::create([
            'client_id' => $validated['client_id'],
            'product_id' => $validated['product_id'],
            'amount' => $validated['amount'],
            'due_date' => $validated['due_date'],
            'status' => 'unpaid',
            'gateway' => $validated['gateway'] ?? 'opay',
            'reference' => 'INV-' . time() . '-' . rand(100, 999),
        ]);

        $this->sendInvoiceNotification($invoice);

        return response()->json([
            'status' => true,
            'message' => 'Invoice created successfully!',
            'data' => new InvoiceResource($invoice),
        ], 201);
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

    public function getInvoicesForProject($projectId, Request $request)
{
    $project = Project::find($projectId);

    if (!$project) {
        return response()->json([
            'status' => false,
            'message' => 'Project not found.',
            'data' => null
        ], 404);
    }

    // Base query
    $invoicesQuery = QueryBuilder::for(Invoice::class)
        ->where('project_id', $projectId)
        ->with('milestone')
        ->allowedFilters([
            'status',
            'payment_method',
            'due_date',
        ]);

    $invoices = $invoicesQuery->get();

    $client = Client::find($project->client_id);
    $creatorName = $client ? $client->name : 'Unknown';

    // =========================
    // 🔹 INVOICES SUMMARY
    // =========================
    $now = Carbon::now();

    $summary = [
        'all' => $invoices->count(),

        'completed' => $invoices
            ->where('status', 'paid')
            ->count(),

        'pending' => $invoices->filter(function ($invoice) use ($now) {
            return
                $invoice->status === 'unpaid' &&
                $invoice->milestone &&
                Carbon::parse($invoice->milestone->end_date)->lt($now);
        })->count(),

        'ongoing' => $invoices->filter(function ($invoice) use ($now) {
            return
                $invoice->status === 'unpaid' &&
                $invoice->milestone &&
                Carbon::parse($invoice->milestone->end_date)->gte($now);
        })->count(),
    ];

    // =========================
    // 🔹 FILTER BY UI STATUS
    // =========================
    if ($request->filled('ui_status')) {
        $uiStatus = $request->ui_status;

        $invoices = $invoices->filter(function ($invoice) use ($uiStatus, $now) {
            return match ($uiStatus) {
                'completed' => $invoice->status === 'paid',

                'pending' =>
                    $invoice->status === 'unpaid' &&
                    $invoice->milestone &&
                    Carbon::parse($invoice->milestone->end_date)->lt($now),

                'ongoing' =>
                    $invoice->status === 'unpaid' &&
                    $invoice->milestone &&
                    Carbon::parse($invoice->milestone->end_date)->gte($now),

                default => true, // all
            };
        })->values();
    }

    // =========================
    // 🔹 TRANSFORM RESPONSE
    // =========================
    $invoiceData = $invoices->map(function ($invoice) use ($project, $creatorName) {
        return [
            'id'             => $invoice->id,
            'status'         => $invoice->status,
            'payment_method' => $invoice->payment_method,
            'created_at'     => $invoice->created_at->toDateTimeString(),
            'due_date'       => $invoice->due_date,
            'amount'         => $invoice->amount,
            'project_name'   => $project->name,
            'client_name'    => $creatorName,
            'milestone'      => $invoice->milestone?->name,
        ];
    });

    return response()->json([
        'status'  => true,
        'message' => 'Invoices retrieved successfully.',
        'data' => [
            'summary'  => $summary,
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

        if (!$user || $user instanceof \App\Models\Admin) {
            return response()->json(['message' => 'Access denied.'], 403);
        }

        $projects = Project::where('client_id', $user->id)
            ->with('invoices')
            ->get();

        $invoiceCounts = [
            'paid' => 0,
            'unpaid' => 0,
            'overdue' => 0,
            'total' => 0,
        ];

        foreach ($projects as $project) {
            foreach ($project->invoices as $invoice) {
                $invoiceCounts['total']++;

                if ($invoice->status === 'paid') {
                    $invoiceCounts['paid']++;
                } elseif ($invoice->status === 'unpaid') {
                    if (Carbon::parse($invoice->due_date)->isPast()) {
                        $invoiceCounts['overdue']++;
                    } else {
                        $invoiceCounts['unpaid']++;
                    }
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

        // Fetch invoices for the client
        $invoices = Invoice::whereHas('project', function ($query) use ($client) {
            $query->where('client_id', $client->id);
        })->with('project.client')->get();

        if ($invoices->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'No invoices found.',
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
                'invoice_id' => 'INV-' . $invoice->id,
                'client_name' => auth()->user()->name,
                'created_at' => $invoice->created_at->format('d-m-Y'),
                'amount' => number_format($invoice->amount, 2),
                'due_date'=>$invoice->due_date,
                'status' => ucfirst($status), // Overdue or Paid/Unpaid
            ];
        });

        return response()->json([
            'status' => true,
            'message' => 'Invoices retrieved successfully.',
            'data' => $formattedInvoices
        ], 200);
    }


public function getInvoiceDetails(Request $request, $invoiceId)
{
    $invoice = Invoice::with([
        'milestone.tasks',
        'project'
    ])->find($invoiceId);

    if (!$invoice) {
        return response()->json([
            'status' => false,
            'message' => 'Invoice not found.',
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
        'invoice_id' => 'INV-' . $invoice->id,
        'status' => ucfirst($status),
        'created_at' => $invoice->created_at->format('d-m-Y'),
        'due_date' => $invoice->due_date,
        'payment_type' => $invoice->payment_method ?? 'N/A',
        'amount' => number_format($invoice->amount, 2),
        'tasks' => $invoice->milestone
            ? $invoice->milestone->tasks->map(function ($task) {
                return [
                    'task_id' => $task->id,
                    'task_name' => $task->label,
                    'status' => ucfirst($task->status),
                    'created_at' => $task->created_at->format('d-m-Y'),
                ];
            })->toArray()
            : []
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
        'message' => 'Invoice details retrieved successfully.',
        'data' => $formattedInvoice
    ], 200);
}


    public function getUserInvoices(Request $request)
{
    $user = auth()->user();

    $search = $request->project_name;


    $allProjectIds = Project::where('client_id', $user->id)->pluck('id');


    $allInvoices = Invoice::whereIn('project_id', $allProjectIds)->get();

    $cards = [
        'all' => $allInvoices->count(),
        'paid' => $allInvoices->where('status', 'paid')->count(),
        'unpaid' => $allInvoices->where('status', 'unpaid')->count(),
        'overdue' => $allInvoices->filter(function ($inv) {
            return $inv->status === 'unpaid' && now()->gt($inv->due_date);
        })->count(),
    ];



    $projectsQuery = Project::where('client_id', $user->id);

    if ($search) {
        $projectsQuery->where('name', 'like', '%' . $search . '%');
    }

    $filteredProjectIds = $projectsQuery->pluck('id');

    $invoices = Invoice::with(['project', 'project.client'])
        ->whereIn('project_id', $filteredProjectIds)
        ->get();



    $invoiceData = $invoices->map(function ($invoice) {

        $projectTotal = Invoice::where('project_id', $invoice->project_id)->count();

        $currentIndex = Invoice::where('project_id', $invoice->project_id)
            ->orderBy('id')
            ->pluck('id')
            ->search($invoice->id) + 1;


            $statusText = $invoice->status;
        if ($invoice->status === 'unpaid' && now()->gt($invoice->due_date)) {
            $statusText = 'overdue';
        }

        return [
            'id' => "INV-" . $invoice->id,
            'amount' => $invoice->amount,
            'project_name' => $invoice->project->name ?? '',
            'client_name' => $invoice->project->client->name ?? '',
            'status' => $statusText,
            'payment_method' => $invoice->payment_method,
            'due_date' => $invoice->due_date,
            'invoice_no' => $currentIndex . " of " . $projectTotal,
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
