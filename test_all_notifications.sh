#!/bin/bash

# Colors
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

BASE_URL="https://user.bareqq.com/api"

echo -e "${BLUE}╔════════════════════════════════════════════════════════════╗${NC}"
echo -e "${BLUE}║     BAREQQ NOTIFICATION SYSTEM - COMPLETE TEST SUITE      ║${NC}"
echo -e "${BLUE}╔════════════════════════════════════════════════════════════╗${NC}"
echo ""

# Function to check notifications in database
check_db_notifications() {
    local role=$1
    local expected_count=$2
    
    echo -e "${YELLOW}📊 Checking $role notifications in database...${NC}"
    
    case $role in
        "admin")
            result=$(mysql -u userbareqq -p userbareqq -se "SELECT COUNT(*) FROM notifications WHERE notifiable_type = 'App\\\\Models\\\\Admin' ORDER BY created_at DESC;")
            ;;
        "client")
            result=$(mysql -u userbareqq -p userbareqq -se "SELECT COUNT(*) FROM notifications WHERE notifiable_type = 'App\\\\Models\\\\Client' ORDER BY created_at DESC;")
            ;;
        "designer")
            result=$(mysql -u userbareqq -p userbareqq -se "SELECT COUNT(*) FROM notifications WHERE notifiable_type = 'App\\\\Models\\\\Designer' ORDER BY created_at DESC;")
            ;;
        "marketer")
            result=$(mysql -u userbareqq -p userbareqq -se "SELECT COUNT(*) FROM notifications WHERE notifiable_type = 'App\\\\Models\\\\Marketer' ORDER BY created_at DESC;")
            ;;
    esac
    
    echo -e "   Found: $result notifications"
    
    if [ "$result" -ge "$expected_count" ]; then
        echo -e "${GREEN}   ✅ PASS${NC}"
    else
        echo -e "${RED}   ❌ FAIL (Expected at least $expected_count)${NC}"
    fi
    echo ""
}

# Function to show latest notifications
show_latest_notifications() {
    local role=$1
    local limit=${2:-3}
    
    echo -e "${BLUE}📋 Latest $limit $role notifications:${NC}"
    
    case $role in
        "admin")
            mysql -u userbareqq -p userbareqq -se "SELECT id, title, LEFT(message, 50) as message, created_at FROM notifications WHERE notifiable_type = 'App\\\\Models\\\\Admin' ORDER BY created_at DESC LIMIT $limit;" | column -t
            ;;
        "client")
            mysql -u userbareqq -p userbareqq -se "SELECT id, title, LEFT(message, 50) as message, created_at FROM notifications WHERE notifiable_type = 'App\\\\Models\\\\Client' ORDER BY created_at DESC LIMIT $limit;" | column -t
            ;;
        "designer")
            mysql -u userbareqq -p userbareqq -se "SELECT id, title, LEFT(message, 50) as message, created_at FROM notifications WHERE notifiable_type = 'App\\\\Models\\\\Designer' ORDER BY created_at DESC LIMIT $limit;" | column -t
            ;;
        "marketer")
            mysql -u userbareqq -p userbareqq -se "SELECT id, title, LEFT(message, 50) as message, created_at FROM notifications WHERE notifiable_type = 'App\\\\Models\\\\Marketer' ORDER BY created_at DESC LIMIT $limit;" | column -t
            ;;
    esac
    echo ""
}

echo -e "${YELLOW}═══════════════════════════════════════════════════════════${NC}"
echo -e "${YELLOW}  SCENARIO 1: Client Creates Order${NC}"
echo -e "${YELLOW}═══════════════════════════════════════════════════════════${NC}"
echo ""
echo -e "${BLUE}🎭 Use Role: CLIENT${NC}"
echo ""
echo -e "${BLUE}Expected Notifications:${NC}"
echo -e "  • Client: Order Confirmed"
echo -e "  • Admin: New Order Received"
echo ""
echo -e "${YELLOW}👉 Action Required:${NC}"
echo -e "   1. Open Postman"
echo -e "   2. ${GREEN}Login as CLIENT:${NC} POST /client/login"
echo -e "   3. Create Order: POST /client/product-orders"
echo -e "   4. Press ENTER when done..."
read -p ""

check_db_notifications "client" 1
check_db_notifications "admin" 1
show_latest_notifications "client" 1
show_latest_notifications "admin" 1

