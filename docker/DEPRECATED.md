# ⚠️ DEPRECATED - Use containers/ Directory Instead

**Status:** This directory is deprecated as of 2025-12-17

**Replacement:** Use the `containers/` directory in the repository root

---

## Why Deprecated?

This `docker/` directory contains an older, simpler container setup that:
- Uses hard-coded paths (`/home/herbaria/running-symbiota`)
- Lacks environment-specific configuration
- Has no production/development separation
- Cannot be used with Quay.io automated builds

## What to Use Instead

The new `containers/` directory provides:
- ✅ Production Dockerfile with baked-in code
- ✅ Development and production variants
- ✅ Environment variables via `.env` files
- ✅ Multi-environment support (integration, alpha, beta, prod)
- ✅ Quay.io automated builds
- ✅ Proper data persistence strategy

## Migration Path

If you're using this directory for a pre-alpha deployment, migrate to the new structure:

### Old (docker/):
```bash
cd docker/
docker-compose up -d
```

### New (containers/):
```bash
cd containers/
docker-compose --env-file .env.int -f docker-compose.prod.yaml up -d
```

See `DEPLOYMENT_WORKFLOW.md` in the repository root for full migration guide.

---

## Current Status

**Pre-Alpha Deployment:** Still running with this directory
**Target:** Migrate to `containers/` setup
**Timeline:** TBD

---

**Do not make changes to this directory.** All new container work should be done in `containers/`.
