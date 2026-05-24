# Postman Testing Guide - دليل اختبار Postman

## 🎯 دليل الاختبار الكامل بناءً على Business Flow

---

## 📋 الإعداد الأولي

### 1. Import Collection
1. افتح Postman
2. File → Import
3. اختر `Bareqq_Complete_API.postman_collection.json`
4. اضغط Import

### 2. Set Collection Variables
1. اضغط على Collection → Variables
2. تأكد من القيم:
   ```
   base_url = https://user.bareqq.com/api
   api_password = Nf:upZTg^7A?Hj
   lang = en  (أو ar للعربية)
   ```

---

## 🔄 Business Flow 1: Admin Setup (إعداد النظام)

### الهدف: Admin يقوم بإعداد المنتجات والإضافات

---

### Step 1: Admin Login
**Folder:** Universal Login (All Roles)  
**Request:** Universal Login - Admin (Email)

**Body:**
```json
{
    "identifier": "admin@bareqq.com",
    "password": "password123"
}
```

**✅ Expected:**
- Status: 200
- Response contains `admin_token`
- Token saved automatically in `{{admin_token}}`

**📝 Notes:**
- يمكن استخدام username أو phone أو email
- الـ token يُحفظ تلقائياً

---

### Step 2: Create Category (Optional)
**Folder:** Admin - Categories  
**Request:** Create Category

**Body:**
```json
{
    "name": "Web Development",
    "name_ar": "تطوير المواقع"
}
```

**✅ Expected:**
- Status: 201
- Category created with ID

---

### Step 3: Create One-Time Product
**Folder:** Admin - Products  
**Request:** Create Product

**Body:**
```json
{
    "name": "Website Design",
    "name_ar": "تصميم موقع إلكتروني",
    "description": "Professional website design service",
    "description_ar": "خدمة تصميم موقع إلكتروني احترافي",
    "note": "Includes 5 pages",
    "note_ar": "يشمل 5 صفحات",
    "price": 5000,
    "product_role": "one_time",
    "category_id": 1
}
```

**✅ Expected:**
- Status: 201
- Product created with ID
- احفظ الـ `product_id` للاستخدام لاحقاً

---

### Step 4: Create Strategy Product
**Folder:** Admin - Products  
**Request:** Create Product

**Body:**
```json
{
    "name": "Social Media Strategy",
    "name_ar": "استراتيجية التواصل الاجتماعي",
    "description": "Complete social media management",
    "description_ar": "إدارة كاملة لوسائل التواصل الاجتماعي",
    "note": "Includes content creation and scheduling",
    "note_ar": "يشمل إنشاء المحتوى والجدولة",
    "product_role": "strategy",
    "monthly_price": 2000,
    "yearly_price": 20000,
    "category_id": 1
}
```

**✅ Expected:**
- Status: 201
- Strategy product created
- احفظ الـ `product_id`

---

### Step 5: Add Strategy Tips
**Folder:** Admin - Strategy Tips  
**Request:** Create Tip

**Body:**
```json
{
    "text": "Post consistently at peak engagement times for your audience",
    "text_ar": "انشر بانتظام في أوقات الذروة لجمهورك",
    "platforms": ["facebook", "instagram", "twitter"],
    "sort_order": 1
}
```

**✅ Expected:**
- Status: 201
- Tip created and linked to product

**🔁 Repeat:** أضف 3-5 tips مختلفة

---

### Step 6: Create Addons (Optional)
**Folder:** Admin - Addons  
**Request:** Create Addon

**Body:**
```json
{
    "name": "Extra Page",
    "name_ar": "صفحة إضافية",
    "description": "Add one more page to your website",
    "description_ar": "أضف صفحة إضافية لموقعك",
    "price": 500
}
```

**✅ Expected:**
- Status: 201
- Addon created

---

## 🔄 Business Flow 2: Client Journey (رحلة العميل)

### الهدف: Client يتصفح المنتجات ويطلب خدمة

---

### Step 7: Client Register
**Folder:** Authentication  
**Request:** Client Register

**Body:**
```json
{
    "username": "testclient",
    "name": "Test Client",
    "email": "testclient@example.com",
    "phone": "01234567890",
    "password": "password123",
    "password_confirmation": "password123"
}
```

**✅ Expected:**
- Status: 201
- Client account created

---

### Step 8: Client Login
**Folder:** Universal Login (All Roles)  
**Request:** Universal Login - Client (Email)

**Body:**
```json
{
    "identifier": "testclient@example.com",
    "password": "password123"
}
```

**✅ Expected:**
- Status: 200
- `client_token` saved automatically

---

### Step 9: Browse Products
**Folder:** Client - Products  
**Request:** Get All Products

**✅ Expected:**
- Status: 200
- List of products with translations
- Products show in language based on `{{lang}}` variable

**🧪 Test Localization:**
1. Set `lang = en` → Run request → Check English names
2. Set `lang = ar` → Run request → Check Arabic names

---

### Step 10: View Product Details
**Folder:** Client - Products  
**Request:** Get Product Details

