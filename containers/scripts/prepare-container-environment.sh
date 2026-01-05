#!/bin/bash
#
# Symbiota Container Environment Setup Script
#
# This script prepares the local environment for running Symbiota containers
# by creating configuration files, directories, and validating prerequisites.
#
# Usage: ./prepare-container-environment.sh [--non-interactive]
#

set -e  # Exit on error

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
CONTAINERS_DIR="$(dirname "$SCRIPT_DIR")"
PROJECT_ROOT="$(dirname "$CONTAINERS_DIR")"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Flags
NON_INTERACTIVE=0
VERBOSE=0

# Parse command line arguments
for arg in "$@"; do
    case $arg in
        --non-interactive)
            NON_INTERACTIVE=1
            shift
            ;;
        -v|--verbose)
            VERBOSE=1
            shift
            ;;
        -h|--help)
            cat <<EOF
Symbiota Container Environment Setup

Usage: $0 [OPTIONS]

This script prepares your environment for running Symbiota containers by:
  - Checking prerequisites (Docker/Podman)
  - Creating .env configuration file
  - Creating required directories
  - Validating config overlay
  - Setting appropriate permissions

Options:
  --non-interactive   Run without prompts (use defaults)
  -v, --verbose      Show detailed output
  -h, --help         Show this help message

EOF
            exit 0
            ;;
        *)
            echo "Unknown option: $arg"
            echo "Use --help for usage information"
            exit 1
            ;;
    esac
done

# Logging functions
log_info() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

log_success() {
    echo -e "${GREEN}[OK]${NC} $1"
}

log_warning() {
    echo -e "${YELLOW}[WARN]${NC} $1"
}

log_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Prompt function (respects non-interactive mode)
prompt() {
    local prompt_text="$1"
    local default_value="$2"
    local result

    if [ "$NON_INTERACTIVE" -eq 1 ]; then
        echo "$default_value"
        return
    fi

    if [ -n "$default_value" ]; then
        read -p "$prompt_text [$default_value]: " result
        echo "${result:-$default_value}"
    else
        read -p "$prompt_text: " result
        echo "$result"
    fi
}

# Check if command exists
command_exists() {
    command -v "$1" >/dev/null 2>&1
}

# Check prerequisites
check_prerequisites() {
    log_info "Checking prerequisites..."

    # Check for Docker or Podman
    if command_exists docker; then
        CONTAINER_RUNTIME="docker"
        log_success "Docker found"
    elif command_exists podman; then
        CONTAINER_RUNTIME="podman"
        log_success "Podman found"
    else
        log_error "Neither Docker nor Podman found. Please install one of them."
        exit 1
    fi

    # Check for docker-compose or podman-compose
    if command_exists docker-compose; then
        COMPOSE_TOOL="docker-compose"
        log_success "docker-compose found"
    elif command_exists podman-compose; then
        COMPOSE_TOOL="podman-compose"
        log_success "podman-compose found"
    else
        log_error "Neither docker-compose nor podman-compose found. Please install one."
        exit 1
    fi

    # Detect OS for SELinux check
    if [ -f /etc/os-release ]; then
        . /etc/os-release
        case "$ID" in
            fedora|rhel|centos|almalinux|rocky)
                SELINUX_DETECTED=1
                log_info "SELinux-based system detected ($PRETTY_NAME)"
                ;;
            *)
                SELINUX_DETECTED=0
                log_info "Non-SELinux system detected ($PRETTY_NAME)"
                ;;
        esac
    fi
}

