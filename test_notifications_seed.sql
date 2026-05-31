-- Test Notifications Seed Script
-- Run this to create test notifications for all roles

-- Clear existing test notifications (optional)
-- DELETE FROM notifications WHERE title LIKE 'Test%';

-- Admin Notifications (assuming admin ID 1 exists)
INSERT INTO notifications (notifiable_id, notifiable_type, title, message, is_read, created_at, updated_at)
VALUES 
(1, 'App\\Models\\Admin', 'Welcome Admin', 'Your admin account is active', 0, NOW(), NOW()),
(1, 'App\\Models\\Admin', 'System Update', 'A new system update is available', 0, NOW(), NOW()),
(1, 'App\\Models\\Admin', 'New Order', 'A new order has been placed', 1, NOW(), NOW());

-- Client Notifications (assuming client ID 1 exists)
INSERT INTO notifications (notifiable_id, notifiable_type, title, message, is_read, created_at, updated_at)
VALUES 
(1, 'App\\Models\\Client', 'Welcome Client', 'Thank you for registering', 0, NOW(), NOW()),
(1, 'App\\Models\\Client', 'Order Confirmed', 'Your order has been confirmed', 0, NOW(), NOW()),
(1, 'App\\Models\\Client', 'Payment Received', 'We have received your payment', 1, NOW(), NOW());

-- Designer Notifications (assuming designer ID 1 exists)
INSERT INTO notifications (notifiable_id, notifiable_type, title, message, is_read, created_at, updated_at)
VALUES 
(1, 'App\\Models\\Designer', 'Welcome Designer', 'Your designer account is ready', 0, NOW(), NOW()),
(1, 'App\\Models\\Designer', 'New Task', 'You have been assigned a new design task', 0, NOW(), NOW()),
(1, 'App\\Models\\Designer', 'Task Completed', 'Your task has been approved', 1, NOW(), NOW());

-- Marketer Notifications (assuming marketer ID 1 exists)
INSERT INTO notifications (notifiable_id, notifiable_type, title, message, is_read, created_at, updated_at)
VALUES 
(1, 'App\\Models\\Marketer', 'Welcome Marketer', 'Your marketer account is active', 0, NOW(), NOW()),
(1, 'App\\Models\\Marketer', 'New Campaign', 'A new marketing campaign has started', 0, NOW(), NOW()),
(1, 'App\\Models\\Marketer', 'Campaign Results', 'Your campaign results are ready', 1, NOW(), NOW());

-- Employee Notifications (assuming employee ID 1 exists)
INSERT INTO notifications (notifiable_id, notifiable_type, title, message, is_read, created_at, updated_at)
VALUES 
(1, 'App\\Models\\Employee', 'Welcome Employee', 'Welcome to the team', 0, NOW(), NOW()),
(1, 'App\\Models\\Employee', 'Meeting Reminder', 'You have a meeting at 3 PM', 0, NOW(), NOW()),
(1, 'App\\Models\\Employee', 'Leave Approved', 'Your leave request has been approved', 1, NOW(), NOW());

-- Verify the inserts
SELECT 
    notifiable_type as 'User Type',
    COUNT(*) as 'Total Notifications',
    SUM(CASE WHEN is_read = 0 THEN 1 ELSE 0 END) as 'Unread',
    SUM(CASE WHEN is_read = 1 THEN 1 ELSE 0 END) as 'Read'
FROM notifications
GROUP BY notifiable_type;
