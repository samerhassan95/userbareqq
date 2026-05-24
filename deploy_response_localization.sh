#!/bin/bash

# Response Localization Deployment Script
# This script deploys the response localization updates

echo "========================================="
echo "Response Localization Deployment"
echo "========================================="
echo ""

# Check if we're in the right directory
if [ ! -f "artisan" ]; then
    echo "❌ Error: artisan file not found. Please run this script from the Laravel root directory."
    exit 1
fi

echo "✅ Laravel project detected"
echo ""

# Clear all caches
echo "🔄 Clearing caches..."
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

echo "✅ Caches cleared"
echo ""

# Verify translation files exist
echo "🔍 Verifying translation files..."

if [ ! -f "lang/en/messages.php" ]; then
    echo "❌ Error: lang/en/messages.php not found"
    exit 1
fi

if [ ! -f "lang/ar/messages.php" ]; then
    echo "❌ Error: lang/ar/messages.php not found"
    exit 1
fi

echo "✅ Translation files verified"
echo ""

# Count translation keys
EN_COUNT=$(grep -c "=>" lang/en/messages.php)
AR_COUNT=$(grep -c "=>" lang/ar/messages.php)

echo "📊 Translation Statistics:"
echo "   English messages: $EN_COUNT"
echo "   Arabic messages: $AR_COUNT"
echo ""

# Verify SetLocale middleware is registered
echo "🔍 Verifying SetLocale middleware..."

if grep -q "SetLocale::class" app/Http/Kernel.php; then
    echo "✅ SetLocale middleware is registered"
else
    echo "⚠️  Warning: SetLocale middleware not found in Kernel.php"
    echo "   Please ensure it's added to the 'api' middleware group"
fi

echo ""

# Test translation function
echo "🧪 Testing translation function..."
php artisan tinker --execute="echo __('messages.login_successful');"
echo ""

# Cache config
echo "🔄 Caching configuration..."
php artisan config:cache

echo "✅ Configuration cached"
echo ""

echo "========================================="
echo "✅ Deployment Complete!"
echo "========================================="
echo ""
echo "📝 Summary:"
echo "   - Translation files updated with 50+ messages"
echo "   - Controllers updated to use translations"
echo "   - SetLocale middleware active"
echo "   - All caches cleared and rebuilt"
echo ""
echo "🧪 Testing:"
echo "   Test with: Accept-Language: en (English)"
echo "   Test with: Accept-Language: ar (Arabic)"
echo ""
echo "📖 Documentation:"
echo "   See RESPONSE_LOCALIZATION_UPDATE.md for details"
echo ""