# Create .env file
create_env_file() {
    local env_file="$CONTAINERS_DIR/.env"
    local env_example="$CONTAINERS_DIR/.env.example"

    if [ -f "$env_file" ]; then
        log_warning ".env file already exists"
        if [ "$NON_INTERACTIVE" -eq 0 ]; then
            read -p "Overwrite existing .env file? [y/N]: " overwrite
            if [[ ! "$overwrite" =~ ^[Yy]$ ]]; then
                log_info "Keeping existing .env file"
                return
            fi
        else
            log_info "Keeping existing .env file (non-interactive mode)"
            return
        fi
    fi

    log_info "Creating .env configuration file..."

    # Determine default paths relative to containers directory
    local default_project_root="../.."
    local default_config_dir="../../config"
    local default_schema_source="../../config/schema"

    # Gather configuration
    PROJECT_ROOT_PATH=$(prompt "Path to Symbiota code (relative to containers/ directory)" "$default_project_root")
    CONFIG_DIR_PATH=$(prompt "Path to config directory (leave empty to skip config overlay)" "$default_config_dir")
    SCHEMA_SOURCE_PATH=$(prompt "Path to database schema files" "$default_schema_source")
    SYMBIOTA_DATA_PATH=$(prompt "Path to Symbiota data directory (images/specimens, leave empty for none)" "")

    # MySQL configuration
    log_info "MySQL configuration:"
    MYSQL_ROOT_PASS=$(prompt "MySQL root password" "password")
    MYSQL_DB=$(prompt "MySQL database name" "symbiota")
    MYSQL_USER=$(prompt "MySQL user" "symbiota-user")
    MYSQL_PASS=$(prompt "MySQL user password" "symbiota-pass")
    MYSQL_DATA=$(prompt "MySQL data directory (use mysql-data for Docker volume, or absolute path)" "mysql-data")

    # Port configuration
    log_info "Port configuration:"
    HTTP_PORT=$(prompt "HTTP port for web interface" "8080")
    MYSQL_PORT=$(prompt "MySQL port" "33060")

    # SELinux flag
    if [ "${SELINUX_DETECTED:-0}" -eq 1 ]; then
        SELINUX_FLAG=":z"
        log_info "Setting SELINUX_FLAG=:z for your system"
    else
        SELINUX_FLAG=""
    fi

    # Write .env file
    cat > "$env_file" <<EOF
# Symbiota Container Configuration
# Generated by prepare-container-environment.sh on $(date)

# ==============================================================================
# PATH CONFIGURATION
# ==============================================================================

# Path to Symbiota code (relative to this containers/ directory)
PROJECT_ROOT=$PROJECT_ROOT_PATH

# Path to Symbiota data directory (images, specimens, etc.)
# Leave empty to use code directory
SYMBIOTA_DATA=$SYMBIOTA_DATA_PATH

# Path to instance-specific config files
# Leave empty to skip config overlay
CONFIG_DIR=$CONFIG_DIR_PATH

# Path to database schema/backup files
SCHEMA_SOURCE=$SCHEMA_SOURCE_PATH

# ==============================================================================
# MYSQL CONFIGURATION
# ==============================================================================

MYSQL_ROOT_PASSWORD=$MYSQL_ROOT_PASS
MYSQL_DATABASE=$MYSQL_DB
MYSQL_USER=$MYSQL_USER
MYSQL_PASSWORD=$MYSQL_PASS

# MySQL data directory
# Default: Uses Docker named volume "mysql-data"
# For separate filesystem: Set to absolute path (e.g., /mnt/data/mysql)
MYSQL_DATA_DIR=$MYSQL_DATA

# ==============================================================================
# PORT CONFIGURATION
# ==============================================================================

HTTP_PORT=$HTTP_PORT
MYSQL_PORT=$MYSQL_PORT

# ==============================================================================
# COMPOSE CONFIGURATION
# ==============================================================================

# Which compose file to use
COMPOSE_FILE=docker-compose.dev.yaml

# Container runtime
COMPOSE=$COMPOSE_TOOL

# SELinux volume flags (for Fedora/RHEL/CentOS/Alma Linux)
# Set to ":z" for SELinux systems, leave empty for others
SELINUX_FLAG=$SELINUX_FLAG

EOF

    chmod 600 "$env_file"  # Protect password file
    log_success "Created .env file"
    log_warning "Remember to review and customize .env as needed"
}