echo -e "${YELLOW}═══════════════════════════════════════════════════════════${NC}"
echo -e "${YELLOW}  SCENARIO 2: Admin Approves Payment${NC}"
echo -e "${YELLOW}═══════════════════════════════════════════════════════════${NC}"
echo ""
echo -e "${BLUE}🎭 Use Role: ADMIN${NC}"
echo ""
echo -e "${BLUE}Expected Notifications:${NC}"
echo -e "  • Client: Payment Approved"
echo ""
echo -e "${YELLOW}👉 Action Required:${NC}"
echo -e "   1. ${GREEN}Login as ADMIN:${NC} POST /admin/login"
echo -e "   2. Approve Payment: POST /admin/product-orders/{id}/approve-payment"
echo -e "   3. Press ENTER when done..."
read -p ""

check_db_notifications "client" 2
show_latest_notifications "client" 2

echo -e "${YELLOW}═══════════════════════════════════════════════════════════${NC}"
echo -e "${YELLOW}  SCENARIO 3: Admin Assigns Team to Order${NC}"
echo -e "${YELLOW}═══════════════════════════════════════════════════════════${NC}"
echo ""
echo -e "${BLUE}🎭 Use Role: ADMIN${NC}"
echo ""
echo -e "${BLUE}Expected Notifications:${NC}"
echo -e "  • Client: Team Assigned"
echo -e "  • Designer(s): New Project Assignment"
echo -e "  • Marketer(s): New Project Assignment"
echo ""
echo -e "${YELLOW}👉 Action Required:${NC}"
echo -e "   1. ${GREEN}Use ADMIN token${NC}"
echo -e "   2. Assign Team: POST /admin/product-orders/{id}/team"
echo -e "   3. Body: { \"designer_ids\": [1], \"marketer_ids\": [1] }"
echo -e "   4. Press ENTER when done..."
read -p ""

check_db_notifications "client" 3
check_db_notifications "designer" 1
check_db_notifications "marketer" 1
show_latest_notifications "client" 1
show_latest_notifications "designer" 1
show_latest_notifications "marketer" 1

echo -e "${YELLOW}═══════════════════════════════════════════════════════════${NC}"
echo -e "${YELLOW}  SCENARIO 4: Admin Creates Post${NC}"
echo -e "${YELLOW}═══════════════════════════════════════════════════════════${NC}"
echo ""
echo -e "${BLUE}🎭 Use Role: ADMIN${NC}"
echo ""
echo -e "${BLUE}Expected Notifications:${NC}"
echo -e "  • Client: New Post Created"
echo -e "  • Designer(s): New Post Created (if assigned to order)"
echo -e "  • Marketer(s): New Post Created (if assigned to order)"
echo ""
echo -e "${YELLOW}👉 Action Required:${NC}"
echo -e "   1. ${GREEN}Use ADMIN token${NC}"
echo -e "   2. Create Post: POST /admin/posts"
echo -e "   3. Body: { \"product_order_id\": {order_id}, ... }"
echo -e "   4. Press ENTER when done..."
read -p ""

check_db_notifications "client" 4
check_db_notifications "designer" 2
check_db_notifications "marketer" 2
show_latest_notifications "client" 1
show_latest_notifications "designer" 1
show_latest_notifications "marketer" 1

echo -e "${YELLOW}═══════════════════════════════════════════════════════════${NC}"
echo -e "${YELLOW}  SCENARIO 5: Admin Assigns Team to Post${NC}"
echo -e "${YELLOW}═══════════════════════════════════════════════════════════${NC}"
echo ""
echo -e "${BLUE}🎭 Use Role: ADMIN${NC}"
echo ""
echo -e "${BLUE}Expected Notifications:${NC}"
echo -e "  • Designer(s): Post Assignment"
echo -e "  • Marketer(s): Post Assignment"
echo ""
echo -e "${YELLOW}👉 Action Required:${NC}"
echo -e "   1. ${GREEN}Use ADMIN token${NC}"
echo -e "   2. Assign Team to Post: POST /admin/posts/{id}/team"
echo -e "   3. Body: { \"team_members\": [{\"member_id\": 1, \"member_type\": \"designer\"}, {\"member_id\": 1, \"member_type\": \"marketer\"}] }"
echo -e "   4. Press ENTER when done..."
read -p ""

check_db_notifications "designer" 3
check_db_notifications "marketer" 3
show_latest_notifications "designer" 1
show_latest_notifications "marketer" 1

