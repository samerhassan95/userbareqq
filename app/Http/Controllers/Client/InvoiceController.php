<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Services\InvoicePdfService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class InvoiceController extends Controller
{
    protected $pdfService;
    
    public function __construct(InvoicePdfService $pdfService)
    {
        $this->pdfService = $pdfService;
    }
    
    /**
     * List all client invoices
     */
    public function index(Request $request)
    {
        try {
            $client = auth()->guard('client')->user();
            
            Log::info('Fetching invoices for client', ['client_id' => $client->id]);
            
            $perPage = $request->input('per_page', 20);
            
            $invoices = Invoice::where('client_id', $client->id)
                ->with(['product'])
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);
            
            return response()->json([
                'status' => true,
                'message' => 'Invoices retrieved successfully',
                'data' => $invoices->map(function($invoice) {
                    return [
                        'id' => $invoice->id,
                        'reference' => $invoice->reference,
                        'amount' => $invoice->amount,
                        'status' => $invoice->status,
                        'payment_method' => $invoice->payment_method,
                        'gateway' => $invoice->gateway,
                        'due_date' => $invoice->due_date,
                        'created_at' => $invoice->created_at,
                        'product' => [
                            'id' => $invoice->product->id ?? null,
                            'name' => $invoice->product->name ?? null,
                        ],
                        'has_pdf' => !empty($invoice->pdf_path),
                        'download_url' => $invoice->pdf_path ? url("/api/client/invoices/{$invoice->id}/download") : null,
                        'view_url' => $invoice->pdf_path ? url("/api/client/invoices/{$invoice->id}/view") : null,
                    ];
                }),
                'pagination' => [
                    'total' => $invoices->total(),
                    'per_page' => $invoices->perPage(),
                    'current_page' => $invoices->currentPage(),
                    'last_page' => $invoices->lastPage(),
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to fetch invoices', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'status' => false,
                'message' => 'Failed to fetch invoices'
            ], 500);
        }
    }
    
    /**
     * Get single invoice details
     */
    public function show($id)
    {
        try {
            $client = auth()->guard('client')->user();
            
            $invoice = Invoice::where('client_id', $client->id)
                ->where('id', $id)
                ->with(['product', 'client'])
                ->firstOrFail();
            
            return response()->json([
                'status' => true,
                'data' => [
                    'id' => $invoice->id,
                    'reference' => $invoice->reference,
                    'amount' => $invoice->amount,
                    'status' => $invoice->status,
                    'payment_method' => $invoice->payment_method,
                    'gateway' => $invoice->gateway,
                    'payment_proof' => $invoice->payment_proof,
                    'due_date' => $invoice->due_date,
                    'created_at' => $invoice->created_at,
                    'product' => [
                        'id' => $invoice->product->id ?? null,
                        'name' => $invoice->product->name ?? null,
                        'description' => $invoice->product->description ?? null,
                    ],
                    'has_pdf' => !empty($invoice->pdf_path),
                    'download_url' => $invoice->pdf_path ? url("/api/client/invoices/{$invoice->id}/download") : null,
                    'view_url' => $invoice->pdf_path ? url("/api/client/invoices/{$invoice->id}/view") : null,
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to fetch invoice', [
                'invoice_id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'status' => false,
                'message' => 'Invoice not found'
            ], 404);
        }
    }
    
    /**
     * Download invoice PDF
     */
    public function download($id)
    {
        try {
            $client = auth()->guard('client')->user();
            
            $invoice = Invoice::where('client_id', $client->id)
                ->where('id', $id)
                ->firstOrFail();
            
            Log::info('Downloading invoice PDF', [
                'invoice_id' => $invoice->id,
                'client_id' => $client->id
            ]);
            
            $pdfPath = $this->pdfService->getInvoicePdf($invoice);
            
            $filename = 'invoice_' . str_pad($invoice->id, 6, '0', STR_PAD_LEFT) . '.pdf';
            
            return response()->download($pdfPath, $filename, [
                'Content-Type' => 'application/pdf',
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to download invoice PDF', [
                'invoice_id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'status' => false,
                'message' => 'Failed to download invoice'
            ], 500);
        }
    }
    
    /**
     * View invoice PDF in browser
     */
    public function view($id)
    {
        try {
            $client = auth()->guard('client')->user();
            
            $invoice = Invoice::where('client_id', $client->id)
                ->where('id', $id)
                ->firstOrFail();
            
            Log::info('Viewing invoice PDF', [
                'invoice_id' => $invoice->id,
                'client_id' => $client->id
            ]);
            
            $pdfPath = $this->pdfService->getInvoicePdf($invoice);
            
            return response()->file($pdfPath, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="invoice_' . $invoice->id . '.pdf"'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to view invoice PDF', [
                'invoice_id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'status' => false,
                'message' => 'Failed to view invoice'
            ], 500);
        }
    }
}