# Create required directories
create_directories() {
    log_info "Checking required directories..."

    # Load .env to get paths
    if [ -f "$CONTAINERS_DIR/.env" ]; then
        set -a
        . "$CONTAINERS_DIR/.env"
        set +a
    fi

    # Create MySQL data directory if it's a path (not a volume name)
    if [ -n "${MYSQL_DATA_DIR}" ] && [[ "${MYSQL_DATA_DIR}" == /* ]]; then
        if [ ! -d "${MYSQL_DATA_DIR}" ]; then
            log_info "Creating MySQL data directory: ${MYSQL_DATA_DIR}"
            mkdir -p "${MYSQL_DATA_DIR}"
            # MySQL container runs as UID 999
            if [ "$NON_INTERACTIVE" -eq 0 ]; then
                log_warning "MySQL container runs as UID 999. You may need to:"
                echo "  sudo chown -R 999:999 ${MYSQL_DATA_DIR}"
            fi
        else
            log_success "MySQL data directory exists: ${MYSQL_DATA_DIR}"
        fi
    fi

    # Create Symbiota data directory if specified
    if [ -n "${SYMBIOTA_DATA}" ]; then
        if [ ! -d "${SYMBIOTA_DATA}" ]; then
            log_info "Creating Symbiota data directory: ${SYMBIOTA_DATA}"
            mkdir -p "${SYMBIOTA_DATA}"
        else
            log_success "Symbiota data directory exists: ${SYMBIOTA_DATA}"
        fi
    fi
}

# Validate config overlay
validate_config() {
    log_info "Validating configuration overlay..."

    if [ -f "$CONTAINERS_DIR/.env" ]; then
        set -a
        . "$CONTAINERS_DIR/.env"
        set +a
    fi

    if [ -z "${CONFIG_DIR}" ]; then
        log_warning "No CONFIG_DIR set - config overlay will be skipped"
        log_warning "You'll need to manually configure Symbiota after starting containers"
        return
    fi

    # Resolve config directory path (relative to containers dir)
    local config_path="${CONTAINERS_DIR}/${CONFIG_DIR}"

    if [ ! -d "$config_path" ]; then
        log_error "Config directory not found: $config_path"
        log_error "Please ensure your config repository is cloned to: $config_path"
        if [ "$NON_INTERACTIVE" -eq 0 ]; then
            echo ""
            echo "Example: git clone <your-config-repo> $config_path"
        fi
        return 1
    fi

    log_success "Config directory found: $config_path"

    # Check for key config files
    local config_files=("symbini.php" "dbconnection.php")
    local missing_files=0

    for file in "${config_files[@]}"; do
        if [ ! -f "${config_path}/${file}" ] && [ ! -f "${config_path}/config/${file}" ]; then
            log_warning "Config file not found: $file"
            missing_files=$((missing_files + 1))
        fi
    done

    if [ $missing_files -gt 0 ]; then
        log_warning "Some config files are missing. You may need to run config/setup.bash"
    else
        log_success "Key config files found"
    fi
}

# Print next steps
print_next_steps() {
    echo ""
    echo "========================================================================"
    echo "  Container Environment Setup Complete"
    echo "========================================================================"
    echo ""
    log_success "Your environment is ready for containerized Symbiota development"
    echo ""
    echo "Next steps:"
    echo ""
    echo "1. Review and customize the .env file:"
    echo "   vi $CONTAINERS_DIR/.env"
    echo ""
    echo "2. Start the development environment:"
    echo "   cd $CONTAINERS_DIR"
    echo "   make dev-up"
    echo ""
    echo "3. Access your Symbiota instance:"
    echo "   Web: http://localhost:${HTTP_PORT:-8080}"
    echo "   DB:  localhost:${MYSQL_PORT:-33060}"
    echo ""
    echo "For production deployment, see README.md for systemd integration."
    echo ""
}

# Main execution
main() {
    echo ""
    echo "========================================================================"
    echo "  Symbiota Container Environment Setup"
    echo "========================================================================"
    echo ""

    cd "$CONTAINERS_DIR"

    check_prerequisites
    create_env_file
    create_directories
    validate_config || true  # Don't fail on validation warnings
    print_next_steps
}

main
