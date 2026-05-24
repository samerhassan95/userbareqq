# 📝 نظام Posts - دليل شامل

## 🎯 نظرة عامة

نظام Posts يسمح بإنشاء وتعديل ومشاهدة المنشورات بصلاحيات مختلفة حسب نوع المستخدم.

---

## 👥 الصلاحيات

| المستخدم | إنشاء | تعديل | حذف | عرض |
|---------|------|------|-----|-----|
| **Admin** | ✅ | ✅ | ✅ | ✅ |
| **Marketer** | ✅ | ✅ | ❌ | ✅ |
| **Designer** | ❌ | ✅ | ❌ | ✅ |
| **Client** | ❌ | ❌ | ❌ | ✅ (Published only) |

---

## 📊 بنية الجدول

### Posts Table:
```sql
CREATE TABLE posts (
    id BIGINT PRIMARY KEY,
    title VARCHAR(255),
    title_ar VARCHAR(255) NULL,
    content TEXT,
    content_ar TEXT NULL,
    image VARCHAR(255) NULL,
    status ENUM('draft', 'published', 'archived') DEFAULT 'draft',
    
    -- Creator (polymorphic)
    created_by_id BIGINT,
    created_by_type VARCHAR(255),
    
    -- Last Editor (polymorphic)
    updated_by_id BIGINT,
    updated_by_type VARCHAR(255),
    
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP NULL
);
```

---

## 🔐 الـ Endpoints

### 1. Admin Endpoints

#### 1.1 Get All Posts
```
GET /api/admin/posts
```

**Query Parameters:**
- `status` (optional): draft, published, archived
- `search` (optional): البحث في العنوان والمحتوى
- `pagination` (optional): true/false
- `per_page` (optional): عدد العناصر

**Response:**
```json
{
    "success": true,
    "message": "Posts retrieved successfully",
    "data": [
        {
            "id": 1,
            "title": "First Post",
            "title_ar": "المنشور الأول",
            "content": "Content here...",
            "content_ar": "المحتوى هنا...",
            "image": "post_image.jpg",
            "status": "published",
            "created_by": {
                "id": 1,
                "name": "Admin Name"
            },
            "updated_by": {
                "id": 2,
                "name": "Designer Name"
            }
        }
    ]
}
```

---

#### 1.2 Create Post
```
POST /api/admin/posts
```

**Headers:**
```
Authorization: Bearer {admin_token}
Content-Type: multipart/form-data
```

**Body (form-data):**
- `title` (required): العنوان بالإنجليزي
- `title_ar` (optional): العنوان بالعربي
- `content` (required): المحتوى بالإنجليزي
- `content_ar` (optional): المحتوى بالعربي
- `image` (optional): صورة المنشور
- `status` (optional): draft, published, archived

**Response:**
```json
{
    "success": true,
    "message": "Post created successfully",
    "data": {
        "id": 1,
        "title": "New Post",
        ...
    }
}
```

---

#### 1.3 Update Post
```
PUT /api/admin/posts/{id}
POST /api/admin/posts/{id}  (for form-data with image)
```

**Body:** نفس حقول الإنشاء (كلها optional)

---

#### 1.4 Delete Post
```
DELETE /api/admin/posts/{id}
```

---

### 2. Marketer Endpoints

#### 2.1 Get All Posts
```
GET /api/marketer/posts
```

#### 2.2 Create Post
```
POST /api/marketer/posts
```

#### 2.3 Update Post
```
PUT /api/marketer/posts/{id}
POST /api/marketer/posts/{id}
```

---

### 3. Designer Endpoints

#### 3.1 Get All Posts
```
GET /api/designer/posts
```

#### 3.2 Update Post (للتصميم والصور)
```
PUT /api/designer/posts/{id}
POST /api/designer/posts/{id}
```

---

### 4. Client Endpoints (View Only)

#### 4.1 Get Published Posts
```
GET /api/client/posts
```

