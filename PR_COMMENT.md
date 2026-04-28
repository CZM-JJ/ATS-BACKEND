PR cleanup & reviewer checklist

Summary
- Removed temporary helper script that contained secrets.
- This PR adds an entrypoint, Docker adjustments, and CI workflow to prepare the app for Railway.

Reviewer checklist
- Confirm `entrypoint.sh` generates an `APP_KEY` and runs migrations safely.
- Confirm `Dockerfile` uses the `PORT` env and starts the app as expected.
- Check `.dockerignore` to ensure large folders are excluded.
- Confirm GH Actions workflow is acceptable (publishes to GHCR).
- Confirm no secrets remain in the branch (the temporary `scripts/set_railway_vars.sh` was removed).

Post-merge steps for maintainer
- In Railway UI: add environment variables from your secure source (do not paste secrets into PR).
- If you exposed any credentials earlier, rotate them now.
- Delete any local temporary scripts used to set variables.

How I tested
- Built and pushed branch `feature/railway-deploy` and updated it to remove temporary secrets script.

Notes
- If you want automatic Railway deploy after GHCR publish, I can add an action that calls Railway API (requires a Railway API key).
