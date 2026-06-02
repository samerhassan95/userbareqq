#!/bin/bash
# ============================================================
# Deploy Meeting Fix: meeting_team_members table + model fixes
# Run this on the SERVER: bash deploy_meeting_fix.sh
# ============================================================

SERVER_PATH="/www/wwwroot/user.bareqq.com"
cd "$SERVER_PATH" || { echo "❌ Cannot cd to $SERVER_PATH"; exit 1; }

echo "============================================"
echo "  Deploying Meeting Team Fix"
echo "============================================"

# 1. Pull latest code
echo ""
echo "📥 Pulling latest code..."
git pull origin main
if [ $? -ne 0 ]; then
    echo "⚠️  git pull failed – trying git reset..."
    git fetch origin
    git reset --hard origin/main
fi

# 2. Run the new migration (creates meeting_team_members table)
echo ""
echo "🗄️  Running migrations..."
php artisan migrate --force
if [ $? -ne 0 ]; then
    echo "❌ Migration failed. Check the error above."
    exit 1
fi

# 3. Clear caches so updated controllers/models are picked up
echo ""
echo "🧹 Clearing caches..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan optimize:clear

# 4. Verify the new table exists
echo ""
echo "✅ Verifying meeting_team_members table..."
php artisan tinker --execute="
if (Schema::hasTable('meeting_team_members')) {
    echo '✅ meeting_team_members table exists' . PHP_EOL;
} else {
    echo '❌ meeting_team_members table MISSING' . PHP_EOL;
}
if (Schema::hasTable('meetings')) {
    echo '✅ meetings table exists' . PHP_EOL;
} else {
    echo '❌ meetings table MISSING' . PHP_EOL;
}
"

echo ""
echo "============================================"
echo "  ✅ Deployment complete!"
echo "============================================"
echo ""
echo "Test these endpoints:"
echo "  GET  /client/meetings"
echo "  GET  /client/meetings/filter?status=waiting"
echo "  GET  /client/meetings/{id}/join"
echo "  POST /client/meetings"
echo "  GET  /client/available-slots?date=2026-06-10"
echo "  GET  /client/unbooked-slots"
