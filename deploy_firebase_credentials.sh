#!/bin/bash

# Deploy Firebase credentials to server
# This script ensures the Firebase JSON file is in the correct location
# 
# IMPORTANT: The Firebase credentials file should be stored securely
# and NEVER committed to git. Keep it in a secure location.

SERVER_PATH="/www/wwwroot/user.bareqq.com"
FIREBASE_DIR="${SERVER_PATH}/storage/firebase"
FIREBASE_FILE="bareqq-575f1-firebase-adminsdk-fbsvc-de8ddf261d.json"

echo "=========================================="
echo "Deploying Firebase Credentials"
echo "=========================================="
echo ""

# Check if firebase directory exists
if [ ! -d "$FIREBASE_DIR" ]; then
    echo "Creating firebase directory..."
    mkdir -p "$FIREBASE_DIR"
    chmod 755 "$FIREBASE_DIR"
fi

# Check if credentials file exists locally
if [ ! -f "$FIREBASE_FILE" ]; then
    echo "❌ ERROR: Firebase credentials file not found: $FIREBASE_FILE"
    echo ""
    echo "Please ensure you have the Firebase credentials file."
    echo "This file should be stored securely and NOT in git."
    echo ""
    echo "Expected location: ./$FIREBASE_FILE"
    echo "Target location: ${FIREBASE_DIR}/${FIREBASE_FILE}"
    echo ""
    exit 1
fi

# Copy credentials to server location
echo "Copying Firebase credentials..."
cp "$FIREBASE_FILE" "$FIREBASE_DIR/"

# Set proper permissions (read/write for owner only)
chmod 600 "${FIREBASE_DIR}/${FIREBASE_FILE}"

# Verify the file was copied
if [ -f "${FIREBASE_DIR}/${FIREBASE_FILE}" ]; then
    echo "✅ Firebase credentials deployed successfully"
    echo "Location: ${FIREBASE_DIR}/${FIREBASE_FILE}"
    
    # Show file info
    echo ""
    echo "File details:"
    ls -lh "${FIREBASE_DIR}/${FIREBASE_FILE}"
    
    # Verify JSON is valid
    echo ""
    echo "Validating JSON..."
    if php -r "json_decode(file_get_contents('${FIREBASE_DIR}/${FIREBASE_FILE}')); if (json_last_error() !== JSON_ERROR_NONE) { exit(1); }"; then
        echo "✅ JSON is valid"
    else
        echo "❌ WARNING: JSON validation failed"
    fi
else
    echo "❌ ERROR: Failed to copy Firebase credentials"
    exit 1
fi

echo ""
echo "=========================================="
echo "Deployment Complete!"
echo "=========================================="
echo ""
echo "Next steps:"
echo "1. Verify config/firebase.php points to this file"
echo "2. Clear cache: php artisan config:clear"
echo "3. Test notifications with: bash test_fixed_scenarios.sh"
echo ""
echo "⚠️  SECURITY NOTE:"
echo "The Firebase credentials file is now in place with secure permissions (600)."
echo "This file should NEVER be committed to git."
echo ""
