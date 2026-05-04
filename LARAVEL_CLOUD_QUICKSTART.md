# Laravel Cloud Quick Start (TL;DR)

## 30-Second Deployment Flow

### Step 1: Prepare Code
```bash
git add .
git commit -m "chore: production ready"
git push origin main
```

### Step 2: Set Environment Variables in Laravel Cloud Dashboard
```
APP_KEY=base64:YOUR_KEY_HERE
APP_URL=https://api.yourdomain.com
FRONTEND_URL=https://yourdomain.vercel.app

DB_HOST=your-db-host
DB_USERNAME=your-db-user
DB_PASSWORD=your-db-password

AWS_ACCESS_KEY_ID=your-s3-key
AWS_SECRET_ACCESS_KEY=your-s3-secret
AWS_BUCKET=your-bucket

MAIL_HOST=your-smtp-host
MAIL_USERNAME=your-email
MAIL_PASSWORD=your-password

SANCTUM_STATEFUL_DOMAINS=yourdomain.vercel.app,api.yourdomain.com
```

### Step 3: Add Domain
- Dashboard → **Domains** → Add `api.yourdomain.com`
- Update DNS CNAME record
- Wait for SSL certificate (auto-provisioned)

### Step 4: Deploy
- Deployment starts automatically when you push to GitHub
- Monitor **Deployments** tab
- Watch logs for migrations

### Step 5: Verify
```bash
curl -X POST https://api.yourdomain.com/api/login
curl -X GET https://api.yourdomain.com/api/me -H "Authorization: Bearer TOKEN"
```

## Key Configuration Files

- **`.env.example`** - Production environment template
- **`config/sanctum.php`** - Token authentication (Stateful domains)
- **`config/cors.php`** - Cross-origin requests (Frontend URL)
- **`config/filesystems.php`** - S3 storage for CVs
- **`bootstrap/app.php`** - Error handling and middleware
- **`app/Providers/AppServiceProvider.php`** - Production optimizations

## Production Defaults

| Setting | Value | Reason |
|---------|-------|--------|
| `APP_DEBUG` | `false` | Don't expose stack traces |
| `LOG_LEVEL` | `error` | Reduce log spam |
| `CACHE_DRIVER` | `redis` | High performance |
| `QUEUE_CONNECTION` | `redis` | Background jobs |
| `SESSION_ENCRYPT` | `true` | Secure cookies |
| `CV_STORAGE_DISK` | `s3` | Persistent uploads |

## Must-Have Environment Variables

```
APP_KEY              # Generated: php artisan key:generate
DB_HOST, DB_USERNAME, DB_PASSWORD
AWS_ACCESS_KEY_ID, AWS_SECRET_ACCESS_KEY, AWS_BUCKET
MAIL_HOST, MAIL_USERNAME, MAIL_PASSWORD
SANCTUM_STATEFUL_DOMAINS (include both frontend & backend domains)
```

## Testing Before Deploy

```bash
# Run tests
php artisan test

# Check code style
./vendor/bin/pint --check

# Verify migrations
php artisan migrate:status

# Test S3 connection
php artisan storage:link
Storage::disk('s3')->files()

# Test email
php artisan tinker
>>> Mail::raw('Test', fn($m) => $m->to('test@example.com'))->send()
```

## After Deployment

```bash
# Verify health
curl https://api.yourdomain.com/up

# Check logs
tail -f storage/logs/laravel.log

# Monitor queue (if using background jobs)
php artisan queue:listen --tries=1 --timeout=0
```

## Troubleshooting

| Problem | Solution |
|---------|----------|
| 500 error | Check `storage/logs/laravel.log` |
| Auth fails | Verify `SANCTUM_STATEFUL_DOMAINS` |
| CV upload fails | Check S3 credentials and bucket |
| Email not sent | Test SMTP in `php artisan tinker` |
| Slow requests | Enable Redis caching |

---

**For detailed guide, see [PRODUCTION_CHECKLIST.md](PRODUCTION_CHECKLIST.md)**