**Note:** العملاء يشاهدون المنشورات المنشورة فقط (status=published)

#### 4.2 Get Single Post
```
GET /api/client/posts/{id}
```

---

## 🔄 سير العمل (Workflow)

### السيناريو 1: Admin ينشئ منشور
```
1. Admin → POST /api/admin/posts
   - title: "New Campaign"
   - content: "..."
   - status: "draft"
   - created_by: Admin

2. Designer → PUT /api/designer/posts/1
   - image: campaign_design.jpg
   - updated_by: Designer

3. Marketer → PUT /api/marketer/posts/1
   - content: "Updated content..."
   - updated_by: Marketer

4. Admin → PUT /api/admin/posts/1
   - status: "published"
   - updated_by: Admin

5. Client → GET /api/client/posts
   - يشاهد المنشور المنشور
```

---

### السيناريو 2: Marketer ينشئ منشور
```
1. Marketer → POST /api/marketer/posts
   - title: "Social Media Post"
   - content: "..."
   - status: "draft"
   - created_by: Marketer

2. Designer → PUT /api/designer/posts/2
   - image: social_design.jpg
   - updated_by: Designer

3. Admin → PUT /api/admin/posts/2
   - status: "published"
   - updated_by: Admin
```

---

## 📸 رفع الصور

### استخدام form-data:
```javascript
const formData = new FormData();
formData.append('title', 'Post Title');
formData.append('content', 'Post content');
formData.append('image', fileInput.files[0]);
formData.append('status', 'draft');

fetch('/api/admin/posts', {
    method: 'POST',
    headers: {
        'Authorization': 'Bearer ' + token
    },
    body: formData
});
```

---

## 🎨 حالات الـ Status

### draft (مسودة):
- المنشور قيد الإعداد
- غير مرئي للعملاء
- يمكن التعديل عليه

### published (منشور):
- المنشور مرئي للجميع
- العملاء يمكنهم مشاهدته
- يمكن التعديل عليه

### archived (مؤرشف):
- المنشور مؤرشف
- غير مرئي للعملاء
- يمكن استرجاعه

---

## 🔒 الأمان

### Middleware:
- `admin` → للـ Admin فقط
- `auth:api` + `check.role:marketer` → للـ Marketer
- `auth:api` + `check.role:designer` → للـ Designer
- `auth:api` + `check.role:client` → للـ Client

### Polymorphic Relations:
```php
created_by_type: 'App\Models\Admin' أو 'App\Models\Client'
created_by_id: معرف المستخدم
```

---

## 📝 ملاحظات مهمة

### 1. الصور:
- يتم حفظها في `public/posts/`
- الحد الأقصى: 2MB
- الصيغ المدعومة: jpeg, png, jpg, gif

### 2. الترجمة:
- `title` و `content` → إنجليزي (required)
- `title_ar` و `content_ar` → عربي (optional)

### 3. Soft Delete:
- الحذف soft delete
- يمكن استرجاع المنشورات المحذوفة

---

## 🧪 الاختبار

### 1. Admin ينشئ منشور:
```bash
curl -X POST "https://user.bareqq.com/api/admin/posts" \
  -H "Authorization: Bearer {admin_token}" \
  -F "title=Test Post" \
  -F "content=This is a test" \
  -F "status=draft" \
  -F "image=@/path/to/image.jpg"
```

### 2. Marketer يعدل منشور:
```bash
curl -X PUT "https://user.bareqq.com/api/marketer/posts/1" \
  -H "Authorization: Bearer {marketer_token}" \
  -H "Content-Type: application/json" \
  -d '{
    "content": "Updated content by marketer"
  }'
```

### 3. Client يشاهد المنشورات:
```bash
curl -X GET "https://user.bareqq.com/api/client/posts" \
  -H "Authorization: Bearer {client_token}"
```

---

**نظام Posts جاهز للاستخدام! 🎉**
