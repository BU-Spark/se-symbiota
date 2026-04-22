# Symbiota MySQL Database Container
#
# MySQL 8.0+ is required for dumps/schema that use utf8mb3 and other 8.0 features.
# (MySQL 5.7 cannot restore mysqldump files produced by MySQL 8.)
# Multi-architecture support (AMD64 + ARM64)

FROM mysql:8.0.42

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
HEALTHCHECK --interval=30s --timeout=3s --start-period=20s --retries=5 \
    CMD mysqladmin ping -h localhost -u root -p$$MYSQL_ROOT_PASSWORD || exit 1

# Default command (runs mysqld)
CMD ["mysqld"]
