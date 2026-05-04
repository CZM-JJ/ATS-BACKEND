# Troubleshooting: CV Upload & Analytics Issues

## Issue 1: Resume/CV Not Visible on Frontend

### Root Causes

**Problem**: Frontend can't download CV from backend

**Endpoint**: `GET /api/applicants/{applicantId}/cv`

**Common Issues**:

1. **S3 Credentials Missing**
   - On production: `CV_STORAGE_DISK=s3`
   - But AWS credentials not set in Laravel Cloud
   - Result: 500 error when trying to download

2. **CORS Headers Missing**
   - S3 URL returned but frontend can't access it
   - Browser blocks cross-origin request
   - Result: 403 error in browser console

3. **S3 Bucket Configuration**
   - Bucket not publicly accessible
   - Bucket CORS not configured
   - IAM permissions incomplete

4. **Local Storage Issue** (development)
   - CV uploaded to local disk
   - Docker container uses different filesystem
   - Result: File not found on next container restart

### Diagnosis Steps

#### Step 1: Check Environment Variables
```powershell
# In Laravel Cloud Dashboard → Settings → Environment Variables
# Verify these are set:
CV_STORAGE_DISK=s3
AWS_ACCESS_KEY_ID=your-key
AWS_SECRET_ACCESS_KEY=your-secret
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=your-bucket-name
```

#### Step 2: Test CV Download Endpoint
```bash
# Get an applicant ID first
curl -X GET http://localhost:8000/api/applicants \
  -H "Authorization: Bearer YOUR_TOKEN"

# Try to download CV (replace ID with real applicant ID)
curl -X GET http://localhost:8000/api/applicants/1/cv \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -v  # verbose to see all headers
```

**Expected Response**:
- Status: 200
- Content-Type: application/pdf (or document type)
- Binary file data

**If Error 404**: CV not uploaded for this applicant
**If Error 500**: Storage configuration issue

#### Step 3: Check S3 Configuration in Code
```bash
# Run in artisan tinker
docker-compose exec app php artisan tinker

>>> config('filesystems.cv_disk')
>>> config('filesystems.disks.s3')
>>> Storage::disk('s3')->listContents('cvs')
```

#### Step 4: Verify S3 Upload Works
```bash
# Upload a test CV
curl -X PUT http://localhost:8000/api/applicants/1 \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "upload_cv=@test.pdf"

# Check if file is in S3
docker-compose exec app php artisan tinker
>>> Storage::disk('s3')->exists('cvs/test.pdf')
```

### Solution Checklist

- [ ] **S3 Bucket Created**
  - AWS Console → S3 → Create Bucket
  - Name: `ats-cv-storage`
  - Region: `us-east-1`

- [ ] **S3 CORS Configured**
  - S3 Bucket → Permissions → CORS
  - Add:
    ```json
    [
      {
        "AllowedHeaders": ["*"],
        "AllowedMethods": ["GET", "HEAD", "PUT", "POST", "DELETE"],
        "AllowedOrigins": ["https://yourdomain.vercel.app", "https://api.yourdomain.com"],
        "ExposeHeaders": ["ETag"],
        "MaxAgeSeconds": 3000
      }
    ]
    ```

- [ ] **IAM User Created** with S3 access only
  - AWS Console → IAM → Users → Create User
  - Attach Policy: `AmazonS3FullAccess` (or custom)
  - Get Access Key & Secret

- [ ] **Laravel Cloud Environment Variables Set**
  ```
  CV_STORAGE_DISK=s3
  AWS_ACCESS_KEY_ID=your-access-key
  AWS_SECRET_ACCESS_KEY=your-secret
  AWS_DEFAULT_REGION=us-east-1
  AWS_BUCKET=ats-cv-storage
  AWS_USE_PATH_STYLE_ENDPOINT=false
  ```

- [ ] **Frontend CORS Header Check**
  - Browser Console → Network tab
  - Click CV download request
  - Check Response Headers has:
    ```
    Access-Control-Allow-Origin: https://yourdomain.vercel.app
    Access-Control-Allow-Credentials: true
    ```

---

## Issue 2: Analytics Data Not Loading

### Root Causes

**Problem**: Frontend shows loading spinner but analytics never load

**Endpoint**: `GET /api/dashboard/overview?days=30`

**Common Issues**:

1. **Permission Denied**
   - Middleware not recognizing user role
   - User doesn't have `canViewAnalytics` permission
   - Result: 403 Forbidden

2. **Database Not Migrated**
   - `applicants` table doesn't exist
   - Result: 500 database error

3. **Lazy Loading Violation** (production only)
   - AppServiceProvider preventing relationship loading
   - Result: 500 error in production

4. **Authentication Not Passing**
   - Token invalid or expired
   - Result: 401 Unauthorized

5. **Database Connection Issue**
   - MySQL not accessible
   - Wrong credentials
   - Result: connection refused

### Diagnosis Steps

#### Step 1: Check Endpoint Response
```bash
curl -X GET http://localhost:8000/api/dashboard/overview \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -v
```

**If 401**: Authentication failed (check token)
**If 403**: Permission denied (check user role)
**If 500**: Server error (check logs)

#### Step 2: Check Logs for Errors
```powershell
# View real-time logs
docker-compose logs -f app

# Make the request and watch logs
curl http://localhost:8000/api/dashboard/overview ...
```

#### Step 3: Verify User Permissions
```bash
docker-compose exec app php artisan tinker

# Check if user has permission
>>> $user = App\Models\User::find(1);
>>> $user->role
>>> $user->permissions

# Check if user role is admin/hr_manager/hr_supervisor
>>> $user->role === 'admin'
```

