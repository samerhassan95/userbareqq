# Invoice PDF System - Visual Flow Diagram

## 🎯 Complete Flow Visualization

```
┌─────────────────────────────────────────────────────────────────────────┐
│                         INVOICE PDF SYSTEM FLOW                          │
└─────────────────────────────────────────────────────────────────────────┘

STEP 1: CLIENT CREATES ORDER
┌──────────┐                                    ┌──────────┐
│  Client  │──POST /api/client/product-orders──>│  Server  │
└──────────┘                                    └──────────┘
                                                      │
                                                      ├─> Create Order
                                                      │   status: pending_payment
                                                      │
                                                      └─> Create Invoice
                                                          status: unpaid
                                                          pdf_path: NULL ❌

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

STEP 2: CLIENT UPLOADS PAYMENT PROOF
┌──────────┐                                    ┌──────────┐
│  Client  │──POST /api/client/invoices/46/────>│  Server  │
│          │     upload-payment-proof           └──────────┘
└──────────┘                                          │
     │                                                │
     └─> [payment.jpg]                               ├─> Save to storage/
                                                      │   payment_proofs/
                                                      │
                                                      └─> Update Invoice
                                                          payment_proof: ✅
                                                          status: unpaid (waiting)

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

STEP 3: ADMIN APPROVES PAYMENT (PDF GENERATED HERE!)
┌──────────┐                                    ┌──────────┐
│  Admin   │──POST /api/admin/product-orders/1/>│  Server  │
│          │     approve-payment                └──────────┘
└──────────┘                                          │
                                                      ├─> Update Invoice
                                                      │   status: paid ✅
                                                      │
                                                      ├─> Generate PDF 📄
                                                      │   InvoicePdfService
                                                      │   ├─> Load template
                                                      │   ├─> Fill data
                                                      │   └─> Save PDF
                                                      │
                                                      ├─> Save to storage/
                                                      │   invoices/invoice_46_xxx.pdf
                                                      │
                                                      ├─> Update Invoice
                                                      │   pdf_path: ✅
                                                      │
                                                      ├─> Update Order
                                                      │   status: paid
                                                      │
                                                      ├─> Create Subscription
                                                      │   (if strategy product)
                                                      │
                                                      └─> Send Notification
                                                          to Client 🔔

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

STEP 4: CLIENT LISTS INVOICES
┌──────────┐                                    ┌──────────┐
│  Client  │──GET /api/client/invoices────────>│  Server  │
└──────────┘                                    └──────────┘
     ▲                                                │
     │                                                │
     └────────────────────────────────────────────────┘
                    Response:
                    {
                      "id": 46,
                      "status": "paid",
                      "has_pdf": true ✅,
                      "download_url": "https://...",
                      "view_url": "https://..."
                    }

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

STEP 5: CLIENT DOWNLOADS PDF
┌──────────┐                                    ┌──────────┐
│  Client  │──GET /api/client/invoices/46/─────>│  Server  │
│          │     download                       └──────────┘
└──────────┘                                          │
     ▲                                                ├─> Check if PDF exists
     │                                                │   ├─> Yes: Return file
     │                                                │   └─> No: Generate first
     │                                                │
     └────────────────────────────────────────────────┘
                    [invoice_000046.pdf] 📥

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

STEP 6: CLIENT VIEWS PDF IN BROWSER
┌──────────┐                                    ┌──────────┐
│  Client  │──GET /api/client/invoices/46/─────>│  Server  │
│          │     view                           └──────────┘
└──────────┘                                          │
     ▲                                                ├─> Check if PDF exists
     │                                                │   ├─> Yes: Return file
     │                                                │   └─> No: Generate first
     │                                                │
     └────────────────────────────────────────────────┘
                    [PDF opens in browser] 👁️
```

---

## 📊 Database State Changes

