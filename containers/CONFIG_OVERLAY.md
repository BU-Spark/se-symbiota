# Configuration Overlay System

## Overview

Symbiota configuration files are **scattered throughout the codebase**, not contained in a single `config/` directory. This includes:

- `config/dbconnection.php` - Database credentials
- `config/symbini.php` - Main configuration
- `content/*` - Site-specific content and skin customization
- `includes/*` - Custom headers and includes
- Root files: `header.php`, `footer.php`, `leftmenu.php`, `index.php`

**The Problem:** These config files contain secrets (database passwords, API keys) that cannot be committed to the public `se-symbiota` repository.

**The Solution:** Runtime config overlay from `se-symbiota-private` repository.

---

## How It Works

### 1. Generic Image Built by Quay.io

The Docker image built from `v3.2.4-alpha` contains:
- ✅ Symbiota application code (public, no secrets)
- ✅ Generic/default configuration files
- ❌ NO environment-specific secrets
- ❌ NO site customizations

### 2. Config Overlay at Container Startup

When the container starts:

1. Entrypoint script (`/entrypoint.sh`) runs
2. Checks for `/config-overlay` mount point
3. If found, copies all files from `/config-overlay/*` to `/var/www/html/symbiota/`
4. This **overlays** the instance-specific config onto the generic code
5. Then starts Apache

**Result:** Generic public image + private config overlay = configured instance

---

## Directory Structure

### On Host Server

```
/mnt/symbiota/app/config/
├── int/                           # Integration environment config
│   ├── config/
│   │   ├── dbconnection.php      # Integration DB credentials
│   │   └── symbini.php           # Integration settings
│   ├── content/
│   │   └── lang/                 # Site content
│   ├── includes/
│   │   └── header_includes.php   # Custom includes
│   ├── header.php                # Custom header
│   ├── footer.php                # Custom footer
│   ├── leftmenu.php              # Custom menu
│   └── index.php                 # Custom homepage
│
└── alpha/                         # Alpha environment config
    ├── config/
    │   ├── dbconnection.php      # Alpha DB credentials (different!)
    │   └── symbini.php           # Alpha settings
    ├── content/
    ├── includes/
    ├── header.php
    ├── footer.php
    ├── leftmenu.php
    └── index.php
```

### In Container (After Overlay)

```
/var/www/html/symbiota/
├── config/
│   ├── dbconnection.php          # ← Overlaid from /config-overlay
│   └── symbini.php               # ← Overlaid from /config-overlay
├── content/
│   └── lang/                     # ← Overlaid from /config-overlay
├── includes/                     # ← Overlaid from /config-overlay
├── header.php                    # ← Overlaid from /config-overlay
├── footer.php                    # ← Overlaid from /config-overlay
├── leftmenu.php                  # ← Overlaid from /config-overlay
├── index.php                     # ← Overlaid from /config-overlay
└── [rest of Symbiota code from image]
```

---

## Setup Instructions

### 1. Prepare Config on Host

```bash
# Create config directories
sudo mkdir -p /mnt/symbiota/app/config/{int,alpha}

# Clone se-symbiota-private
cd /tmp
git clone --branch config-v3.2.4-local-dev git@github.com:BU-Spark/se-symbiota-private.git

# Copy to integration config
sudo cp -r se-symbiota-private/* /mnt/symbiota/app/config/int/

# Copy to alpha config
sudo cp -r se-symbiota-private/* /mnt/symbiota/app/config/alpha/

# Set ownership
sudo chown -R $(whoami):$(whoami) /mnt/symbiota/app/config

# Clean up
rm -rf se-symbiota-private
```

### 2. Customize Per Environment

**Integration (`/mnt/symbiota/app/config/int/config/dbconnection.php`):**
```php
static $SERVERS = array(
    array(
        'type' => 'readonly',
        'host' => 'symbiota-int-db',        // Different host!
        'username' => 'symbiota-r',
        'password' => 'int-r-password',      // Different password!
        'database' => 'symbiota_int',       // Different database!
        'port' => '3306',
        'charset' => 'utf8'
    ),
    array(
        'type' => 'write',
        'host' => 'symbiota-int-db',
        'username' => 'symbiota-rw',
        'password' => 'int-rw-password',    // Different password!
        'database' => 'symbiota_int',
        'port' => '3306',
        'charset' => 'utf8'
    )
);
```

