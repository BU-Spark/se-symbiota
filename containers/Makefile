# Default to podman-compose unless overridden
COMPOSE ?= podman-compose
NETWORK = symbiota-network

# Compose file location
COMPOSE_FILE = docker-compose.yaml

.PHONY: all up down restart logs ps exec network status

all: up

# Create network if needed, then start containers
up: network
	$(COMPOSE) -f $(COMPOSE_FILE) up -d

down:
	$(COMPOSE) -f $(COMPOSE_FILE) down

restart:
	$(MAKE) down
	$(MAKE) up

logs:
	$(COMPOSE) -f $(COMPOSE_FILE) logs -f

ps:
	$(COMPOSE) -f $(COMPOSE_FILE) ps

exec:
	@echo "Use: make exec SERVICE=container-name"
	podman exec -it $(SERVICE) bash

status:
	podman network inspect $(NETWORK) >/dev/null && echo "✅ Network exists: $(NETWORK)" || echo "❌ Network missing"

# Create network if it doesn't already exist
network:
	@if ! podman network exists $(NETWORK); then \
		echo "🔧 Creating network: $(NETWORK)"; \
		podman network create $(NETWORK); \
	else \
		echo "✅ Network already exists: $(NETWORK)"; \
	fi
