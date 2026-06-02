#!/bin/bash

# Deploy Meeting Slot Auto-Population and Route Fixes
# This script uploads the updated files to fix meeting creation

echo "========================================"
echo "Meeting System Fixes Deployment"
echo "========================================"
echo ""

# Colors for output
GREEN='\033[0;32m'
BLUE='\033[0;34m'
RED='\033[0;31m'
NC='\033[0m' # No Color

echo -e "${BLUE}Files to be deployed:${NC}"
echo "1. app/Http/Requests/MeetingRequest.php - Updated validation rules"
echo "2. app/Http/Controllers/MeetingController.php - Auto-populate logic"
echo "3. app/Http/Controllers/Client/ClientMeetingController.php - Simplified validation"
echo "4. routes/api.php - Fixed route ordering (CRITICAL FIX)"
echo ""

echo -e "${BLUE}Changes Summary:${NC}"
echo "✓ Meeting creation now auto-populates date/time from slot_id"
echo "✓ Available slots endpoint accessible by both admin and client"
echo "✓ Fixed route ordering to prevent 404 errors"
echo "✓ slot_id is now required instead of date/time fields"
echo ""

# Server details (update these)
SERVER_USER="your_username"
SERVER_HOST="your_server_ip"
SERVER_PATH="/path/to/your/laravel/project"

echo -e "${BLUE}Uploading files to server...${NC}"
echo ""

# Upload files
echo "1. Uploading MeetingRequest.php..."
scp app/Http/Requests/MeetingRequest.php ${SERVER_USER}@${SERVER_HOST}:${SERVER_PATH}/app/Http/Requests/

echo "2. Uploading MeetingController.php..."
scp app/Http/Controllers/MeetingController.php ${SERVER_USER}@${SERVER_HOST}:${SERVER_PATH}/app/Http/Controllers/

echo "3. Uploading ClientMeetingController.php..."
scp app/Http/Controllers/Client/ClientMeetingController.php ${SERVER_USER}@${SERVER_HOST}:${SERVER_PATH}/app/Http/Controllers/Client/

echo "4. Uploading api.php (ROUTE FIX)..."
scp routes/api.php ${SERVER_USER}@${SERVER_HOST}:${SERVER_PATH}/routes/

echo ""
echo -e "${BLUE}Clearing cache on server...${NC}"
ssh ${SERVER_USER}@${SERVER_HOST} << 'EOF'
cd /path/to/your/laravel/project
php artisan route:clear
php artisan cache:clear
php artisan config:clear
EOF

echo ""
echo -e "${GREEN}========================================"
echo "Deployment Complete!"
echo "========================================${NC}"
echo ""
echo -e "${BLUE}Testing Steps:${NC}"
echo "1. Test available slots (admin token):"
echo "   GET /api/meetings/available-slots?date=2026-06-15"
echo ""
echo "2. Test available slots (client token):"
echo "   GET /api/meetings/available-slots?date=2026-06-15"
echo ""
echo "3. Test unbooked slots:"
echo "   GET /api/meetings/unbooked-slots"
echo ""
echo "4. Test meeting creation with slot_id:"
echo "   POST /api/meetings"
echo "   {"
echo "     \"slot_id\": 1,"
echo "     \"meeting_name\": \"Test Meeting\","
echo "     \"description\": \"Test\""
echo "   }"
echo ""
echo -e "${RED}IMPORTANT:${NC}"
echo "- The route order fix is CRITICAL - it prevents 404 errors"
echo "- Clear route cache after deployment"
echo "- Test all endpoints after deployment"
echo ""
echo "See MEETING_UPDATES_FINAL_SUMMARY.md for complete documentation"
