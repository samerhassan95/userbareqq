<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice #{{ $invoice->id }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            margin: 40px;
            color: #333;
            line-height: 1.6;
        }
        
        .header {
            text-align: center;
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 3px solid #4CAF50;
        }
        
        .company-name {
            font-size: 32px;
            font-weight: bold;
            color: #4CAF50;
            margin-bottom: 5px;
        }
        
        .invoice-title {
            font-size: 24px;
            color: #666;
            margin-top: 10px;
        }
        
        .invoice-info {
            display: table;
            width: 100%;
            margin: 30px 0;
        }
        
        .invoice-info-left,
        .invoice-info-right {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }
        
        .invoice-info-right {
            text-align: right;
        }
        
        .info-label {
            font-weight: bold;
            color: #666;
            font-size: 12px;
            text-transform: uppercase;
        }
        
        .info-value {
            font-size: 14px;
            margin-bottom: 10px;
        }
        
        .status {
            padding: 6px 12px;
            border-radius: 4px;
            display: inline-block;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .status-paid {
            background-color: #d4edda;
            color: #155724;
        }
        
        .status-unpaid {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .bill-to {
            margin: 30px 0;
            padding: 20px;
            background-color: #f9f9f9;
            border-left: 4px solid #4CAF50;
        }
        
        .bill-to h3 {
            color: #4CAF50;
            margin-bottom: 10px;
            font-size: 16px;
        }
        
        .bill-to p {
            margin: 5px 0;
            font-size: 14px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 30px 0;
        }
        
        th {
            background-color: #4CAF50;
            color: white;
            padding: 12px;
            text-align: left;
            font-size: 14px;
            font-weight: bold;
        }
        
        td {
            border-bottom: 1px solid #ddd;
            padding: 12px;
            font-size: 14px;
        }
        
        tr:last-child td {
            border-bottom: none;
        }
        
        .total-section {
            margin-top: 30px;
            text-align: right;
        }
        
        .total-row {
            margin: 10px 0;
            font-size: 16px;
        }
        
        .total-label {
            display: inline-block;
            width: 150px;
            font-weight: bold;
            color: #666;
        }
        
        .total-value {
            display: inline-block;
            width: 150px;
            text-align: right;
        }
        
        .grand-total {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 2px solid #4CAF50;
            font-size: 20px;
            font-weight: bold;
            color: #4CAF50;
        }
        
        .footer {
            margin-top: 60px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            text-align: center;
            font-size: 12px;
            color: #666;
        }
        
        .footer p {
            margin: 5px 0;
        }
        
        .payment-info {
            margin: 20px 0;
            padding: 15px;
            background-color: #f0f8ff;
            border-radius: 4px;
        }
        
        .payment-info p {
            margin: 5px 0;
            font-size: 13px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">Bareqq</div>
        <div class="invoice-title">INVOICE</div>
    </div>
    
    <div class="invoice-info">
        <div class="invoice-info-left">
            <div class="info-label">Invoice Number</div>
            <div class="info-value">#{{ str_pad($invoice->id, 6, '0', STR_PAD_LEFT) }}</div>
            
            @if($invoice->reference)
            <div class="info-label">Reference</div>
            <div class="info-value">{{ $invoice->reference }}</div>
            @endif
        </div>
        
        <div class="invoice-info-right">
            <div class="info-label">Invoice Date</div>
            <div class="info-value">{{ $invoice->created_at->format('F d, Y') }}</div>
            
            <div class="info-label">Due Date</div>
            <div class="info-value">{{ \Carbon\Carbon::parse($invoice->due_date)->format('F d, Y') }}</div>
            
            <div class="info-label">Status</div>
            <div class="info-value">
                <span class="status status-{{ $invoice->status }}">{{ ucfirst($invoice->status) }}</span>
            </div>
        </div>
    </div>
    
    <div class="bill-to">
        <h3>Bill To:</h3>
        <p><strong>{{ $client->name }}</strong></p>
        <p>{{ $client->email }}</p>
        @if($client->phone)
        <p>{{ $client->phone }}</p>
        @endif
        @if($client->company_name)
        <p>{{ $client->company_name }}</p>
        @endif
    </div>
    
    <table>
        <thead>
            <tr>
                <th style="width: 50%;">Description</th>
                <th style="width: 20%;">Duration</th>
                <th style="width: 30%; text-align: right;">Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <strong>{{ $product->name ?? 'Service' }}</strong>
                    @if($product && $product->description)
                    <br><small style="color: #666;">{{ $product->description }}</small>
                    @endif
                </td>
                <td>{{ $order ? ucfirst($order->duration) : 'N/A' }}</td>
                <td style="text-align: right;">${{ number_format($invoice->amount, 2) }}</td>
            </tr>
        </tbody>
    </table>
    
    <div class="total-section">
        <div class="total-row">
            <span class="total-label">Subtotal:</span>
            <span class="total-value">${{ number_format($invoice->amount, 2) }}</span>
        </div>
        
        <div class="total-row grand-total">
            <span class="total-label">Total:</span>
            <span class="total-value">${{ number_format($invoice->amount, 2) }}</span>
        </div>
    </div>
    
    @if($invoice->payment_method)
    <div class="payment-info">
        <p><strong>Payment Method:</strong> {{ ucfirst(str_replace('_', ' ', $invoice->payment_method)) }}</p>
        @if($invoice->gateway)
        <p><strong>Payment Gateway:</strong> {{ ucfirst($invoice->gateway) }}</p>
        @endif
    </div>
    @endif
    
    <div class="footer">
        <p><strong>Thank you for your business!</strong></p>
        <p>For questions about this invoice, please contact us at support@bareqq.com</p>
        <p style="margin-top: 10px; font-size: 11px;">This is a computer-generated invoice and does not require a signature.</p>
    </div>
</body>
</html>