**URL:** Change `1` to your product ID

**✅ Expected:**
- Status: 200
- Full product details
- For strategy products: includes `monthly_price`, `yearly_price`, `strategy_tips`
- For one-time products: includes `price` only

---

### Step 11: Create One-Time Order
**Folder:** Client - Product Orders  
**Request:** Create One-Time Order

**Body:**
```json
{
    "product_id": 1,
    "product_role": "one_time",
    "total_price": "5000"
}
```

**✅ Expected:**
- Status: 201
- Order created
- Invoice created automatically
- احفظ `order_id` و `invoice_id`

---

### Step 12: Create Strategy Order (Monthly)
**Folder:** Client - Product Orders  
**Request:** Create Strategy Order (Monthly)

**Body:**
```json
{
    "product_id": 2,
    "product_role": "strategy",
    "total_price": "2000",
    "duration": "month"
}
```

**✅ Expected:**
- Status: 201
- Strategy order created
- Subscription created
- Invoice created
- احفظ `order_id`

---

### Step 13: View My Orders
**Folder:** Client - Product Orders  
**Request:** Get My Orders

**✅ Expected:**
- Status: 200
- List of all client orders
- Each order shows status, price, product info

---

### Step 14: View Order Details
**Folder:** Client - Product Orders  
**Request:** Get Order Details

**URL:** Change `1` to your order ID

**✅ Expected for One-Time Order:**
```json
{
    "id": 1,
    "product": {...},
    "status": "pending_payment",
    "total_price": 5000,
    "payment": {...}
}
```

**✅ Expected for Strategy Order:**
```json
{
    "id": 2,
    "strategy_id": 12,
    "starts_at": "2026-05-21",
    "ends_at": "2027-05-20",
    "strategy_tips": [...],
    "team_discussion": {
        "team_count": 4,
        "team": [...]
    },
    "works_preview": [...]
}
```

---

### Step 15: View Strategy Works (All)
**Folder:** Client - Product Orders  
**Request:** Get Strategy Works (All)

**URL:** Change `7` to your strategy order ID

**✅ Expected:**
- Status: 200
- List of all scheduled works/posts
- Each work shows date, time, platforms, status

---

### Step 16: View Strategy Works (By Date)
**Folder:** Client - Product Orders  
**Request:** Get Strategy Works (By Date)

**URL:** Change date to `2026-05-22` or any date

**✅ Expected:**
- Status: 200
- Only works scheduled for that specific date

---

### Step 17: View My Invoices
**Folder:** Client - Invoices  
**Request:** Get My Invoices

**✅ Expected:**
- Status: 200
- List of all invoices
- Shows status: paid, unpaid, overdue

---

### Step 18: View Invoice Details
**Folder:** Client - Invoices  
**Request:** Get Invoice Details

**URL:** Change `1` to your invoice ID

**✅ Expected:**
- Status: 200
- Full invoice details
- Amount, due date, payment method

---

### Step 19: Upload Payment Proof
**Folder:** Client - Invoices  
**Request:** Upload Payment Proof

**Body:** Form-data
- Key: `payment_proof`
- Type: File
- Value: Select an image/PDF file

**✅ Expected:**
- Status: 200
- Payment proof uploaded
- Waiting for admin approval

---

## 🔄 Business Flow 3: Admin Management (إدارة الطلبات)

### الهدف: Admin يدير الطلبات والفواتير

---

### Step 20: View All Orders
**Folder:** Admin - Product Orders  
**Request:** Get All Orders

**✅ Expected:**
- Status: 200
- List of all orders from all clients
- Statistics summary

---

### Step 21: View Order Details (Admin)
**Folder:** Admin - Product Orders  
**Request:** Get Order Details

**✅ Expected:**
- Status: 200
- Full order details
- Client information

---

### Step 22: Approve Payment
**Folder:** Admin - Product Orders  
**Request:** Approve Payment

**URL:** Change `1` to order ID

**✅ Expected:**
- Status: 200
- Order status changed to "paid"
- Invoice status changed to "paid"

---

### Step 23: Update Order Status
**Folder:** Admin - Product Orders  
**Request:** Update Order Status

**Body:**
```json
{
    "status": "in_progress"
}
```

**✅ Expected:**
- Status: 200
- Order status updated

**📝 Available Statuses:**
- `pending_payment`
- `paid`
- `in_progress`
- `delivered`
- `completed`
- `cancelled`

---

### Step 24: Upload Deliverable
**Folder:** Admin - Product Orders  
**Request:** Upload Deliverable

**Body:** Form-data
- Key: `deliverable`
- Type: File
- Value: Select file (ZIP, PDF, etc.)

**✅ Expected:**
- Status: 200
- Deliverable uploaded
- Client can download

---

### Step 25: View All Invoices (Admin)
**Folder:** Admin - Invoices  
**Request:** Get All Invoices

**✅ Expected:**
- Status: 200
- All invoices from all clients
- Summary statistics

---

