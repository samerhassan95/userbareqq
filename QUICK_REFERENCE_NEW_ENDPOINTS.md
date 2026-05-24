# Quick Reference - New Admin Endpoints

## 📋 Clients Management

### Get All Clients
```
GET /api/admin/clients?search=&role=&per_page=15
Authorization: Bearer {admin_token}
Accept-Language: en|ar
```

### Get Client Details
```
GET /api/admin/clients/{id}
Authorization: Bearer {admin_token}
Accept-Language: en|ar
```

---

## 📝 Content Management - Products

### Get All Products Content (EN + AR)
```
GET /api/admin/content/products?type=
Authorization: Bearer {admin_token}
Accept-Language: en|ar
```

### Get Single Product Content (EN + AR)
```
GET /api/admin/content/products/{id}
Authorization: Bearer {admin_token}
Accept-Language: en|ar
```

---

## 💡 Content Management - Strategy Tips

### Get All Strategy Tips Content (EN + AR)
```
GET /api/admin/content/strategy-tips?product_id=
Authorization: Bearer {admin_token}
Accept-Language: en|ar
```

### Get Single Strategy Tip Content (EN + AR)
```
GET /api/admin/content/strategy-tips/{id}
Authorization: Bearer {admin_token}
Accept-Language: en|ar
```

---

## 📦 Files Created

✅ `app/Http/Controllers/Admin/AdminClientController.php`
✅ `app/Http/Controllers/Admin/AdminContentController.php`
✅ `ADMIN_CLIENTS_CONTENT_ENDPOINTS.md`
✅ `ADMIN_CLIENTS_CONTENT_SUMMARY.md`
✅ `deploy_admin_clients_content.sh`

## 📝 Files Modified

✅ `routes/admin.php`
✅ `lang/en/messages.php`
✅ `lang/ar/messages.php`
✅ `Bareqq_Complete_API.postman_collection.json`

---

## 🚀 Deployment

```bash
chmod +x deploy_admin_clients_content.sh
./deploy_admin_clients_content.sh
```

---

## 📚 Documentation

- **Full Documentation:** `ADMIN_CLIENTS_CONTENT_ENDPOINTS.md`
- **Summary:** `ADMIN_CLIENTS_CONTENT_SUMMARY.md`
- **Postman Collection:** `Bareqq_Complete_API.postman_collection.json`
  - Folder: **Admin - Clients** (2 endpoints)
  - Folder: **Admin - Content Management** (4 endpoints)