echo -e "${YELLOW}═══════════════════════════════════════════════════════════${NC}"
echo -e "${YELLOW}  SCENARIO 6: Someone Adds Feedback${NC}"
echo -e "${YELLOW}═══════════════════════════════════════════════════════════${NC}"
echo ""
echo -e "${RED}🎭 Use Role: CLIENT (NOT Admin/Designer/Marketer!)${NC}"
echo -e "${RED}⚠️  IMPORTANT: The person adding feedback will NOT be notified${NC}"
echo ""
echo -e "${BLUE}Expected Notifications:${NC}"
echo -e "  • Admin: New Feedback (if CLIENT adds feedback)"
echo -e "  • Designer(s): New Feedback (if CLIENT adds feedback)"
echo -e "  • Marketer(s): New Feedback (if CLIENT adds feedback)"
echo -e "  • Client: NOT notified (they added the feedback)"
echo ""
echo -e "${YELLOW}👉 Action Required:${NC}"
echo -e "   1. ${GREEN}Login as CLIENT:${NC} POST /client/login"
echo -e "   2. Add Feedback: POST /posts/{id}/feedback"
echo -e "   3. Body: { \"comment\": \"Great work!\" }"
echo -e "   4. Press ENTER when done..."
read -p ""

check_db_notifications "admin" 2
check_db_notifications "client" 5
check_db_notifications "designer" 4
check_db_notifications "marketer" 4
show_latest_notifications "admin" 1
show_latest_notifications "client" 1
show_latest_notifications "designer" 1
show_latest_notifications "marketer" 1

echo -e "${YELLOW}═══════════════════════════════════════════════════════════${NC}"
echo -e "${YELLOW}  SCENARIO 7: Post Approved${NC}"
echo -e "${YELLOW}═══════════════════════════════════════════════════════════${NC}"
echo ""
echo -e "${RED}🎭 Use Role: CLIENT (NOT Admin!)${NC}"
echo -e "${RED}⚠️  IMPORTANT: The person approving will NOT be notified${NC}"
echo ""
echo -e "${BLUE}Expected Notifications:${NC}"
echo -e "  • Admin: Post Approved (if CLIENT approves)"
echo -e "  • Designer(s): Post Approved (if CLIENT approves)"
echo -e "  • Marketer(s): Post Approved (if CLIENT approves)"
echo -e "  • Client: NOT notified (they approved it)"
echo ""
echo -e "${YELLOW}👉 Action Required:${NC}"
echo -e "   1. ${GREEN}Login as CLIENT:${NC} POST /client/login"
echo -e "   2. Approve Post: POST /posts/{id}/approve"
echo -e "   3. Press ENTER when done..."
read -p ""

check_db_notifications "admin" 3
check_db_notifications "client" 6
check_db_notifications "designer" 5
check_db_notifications "marketer" 5
show_latest_notifications "admin" 1
show_latest_notifications "client" 1
show_latest_notifications "designer" 1
show_latest_notifications "marketer" 1

echo ""
echo -e "${GREEN}╔════════════════════════════════════════════════════════════╗${NC}"
echo -e "${GREEN}║                    TEST SUMMARY                            ║${NC}"
echo -e "${GREEN}╔════════════════════════════════════════════════════════════╗${NC}"
echo ""

echo -e "${BLUE}📊 Final Notification Counts:${NC}"
echo ""

admin_count=$(mysql -u userbareqq -p userbareqq -se "SELECT COUNT(*) FROM notifications WHERE notifiable_type = 'App\\\\Models\\\\Admin';")
client_count=$(mysql -u userbareqq -p userbareqq -se "SELECT COUNT(*) FROM notifications WHERE notifiable_type = 'App\\\\Models\\\\Client';")
designer_count=$(mysql -u userbareqq -p userbareqq -se "SELECT COUNT(*) FROM notifications WHERE notifiable_type = 'App\\\\Models\\\\Designer';")
marketer_count=$(mysql -u userbareqq -p userbareqq -se "SELECT COUNT(*) FROM notifications WHERE notifiable_type = 'App\\\\Models\\\\Marketer';")

echo -e "  Admin:     $admin_count notifications (Expected: 3)"
echo -e "  Client:    $client_count notifications (Expected: 6)"
echo -e "  Designer:  $designer_count notifications (Expected: 5)"
echo -e "  Marketer:  $marketer_count notifications (Expected: 5)"
echo ""

total=$((admin_count + client_count + designer_count + marketer_count))
echo -e "${GREEN}  Total: $total notifications created${NC}"
echo ""

echo -e "${BLUE}📋 All Notifications by Role:${NC}"
echo ""
echo -e "${YELLOW}ADMIN:${NC}"
show_latest_notifications "admin" 10
echo -e "${YELLOW}CLIENT:${NC}"
show_latest_notifications "client" 10
echo -e "${YELLOW}DESIGNER:${NC}"
show_latest_notifications "designer" 10
echo -e "${YELLOW}MARKETER:${NC}"
show_latest_notifications "marketer" 10

echo -e "${GREEN}✅ Testing Complete!${NC}"
echo ""
echo -e "${BLUE}Next Steps:${NC}"
echo -e "  1. Test GET /notifications endpoint for each role"
echo -e "  2. Test marking notifications as read"
echo -e "  3. Verify Firebase push notifications (if device_token set)"