### Step 26: Create Invoice Manually
**Folder:** Admin - Invoices  
**Request:** Create Invoice

**Body:**
```json
{
    "client_id": 2,
    "product_id": 1,
    "amount": 5000,
    "due_date": "2026-06-01",
    "payment_method": "bank_transfer"
}
```

**✅ Expected:**
- Status: 201
- Invoice created

---

## 🧪 Testing Scenarios (سيناريوهات الاختبار)

---

### Scenario 1: Complete One-Time Order Flow
```
1. Client Login
2. Browse Products
3. View Product Details (one-time)
4. Create One-Time Order
5. View My Orders
6. View Order Details
7. View My Invoices
8. Upload Payment Proof
9. [Admin] Approve Payment
10. [Admin] Update Status to "in_progress"
11. [Admin] Upload Deliverable
12. [Client] View Order Details (check deliverable)
```

---

### Scenario 2: Complete Strategy Order Flow
```
1. Client Login
2. Browse Products
3. View Product Details (strategy)
4. Create Strategy Order (Monthly)
5. View Order Details (check strategy fields)
6. View Strategy Works (All)
7. View Strategy Works (By Date)
8. Upload Payment Proof
9. [Admin] Approve Payment
10. [Admin] Add Team Members (future feature)
11. [Admin] Add Strategy Works (future feature)
12. [Client] View Updated Works
```

---

### Scenario 3: Localization Testing
```
1. Set lang = en
2. Client Login
3. Browse Products → Check English
4. View Product Details → Check English
5. Create Order → Check English messages
6. Set lang = ar
7. Browse Products → Check Arabic
8. View Product Details → Check Arabic
9. View Order Details → Check Arabic
```

---

### Scenario 4: Admin Email Login
```
1. Universal Login - Admin (Email)
   - identifier: "admin@bareqq.com"
2. Check response includes email field
3. Universal Login - Admin (Username)
   - identifier: "testadmin"
4. Universal Login - Admin (Phone)
   - identifier: "01111111111"
5. All should work successfully
```

---

## ✅ Validation Checklist

### For Each Request:
- [ ] Status code is correct (200, 201, 404, etc.)
- [ ] Response structure matches documentation
- [ ] Required fields are present
- [ ] Data types are correct
- [ ] Translations work (en/ar)
- [ ] Tokens are saved automatically
- [ ] Error messages are clear

### For Strategy Orders:
- [ ] `strategy_id` is present
- [ ] `starts_at` and `ends_at` are present
- [ ] `strategy_tips` array is present
- [ ] `team_discussion` object is present
- [ ] `works_preview` array is present

### For Localization:
- [ ] English responses when `lang = en`
- [ ] Arabic responses when `lang = ar`
- [ ] Fallback to English if Arabic missing
- [ ] System messages translated

---

## 🐛 Common Issues & Solutions

### Issue 1: Token Not Saved
**Solution:** Check "Tests" tab in login requests - should have script to save token

### Issue 2: 401 Unauthorized
**Solution:** 
- Check token is set: `{{client_token}}` or `{{admin_token}}`
- Re-login if token expired

### Issue 3: 422 Validation Error
**Solution:** 
- Check request body matches documentation
- Ensure required fields are present
- Check data types (string, number, array)

### Issue 4: Arabic Not Showing
**Solution:**
- Check `Accept-Language` header is set to `ar`
- Or set `lang` variable to `ar`
- Ensure Arabic fields exist in database

### Issue 5: Strategy Fields Missing
**Solution:**
- Ensure order is strategy type (`product_role = "strategy"`)
- Check migrations ran successfully
- Verify relationships loaded

---

## 📊 Expected Results Summary

### After Complete Testing:
- ✅ 2+ Products created (one-time + strategy)
- ✅ 3+ Strategy tips created
- ✅ 1+ Client registered
- ✅ 2+ Orders created (one-time + strategy)
- ✅ 2+ Invoices created
- ✅ Payment proof uploaded
- ✅ Payment approved by admin
- ✅ Order status updated
- ✅ Deliverable uploaded
- ✅ Strategy works visible
- ✅ Localization working (en/ar)
- ✅ Admin email login working

---

## 🎉 Success Criteria

### You've successfully tested everything when:
1. ✅ All requests return expected status codes
2. ✅ All responses match documentation
3. ✅ Localization works for both languages
4. ✅ Strategy orders show all required fields
5. ✅ Admin can manage orders and invoices
6. ✅ Client can view and interact with orders
7. ✅ Payment flow works end-to-end
8. ✅ No errors in server logs

---

## 📝 Notes

- **Test in Order:** Follow the business flows sequentially
- **Save IDs:** Keep track of created IDs for later requests
- **Check Logs:** Monitor server logs for errors
- **Use Variables:** Leverage Postman variables for dynamic data
- **Test Both Languages:** Always test en and ar
- **Document Issues:** Note any bugs or unexpected behavior

---

## 🚀 Ready to Test!

ابدأ من **Business Flow 1** واتبع الخطوات بالترتيب. حظاً موفقاً! 🍀
