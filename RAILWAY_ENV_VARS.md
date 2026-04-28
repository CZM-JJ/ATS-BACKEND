# Railway Environment Variables (copy into Railway project)

Copy the following into your Railway project's Environment / Variables section. Replace angle-bracket values with the values Railway shows for your attached plugins (Postgres, Redis, etc.).

Required / recommended variables

- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL=https://<your-service>.up.railway.app`
- `APP_KEY=` (optional; leave blank to let the container generate one on first start)

Database (Railway Postgres plugin)

- `DB_CONNECTION=pgsql`
- `DB_HOST=<postgres_host>`
- `DB_PORT=<postgres_port>`
- `DB_DATABASE=<postgres_database>`
- `DB_USERNAME=<postgres_username>`
- `DB_PASSWORD=<postgres_password>`

Redis (if using Railway Redis plugin)

- `REDIS_HOST=<redis_host>`
- `REDIS_PORT=<redis_port>`
- `REDIS_PASSWORD=<redis_password>`
- `REDIS_CLIENT=phpredis`

Queue and cache

- `QUEUE_CONNECTION=database` (or `redis` if you set Redis)
- `CACHE_STORE=database` (or `redis`)

Mail (optional)

- `MAIL_MAILER=smtp`
- `MAIL_HOST=<smtp_host>`
- `MAIL_PORT=<smtp_port>`
- `MAIL_USERNAME=<smtp_user>`
- `MAIL_PASSWORD=<smtp_pass>`
- `MAIL_FROM_ADDRESS=hello@example.com`
- `MAIL_FROM_NAME="Your App Name"`

Notes

- For `DB_*` and `REDIS_*` values, open your Railway project, go to the Postgres/Redis plugin, and copy the host/port/db/user/password values into the corresponding variables above.
- If you prefer to provide `APP_KEY` yourself, generate one locally with `php artisan key:generate --show` and paste it into `APP_KEY`.
- After setting variables, deploy/restart the service in Railway. The container's `entrypoint.sh` will generate a key if missing and run migrations on startup.
