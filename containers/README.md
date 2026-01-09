# Symbiota Container Deployment

This directory contains Docker/Podman container configurations for running Symbiota in development and production environments.

## Features

- **Multi-platform support:** Works on ARM64 (Mac M-series) and AMD64 (Linux servers)
- **Development mode:** Live code editing with bind mounts
- **Production mode:** Optimized for deployment with systemd integration
- **Flexible runtime:** Use Docker or Podman interchangeably
- **Bootstrap script:** Automated complete installation (EXPERIMENTAL - see below)

## Bootstrap Installation (EXPERIMENTAL)

**WARNING: This is experimental and has not been fully tested. Use at your own risk.**

The `scripts/bootstrap-symbiota.sh` script provides a complete automated installation that:
- Creates proper separation of code, config, and data
- Initializes database with schema
- Generates all configuration files
- Sets up a ready-to-run Symbiota instance

**Status:** Work in progress. The script has been designed but requires real-world testing before production use.

**To try it (at your own risk):**
```bash
./scripts/bootstrap-symbiota.sh --help
# Read the help, then:
./scripts/bootstrap-symbiota.sh --install-dir /path/to/test
```

**For production use, stick with the manual Quick Start below until this is fully tested.**

## Quick Start

### Development Environment

1. **Configure environment:**
   ```bash
   cp .env.example .env
   # Edit .env to set PROJECT_ROOT and other paths
   ```

2. **Start containers:**
   ```bash
   make dev-up
   ```

3. **Access Symbiota:**
   - Web interface: http://localhost:8080 (or port from .env)
   - Database: localhost:33060

4. **View logs:**
   ```bash
   make logs
   ```

5. **Stop containers:**
   ```bash
   make dev-down
   ```

### Production Deployment (Ubuntu 20.04 Server)

1. **Deploy code to server:**
   ```bash
   # On server
   sudo mkdir -p /opt/symbiota
   # Copy code to /opt/symbiota
   ```

2. **Create .env file:**
   ```bash
   cd /opt/symbiota/containers
   cp .env.example .env
   # Edit .env with production settings
   ```

3. **Install systemd service:**
   ```bash
   sudo cp systemd/symbiota.service /etc/systemd/system/
   sudo systemctl daemon-reload
   sudo systemctl enable symbiota
   sudo systemctl start symbiota
   ```

4. **Check status:**
   ```bash
   sudo systemctl status symbiota
   sudo journalctl -u symbiota -f
   ```

## Configuration

### Environment Variables (.env)

```bash
# Paths
PROJECT_ROOT=../..           # Path to Symbiota code
SCHEMA_SOURCE=../../schema   # Path to database schemas

# MySQL
MYSQL_ROOT_PASSWORD=secure_password
MYSQL_DATABASE=symbiota
MYSQL_USER=symbiota_user
MYSQL_PASSWORD=secure_password

# Ports (HOST:CONTAINER mapping)
# Format: "HOST_PORT:CONTAINER_PORT"
# - Left side: External port on your host machine (set in .env)
# - Right side: Internal port inside container (hardcoded in compose file)
#
# Development: All services exposed for debugging
HTTP_PORT=8080              # Web interface - host port for http://localhost:8080
MYSQL_PORT=33060            # Database - host port for MySQL client connections
OCR_PORT=8081               # OCR service - host port for testing endpoints
#
# Production: Only HTTP_PORT exposed; MySQL/OCR internal-only for security

# MySQL Data Storage
MYSQL_DATA_DIR=mysql-data   # Default: Docker volume
# For separate filesystem: MYSQL_DATA_DIR=/mnt/data/mysql
```

**Using a separate filesystem for database:**
If your database should be on a separate mounted filesystem:
```bash
# In .env
MYSQL_DATA_DIR=/mnt/data/mysql

# Ensure directory exists and has correct permissions
sudo mkdir -p /mnt/data/mysql
sudo chown -R 999:999 /mnt/data/mysql  # MySQL container runs as UID 999
```

### Config File Overlay

Symbiota requires instance-specific config files (from `se-symbiota-private`). There are two approaches:

**Option 1: Manual overlay (before starting containers)**
```bash
# Development
cp -r config/* worktrees/container-dev/

# Production
cp -r /path/to/config/* /opt/symbiota/
```

**Option 2: Bake into production image (recommended for immutable deploys)**
```bash
# Set CONFIG_DIR in .env
CONFIG_DIR=/path/to/se-symbiota-private

# Build will automatically include config
make prod-build
```

If `CONFIG_DIR` is set, config files are copied into the image at build time.

## Database Setup

1. **Import schema:**
   ```bash
   # Access database container
   make db-shell

   # Or manually:
   docker exec -it symbiota-db mysql -u root -p
   ```

2. **Create Symbiota users:**
   ```sql
   CREATE USER 'symbiota-r'@'%' IDENTIFIED BY 'symbiota-r-pass';
   GRANT SELECT ON symbiota.* TO 'symbiota-r'@'%';

   CREATE USER 'symbiota-rw'@'%' IDENTIFIED BY 'symbiota-rw-pass';
   GRANT SELECT, INSERT, UPDATE, DELETE ON symbiota.* TO 'symbiota-rw'@'%';

   FLUSH PRIVILEGES;
   ```

3. **Import backup (if available):**
   ```bash
   # From host
   docker exec -i symbiota-db mysql -u root -p<password> symbiota < backup.sql
   ```

## Makefile Commands

Run `make help` to see all available commands.

**Development:**
- `make dev-up` - Start development environment
- `make dev-down` - Stop development environment
- `make dev-build` - Rebuild containers

**Production:**
- `make prod-up` - Start production environment
- `make prod-down` - Stop production environment
- `make prod-build` - Rebuild containers

**Common:**
- `make logs` - View container logs
- `make shell` - Access web container shell
- `make db-shell` - Access database shell
- `make ps` - List running containers
- `make status` - Show detailed status

## Using Podman Instead of Docker

Set the `COMPOSE` variable:

```bash
# In .env
COMPOSE=podman-compose

# Or on command line
make dev-up COMPOSE=podman-compose
```

## Troubleshooting

### Containers won't start
```bash
make status          # Check container status
make logs            # View error messages
docker ps -a         # See all containers
```

### Permission errors (SELinux)
The compose files include `:z` flags for SELinux compatibility on Fedora/RHEL systems.

### Port already in use
Change `HTTP_PORT` and `MYSQL_PORT` in `.env`

### Database connection errors
1. Check database is running: `make ps`
2. Verify credentials in `.env` match Symbiota config files
3. Check network: `docker network inspect symbiota-network`

## Architecture

- **Web container:** PHP 8.2 + Apache + Tesseract OCR
- **Database container:** MySQL 5.7
- **Network:** Bridge network for container communication
- **Volumes:** Named volume for persistent database storage

## Next Steps

- Configure Xdebug for debugging (future enhancement)
- Add SSL/TLS support for production
- Configure automated backups
- Add monitoring/logging integration