#!/bin/bash

# Test script for previously failing notification scenarios
# Scenarios 5 & 6: Post team assignment and Feedback

BASE_URL="https://user.bareqq.com/api"

echo "=========================================="
echo "Testing Previously Failing Scenarios"
echo "=========================================="
echo ""

# Get tokens from environment or prompt
read -p "Enter Admin Token: " ADMIN_TOKEN
read -p "Enter Client Token: " CLIENT_TOKEN
read -p "Enter Designer Token: " DESIGNER_TOKEN

echo ""
echo "=========================================="
echo "SCENARIO 5: Post Team Assignment"
echo "Role to use: ADMIN"
echo "Endpoint: POST /admin/posts/{post_id}/assign-team"
echo "Expected: Designer + Marketer notified"
echo "=========================================="
echo ""

read -p "Enter Post ID to assign team: " POST_ID
read -p "Enter Designer ID: " DESIGNER_ID
read -p "Enter Marketer ID: " MARKETER_ID

echo "Calling endpoint..."
curl -X POST "${BASE_URL}/admin/posts/${POST_ID}/assign-team" \
  -H "Authorization: Bearer ${ADMIN_TOKEN}" \
  -H "Content-Type: application/json" \
  -d "{
    \"designer_id\": ${DESIGNER_ID},
    \"marketer_id\": ${MARKETER_ID}
  }" | jq '.'

echo ""
echo "Checking notifications in database..."
echo "Run this on server:"
echo "mysql -u userbareqq -p userbareqq -e \"SELECT id, notifiable_type, notifiable_id, title, created_at FROM notifications WHERE notifiable_type IN ('App\\\\Models\\\\Designer', 'App\\\\Models\\\\Marketer') ORDER BY created_at DESC LIMIT 5;\""
echo ""
read -p "Press Enter to continue to Scenario 6..."

echo ""
echo "=========================================="
echo "SCENARIO 6: Feedback on Post"
echo "Role to use: CLIENT or ADMIN"
echo "Endpoint: POST /posts/{post_id}/feedback"
echo "Expected: Admin + Designer + Marketer notified (except the one who added feedback)"
echo "=========================================="
echo ""

read -p "Enter Post ID for feedback: " FEEDBACK_POST_ID
read -p "Use Client or Admin token? (client/admin): " FEEDBACK_ROLE

if [ "$FEEDBACK_ROLE" = "client" ]; then
    FEEDBACK_TOKEN=$CLIENT_TOKEN
    echo "Using CLIENT token (Admin + Designer + Marketer should be notified)"
else
    FEEDBACK_TOKEN=$ADMIN_TOKEN
    echo "Using ADMIN token (Client + Designer + Marketer should be notified)"
fi

read -p "Enter feedback comment: " FEEDBACK_COMMENT

echo "Calling endpoint..."
curl -X POST "${BASE_URL}/posts/${FEEDBACK_POST_ID}/feedback" \
  -H "Authorization: Bearer ${FEEDBACK_TOKEN}" \
  -H "Content-Type: application/json" \
  -d "{
    \"comment\": \"${FEEDBACK_COMMENT}\",
    \"status\": \"pending\"
  }" | jq '.'

echo ""
echo "Checking notifications in database..."
echo "Run this on server:"
echo "mysql -u userbareqq -p userbareqq -e \"SELECT id, notifiable_type, notifiable_id, title, message, created_at FROM notifications ORDER BY created_at DESC LIMIT 10;\""
echo ""

echo "=========================================="
echo "Test Complete!"
echo "=========================================="
echo ""
echo "Verification Steps:"
echo "1. Check database for new notifications"
echo "2. Verify correct roles were notified"
echo "3. Verify the user who performed action was NOT notified"
echo "4. Check logs: tail -50 /www/wwwroot/user.bareqq.com/storage/logs/laravel.log"
echo ""
