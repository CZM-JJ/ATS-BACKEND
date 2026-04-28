# Deploying to Railway

Steps to deploy this Laravel app to Railway:

1. Create a new Railway project and connect your GitHub repository.
2. In Railway project settings, set the build to use the repository (the included `railway.json` uses the `dockerfile` builder).
3. Add environment variables (see list below).
4. Deploy (Railway will build the Docker image using `Dockerfile`).
5. After deployment, the container entrypoint will generate `APP_KEY` if missing and run migrations automatically. If needed, run manual commands via Railway CLI: `railway run php artisan migrate --force`.

Recommended environment variables:

- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL` (your Railway service URL, e.g. `https://your-app.up.railway.app`)
- `APP_KEY` (optional; if unset the container will generate one on start)

- Database (if using PostgreSQL from Railway):
  - `DB_CONNECTION=pgsql`
  - `DB_HOST`
  - `DB_PORT`
  - `DB_DATABASE`
  - `DB_USERNAME`
  - `DB_PASSWORD`

- Mail settings (optional): `MAIL_MAILER`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_FROM_ADDRESS`, `MAIL_FROM_NAME`
- `REDIS_HOST`, `REDIS_PASSWORD`, `REDIS_PORT` (if using Redis)
- `QUEUE_CONNECTION` (e.g., `database` or `redis`)

Notes:

- The Docker image runs the `entrypoint.sh` which will copy `.env.example` to `.env` if missing, create an `APP_KEY` if not provided, and attempt to run migrations on startup.
- If you prefer not to run migrations automatically, remove the migration section from `entrypoint.sh` and run migrations manually via Railway CLI or the UI.
- Ensure persistent storage needs (file uploads) are configured to use S3 or an external storage service; Railway containers have ephemeral storage.

Optional: Auto-publish Docker image to GitHub Container Registry (GHCR)

- There's a GitHub Actions workflow included at `.github/workflows/publish-image.yml` which builds the Docker image and pushes it to GHCR as `ghcr.io/<owner>/ats-backend:<commit-sha>` on pushes to `main`.
- To let Railway deploy from the published image instead of building from the repository, create a new Railway service and choose "Container Registry" or provide the image `ghcr.io/<owner>/ats-backend:<tag>`.
- For private GHCR images, provide the registry credentials in Railway (create a secret with username and personal access token having `read:packages`). For public images, Railway can pull without extra credentials.

Notes about CI/CD and PRs

- If you'd like, I can create a branch with these changes and open a PR for you. I cannot push the branch or open the PR from this environment — you can run the following locally to create a branch and push the changes after pulling them:

```bash
git checkout -b feature/railway-deploy
git add .github/entrypoint.sh Dockerfile .dockerignore RAILWAY.md .github/workflows/publish-image.yml
git commit -m "ci: add Railway deployment entrypoint and GHCR publish workflow"
git push -u origin feature/railway-deploy
```

After pushing, open a PR in GitHub and merge to `main` to trigger the image publish workflow.
