#!/bin/sh
set -e

# WARNING: This script contains secrets and will set them in your Railway project.
# Run this locally only on a secure machine. Do NOT commit this file with secrets
# to a public repository. You can delete this script after running.

# Require railway CLI
if ! command -v railway >/dev/null 2>&1; then
  echo "railway CLI not found. Install with: npm i -g @railway/cli" >&2
  exit 1
fi

echo "Make sure you're logged in: railway login"
echo "Make sure the project is linked or set RAILWAY_PROJECT_ID env."

railway variables set APP_DEBUG "false"
railway variables set APP_ENV "production"
railway variables set APP_FAKER_LOCALE "en_US"
railway variables set APP_FALLBACK_LOCALE "en"
railway variables set APP_KEY "base64:QYpMalUPVll3r/poplkAyOkF6imAY1lAnHlxENn95qA="
railway variables set APP_LOCALE "en"
railway variables set APP_MAINTENANCE_DRIVER "file"
railway variables set APP_NAME "Applicant Tracking System"
railway variables set APP_URL "https://web-production-5b219.up.railway.app"
railway variables set AWS_DEFAULT_REGION "us-east-1"
railway variables set AWS_USE_PATH_STYLE_ENDPOINT "false"
railway variables set BCRYPT_ROUNDS "12"
railway variables set BROADCAST_CONNECTION "log"
railway variables set CACHE_STORE "database"

# DATABASE: If you use Railway's DB plugin, replace DB_HOST/DB_PORT/DB_DATABASE/DB_USERNAME/DB_PASSWORD
# with the values shown in the Railway plugin UI. Current values below are from your list.
railway variables set DB_CONNECTION "mysql"
railway variables set DB_DATABASE "ats_production"
railway variables set DB_HOST "localhost"
railway variables set DB_PORT "3306"
railway variables set DB_USERNAME "railway"
railway variables set DB_PASSWORD "mpiGwQdSXqAOmFuETuAWRfEpIgIyIEtU"

railway variables set FILESYSTEM_DISK "local"
railway variables set FRONTEND_URL "https://ats-czm.vercel.app"
railway variables set LOG_CHANNEL "stack"
railway variables set LOG_DEPRECATIONS_CHANNEL "null"
railway variables set LOG_LEVEL "error"
railway variables set LOG_STACK "single"
railway variables set MAIL_FROM_ADDRESS "jchua@czarkmak.com"
railway variables set MAIL_FROM_NAME "CZARK MAK CORPORATION"
railway variables set MAIL_HOST "smtp.gmail.com"
railway variables set MAIL_MAILER "smtp"
railway variables set MAIL_PASSWORD "iesoqnsxpjujdnct"
railway variables set MAIL_PORT "587"
railway variables set MAIL_SCHEME "tls"
railway variables set MAIL_USERNAME "jchua@czarkmak.com"

railway variables set MEMCACHED_HOST "127.0.0.1"
railway variables set QUEUE_CONNECTION "database"
railway variables set REDIS_CLIENT "phpredis"
railway variables set REDIS_HOST "127.0.0.1"
# Set empty password (if none): use "" (empty string)
railway variables set REDIS_PASSWORD ""
railway variables set REDIS_PORT "6379"

railway variables set SANCTUM_STATEFUL_DOMAINS "ats-czm.vercel.app,web-production-5b219.up.railway.app,localhost,127.0.0.1"
railway variables set SESSION_DOMAIN "your-domain.com"
railway variables set SESSION_DRIVER "database"
railway variables set SESSION_ENCRYPT "true"
railway variables set SESSION_LIFETIME "120"
railway variables set SESSION_PATH "/"
railway variables set SESSION_SECURE_COOKIE "true"
railway variables set VITE_APP_NAME "CZARK MAK TALENT HUB"

echo "Done. Verify variables in Railway UI and restart the service."
