#!/bin/bash

echo "Deploying Sliders Implementation..."

# Upload controller
echo "Uploading SliderController..."
# scp app/Http/Controllers/SliderController.php root@user.bareqq.com:/www/wwwroot/user.bareqq.com/app/Http/Controllers/

# Upload routes
echo "Uploading routes..."
# scp routes/api.php root@user.bareqq.com:/www/wwwroot/user.bareqq.com/routes/

# Upload migration
echo "Uploading migration..."
# scp database/migrations/2026_06_01_160000_make_product_id_nullable_in_sliders_table.php root@user.bareqq.com:/www/wwwroot/user.bareqq.com/database/migrations/

echo ""
echo "Now run these commands on the server:"
echo "cd /www/wwwroot/user.bareqq.com"
echo "php artisan migrate"
echo "php artisan route:clear"
echo "php artisan cache:clear"
echo "php artisan config:clear"
echo ""
echo "Then test the slider creation again."