```
┌─────────────────────────────────────────────────────────────────────────┐
│                         DATABASE STATE FLOW                              │
└─────────────────────────────────────────────────────────────────────────┘

AFTER STEP 1 (Order Created):
┌─────────────────────────────────────────────────────────────────────────┐
│ orders table                                                             │
├─────────────────────────────────────────────────────────────────────────┤
│ id: 1                                                                    │
│ client_id: 2                                                             │
│ product_id: 2                                                            │
│ status: pending_payment ⏳                                               │
│ total_price: 200.00                                                      │
└─────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────┐
│ invoices table                                                           │
├─────────────────────────────────────────────────────────────────────────┤
│ id: 46                                                                   │
│ client_id: 2                                                             │
│ product_id: 2                                                            │
│ amount: 200.00                                                           │
│ status: unpaid ❌                                                        │
│ payment_proof: NULL                                                      │
│ pdf_path: NULL ❌                                                        │
└─────────────────────────────────────────────────────────────────────────┘

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

AFTER STEP 2 (Payment Proof Uploaded):
┌─────────────────────────────────────────────────────────────────────────┐
│ invoices table                                                           │
├─────────────────────────────────────────────────────────────────────────┤
│ id: 46                                                                   │
│ status: unpaid ⏳ (waiting approval)                                     │
│ payment_proof: payment_proofs/xyz.jpg ✅                                 │
│ pdf_path: NULL ❌                                                        │
└─────────────────────────────────────────────────────────────────────────┘

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

AFTER STEP 3 (Admin Approves - PDF GENERATED!):
┌─────────────────────────────────────────────────────────────────────────┐
│ orders table                                                             │
├─────────────────────────────────────────────────────────────────────────┤
│ id: 1                                                                    │
│ status: paid ✅                                                          │
└─────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────┐
│ invoices table                                                           │
├─────────────────────────────────────────────────────────────────────────┤
│ id: 46                                                                   │
│ status: paid ✅                                                          │
│ payment_proof: payment_proofs/xyz.jpg ✅                                 │
│ pdf_path: invoices/invoice_46_1717234567.pdf ✅ 📄                       │
└─────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────┐
│ subscriptions table (if strategy product)                               │
├─────────────────────────────────────────────────────────────────────────┤
│ id: 1                                                                    │
│ client_id: 2                                                             │
│ product_id: 2                                                            │
│ status: active ✅                                                        │
│ starts_at: 2026-06-01                                                    │
│ expires_at: 2026-07-01                                                   │
└─────────────────────────────────────────────────────────────────────────┘
```

---

## 🗂️ File System Changes

```
┌─────────────────────────────────────────────────────────────────────────┐
│                         FILE SYSTEM STATE                                │
└─────────────────────────────────────────────────────────────────────────┘

AFTER STEP 2 (Payment Proof Uploaded):
storage/app/public/
└── payment_proofs/
    └── xyz.jpg ✅

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

AFTER STEP 3 (Admin Approves - PDF Generated):
storage/app/public/
├── payment_proofs/
│   └── xyz.jpg ✅
└── invoices/
    └── invoice_46_1717234567.pdf ✅ 📄

Accessible via:
https://user.bareqq.com/storage/invoices/invoice_46_1717234567.pdf
```

---

## 🔐 Security & Access Control

```
┌─────────────────────────────────────────────────────────────────────────┐
│                         ACCESS CONTROL                                   │
└─────────────────────────────────────────────────────────────────────────┘

CLIENT PERMISSIONS:
✅ Create orders (their own)
✅ Upload payment proof (their own invoices)
✅ List invoices (their own only)
✅ View invoice details (their own only)
✅ Download PDF (their own only)
✅ View PDF (their own only)
❌ Approve payments
❌ View other clients' invoices

ADMIN PERMISSIONS:
✅ View all orders
✅ View all invoices
✅ Approve payments (generates PDF)
✅ Update order status
✅ Create/update/delete invoices
✅ View all clients' data
```

---

## ⚡ Key Timing Points

```
┌─────────────────────────────────────────────────────────────────────────┐
│                         WHEN THINGS HAPPEN                               │
└─────────────────────────────────────────────────────────────────────────┘

PDF Generation:
├─> Automatic: When admin approves payment ✅
├─> On-demand: When client downloads (if missing) ✅
└─> Never: When order is created ❌

Invoice Status Changes:
├─> unpaid: When order is created
├─> unpaid: After payment proof uploaded (waiting)
└─> paid: When admin approves ✅

Order Status Changes:
├─> pending_payment: When order is created
├─> paid: When admin approves payment
├─> in_progress: When work starts
└─> delivered: When work is complete

Notifications Sent:
├─> Order created: Client + Admin
├─> Payment proof uploaded: Admin (optional)
├─> Payment approved: Client ✅
└─> Order status changed: Client
```

---

## 🎨 PDF Template Structure

```
┌─────────────────────────────────────────────────────────────────────────┐
│                         BAREQQ INVOICE                                   │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                          │
│  Invoice #000046                          Date: June 01, 2026           │
│  Reference: INV-001                       Due: June 08, 2026            │
│                                           Status: [PAID]                │
│                                                                          │
│  Bill To:                                                                │
│  John Doe                                                                │
│  john@example.com                                                        │
│  +20 123 456 7890                                                        │
│                                                                          │
├─────────────────────────────────────────────────────────────────────────┤
│  Description              Duration              Amount                  │
├─────────────────────────────────────────────────────────────────────────┤
│  Social Media Strategy    Month                 $200.00                 │
│  Monthly management                                                      │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                          │
│                                          Subtotal:  $200.00             │
│                                          Total:     $200.00             │
│                                                                          │
│  Payment Method: Bank Transfer                                           │
│  Payment Gateway: Opay                                                   │
│                                                                          │
├─────────────────────────────────────────────────────────────────────────┤
│  Thank you for your business!                                            │
│  For questions, contact: support@bareqq.com                              │
└─────────────────────────────────────────────────────────────────────────┘
```

---

**Visual flow complete!** 🎉
