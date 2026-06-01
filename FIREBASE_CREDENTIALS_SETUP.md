# Firebase Credentials Setup

## ⚠️ IMPORTANT SECURITY NOTE

The Firebase credentials file (`bareqq-575f1-firebase-adminsdk-fbsvc-de8ddf261d.json`) contains sensitive information and should **NEVER** be committed to git.

This file has been added to `.gitignore` to prevent accidental commits.

---

## 📁 File Location

**Local Development:**
- Keep the file in a secure location outside the git repository
- Or keep it in the project root (it's in `.gitignore`)

**Production Server:**
- Path: `/www/wwwroot/user.bareqq.com/storage/firebase/bareqq-575f1-firebase-adminsdk-fbsvc-de8ddf261d.json`
- Permissions: `600` (read/write for owner only)

---

## 🚀 Deployment Methods

### Method 1: Using SCP (Recommended)

From your local machine:

```bash
scp bareqq-575f1-firebase-adminsdk-fbsvc-de8ddf261d.json root@user.bareqq.com:/www/wwwroot/user.bareqq.com/storage/firebase/

# Set proper permissions
ssh root@user.bareqq.com "chmod 600 /www/wwwroot/user.bareqq.com/storage/firebase/bareqq-575f1-firebase-adminsdk-fbsvc-de8ddf261d.json"
```

### Method 2: Using the Deployment Script

If the file is already on the server:

```bash
cd /www/wwwroot/user.bareqq.com
bash deploy_firebase_credentials.sh
```

### Method 3: Manual Copy

1. Copy the file content
2. On the server, create the file:

```bash
cd /www/wwwroot/user.bareqq.com
mkdir -p storage/firebase
nano storage/firebase/bareqq-575f1-firebase-adminsdk-fbsvc-de8ddf261d.json
# Paste the content
# Save and exit (Ctrl+X, Y, Enter)

# Set permissions
chmod 600 storage/firebase/bareqq-575f1-firebase-adminsdk-fbsvc-de8ddf261d.json
```

---

## ✅ Verification

After deploying, verify the file is in place:

```bash
# Check file exists
ls -lh /www/wwwroot/user.bareqq.com/storage/firebase/bareqq-575f1-firebase-adminsdk-fbsvc-de8ddf261d.json

# Verify JSON is valid
php -r "json_decode(file_get_contents('/www/wwwroot/user.bareqq.com/storage/firebase/bareqq-575f1-firebase-adminsdk-fbsvc-de8ddf261d.json')); echo json_last_error() === JSON_ERROR_NONE ? 'Valid JSON' : 'Invalid JSON';"

# Check config points to correct file
cd /www/wwwroot/user.bareqq.com
php artisan tinker
>>> config('firebase.credentials')
# Should output: /www/wwwroot/user.bareqq.com/storage/firebase/bareqq-575f1-firebase-adminsdk-fbsvc-de8ddf261d.json
```

---

## 🔧 Configuration

The file path is configured in `config/firebase.php`:

```php
'credentials' => env('FIREBASE_CREDENTIALS', storage_path('firebase/bareqq-575f1-firebase-adminsdk-fbsvc-de8ddf261d.json')),
```

You can override this in `.env` if needed:

```env
FIREBASE_CREDENTIALS=/custom/path/to/firebase-credentials.json
```

---

## 🐛 Troubleshooting

### File Not Found Error

```bash
# Check if directory exists
ls -la /www/wwwroot/user.bareqq.com/storage/firebase/

# Create directory if missing
mkdir -p /www/wwwroot/user.bareqq.com/storage/firebase
chmod 755 /www/wwwroot/user.bareqq.com/storage/firebase
```

### Permission Denied Error

```bash
# Fix permissions
chmod 600 /www/wwwroot/user.bareqq.com/storage/firebase/bareqq-575f1-firebase-adminsdk-fbsvc-de8ddf261d.json
chown www-data:www-data /www/wwwroot/user.bareqq.com/storage/firebase/bareqq-575f1-firebase-adminsdk-fbsvc-de8ddf261d.json
```

### Invalid JSON Error

- Re-download the file from Firebase Console
- Ensure no extra characters were added during copy/paste
- Verify the file is complete (should end with `}`)

---

## 🔐 Security Best Practices

1. **Never commit to git** - Already in `.gitignore`
2. **Restrict file permissions** - Use `chmod 600`
3. **Limit access** - Only authorized personnel should have access
4. **Rotate credentials** - If compromised, generate new credentials from Firebase Console
5. **Use environment variables** - For additional security, consider using environment variables for sensitive values

---

## 📝 Getting New Credentials

If you need to regenerate the credentials:

1. Go to [Firebase Console](https://console.firebase.google.com/)
2. Select your project: `bareqq-575f1`
3. Go to Project Settings → Service Accounts
4. Click "Generate New Private Key"
5. Download the JSON file
6. Deploy to server using one of the methods above

---

## ✅ After Deployment

Once the file is in place:

1. Clear cache:
```bash
cd /www/wwwroot/user.bareqq.com
php artisan config:clear
php artisan cache:clear
```

2. Restart PHP-FPM:
```bash
systemctl restart php-fpm-82
```

3. Test notifications:
```bash
bash test_fixed_scenarios.sh
```

---

## 📚 Related Documentation

- `NOTIFICATION_SYSTEM_COMPLETE.md` - Complete system overview
- `FINAL_DEPLOYMENT_AND_TEST.md` - Deployment guide
- `QUICK_TEST_CHECKLIST.md` - Testing guide
- `deploy_firebase_credentials.sh` - Automated deployment script