**Alpha (`/mnt/symbiota/app/config/alpha/config/dbconnection.php`):**
```php
static $SERVERS = array(
    array(
        'type' => 'readonly',
        'host' => 'symbiota-alpha-db',      // Different host!
        'username' => 'symbiota-r',
        'password' => 'alpha-r-password',    // Different password!
        'database' => 'symbiota',
        'port' => '3306',
        'charset' => 'utf8'
    ),
    array(
        'type' => 'write',
        'host' => 'symbiota-alpha-db',
        'username' => 'symbiota-rw',
        'password' => 'alpha-rw-password',   // Different password!
        'database' => 'symbiota',
        'port' => '3306',
        'charset' => 'utf8'
    )
);
```

### 3. Configure .env File

**Integration (`.env.int`):**
```bash
CONFIG_OVERLAY_DIR=/mnt/symbiota/app/config/int
```

**Alpha (`.env.alpha`):**
```bash
CONFIG_OVERLAY_DIR=/mnt/symbiota/app/config/alpha
```

### 4. Start Container

```bash
docker-compose --env-file .env.alpha -f docker-compose.prod.yaml up -d
```

**On startup, you'll see:**
```
=========================================
Symbiota Container Starting
=========================================
Config overlay found at /config-overlay
Overlaying configuration onto /var/www/html/symbiota...
Configuration overlay complete

Files overlaid:
  - config/dbconnection.php
  - config/symbini.php
  - content/lang/index.php
  - includes/header_includes.php
  - header.php
  - footer.php
  - leftmenu.php
  - index.php

Verifying critical files...
  ✓ /var/www/html/symbiota/config/dbconnection.php
  ✓ /var/www/html/symbiota/config/symbini.php

Starting Apache...
=========================================
```

---

## Maintaining Config

### Updating Config for All Environments

```bash
# Update se-symbiota-private
cd ~/se-symbiota-private
vim config/symbini.php
git commit -am "Update Symbiota settings"
git push

# Deploy to integration
cp -r ~/se-symbiota-private/* /mnt/symbiota/app/config/int/
docker-compose restart symbiota-int

# Deploy to alpha (after testing in int)
cp -r ~/se-symbiota-private/* /mnt/symbiota/app/config/alpha/
docker-compose restart symbiota-alpha
```

### Environment-Specific Changes

Only change files in `/mnt/symbiota/app/config/{env}/` directly:

```bash
# Update only alpha database password
vim /mnt/symbiota/app/config/alpha/config/dbconnection.php

# Restart alpha
docker-compose restart symbiota-alpha
```

---

## Troubleshooting

### Config Not Being Applied

**Check container logs:**
```bash
docker-compose logs symbiota
```

Look for:
```
Config overlay found at /config-overlay
Overlaying configuration onto /var/www/html/symbiota...
```

If you see `WARNING: No config overlay found`, check:

1. **Mount point exists:**
   ```bash
   docker-compose exec symbiota ls -la /config-overlay
   ```

2. **.env file has correct path:**
   ```bash
   grep CONFIG_OVERLAY_DIR .env.prod
   ```

3. **Host directory exists:**
   ```bash
   ls -la /opt/symbiota-config/alpha
   ```

### Database Connection Errors

**Verify database host in config overlay:**
```bash
grep "'host'" /opt/symbiota-config/alpha/config/dbconnection.php
```

Should match the database container name from `docker-compose.prod.yaml`.

### Missing Files After Overlay

**Check what files are in overlay:**
```bash
find /opt/symbiota-config/alpha -type f
```

**Check what was overlaid:**
```bash
docker-compose logs symbiota | grep "Files overlaid:" -A 20
```

---

## Security Notes

### ✅ Secrets Stay on Host

- Database passwords are ONLY in `/opt/symbiota-config/`
- Never committed to public `se-symbiota` repository
- Not baked into Docker image
- Quay.io never sees secrets

### ✅ Config Directory Permissions

```bash
# Restrict access to config directory
sudo chown -R root:root /opt/symbiota-config
sudo chmod -R 600 /opt/symbiota-config
sudo chmod -R +X /opt/symbiota-config  # Keep directory traversal
```

### ✅ Read-Only Mount

Config overlay is mounted as `:ro` (read-only) in container:
```yaml
volumes:
  - ${CONFIG_OVERLAY_DIR}:/config-overlay:ro
```

Container cannot modify the host config files.

---

## Benefits of This Approach

✅ **Secrets separation** - No secrets in public repo or Docker image
✅ **Multi-environment** - Each environment has its own config directory
✅ **Easy updates** - Change config files on host, restart container
✅ **Quay.io compatible** - Generic image built from public repo
✅ **Immutable deployments** - Code in image, config overlaid at runtime
✅ **Symbiota-compatible** - Handles scattered config file structure

---

## See Also

- `DEPLOYMENT_WORKFLOW.md` - Full deployment process
- `MULTI_ENVIRONMENT_SETUP.md` - Setting up multiple environments
- `.env.prod.example` - Production environment template
- `.env.int.example` - Integration environment template
