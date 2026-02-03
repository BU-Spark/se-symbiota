# Symbiota Container Management Makefile
#
# Usage:
#   make dev-up      # Start development environment
#   make prod-up     # Start production environment
#   make down        # Stop containers
#   make logs        # View logs
#   make shell       # Access container shell

# Load environment variables from .env if it exists
ifneq (,$(wildcard .env))
    include .env
    export
endif

# Default compose tool (docker-compose or podman-compose)
COMPOSE ?= docker-compose

# Runtime method (compose or systemd)
RUNTIME ?= compose

# Compose files
DEV_COMPOSE = docker-compose.dev.yaml
PROD_COMPOSE = docker-compose.prod.yaml

# Temporary compose files with SYMBIOTA_DATA uncommented
DEV_COMPOSE_TMP = .docker-compose.dev.tmp.yaml
PROD_COMPOSE_TMP = .docker-compose.prod.tmp.yaml

.PHONY: help dev-up dev-down prod-up prod-down down restart logs ps shell db-shell build clean status prepare-compose

# Default target
all: help

help:
	@echo "Symbiota Container Management"
	@echo ""
	@echo "Development:"
	@echo "  make dev-up       Start development environment"
	@echo "  make dev-down     Stop development environment"
	@echo "  make dev-build    Build development containers"
	@echo "  make dev-rebuild  Rebuild with --no-cache"
	@echo ""
	@echo "Production:"
	@echo "  make prod-up      Start production environment (RUNTIME=compose|systemd)"
	@echo "  make prod-down    Stop production environment (RUNTIME=compose|systemd)"
	@echo "  make prod-build   Build production containers"
	@echo "  make prod-rebuild Rebuild with --no-cache"
	@echo ""
	@echo "Common:"
	@echo "  make logs         View container logs"
	@echo "  make ps           List running containers"
	@echo "  make shell        Access web container shell"
	@echo "  make db-shell     Access database container shell"
	@echo "  make restart      Restart containers"
	@echo "  make clean        Remove containers, volumes, and images"
	@echo "  make status       Show container status"
	@echo ""
	@echo "Configuration:"
	@echo "  COMPOSE=$(COMPOSE)"
	@echo "  RUNTIME=$(RUNTIME) (compose or systemd)"

# Prepare compose file with data volume mounts uncommented if needed
prepare-compose:
	@cp $(DEV_COMPOSE) $(DEV_COMPOSE_TMP)
	@cp $(PROD_COMPOSE) $(PROD_COMPOSE_TMP)
	@if [ -n "$(CONTENT_DIR)" ]; then \
		echo "CONTENT_DIR is set, enabling content volume mount..."; \
		sed -i 's/# - $${CONTENT_DIR}:\/var\/www\/html\/symbiota\/content$${SELINUX_FLAG}/- $${CONTENT_DIR}:\/var\/www\/html\/symbiota\/content$${SELINUX_FLAG}/' $(DEV_COMPOSE_TMP); \
		sed -i 's/# - $${CONTENT_DIR}:\/var\/www\/html\/symbiota\/content$${SELINUX_FLAG}/- $${CONTENT_DIR}:\/var\/www\/html\/symbiota\/content$${SELINUX_FLAG}/' $(PROD_COMPOSE_TMP); \
	fi
	@if [ -n "$(LOGS_DIR)" ]; then \
		echo "LOGS_DIR is set, enabling logs volume mount..."; \
		sed -i 's/# - $${LOGS_DIR}:\/var\/www\/html\/symbiota\/content\/logs$${SELINUX_FLAG}/- $${LOGS_DIR}:\/var\/www\/html\/symbiota\/content\/logs$${SELINUX_FLAG}/' $(DEV_COMPOSE_TMP); \
		sed -i 's/# - $${LOGS_DIR}:\/var\/www\/html\/symbiota\/content\/logs$${SELINUX_FLAG}/- $${LOGS_DIR}:\/var\/www\/html\/symbiota\/content\/logs$${SELINUX_FLAG}/' $(PROD_COMPOSE_TMP); \
	fi

clean-tmp:
	@rm -f $(DEV_COMPOSE_TMP) $(PROD_COMPOSE_TMP)

# Development targets
dev-up: prepare-compose
	@echo "Starting development environment..."
	@if [ ! -f .env ]; then \
		echo "No .env file found. Copying .env.example..."; \
		cp .env.example .env; \
		echo "Please edit .env with your settings"; \
		exit 1; \
	fi
	$(COMPOSE) -f $(DEV_COMPOSE_TMP) up -d
	@$(MAKE) clean-tmp
	@echo "Development environment running"
	@echo "   Web: http://localhost:$${HTTP_PORT:-8080}"
	@echo "   DB:  localhost:$${MYSQL_PORT:-33060}"

dev-down:
	$(COMPOSE) -f $(DEV_COMPOSE) down
	@$(MAKE) clean-tmp

dev-build: prepare-compose
	$(COMPOSE) -f $(DEV_COMPOSE_TMP) build
	@$(MAKE) clean-tmp

