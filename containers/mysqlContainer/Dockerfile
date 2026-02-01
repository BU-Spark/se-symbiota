# Symbiota MySQL Database Container
#
# Uses MySQL 5.7 for compatibility with Symbiota requirements
# Multi-architecture support (AMD64 + ARM64)

FROM mysql:5.7.44

# Build arguments for multi-arch support
ARG TARGETPLATFORM
ARG BUILDPLATFORM

# Environment variables will be provided by docker-compose
# from .env file - these are just placeholders
ENV MYSQL_ROOT_PASSWORD=changeme
ENV MYSQL_DATABASE=symbiota
ENV MYSQL_USER=changeme
ENV MYSQL_PASSWORD=changeme

# Expose MySQL port
EXPOSE 3306

# Optional: Copy custom MySQL configuration
# COPY my.cnf /etc/mysql/conf.d/custom.cnf

# Optional: Copy initialization scripts
# Files in /docker-entrypoint-initdb.d/ run on first start
# COPY init-schema.sql /docker-entrypoint-initdb.d/

# Health check
HEALTHCHECK --interval=30s --timeout=3s --start-period=10s --retries=3 \
    CMD mysqladmin ping -h localhost -u root -p${MYSQL_ROOT_PASSWORD} || exit 1

# Default command (runs mysqld)
CMD ["mysqld"]
