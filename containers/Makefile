# Default to podman-compose unless overridden
COMPOSE ?= docker-compose
NETWORK = symbiota-network

# Compose file location
COMPOSE_FILE = docker-compose.yaml

.PHONY: all up down restart logs ps exec network status

all: up

# Create network if needed, then start containers
up: network
	$(COMPOSE) -f $(COMPOSE_FILE) up -d --build

down:
	$(COMPOSE) -f $(COMPOSE_FILE) down

restart:
	$(MAKE) down
	$(MAKE) up

logs:
	$(COMPOSE) -f $(COMPOSE_FILE) logs -f

ps:
	$(COMPOSE) -f $(COMPOSE_FILE) ps


status:
	docker network inspect $(NETWORK) >/dev/null && echo "✅ Network exists: $(NETWORK)" || echo "❌ Network missing"


# Create network if it doesn't already exist
network:
	@if ! docker network inspect $(NETWORK) >/dev/null 2>&1; then \
		echo "🔧 Creating network: $(NETWORK)"; \
		docker network create $(NETWORK); \
	else \
		echo "✅ Network already exists: $(NETWORK)"; \
	fi
