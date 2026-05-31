-- Notification Templates for Bareqq Platform
-- Run this SQL to create all notification templates

-- Clear existing templates (optional - uncomment if needed)
-- DELETE FROM notification_templates;

INSERT INTO notification_templates (type, title, message, title_ar, message_ar, created_at, updated_at) VALUES
-- Order notifications
('order_created', 'New Order Received', 'New order #{order_id} from {client_name}', 'طلب جديد', 'طلب جديد #{order_id} من {client_name}', NOW(), NOW()),
('order_confirmed', 'Order Confirmed', 'Your order #{order_id} has been received and is being processed', 'تأكيد الطلب', 'تم استلام طلبك #{order_id} وجاري معالجته', NOW(), NOW()),
('payment_approved', 'Payment Approved', 'Your payment for order #{order_id} has been approved. Work will begin soon!', 'تمت الموافقة على الدفع', 'تمت الموافقة على دفعتك للطلب #{order_id}. سيبدأ العمل قريباً!', NOW(), NOW()),

-- Team assignment
('team_assigned_to_order', 'New Project Assignment', 'You have been assigned to {client_name}\'s project', 'تعيين مشروع جديد', 'تم تعيينك لمشروع {client_name}', NOW(), NOW()),
('team_assigned_notification_client', 'Team Assigned', 'Your team has been assigned to your project', 'تم تعيين الفريق', 'تم تعيين فريقك لمشروعك', NOW(), NOW()),

-- Post notifications
('post_created', 'New Post Created', 'New post "{title}" created for your review', 'منشور جديد', 'تم إنشاء منشور جديد "{title}" للمراجعة', NOW(), NOW()),
('post_assigned', 'Post Assignment', 'You have been assigned to work on post: {title}', 'تعيين منشور', 'تم تعيينك للعمل على منشور: {title}', NOW(), NOW()),
('post_updated', 'Post Updated', 'Post "{title}" has been updated', 'تحديث المنشور', 'تم تحديث المنشور "{title}"', NOW(), NOW()),

-- Feedback notifications
('feedback_added', 'New Feedback', '{user_name} added feedback on post: {title}', 'ملاحظات جديدة', '{user_name} أضاف ملاحظات على المنشور: {title}', NOW(), NOW()),

-- Approval notifications
('post_approved', 'Post Approved! 🎉', 'Post "{title}" has been approved', 'تمت الموافقة على المنشور', 'تمت الموافقة على المنشور "{title}"', NOW(), NOW()),
('post_published', 'Post Published 🚀', 'Your post "{title}" has been published successfully', 'تم نشر المنشور', 'تم نشر منشورك "{title}" بنجاح', NOW(), NOW()),

-- Order completion
('order_completed', 'Order Completed', 'Your order #{order_id} has been completed. Thank you!', 'اكتمل الطلب', 'اكتمل طلبك #{order_id}. شكراً لك!', NOW(), NOW()),
('order_status_changed', 'Order Status Updated', 'Your order #{order_id} status: {status}', 'تحديث حالة الطلب', 'حالة طلبك #{order_id}: {status}', NOW(), NOW())

ON DUPLICATE KEY UPDATE
    title = VALUES(title),
    message = VALUES(message),
    title_ar = VALUES(title_ar),
    message_ar = VALUES(message_ar),
    updated_at = NOW();
