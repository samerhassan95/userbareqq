<?php

namespace App\Services;

use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class InvoicePdfService
{
    /**
     * Generate PDF for an invoice
     */
    public function generateInvoicePdf(Invoice $invoice): string
    {
        try {
            // Load invoice with relationships
            $invoice->load(['client', 'product']);
            
            // Try to load product order if exists
            $order = $invoice->productOrder ?? null;
            
            Log::info('Generating PDF for invoice', ['invoice_id' => $invoice->id]);
            
            // Generate PDF from blade template
            $pdf = Pdf::loadView('invoices.template', [
                'invoice' => $invoice,
                'client' => $invoice->client,
                'product' => $invoice->product,
                'order' => $order,
            ]);
            
            // Set paper size and orientation
            $pdf->setPaper('a4', 'portrait');
            
            // Generate filename
            $filename = 'invoice_' . $invoice->id . '_' . time() . '.pdf';
            $path = 'invoices/' . $filename;
            
            // Ensure directory exists
            if (!Storage::disk('public')->exists('invoices')) {
                Storage::disk('public')->makeDirectory('invoices');
            }
            
            // Save PDF to storage
            Storage::disk('public')->put($path, $pdf->output());
            
            Log::info('PDF generated successfully', [
                'invoice_id' => $invoice->id,
                'path' => $path
            ]);
            
            return $path;
            
        } catch (\Exception $e) {
            Log::error('Failed to generate invoice PDF', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
    
    /**
     * Get invoice PDF path, generate if doesn't exist
     */
    public function getInvoicePdf(Invoice $invoice): string
    {
        // Refresh invoice from database to get latest pdf_path
        $invoice->refresh();
        
        // Check if PDF path exists in database and file exists on disk
        if ($invoice->pdf_path && Storage::disk('public')->exists($invoice->pdf_path)) {
            // Return existing PDF
            return Storage::disk('public')->path($invoice->pdf_path);
        }
        
        // Generate new PDF if doesn't exist
        $pdfPath = $this->generateInvoicePdf($invoice);
        $invoice->pdf_path = $pdfPath;
        $invoice->save();
        
        return Storage::disk('public')->path($pdfPath);
    }
    
    /**
     * Regenerate PDF for an invoice
     */
    public function regenerateInvoicePdf(Invoice $invoice): string
    {
        // Delete old PDF if exists
        if ($invoice->pdf_path && Storage::disk('public')->exists($invoice->pdf_path)) {
            Storage::disk('public')->delete($invoice->pdf_path);
        }
        
        // Generate new PDF
        $invoice->pdf_path = $this->generateInvoicePdf($invoice);
        $invoice->save();
        
        return $invoice->pdf_path;
    }
}