dev-rebuild: prepare-compose
	$(COMPOSE) -f $(DEV_COMPOSE_TMP) build --no-cache
	@$(MAKE) clean-tmp

# Production targets
prod-up: prepare-compose
	@echo "Starting production environment..."
	@if [ ! -f .env ]; then \
		echo "No .env file found. Create one first!"; \
		exit 1; \
	fi
	@if [ "$(RUNTIME)" = "systemd" ]; then \
		echo "Using systemd runtime..."; \
		sudo systemctl start symbiota; \
	else \
		$(COMPOSE) -f $(PROD_COMPOSE_TMP) up -d; \
		echo "Production environment running"; \
	fi
	@$(MAKE) clean-tmp

prod-down:
	@if [ "$(RUNTIME)" = "systemd" ]; then \
		echo "Using systemd runtime..."; \
		sudo systemctl stop symbiota; \
	else \
		$(COMPOSE) -f $(PROD_COMPOSE) down; \
	fi
	@$(MAKE) clean-tmp

prod-build: prepare-compose
	@if [ -n "$(CONFIG_DIR)" ] && [ -d "$(CONFIG_DIR)" ]; then \
		echo "Building with config from: $(CONFIG_DIR)"; \
		$(COMPOSE) -f $(PROD_COMPOSE_TMP) build --build-arg CONFIG_DIR=$(CONFIG_DIR); \
	else \
		echo "Building without baked-in config"; \
		$(COMPOSE) -f $(PROD_COMPOSE_TMP) build; \
	fi
	@$(MAKE) clean-tmp

prod-rebuild: prepare-compose
	@if [ -n "$(CONFIG_DIR)" ] && [ -d "$(CONFIG_DIR)" ]; then \
		echo "Rebuilding with config from: $(CONFIG_DIR)"; \
		$(COMPOSE) -f $(PROD_COMPOSE_TMP) build --no-cache --build-arg CONFIG_DIR=$(CONFIG_DIR); \
	else \
		echo "Rebuilding without baked-in config"; \
		$(COMPOSE) -f $(PROD_COMPOSE_TMP) build --no-cache; \
	fi
	@$(MAKE) clean-tmp

# Systemd targets
systemd-install:
	@echo "Installing systemd service..."
	@if [ ! -f systemd/symbiota.service ]; then \
		echo "Error: systemd/symbiota.service not found"; \
		exit 1; \
	fi
	sudo cp systemd/symbiota.service /etc/systemd/system/
	sudo systemctl daemon-reload
	@echo "Systemd service installed. Enable with: sudo systemctl enable symbiota"

systemd-enable:
	sudo systemctl enable symbiota

systemd-disable:
	sudo systemctl disable symbiota

systemd-status:
	sudo systemctl status symbiota

systemd-logs:
	sudo journalctl -u symbiota -f

# Common targets (uses DEV_COMPOSE by default, override with COMPOSE_FILE=)
COMPOSE_FILE ?= $(DEV_COMPOSE)

down:
	$(COMPOSE) -f $(COMPOSE_FILE) down
	@$(MAKE) clean-tmp

restart: down
	@sleep 2
	$(MAKE) dev-up COMPOSE_FILE=$(COMPOSE_FILE)

logs:
	@if [ "$(RUNTIME)" = "systemd" ]; then \
		$(MAKE) systemd-logs; \
	else \
		$(COMPOSE) -f $(COMPOSE_FILE) logs -f; \
	fi

ps:
	$(COMPOSE) -f $(COMPOSE_FILE) ps

shell:
	@echo "Accessing web container shell..."
	$(COMPOSE) -f $(COMPOSE_FILE) exec symbiota bash

db-shell:
	@echo "Accessing database container shell..."
	$(COMPOSE) -f $(COMPOSE_FILE) exec symbiota-db mysql -u root -p$${MYSQL_ROOT_PASSWORD:-password} $${MYSQL_DATABASE:-symbiota}

build: prepare-compose
	$(COMPOSE) -f $(COMPOSE_FILE) build
	@$(MAKE) clean-tmp

clean:
	@echo "This will remove all containers, volumes, and images!"
	@read -p "Are you sure? [y/N] " -n 1 -r; \
	echo; \
	if [[ $$REPLY =~ ^[Yy]$$ ]]; then \
		$(COMPOSE) -f $(DEV_COMPOSE) down -v --rmi all; \
		$(COMPOSE) -f $(PROD_COMPOSE) down -v --rmi all; \
		rm -f $(DEV_COMPOSE_TMP) $(PROD_COMPOSE_TMP); \
		echo "Cleanup complete"; \
	fi

status:
	@echo "Container Status:"
	@if [ "$(RUNTIME)" = "systemd" ]; then \
		$(MAKE) systemd-status; \
	else \
		$(COMPOSE) -f $(COMPOSE_FILE) ps; \
		echo ""; \
		echo "Network Status:"; \
		docker network ls | grep symbiota || echo "No symbiota network found"; \
	fi
