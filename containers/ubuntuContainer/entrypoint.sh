#!/bin/bash
set -e

# Symbiota Container Entrypoint
# Overlays instance-specific configuration at runtime before starting Apache

echo "========================================="
echo "Symbiota Container Starting"
echo "========================================="

# Configuration overlay directory (mounted from host)
CONFIG_OVERLAY_DIR="/config-overlay"

# Symbiota installation directory
SYMBIOTA_DIR="/var/www/html/symbiota"

# Check if config overlay directory exists and is not empty
if [ -d "$CONFIG_OVERLAY_DIR" ] && [ "$(ls -A $CONFIG_OVERLAY_DIR)" ]; then
    echo "Config overlay found at $CONFIG_OVERLAY_DIR"
    echo "Overlaying configuration onto $SYMBIOTA_DIR..."

    # Copy config overlay, preserving structure and overwriting existing files
    # Exclude .git directory to reduce noise and improve performance
    # This overlays:
    #   - config/dbconnection.php (database credentials)
    #   - config/symbini.php (main config)
    #   - content/* (site content and skin)
    #   - includes/* (custom headers)
    #   - header.php, footer.php, leftmenu.php, index.php (root customizations)
    rsync -a --exclude='.git' "$CONFIG_OVERLAY_DIR"/ "$SYMBIOTA_DIR/"

    # Ensure proper ownership
    chown -R www-data:www-data "$SYMBIOTA_DIR"

    echo "Configuration overlay complete"
    echo ""
    echo "Files overlaid:"
    find "$CONFIG_OVERLAY_DIR" -type f ! -path '*/.git/*' | sed "s|$CONFIG_OVERLAY_DIR|  -|"
    echo ""
else
    echo "WARNING: No config overlay found at $CONFIG_OVERLAY_DIR"
    echo "Container will start with default/generic configuration"
    echo "This is expected for development, but NOT for production"
    echo ""
fi

# Verify critical files exist
CRITICAL_FILES=(
    "$SYMBIOTA_DIR/config/dbconnection.php"
    "$SYMBIOTA_DIR/config/symbini.php"
)

echo "Verifying critical files..."
for file in "${CRITICAL_FILES[@]}"; do
    if [ -f "$file" ]; then
        echo "  ✓ $file"
    else
        echo "  ✗ MISSING: $file"
        echo "ERROR: Critical configuration file missing!"
        echo "Cannot start without proper configuration."
        exit 1
    fi
done
echo ""

echo "Configuring Apache logging to stdout..."
# Redirect Apache error log to stdout so podman logs can capture it
ln -sf /proc/self/fd/1 /var/log/apache2/error.log
ln -sf /proc/self/fd/1 /var/log/apache2/access.log

echo "Starting Apache..."
echo "========================================="
echo ""

# Start Apache in foreground
exec apache2ctl -D FOREGROUND
