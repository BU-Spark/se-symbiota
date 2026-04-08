#!/bin/bash

# Target Container Name
CONTAINER="symbiota-web-dev"

# Path to php.ini inside the container
PHP_INI="/etc/php/8.1/apache2/php.ini"

echo "Updating PHP configuration in $CONTAINER..."

# 1. Update upload_max_filesize to 2G
docker exec -u root $CONTAINER sed -i "s/^upload_max_filesize = .*/upload_max_filesize = 2G/" "$PHP_INI"

# 2. Update post_max_size to 2G
docker exec -u root $CONTAINER sed -i "s/^post_max_size = .*/post_max_size = 2G/" "$PHP_INI"

# 3. Update memory_limit to 1024M
docker exec -u root $CONTAINER sed -i "s/^memory_limit = .*/memory_limit = 1024M/" "$PHP_INI"

# 4. Restart the Apache service inside the container
docker exec -u root $CONTAINER service apache2 restart

echo "Configuration complete. Container $CONTAINER has been updated and restarted."