#### Step 4: Check Database Connection
```bash
docker-compose exec app php artisan tinker

# Test DB connection
>>> DB::connection()->getPDO()

# Check if applicants table exists
>>> DB::table('applicants')->count()

# Run a single query
>>> App\Models\Applicant::count()
```

#### Step 5: Check Migrations
```powershell
# List migration status
docker-compose exec app php artisan migrate:status

# Should show all "2026_*" migrations as "Ran"
```

### Solution Checklist

- [ ] **Database Migrated**
  ```powershell
  docker-compose exec app php artisan migrate:status
  # All should be "Ran"
  
  # If not, run:
  docker-compose exec app php artisan migrate --force
  ```

- [ ] **User Has Correct Role**
  ```bash
  docker-compose exec app php artisan tinker
  >>> $user = User::find(1)
  >>> $user->role = 'admin';
  >>> $user->save();
  ```

- [ ] **Check Middleware Permission**
  - Route has permission check: ✅ Yes (`->middleware('perm:canViewAnalytics')`)
  - User role gives permission: ✅ Check [app/Http/Middleware/CheckPermission.php]()
  - 'admin', 'hr_manager', 'hr_supervisor' have access

- [ ] **Test Analytics Query**
  ```bash
  docker-compose exec app php artisan tinker
  
  >>> $overview = new App\Http\Controllers\DashboardController();
  >>> $overview->overview(request())
  # Should return analytics array
  ```

- [ ] **Check for Lazy Loading Issues** (production only)
  - Error in logs about lazy loading?
  - Check if models are eager-loading relationships
  - May need to add `with()` calls in DashboardController

---

## Testing Locally vs Production

### Local (Docker)
```
You: Upload CV
↓
App: Store in local disk (`storage/app/public/cvs`)
↓
Frontend: Download from `http://localhost:8000/storage/cvs/...`
```

### Production (Laravel Cloud)
```
You: Upload CV
↓
App: Store in S3 bucket (`s3://ats-bucket/cvs/...`)
↓
Frontend: Download from S3 signed URL
```

**Key Difference**: Frontend gets **S3 URL** not backend URL

```php
// In production
$url = Storage::disk('s3')->url($applicant->cv_path);
// Returns: https://ats-bucket.s3.amazonaws.com/cvs/filename.pdf

// In local
$url = Storage::disk('local')->url($applicant->cv_path);
// Returns: http://localhost:8000/storage/cvs/filename.pdf
```

---

## Quick Fixes

### Fix 1: CV Not Downloading
```powershell
# Option A: Test S3 Connection
docker-compose exec app php artisan tinker
>>> Storage::disk('s3')->put('test.txt', 'hello')
>>> Storage::disk('s3')->exists('test.txt')

# Option B: Use Local Storage Temporarily
# Change in docker-compose.yml:
# CV_STORAGE_DISK=local  # instead of s3
```

### Fix 2: Analytics Not Loading
```powershell
# Verify migrations ran
docker-compose exec app php artisan migrate:status

# Manually run if missing
docker-compose exec app php artisan migrate:fresh --seed

# Clear cache
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan config:clear
```

### Fix 3: Permission Denied (403)
```bash
# Set user as admin
docker-compose exec app php artisan tinker
>>> User::find(1)->update(['role' => 'admin']);
```

---

## Frontend Debugging

### Check Network Tab
1. Open DevTools (F12)
2. Go to Network tab
3. Make request
4. Check:
   - **Status Code** (200, 401, 403, 500?)
   - **Response Headers** (CORS headers?)
   - **Response Body** (error message?)

### Check Console for Errors
```javascript
// In browser console
// Should see response
fetch('/api/dashboard/overview', {
  headers: { 'Authorization': 'Bearer token' }
}).then(r => r.json()).then(console.log)
```

---

## Before Committing to GitHub

### Checklist

- [ ] **CV Upload Working**
  - [ ] Local: Can upload & download CV
  - [ ] Can configure S3 settings

- [ ] **Analytics Working**
  - [ ] Local: Analytics endpoint returns data
  - [ ] User has admin role
  - [ ] Migrations all ran

- [ ] **Production Config**
  - [ ] `.env.example` updated with S3 settings
  - [ ] PRODUCTION_CHECKLIST.md mentions S3 setup
  - [ ] Environment variables documented

- [ ] **Tests Passing**
  ```powershell
  docker-compose exec app php artisan test
  ```

- [ ] **No Errors in Logs**
  ```powershell
  docker-compose logs app | grep -i error
  ```

---

## Common Error Messages

| Error | Cause | Fix |
|-------|-------|-----|
| `No CV uploaded` | Applicant has no CV | Upload a CV first |
| `CV file not found` | File deleted from S3 | Re-upload |
| `Unable to read CV` | S3 permission denied | Check IAM policy |
| `403 Forbidden` | User not authorized | Make user admin |
| `401 Unauthorized` | Token invalid | Get new token |
| `500 Server Error` | Database error | Check `docker-compose logs app` |
| `CORS error` | Frontend can't access | Check S3 CORS config |

---

## Next Steps

1. **Test CV download** locally first
2. **Test analytics** locally first
3. **Set up S3** for production
4. **Update .env.example** with complete S3 config
5. **Update PRODUCTION_CHECKLIST.md** with S3 setup
6. **Commit all changes**
7. **Deploy to Laravel Cloud**
8. **Test both features** on production

---

## Need Help?

1. Check logs: `docker-compose logs -f app`
2. Test endpoint: `curl -v http://localhost:8000/api/...`
3. Run tinker: `docker-compose exec app php artisan tinker`
4. Review error messages in Network tab (browser)

**Remember**: 90% of issues are missing S3 credentials or wrong permissions!
